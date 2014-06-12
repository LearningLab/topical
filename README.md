Topical
=======

This is a plugin for managing rich topics for WordPress. Topics use both taxonomies and custom post types to create pivot points around which a site can be organized. Think of this as both a drop-in solution for topic pages and a framework for building more complex pathways for user participation.

A topic is a post type that (thanks to Posts 2 Posts) also acts like a taxonomy. Use it to organize posts, and to write extended background info for subjects you'll come back to.

Topical depends on the [Timber][] framework and [Posts2Posts][p2p].

 [Timber]: http://jarednova.github.io/timber/
 [p2p]: http://wordpress.org/plugins/posts-to-posts/

Topical was built for [Learning Lab][LL] at [WBUR][], but I've tried to keep it generic enough to be used elsewhere.

 [LL]: http://learninglab.wbur.org
 [WBUR]: http://www.wbur.org

Workflow:
---------

Topics can be created in two ways: While writing a post, or independently.

 - When writing a post, add and assign topics like any other taxonomy. When you're done writing your post (before or after publishing), go into the newly created topic and add a better title, background info and other details.
 
 - When creating a topic independently, simply add it the same way you'd add a post or page (under Topics). Make sure to add a Short Title, which will display in the post admin. (This isn't technically required, but if your topic is called "Everything you ever wanted to know about Common Core," it makes sense to have a short title "Common Core".)


P2P:
----

Topical uses Posts2Posts under the hood, to maintain connections between topics and posts. Other content types (attachments, etc) may be added later.

Topics should still have both a short and long name, for readability and SEO.