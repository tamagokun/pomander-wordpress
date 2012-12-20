<?php

group('deploy',function() {

  desc("Deploy Wordpress in environment.");
  task('wordpress','app', function($app) {
    info("fetch","Wordpress {$app->env->wordpress["version"]}");
    $cmd = array(
			"mkdir -p {$app->env->release_dir}/wordpress",
      "curl -s https://nodeload.github.com/WordPress/WordPress/tar.gz/{$app->env->wordpress["version"]} > {$app->env->release_dir}/wordpress.tar",
			"tar --strip-components=1 -xzf {$app->env->release_dir}/wordpress.tar -C {$app->env->release_dir}/wordpress",
			"rm -f {$app->env->release_dir}/wordpress.tar",
      "rm -rf {$app->env->release_dir}/wordpress/public",
      "ln -s {$app->env->release_dir}/public {$app->env->release_dir}/wordpress/public",
      "rm -rf {$app->env->release_dir}/wordpress/vendor",
      "ln -s {$app->env->release_dir}/vendor {$app->env->release_dir}/wordpress/vendor",
      "mkdir -p {$app->env->release_dir}/vendor/plugins",
      "touch {$app->env->release_dir}/wordpress/.htaccess"
    );
		if($app->env->releases === false)
		{
			$cmd[] = "mkdir -p {$app->env->release_dir}/public/uploads";
		}else
		{
			$cmd[] = "mkdir -p {$app->env->shared_dir}/uploads";
			$cmd[] = "rm -rf {$app->env->release_dir}/public/uploads";
			$cmd[] = "ln -s {$app->env->shared_dir}/uploads {$app->env->release_dir}/public/uploads";
		}
    run($cmd);
  });

  desc("Deploy plugins in environment.");
  task('plugins','app', function($app) {
    if(!isset($app->env->plugins)) return;
		foreach($app->env->plugins as $name=>$plugin)
    {
      $spec = new \Pomander\Wordpress\PluginSpecification($app,$name,$plugin);
      info("plugin","{$spec->name} at {$spec->url}");
      $spec->run();
    }
    info("plugins","Successfully deployed.");
  });

	task('finalize', 'deploy:wordpress','deploy:plugins','wp_config','htaccess');

});

//db
group('db', function() {
  desc("Create database in environment if it doesn't already exist");
  task('create','db', function($app) {
    info("create","database {$app->env->database["name"]}");
    run($app->env->adapter->create());
  });

  desc("Perform a backup of environment's database for use in merging");
  task('backup','db', function($app) {
    info("backup",$app->env->database["name"]);
    run($app->env->adapter->dump($app->env->shared_dir."/dump.sql","--lock-tables=FALSE --skip-add-drop-table | sed -e 's|INSERT INTO|REPLACE INTO|' -e 's|CREATE TABLE|CREATE TABLE IF NOT EXISTS|'"));
    info("fetch","{$app->env->shared_dir}/dump.sql");
    get("{$app->env->shared_dir}/dump.sql","./tmpdump.sql");
    $app->old_url = $app->env->url;
    info("clean","dump.sql");
    run("rm {$app->env->shared_dir}/dump.sql");
  });

  desc("Merge a backed up database into environment");
  task('merge','db', function($app) {
    info("merge","database {$app->env->database["name"]}");
    $file = $app->env->shared_dir."/dump.sql";
    if(!file_exists("./tmpdump.sql"))
      warn("merge","i need a backup to merge with (dump.sql). Try running db:backup first");
    if(isset($app->old_url))
    {
      info("premerge","replace {$app->old_url} with {$app->env->url}");
			$handle = fopen("./tmpdump.sql", 'rb');
			$sql = fread($handle, filesize("./tmpdump.sql"));
			fclose($handle);
			$sql = preg_replace("|http://{$app->old_url}|", "http://{$app->env->url}", $sql);
			$sql = preg_replace('!s:(\d+):([\\\\]?"[\\\\]?"|[\\\\]?"((.*?)[^\\\\])[\\\\]?");!e', "'s:'.strlen(\Pomander\Wordpress::unescape_mysql('$3')).':\"'.\Pomander\Wordpress::unescape_quotes('$3').'\";'", $sql);
			$sql = $sql."\nUPDATE {$app->env->wordpress["db_prefix"]}options SET option_value=\"http://{$app->env->url}\" WHERE option_name=\"siteurl\" OR option_name=\"home\";\n";
			$handle = fopen("./tmpdump.sql", 'w');
			fwrite($handle, $sql);
			fclose($handle);
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
    $file = $app->env->database["name"]."_".@date('Ymd_His').".bak.sql.bz2";
    info("full backup",$file);
    $cmd = array(
      "umask 002",
      "mkdir -p {$app->env->shared_dir}/backup",
      $app->env->adapter->backup($app->env->shared_dir."/backup/".$file, "--add-drop-table")
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
    get("{$app->env->release_dir}/public/uploads/","./public/uploads/");
  });

  desc("Place all local uploads into environment");
  task('push','app', function($app) {
    info("uploads","deploying");
    put("./public/uploads/","{$app->env->release_dir}/public/uploads");
  });
});

//wordpress plugins
group('plugins', function() {
  
});

//wordpress
desc("Create and deploy wp-config.php for environment");
task('wp_config','app', function($app) {
  info("config","creating wp-config.php");
  file_put_contents("./tmp-wp-config",include(__DIR__."/generators/wp-config.php"));
  put("./tmp-wp-config","{$app->env->release_dir}/wp-config.php");
  unlink("./tmp-wp-config");
});

desc("Create and deploy .htaccess for environments");
task('htaccess','app', function($app) {
  info("htaccess","creating .htaccess");
  file_put_contents("./tmp-htaccess",include(__DIR__."/generators/htaccess.php"));
  put("./tmp-htaccess","{$app->env->release_dir}/wordpress/.htaccess");
  unlink("./tmp-htaccess");
});

desc("Wordpress task stack for local machine (1 and done)");
task('wpify','environment','deploy:wordpress','deploy:plugins','db:create','wp_config','htaccess', function($app) {
  info("wpify","success");
});

desc("Alias for wpify");
task("setup","wpify");

group('config', function() {
	task('wordpress',function() {
		umask(002);
		@mkdir("./deploy");
		@mkdir("./public");
		@mkdir("./wordpress");
		@mkdir("./vendor");
		@mkdir("./vendor/plugins");
		@file_put_contents("./.gitignore",include(__DIR__."/generators/gitignore.php"));
		copy(__DIR__."/generators/config.php","./deploy/development.php");
		info("success","run 'wpify' or 'setup' after you configure development to get going");
	});
});
