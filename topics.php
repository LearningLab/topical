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
* Topic, an extended TimberPost for topic pages
* 
* @param mixed Lookup for a single topic page
*/
class Topic extends TimberPost {
    
    function get_posts() {

        $connected = new WP_Query(array(
            'connected_type' => 'posts_to_topics',
            'connected_items' => $this->ID,
            'nopaging' => true,
        ));

        return Timber::handle_post_results($connected->posts);
    }
}