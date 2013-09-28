<?php

/*
	If you are at development environment leave these options below commented.
	You should config only for staging or production environment.
*/

// $env->repository('set your repository location here');
// $env->url('set your application url here');
// $env->deploy_to('set your application location on server');
// $env->releases(true);
// $env->user('set your ssh user');
// $env->scm('set your scm. defaults to git');
// $env->revision('');
// $env->backup(true);

// Config only for staging or production environment
// $env->app(array(             // Your application server(s) host or IP	
// 	'your app-server here'
// ));

// Config only for staging or production environment
// $env->db(array(              // Your database server(s) host or IP
// 	'your db-server here'
// ));

// If your app uses a database uncomment this:
$env->database(array(
	'name' => '',
	'user' => '',
	'password' => '',
	'host' => '127.0.0.1',
	'charset' => 'utf8'
));

$env->wordpress(array(
	'version' => '3.4.2',
	'db_prefix' => 'wp_',
	'base_uri' => '/wordpress'
));

$env->plugins(array(

));