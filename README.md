pomander-wordpress - Wordpress tasks for Pomander
=================================================

This is a plugin to help fully manage your Wordpress projects
with the help of Pomander.

Install
-------

Requirements:

- [pomander](https://github.com/tamagokun/pomander)

Usage
-----

* Run `pomify` if no Pomfile exists
* Include plugin in Pomfile `$pom->load('Wordpress');`
* `pom -T` to see the stuff.

Getting Started
---------------

```bash
$ pom config
```

Modify your development.yml or development.php

```bash
$ pom setup
```

Done!

Tasks
-----

```
db:backup           Perform a backup of environment's database for use in merging
db:create           Create database in environment if it doesn't already exist
db:full             Store a full database backup
db:merge            Merge a backed up database into environment
deploy:plugins      Deploy plugins in environment.
deploy:wordpress    Deploy Wordpress in environment.
htaccess            Create and deploy .htaccess for environments
setup               Alias of wpify
uploads:pull        Download uploads from environment
uploads:push        Place all local uploads into environment
wp_config           Create and deploy wp-config.php for environment
wpify               Wordpress task stack for local machine (1 and done)
```

Configuration
------------

This plugin introduces some new configuration options for your environment .yml files. Here is an overview:

```yaml
wordpress:              # wp-config stack. Self-explanatory, right?
  version: 3.3.1
  db: wp_database
  db_user: root
  db_password:
  db_host: localhost
  db_prefix: wp_
  db_charset: utf8
  base_uri: /wordpress  # Base uri for Wordpress installation (example: dev.local/mywebsite/wordpress)
```

Plugins are handled by a `plugins` hash. You can provide a specific version, as well as where to find the plugin (supports svn,git,and dir). Plugins are deployed into `vendor/plugins`.

```yaml
plugins:                
	more-types: {version: latest}
	more-fields: {version: 2.1, svn: http://plugins.svn.wordpress.org/more-fields}
	gravityforms: {dir: some_other_dir/gravityforms}
	my-plugin: {branch: origin/master, git: https://github.com/dude/my-plugin.git}
```

Plugins specified without a location will default to the Wordpress plugin repository.


Structure
---------

You can certainly use this plugin however you please, but some tasks are
expecting a certain Wordpress structure that I feel is much better than
the usualy Wordpress folder structure. Here we go:

```
deploy/             This is where your Pomander configs go (nothing weird about that)
public/             Welcome to your new wp-content folder.
--- themes/
--- uploads/
vendor/             Plugins go in here.
--- plugins/
wordpress/          Your Wordpress installation goes here. You should never really have to go into this folder
wp-config.php       See that? We keep wp-config outside of your wordpress installation for added security
```
