pomander-wordpress - Wordpress tasks for Pomander
=================================================

This is a plugin to help fully manage your Wordpress projects
with the help of Pomander.

Usage
-----

* Run `pomify` if no Pomfile exists
* Include plugin in Pomfile `include 'pomander-wordpress/wordpress.php';`
* `pom -T` to see the stuff.

Tasks
-----

`db:backup` - Create a backup for use with db:merge

`db:create` - Create Wordpress database if it doesn't already exist

`db:full` - Store a .bz2 SQL dump

`db:merge` - Merge a backup from db:backup into another environment

`deploy:initial` - Complete Wordpress deployment stack (setup, update, wordpress, wp-config, htaccess)

`deploy:wordpress` - Check out latest Wordpress source into project

`htaccess` - Generates a .htaccess file, so you don't need to deal with permalinks

`setup` - Sets up project structure. See below for what this means, since it's different than a standard Wordpress.

`uploads:pull` - Pull uploads down from environment

`uploads:push` - Push all local uploads to an environment

`wp_config` - Creates wp-config.php, so you don't have to

`wpify` - Used to setup your local environment for development


Structure
---------

You can certainly use this plugin however you please, but some tasks are
expecting a certain Wordpress structure that I feel is much better than
the usualy Wordpress folder structure. Here we go:

`deploy/` - This is where your Pomander configs go (nothing weird about that)

`public/` - This is essentially your wp-content folder. Themes, plugins, and uploads all go here

`wordpress/` - Your Wordpress installation goes here. You should never really have to go into this folder

`wp-config.php` - See that? We keep wp-config outside of your wordpress installation for added security
