<?php

group('deploy',function() {

  desc("Deploy Wordpress in environment.");
  task('wordpress','app', function($app) {
    info("fetch","Wordpress {$app->env->wordpress["version"]}");
    $cmd = array(
      "svn export http://svn.automattic.com/wordpress/tags/{$app->env->wordpress["version"]} {$app->env->deploy_to}/wordpress --force --quiet",
      "rm -rf {$app->env->deploy_to}/wordpress/public",
      "ln -s {$app->env->deploy_to}/public {$app->env->deploy_to}/wordpress/public",
      "rm -rf {$app->env->deploy_to}/wordpress/vendor",
      "ln -s {$app->env->deploy_to}/vendor {$app->env->deploy_to}/wordpress/vendor",
      "mkdir -p {$app->env->deploy_to}/public/uploads",
      "mkdir -p {$app->env->deploy_to}/vendor/plugins",
      "touch {$app->env->deploy_to}/wordpress/.htaccess"
    );
    run($cmd);
  });
  
  desc("Deploy plugins in environment.");
  task('plugins','app', function($app) {
    require "lib/PluginSpecification.php";
    foreach($app->env->plugins as $name=>$plugin)
    {
      $spec = new PluginSpecification($app,$name,$plugin);
      info("fetch","{$spec->name} plugin at {$spec->url}");
      $spec->run();
    }
    info("plugins","Successfully deployed.");
  });
  
  task('all','app','deploy:setup','deploy:update','deploy:wordpress','deploy:plugins','wp_config','htaccess');

  desc("Complete Wordpress deployment stack (1 and done)");
  task('initial','deploy:all','db:create');

});

//db
group('db', function() {
  desc("Create database in environment if it doesn't already exist");
  task('create','db', function($app) {
    info("create","database {$app->env->wordpress["db"]}");
    run($app->env->adapter->create());
  });

  desc("Perform a backup of environment's database for use in merging");
  task('backup','db', function($app) {
    info("backup",$app->env->wordpress["db"]);
    run($app->env->adapter->dump($app->env->deploy_to."/dump.sql","--lock-tables=FALSE --skip-add-drop-table | sed -e 's|INSERT INTO|REPLACE INTO|' -e 's|CREATE TABLE|CREATE TABLE IF NOT EXISTS|'"));
    info("fetch","{$app->env->deploy_to}/dump.sql");
    get("{$app->env->deploy_to}/dump.sql","./tmpdump.sql");
    $app->old_url = $app->env->url;
    info("clean","dump.sql");
    run("rm {$app->env->deploy_to}/dump.sql");
  });

  desc("Merge a backed up database into environment");
  task('merge','db', function($app) {
    info("merge","database {$app->env->wordpress["db"]}");
    $file = $app->env->deploy_to."/deploy/dump.sql";
    if(!file_exists("./tmpdump.sql"))
      warn("merge","i need a backup to merge with (dump.sql). Try running db:backup first");
    if(isset($app->old_url))
    {
      info("premerge","replace {$app->old_url} with {$app->env->url}");
      shell_exec("sed -e 's|http://{$app->old_url}|http://{$app->env->url}|g' ./tmpdump.sql > ./dump.sql.changed");
      shell_exec("rm ./tmpdump.sql && mv ./dump.sql.changed ./tmpdump.sql");
    }
    if( isset($app->env->backup) && $app->env->backup)
      $app->invoke("db:full");
    info("merge","dump.sql");
    put("./tmpdump.sql",$file);
    run($app->env->adapter->merge($file),"rm -rf $file");
    info("clean","tmpdump.sql");
    unlink("./tmpdump.sql");
  });

  desc("Store a full database backup");
  task('full','db',function($app) {
    $file = $app->env->wordpress["db"]."_".@date('Ymd_His').".bak.sql.bz2";
    info("full backup",$file);
    $cmd = array(
      "umask 002",
      "mkdir -p {$app->env->deploy_to}/backup",
      $app->env->adapter->backup($app->env->deploy_to."/backup/".$file, "--add-drop-table")
    );
    run($cmd);
  });
});

//wordpress uploads
group('uploads', function() {
  desc("Download uploads from environment");
  task('pull','app', function($app) {
    info("uploads","backing up environment uploads");
    umask(002);
    if(!file_exists("./public/uploads")) @mkdir("./public/uploads");
    get("{$app->env->deploy_to}/public/uploads/","./public/uploads/");
  });

  desc("Place all local uploads into environment");
  task('push','app', function($app) {
    info("uploads","deploying");
    put("./public/uploads/","{$app->env->deploy_to}/public/uploads");
  });
});

//wordpress plugins
group('plugins', function() {
  
});

//wordpress
desc("Create and deploy wp-config.php for environment");
task('wp_config','app', function($app) {
  info("config","creating wp-config.php");
  file_put_contents("./tmp-wp-config",include("lib/generators/wp-config.php"));
  put("./tmp-wp-config","{$app->env->deploy_to}/wp-config.php");
  unlink("./tmp-wp-config");
});

desc("Create and deploy .htaccess for environments");
task('htaccess','app', function($app) {
  info("htaccess","creating .htaccess");
  file_put_contents("./tmp-htaccess",include("lib/generators/htaccess.php"));
  put("./tmp-htaccess","{$app->env->deploy_to}/wordpress/.htaccess");
  unlink("./tmp-htaccess");
});

desc("Wordpress task stack for local machine (1 and done)");
task('wpify','environment','deploy:wordpress','db:create','wp_config','htaccess', function($app) {
  info("wpify","success");
});

group("setup", function() {
  task("new",function($app) {
    umask(002);
    info("create","deploy/");
    @mkdir("./deploy");
    info("create","public/");
    @mkdir("./public");
    info("create","wordpress/");
    @mkdir("./wordpress");
    info("create","vendor/");
    @mkdir("./vendor");
    info("create","vendor/plugins");
    @mkdir("./vendor/plugins");
    info("create",".gitignore");
    @file_put_contents("./.gitignore",include("lib/generators/gitignore.php"));
    info("success","project structure created");
  });
});
desc("Setup project structure and create development.yml");
task("setup","setup:new","config",function() {
  info("success","run 'wpify' after you configure development.yml to get going");
});

task('config',function() {
  copy(__DIR__."/lib/generators/config.yml","./deploy/development.yml");
});

?>
