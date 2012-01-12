pomander-wordpress - Wordpress tasks for Pomander
=================================================

This is a plugin to help fully manage your Wordpress projects
with the help of Pomander.

Install
-------

Requirements:

- [pomander](https://github.com/tamagokun/pomander)

Until phark is more developed to allow installation of remote packages, this is how we do it:

    $ git clone git://github.com/tamagokun/pomander-wordpress.git && cd pomander-wordpress
    $ phark install .

Usage
-----

* Run `pomify` if no Pomfile exists
* Include plugin in Pomfile `include 'pomander-wordpress/wordpress.php';`
* `pom -T` to see the stuff.

Tasks
-----

```
db:backup           Perform a backup of environment's database for use in merging
db:create           Create database in environment if it doesn't already exist
db:full             Store a full database backup
db:merge            Merge a backed up database into environment
deploy:initial      Complete Wordpress deployment stack (1 and done)
deploy:wordpress    Deploy Wordpress in environment.
htaccess            Create and deploy .htaccess for environments
setup               Setup project structure and create development.yml
uploads:pull        Download uploads from environment
uploads:push        Place all local uploads into environment
wp_config           Create and deploy wp-config.php for environment
wpify               Wordpress task stack for local machine (1 and done)
```

Structure
---------

You can certainly use this plugin however you please, but some tasks are
expecting a certain Wordpress structure that I feel is much better than
the usualy Wordpress folder structure. Here we go:

```
deploy/             This is where your Pomander configs go (nothing weird about that)
public/             This is essentially your wp-content folder.
--- plugins/
--- themes/
--- uploads/
wordpres/           Your Wordpress installation goes here. You should never really have to go into this folder
wp-config.php       See that? We keep wp-config outside of your wordpress installation for added security
```
