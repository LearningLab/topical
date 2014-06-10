<?php
/***
 * Plugin Name: Topical WordPress
 * Description: Extensible topic pages for WordPress
 * Version: 0.0.1
 * Author: Chris Amico
 * Author URI: http://glasseyemedia.org
 * License: GPLv2
***/

/*
    Copyright 2014 Glass Eye Media LLC. 

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

require_once(__DIR__ . '/topics.php');

class Topical {

	function __construct() {
        // create a post type
        add_action('init', array(&$this, 'create_post_type'), 10);

        // setup posts2posts connections, now that we have topic posts
        add_action('p2p_init', array(&$this, 'create_connections'), 10);

        // topic titles for editing
        add_filter('p2p_candidate_title', array(&$this, 'topic_short_title'), 10, 3);
        add_filter('p2p_connected_title', array(&$this, 'topic_short_title'), 10, 3);

        // order topics by name in the admin
        add_filter( 'p2p_connectable_args', array(&$this, 'topic_ordering'), 10, 3 );

        // add a metabox to topic (post) admin to edit the short title (Common Core, STEM, etc)
        add_action('add_meta_boxes_topic', array(&$this, 'add_metaboxes'), 10);

        // handle saving topics, to store metadata and such
        add_action('save_post_topic', array(&$this, 'save_post_topic'), 10, 3);

        // path to the root of this plugin, because it's useful
        $this->dir = plugin_dir_path(__FILE__);

        // add view locations
        $this->configure_views();

        // add routes, right away!
        $this->setup_routes();

	}

    function configure_views() {
        if (is_array(Timber::$dirname)) {
            $views = Timber::$dirname;
        } else {
            $views = array(Timber::$dirname);
        }

        $views[] = $this->dir . "views";
        Timber::$dirname = $views;
    }

    function create_post_type() {
        $labels = array(
            'name'                => _x('Topics', 'Post Type General Name', 'topical'),
            'singular_name'       => _x('Topic', 'Post Type Singular Name', 'topical'),
            'menu_name'           => __('Topics', 'topical'),
            'parent_item_colon'   => __('Parent Topic:', 'topical'),
            'all_items'           => __('All Topics', 'topical'),
            'view_item'           => __('View Topic', 'topical'),
            'add_new_item'        => __('Add New Topic', 'topical'),
            'add_new'             => __('Add New', 'topical'),
            'edit_item'           => __('Edit Topic', 'topical'),
            'update_item'         => __('Update Topic', 'topical'),
            'search_items'        => __('Search Topics', 'topical'),
            'not_found'           => __('Not found', 'topical'),
            'not_found_in_trash'  => __('Not found in Trash', 'topical'),
        );

        $rewrite = array(
            'slug'                => 'topics',
            'with_front'          => true,
            'pages'               => true,
            'feeds'               => true,
        );

        $supports = array( 'title', 'editor', 'excerpt', 
            'author', 'thumbnail', 'revisions');

        $args = array(
            'label'               => __('topic', 'topical'),
            'description'         => __('A topic page', 'topical'),
            'labels'              => $labels,
            'supports'            => $supports,
            //'taxonomies'          => array( 'category', 'post_tag' ),
            'hierarchical'        => false,
            'public'              => true,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'show_in_nav_menus'   => true,
            'show_in_admin_bar'   => true,
            'menu_position'       => 5,
            'menu_icon'           => null,
            'can_export'          => true,
            'has_archive'         => true,
            'exclude_from_search' => false,
            'publicly_queryable'  => true,
            'rewrite'             => $rewrite,
            'capability_type'     => 'page',
        );

        $this->post_type = register_post_type('topic', $args);

    }

    function create_connections() {
        $args = array(
            'name' => 'posts_to_topics',
            'from' => 'post',
            'to'   => 'topic',
            
            'admin_box' => array(
                'show'    => 'any',
                'context' => 'side'),

            'admin_column' => 'from',

            'from_labels' => array(
                'column_title' => __('Topics'),
                'create' => __('Add posts', 'topical')),

            'to_labels' => array(
                'create' => __('Add to topics'))

        );

        p2p_register_connection_type($args);
    }

    function add_metaboxes($post) {
        /***
        add_meta_box( $id, $title, $callback, $post_type, $context,
                 $priority, $callback_args );
        ***/
        add_meta_box('short_title', 'Short Title', array(&$this, 'render_metabox'),
            'topic', 'side', 'high', array());
    }

    function render_metabox($post, $metabox) {
        // stash post in the metabox array, and use it as context
        $metabox['post'] = $post;

        Timber::render('topical/admin/metabox_short_title.twig', $metabox);

    }

    function setup_routes() {}

    /*
    Handle saving a topic post
    
    @param int     $post_ID Post ID.
    @param WP_Post $post    Post object.
    @param bool    $update  Whether this is an existing post being updated or not.
    */
    function save_post_topic($post_id, $post, $update) {

        if (isset($_POST['short_title']) && trim($_POST['short_title'])) {
            update_post_meta($post_id, 'short_title', trim($_POST['short_title']));
        }
    }

    /*
    Show the short title when editing a post

    @param str $title This topic's title
    @param object $post The entire post object
    @param object $ctype The connection type from P2P
    */
    function topic_short_title($title, $post, $ctype) {
        // get a short title, which may be nothing
        $short_title = get_post_meta($post->ID, 'short_title', true);
        
        // return a short title if we have one
        return $short_title ? $short_title : $title;
    }

    /*
    Called before querying for posts that the user can connect to the current post.
    
    @param array $args Array of current query args
    @param object $ctype Connection type
    @param int $post_id ID of the post we're dealing with
    */
    function topic_ordering($args, $ctype, $post_id) {

        // check that we're dealing with posts_to_topics and that we're querying topics
        if ($ctype->name == 'posts_to_topics' && $ctype->get_direction() == 'to') {
            $args['orderby'] = 'title';
            $args['order'] = 'asc';
        }

        // return $args always, because this is a filter
        return $args;
    }

    /***
    @param array $slugs
    @return array|bool|null

    Given an array of slugs, return a list of Topic objects.
    This is a slightly optimized alternative to repeatedly calling
    Topical::get_topic()
    ***/
    public static function get_topics($slugs) {

    }
}

/*
Utility function that return the first thing in an array that evaluates to true
Returns null if none pass.

@param array $things Array of objects to test
@return mixed First thing that passes simple truthiness
*/
function firstof($things) {
    foreach ($things as $obj) {
        if ($obj) {
            return $obj;
        }
    }

    return null;
}

$topical = new Topical();