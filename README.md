Topical
=======

This is a plugin for managing rich topics for WordPress. Topics use both taxonomies and custom post types to create pivot points around which a site can be organized. Think of this as both a drop-in solution for topic pages and a framework for building more complex pathways for user participation.

A topic ties together a post type and a taxonomy:

 - the post type contains background information, a featured image, excerpt and other post-like features
 - the taxonomy (topic) allows us to tie regular posts (or other post types) to the topic

Topical depends on the [Timber][] framework and [Posts2Posts][p2p].

 [Timber]: http://jarednova.github.io/timber/
 [p2p]: http://wordpress.org/plugins/posts-to-posts/


Workflow:
---------

Topics can be created in two ways: While writing a post, or independently.

 - When writing a post, add and assign topics like any other taxonomy (specifically, like a category). Creating a topic this way creates a corresponding topic page. When you're done writing your post (before or after publishing), go into the newly created topic and add a better title, background info and other details.
 
 - When creating a topic independently, simply add it the same way you'd add a post or page (under Topics). Make sure to add a Short Title, which will display in the post admin (under the hood, this becomes the topic's corresponding taxonomy).


P2P:
----

Topical uses Posts2Posts under the hood, to maintain connections between topics and posts. Other content types (attachments, etc) may be added later.

Topics should still have both a short and long name, for readability and SEO.