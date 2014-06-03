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

        // create a taxonomy
        add_action('init', array(&$this, 'create_taxonomy'), 10);

        // create a topic post when a topic term is created
        add_action('created_topic', array(&$this, 'created_topic', 10, 2));

        // ensure a topic post type exists when a taxonomy is updated
        add_action('edited_terms', array(&$this, 'edited_terms'), 10, 2);

        // handle deleting a term
        add_action('delete_term', array(&$this, 'delete_term'), 10, 4);

        // create a topic taxonomy when a topic post is created or updated
        add_action('save_post_topic', array(&$this, 'save_post_topic'), 10, 3);

        // add a metabox to topic (post) admin to edit the short title, which will
        // match the connected taxonomy (Common Core, STEM, etc)
        add_action('add_meta_boxes_topic', array(&$this, 'add_metaboxes'), 10);

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
            'name'                => _x( 'Topics', 'Post Type General Name', 'topical' ),
            'singular_name'       => _x( 'Topic', 'Post Type Singular Name', 'topical' ),
            'menu_name'           => __( 'Topics', 'topical' ),
            'parent_item_colon'   => __( 'Parent Topic:', 'topical' ),
            'all_items'           => __( 'All Topics', 'topical' ),
            'view_item'           => __( 'View Topic', 'topical' ),
            'add_new_item'        => __( 'Add New Topic', 'topical' ),
            'add_new'             => __( 'Add New', 'topical' ),
            'edit_item'           => __( 'Edit Topic', 'topical' ),
            'update_item'         => __( 'Update Topic', 'topical' ),
            'search_items'        => __( 'Search Topics', 'topical' ),
            'not_found'           => __( 'Not found', 'topical' ),
            'not_found_in_trash'  => __( 'Not found in Trash', 'topical' ),
        );

        $rewrite = array(
            'slug'                => 'topics',
            'with_front'          => true,
            'pages'               => true,
            'feeds'               => true,
        );

        $supports = array( 'title', 'editor', 'excerpt', 
            'author', 'thumbnail', 'revisions' );

        $args = array(
            'label'               => __( 'topic', 'topical' ),
            'description'         => __( 'A topic page', 'topical' ),
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
            'menu_icon'           => '',
            'can_export'          => true,
            'has_archive'         => true,
            'exclude_from_search' => false,
            'publicly_queryable'  => true,
            'rewrite'             => $rewrite,
            'capability_type'     => 'page',
        );

        $this->post_type = register_post_type( 'topic', $args );

    }

    function create_taxonomy() {

        $labels = array(
            'name'                       => _x( 'Topics', 'Taxonomy General Name', 'topical' ),
            'singular_name'              => _x( 'Topic', 'Taxonomy Singular Name', 'topical' ),
            'menu_name'                  => __( 'Topics', 'topical' ),
            'all_items'                  => __( 'All Topics', 'topical' ),
            'parent_item'                => __( 'Parent Topic', 'topical' ),
            'parent_item_colon'          => __( 'Parent Topic:', 'topical' ),
            'new_item_name'              => __( 'New Topic Name', 'topical' ),
            'add_new_item'               => __( 'Add New Topic', 'topical' ),
            'edit_item'                  => __( 'Edit Topic', 'topical' ),
            'update_item'                => __( 'Update Topic', 'topical' ),
            'separate_items_with_commas' => __( 'Separate topics with commas', 'topical' ),
            'search_items'               => __( 'Search Topic', 'topical' ),
            'add_or_remove_items'        => __( 'Add or remove topics', 'topical' ),
            'choose_from_most_used'      => __( 'Choose from the most used topics', 'topical' ),
            'not_found'                  => __( 'Not Found', 'topical' ),
        );

        $rewrite = array(
            'slug'                       => 'topics',
            'with_front'                 => true,
            'hierarchical'               => false,
        );

        $args = array(
            'labels'                     => $labels,
            'hierarchical'               => true,
            'public'                     => true,
            'show_ui'                    => true,
            'show_admin_column'          => true,
            'show_in_nav_menus'          => true,
            'show_tagcloud'              => false, // never ever
            'rewrite'                    => $rewrite,

        );

        register_taxonomy( 'topic', array( 'post' ), $args );

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

    function save_post_topic($post_id, $post, $update) {}

    /***
    Runs when a term (topic) is created, ensuring there is a corresponding topic post.

    The post and term should have the same slug. 
    For backup, save the term name, slug and id to post_meta.

    This only runs when a topic taxonomy is created, so we can assume
    a taxonomy slug of 'topic'.
    
    @param int $term_id Term ID.
    @param int $tt_id   Term taxonomy ID.
    ***/
    function created_topic($term_id, $tt_id) {

    }

    /***
    Run when a term is updated, ensuring there is a corresponding topic post

    @param int    $term_id  Term ID
    @param string $taxonomy Taxonomy slug
    ***/
    function edited_terms($term_id, $taxonomy) {
        if ($taxonomy == 'topic') {
            $term = get_term($term_id, $taxonomy);            
        }

        error_log('edited_terms');
        error_log($term_id, $taxonomy);
    }

    /***
    @param int     $term         Term ID.
    @param int     $tt_id        Term taxonomy ID.
    @param string  $taxonomy     Taxonomy slug.
    @param mixed   $deleted_term Copy of the already-deleted term
    ***/
    function delete_term($term, $tt_id, $taxonomy, $deleted_term) {

    }

    /***
    @param string $slug
    @return Topic

    Given a slug, return a Topic object that links both a post type and taxonomy
    ***/
    public static function get_topic($slug) {
        return new Topic($slug);
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


$topical = new Topical();