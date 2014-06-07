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
        add_action('created_topic', array(&$this, 'created_term'), 10, 2);

        // ensure a topic post type exists when a taxonomy is updated
        add_action('edited_terms', array(&$this, 'edited_terms'), 10, 2);

        // handle deleting a term
        add_action('delete_topic', array(&$this, 'delete_term'), 10, 3);

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

        // hide the existing post slug options, because the term slug is what counts
        remove_meta_box('slugdiv', 'topic', 'normal'); 
    }

    function render_metabox($post, $metabox) {
        // stash post in the metabox array, and use it as context
        $metabox['post'] = $post;
        $metabox['term'] = $this->get_term($post);

        Timber::render('topical/admin/metabox_short_title.twig', $metabox);

    }

    function setup_routes() {}

    /*
    Handle saving a topic post, ensuring a term is linked
    
    @param int     $post_ID Post ID.
    @param WP_Post $post    Post object.
    @param bool    $update  Whether this is an existing post being updated or not.
    */
    function save_post_topic($post_id, $post, $update) {
        // try to get a term right away
        $term = $this->get_term($post);

        // check that a short_title is set
        if (isset($_POST['short_title'])) {
            $short_title = trim($_POST['short_title']);

            // let's say this is a new post, with no term attached
            // so create a term from the $short_title
            // we also need to set the post's slug so they match
            if ($short_title && !$term) {
                // just to make sure there isn't a term out there already
                $slug = sanitize_title($short_title);
                $term = get_term_by('slug', $slug, 'topic');

                // set the post slug first, so everything matches
                // unhook actions, too
                remove_action('save_post_topic', array(&$this, 'save_post_topic'), 10, 3);
                
                wp_update_post(array(
                    'ID' => $post_id,
                    'post_name' => $slug
                ));
                
                add_action('save_post_topic', array(&$this, 'save_post_topic'), 10, 3);


                if ($term) {
                    // ok, if we have a term now, connect it to the post by matching slugs


                } else {
                    // still no term, good, let's make one
                    // remembering of course to unhook actions first
                    remove_action('created_topic', array(&$this, 'created_term'), 10, 2);

                    wp_insert_term($short_title, 'topic', 
                        array('slug'=>$slug));
                    
                    add_action('created_topic', array(&$this, 'created_term'), 10, 2);

                }

            } elseif ($short_title && $term && $term->name !== $short_title) {
                // we have an existing term, and we have a short_title, and they don't match
                // so update the term, leave the slugs matching
                remove_action('edited_terms', array(&$this, 'edited_terms'), 10, 2);

                wp_update_term($term->term_id, 'topic', array(
                    'name' => $short_title,
                    'slug' => $slug
                ));
                
                add_action('edited_terms', array(&$this, 'edited_terms'), 10, 2);

            }
            
        }

        error_log('Saved topic.');
        error_log(print_r($term, true));
    }

    /***
    Runs when a term (topic) is created, ensuring there is a corresponding topic post.

    The post and term should have the same slug. 
    For backup, save the term name, slug and id to post_meta.

    This only runs when a topic taxonomy is created, so we can assume
    a taxonomy slug of 'topic'.
    
    @param int $term_id Term ID.
    @param int $tt_id   Term taxonomy ID.
    ***/
    function created_term($term_id, $tt_id) {
        $term = get_term($term_id, 'topic');
        $topic = $this->get_or_create_topic($term);
    }

    /***
    Run when a term is updated, ensuring there is a corresponding topic post

    @param int    $term_id  Term ID
    @param string $taxonomy Taxonomy slug
    ***/
    function edited_terms($term_id, $taxonomy) {
        if ($taxonomy == 'topic') {
            $term = get_term($term_id, $taxonomy);
            $topic = $this->get_or_create_topic($term);
        }
    }

    /***
    Hide a topic post if its corresponding term is deleted.
    This is mostly for safety.

    It only runs when a topic taxonomy is deleted, so we can
    assume a taxonomy slug of "topic"

    @param int     $term_id         Term ID.
    @param int     $tt_id        Term taxonomy ID.
    @param mixed   $deleted_term Copy of the already-deleted term
    ***/
    function delete_term($term_id, $tt_id, $deleted_term) {

        $topic = $this->get_topic($deleted_term);

        // if the topic is gone, we can just skip past all this
        if ($topic) {
            $args = array(
                'ID' => $topic->ID,
                'post_status' => 'draft'
            );

            wp_update_post($args);
        }
    }

    /***
    Given a term, get or create a new Topic post.
    This will also publish a topic, if it exists.

    @param object $term
    @return object Topic 
    ***/
    function get_or_create_topic($term) {
        $term = $this->normalize_term($term);
        if (!$term) { return null; }

        // check for a post with this term's slug
        $topic = $this->get_topic($term);
        if ($topic) {
            // update post meta to store stuff
            $args = array(
                'ID' => $topic->ID,
                'post_status' => 'publish'
            );
            wp_update_post($args);

        } else {
            // create topic
            $this->create_topic($term);
        }

    }

    /*
    Get a term from a post or slug
    @param string|object $post The topic post
    @return Term a term object
    */
    function get_term($post) {
        if (is_object($post)) {
            // this will work even if it's a TimberPost
            $slug = $post->post_name;
        } elseif (is_string($post)) {
            $slug = $post;
        }

        $term = get_term_by('slug', $slug, 'topic');
        return $term;
    }

    /*
    Get a topic from a term or slug

    @param mixed $term Get a Topic post matching this term
    @return object Topic post
    */
    function get_topic($term) {
        
        // normalize term and slug
        $term = $this->normalize_term($term);
        if (!$term) {
            error_log('No term. Aborting.');
            return;
        }

        // use WP_Query 
        $args = array(
            'name' => $term->slug,
            'post_type' => 'topic',
            'posts_per_page' => 1
        );

        // run the query
        $topics = new WP_Query($args);

        // if no posts found, return null
        if ($topics->found_posts == 0) {
            return null;
        }

        // if multiple posts found, return the first and log an error, for now
        if ($topics->found_posts > 1) {
            error_log("Multiple Topics: Found {$topics->found_posts} posts with slug '{$term->slug}'");
        }

        // return a post
        return $topics->next_post();
    }

    /*
    Create a topic from a term or slug

    @param mixed $term Term object or slug
    @return object WP_Post A topic post type
    */
    function create_topic($term) {
        $term = $this->normalize_term($term);

        // create the topic
        $args = array(
            'post_name' => $term->slug,
            'post_title' => $term->name,
            'post_excerpt' => $term->description,
            'post_type' => 'topic',
            'post_status' => 'publish'
        );

        // create the post, or return an error
        $created = wp_insert_post($args, true);

        if (is_wp_error($created)) {
            // returning errors seems like a terrible convention
            return $created;
        } else {
            // return the full post
            return get_post($created);
        }
    }

    /*
    Make sure we have a proper term object

    @param mixed $term A slug or Term object to normalize
    @return object|null Term object or null
    */
    function normalize_term($term) {
        if (is_object($term)) {
            $slug = $term->slug;
        } else {
            $slug = $term;
            $term = get_term_by('slug', $slug, 'topic');
        }

        // bail out if we don't have a term at this point
        if (!$term) { return null; }
        return $term;
    }

    /***
    @param array $slug Array of slugs to lookup
    @return array Array of Topic posts

    Given a slug, return a Topic object that links both a post type and taxonomy
    public static function get_topic($slug) {
        return new Topic($slug);
    }
    ***/

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