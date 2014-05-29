<?php
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


/**
* Topic, a container for a post type and taxonomy, used to access both
* 
* @param string $slug Slug for a topic post type or taxonomy
*/
class Topic {
    
    function __construct($slug) {

        // stash the slug
        $this->slug = $slug;
        
        // get post
        $this->post = get_page_by_path($slug, OBJECT, 'topic');

        // get taxonomy
        $this->term = get_term_by('slug', $slug, 'topic', OBJECT);
    }

    public static function get_for_term($term) {
        // again with the redundant lookup
        return new Topic($term->slug);
    }

    public static function get_for_post($post) {
        // this seems inefficient, optimize later
        return new Topic($post->post_name);
    }
}