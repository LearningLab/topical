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

class Topical {

	function __construct() {
        // create a post type

        // create a taxonomy
        add_action('init', array(&$this, 'create_taxonomy'), 10);

        // create a topic post type when a taxonomy is created or updated
        // create a topic taxonomy when a topic post is created or updated

        // add routes

	}

    function create_post_type() {}

    function create_taxonomy() {

        $labels = array(
            'name'                       => _x( 'Topics', 'Taxonomy General Name', 'topical' ),
            'singular_name'              => _x( 'Topic', 'Taxonomy Singular Name', 'topical' ),
            'menu_name'                  => __( 'Taxonomy', 'topical' ),
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
        $args = array(
            'labels'                     => $labels,
            'hierarchical'               => true,
            'public'                     => true,
            'show_ui'                    => true,
            'show_admin_column'          => true,
            'show_in_nav_menus'          => true,
            'show_tagcloud'              => true,
        );
        register_taxonomy( 'topic', array( 'post' ), $args );

    }

    /***
    @param string $slug
    @return Topic

    Given a slug, return a Topic object that links both a post type and taxonomy
    ***/
    public static function get_topic($slug) {

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