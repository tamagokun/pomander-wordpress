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

* `pom init` if no configuration found.
* Include plugin in environment config `$env->load('Wordpress');`
* `pom -T` to see the stuff.

Getting Started
---------------

```bash
$ pom init
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

This plugin introduces a __wordpress__ option, and a __plugins__ option. These are both array structures that you can configure either in a PHP based config, or a YAML based config.

An example of what a .php config for a Wordpress might look like:

```php
$env->database(array(
	'name' => 'my_wordpress',
	'user' => 'root',
	'password' => '',
	'host' => '127.0.0.1',
	'charset' => 'utf8'
));

$env->wordpress(array(
	'version' => '3.5.2',
	'db_prefix' => 'wp_',
	'base_uri' => ''
));

$env->plugins(array(
	'advanced-custom-fields' => array('version' => 'latest'),
	'gravityforms' => array('dir' => 'lib/gravityforms')
));
```

And an example of a YAML based config:

```yaml
database:
	name: my_wordpress
	user: root
	password:
	host: 127.0.0.1
	charset: utf8

wordpress:
	version: 3.5.2
	db_prefix: wp_
	base_uri: /wordpress # Base uri for Wordpress installation (example: dev.local/wordpress)

plugins:
	more-types: {version: latest}
	more-fields: {version: 2.1, svn: http://plugins.svn.wordpress.org/more-fields}
	gravityforms: {dir: some_other_dir/gravityforms}
	my-plugin: {branch: origin/master, git: https://github.com/dude/my-plugin.git}
```

Plugins can be provided with:

 * __version__ - defaults to "latest"
 * __location__ (git/svn/dir) - defaults to Wordpress plugin repository
 * __branch__ - Specify which branch of a repository to use

Structure
---------

You can certainly use this plugin however you please, but some tasks are
expecting a certain Wordpress structure that I feel is much better than
the typical Wordpress folder structure. Here we go:

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

Examples
--------

### Moving Uploads

Grab uploads from production to ease development:

```bash
$ pom production uploads:pull
```

You can chain commands to move uploads between environments:

```bash
$ pom production uploads:pull staging uploads:push
```

### Moving Databases

Merge production into development:

```bash
$ pom production db:backup development db:merge
```

If your environment has `backup: true`, Pomander will create a backup of the db prior to any merge procedure.
