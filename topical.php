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