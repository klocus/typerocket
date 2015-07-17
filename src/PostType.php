<?php
namespace TypeRocket;

class PostType extends Registrable
{

    private $title = null;
    private $form = null;
    private $taxonomies = array();
    private $icon = null;

    function setIcon( $name )
    {
        $name       = strtolower( $name );
        $icons      = new Icons();
        $this->icon = $icons[$name];
        add_action( 'admin_head', array( $this, 'style' ) );

        return $this;
    }

    public function getTitlePlaceholder()
    {
        return $this->title;
    }

    public function setTitlePlaceholder( $text )
    {
        $this->title = (string) $text;

        return $this;
    }

    /**
     * @param $key
     *
     * @return mixed
     */
    public function getForm( $key )
    {
        return $this->form[$key];
    }

    /**
     * @param bool|true|callable $value
     *
     * @return $this
     */
    public function setTitleFrom( $value = true )
    {

        if (is_callable( $value )) {
            $this->form['title'] = $value;
        } else {
            $this->form['title'] = true;
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function removeTitleFrom()
    {
        $this->form['title'] = null;

        return $this;
    }

    /**
     * @param bool|true|callable $value
     *
     * @return $this
     */
    public function setTopFrom( $value = true )
    {
        if (is_callable( $value )) {
            $this->form['top'] = $value;
        } else {
            $this->form['top'] = true;
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function removeTopFrom()
    {
        $this->form['top'] = null;

        return $this;
    }

    /**
     * @param bool|true|callable $value
     *
     * @return $this
     */
    public function setBottomFrom( $value = true )
    {
        if (is_callable( $value )) {
            $this->form['bottom'] = $value;
        } else {
            $this->form['bottom'] = true;
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function removeBottomFrom()
    {
        $this->form['bottom'] = null;

        return $this;
    }

    /**
     * @param bool|true|callable $value
     *
     * @return $this
     */
    public function setEditorFrom( $value = true )
    {
        if (is_callable( $value )) {
            $this->form['editor'] = $value;
        } else {
            $this->form['editor'] = true;
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function removeEditorFrom()
    {
        $this->form['editor'] = null;

        return $this;
    }

    public function setSlug( $slug )
    {
        $this->args['rewrite'] = array( 'slug' => Sanitize::dash( $slug ) );

        return $this;
    }

    public function getSlug()
    {
        return $this->args['rewrite']['slug'];
    }

    public function style()
    { ?>

        <style type="text/css">
            #adminmenu #menu-posts-<?php echo $this->id; ?> .wp-menu-image:before {
                font: 400 15px/1 'typerocket-icons' !important;
                content: '<?php echo $this->icon; ?>';
                speak: none;
                -webkit-font-smoothing: antialiased;
            }
        </style>

    <?php }

    /**
     * Make Post Type. Do not use before init.
     *
     * @param string $singular singular name is required
     * @param string $plural plural name
     * @param array $settings args override and extend
     *
     * @return $this
     */
    function setup( $singular, $plural = null, $settings = array() )
    {

        $this->form = array(
            array(
                'top'    => null,
                'title'  => null,
                'editor' => null,
                'bottom' => null
            )
        );

        if(is_null($plural)) {
            $plural = Inflect::pluralize($singular);
        }

        // make lowercase
        $singular      = strtolower( $singular );
        $plural        = strtolower( $plural );
        $upperSingular = ucwords( $singular );
        $upperPlural   = ucwords( $plural );

        $labels = array(
            'add_new'            => 'Add New',
            'add_new_item'       => 'Add New ' . $upperSingular,
            'edit_item'          => 'Edit ' . $upperSingular,
            'menu_name'          => $upperPlural,
            'name'               => $upperPlural,
            'new_item'           => 'New ' . $upperSingular,
            'not_found'          => 'No ' . $plural . ' found',
            'not_found_in_trash' => 'No ' . $plural . ' found in Trash',
            'parent_item_colon'  => '',
            'search_items'       => 'Search ' . $upperPlural,
            'singular_name'      => $upperSingular,
            'view_item'          => 'View ' . $upperSingular,
        );

        // setup object for later use
        $plural   = Sanitize::underscore( $plural );
        $singular = Sanitize::underscore( $singular );
        $this->id       = ! $this->id ? $singular : $this->id;

        if (array_key_exists( 'capabilities', $settings ) && $settings['capabilities'] === true) :
            $settings['capabilities'] = array(
                'publish_posts'       => 'publish_' . $plural,
                'edit_post'           => 'edit_' . $singular,
                'edit_posts'          => 'edit_' . $plural,
                'edit_others_posts'   => 'edit_others_' . $plural,
                'delete_post'         => 'delete_' . $singular,
                'delete_posts'        => 'delete_' . $plural,
                'delete_others_posts' => 'delete_others_' . $plural,
                'read_post'           => 'read_' . $singular,
                'read_private_posts'  => 'read_private_' . $plural,
            );
        endif;

        $defaults = array(
            'labels'      => $labels,
            'description' => $plural,
            'rewrite'     => array( 'slug' => Sanitize::dash( $this->id ) ),
            'public'      => true,
            'supports'    => array( 'title', 'editor' ),
            'has_archive' => true,
            'taxonomies'  => array()
        );

        if (array_key_exists( 'admin_only', $settings ) && $settings['admin_only'] == true) {
            $admin_only = array(
                'public'      => false,
                'has_archive' => false,
                'show_ui'     => true
            );
            unset( $settings['admin_only'] );
            $defaults = array_merge( $defaults, $admin_only );
        }


        if (array_key_exists( 'taxonomies', $settings )) {
            $this->taxonomies       = array_merge( $this->taxonomies, $settings['taxonomies'] );
            $settings['taxonomies'] = $this->taxonomies;
        }

        $this->args = array_merge( $defaults, $settings );

        return $this;
    }

    function register()
    {
        $this->dieIfReserved();

        do_action( 'tr_register_post_type_' . $this->id, $this );
        register_post_type( $this->id, $this->args );

        return $this;
    }

    /**
     * @param string|Metabox $s
     */
    function metaboxRegistrationById( $s )
    {
        if ( ! is_string( $s )) {
            $s = (string) $s->getId();
        }

        if ( ! in_array( $s, $this->args['supports'] )) {
            $this->args['supports'][] = $s;
        }
    }

    /**
     * @param string|Taxonomy $s
     */
    function taxonomyRegistrationById( $s )
    {

        if ( ! is_string( $s )) {
            $s = (string) $s->getId();
        }

        if ( ! in_array( $s, $this->taxonomies )) {
            $this->taxonomies[]       = $s;
            $this->args['taxonomies'] = $this->taxonomies;
        }

    }

    function addFormContent( $post, $type )
    {
        if ($post->post_type == $this->id) :

            $func = 'add_form_content_' . $this->id . '_' . $type;

            echo '<div class="typerocket-container">';

            $form = $this->getForm( $type );
            if (is_callable( $form )) {
                call_user_func( $form );
            } elseif (function_exists( $func )) {
                call_user_func( $func, $post );
            } elseif (TR_DEBUG == true) {
                echo "<div class=\"tr-dev-alert-helper\"><i class=\"icon tr-icon-bug\"></i> Add content here by defining: <code>function {$func}() {}</code></div>";
            }

            echo '</div>';


        endif;
    }

    function editFormTop( $post )
    {
        $this->addFormContent( $post, 'top' );
    }

    function editFormAfterTitle( $post )
    {
        $this->addFormContent( $post, 'title' );
    }

    function editFormAfterEditor( $post )
    {
        $this->addFormContent( $post, 'editor' );
    }

    function dbxPostSidebar( $post )
    {
        $this->addFormContent( $post, 'bottom' );
    }

    function enterTitleHere( $s )
    {
        global $post;

        if ($post->post_type == $this->id) :
            return $this->title;
        else :
            return $s;
        endif;
    }

    function stringRegistration( $v )
    {
        $this->taxonomyRegistrationById( $v );
    }

}
