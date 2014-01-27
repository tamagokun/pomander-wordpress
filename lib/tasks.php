<?php

group('deploy',function() {

    desc("Deploy Wordpress in environment.");
    task('wordpress','app', function($app) {
        $release = new \Pomander\Wordpress\Release($app->env->wordpress["version"]);
        info("fetch","Wordpress {$release->version}");

        $cmd = $release->deploy($app->env);

        if( $app->env->releases === false ) {
            $cmd[] = "mkdir -p {$app->env->release_dir}/public/uploads";
        } else {
            $cmd[] = "mkdir -p {$app->env->shared_dir}/uploads";
            $cmd[] = "mkdir -p {$app->env->release_dir}/public";
            $cmd[] = "rm -rf {$app->env->release_dir}/public/uploads";
            $cmd[] = "ln -s {$app->env->shared_dir}/uploads {$app->env->release_dir}/public/uploads";
        }
        run($cmd);
    });

    desc("Deploy plugins in environment.");
    task('plugins','app', function($app) {
        if(!isset($app->env->plugins)) return;
        foreach($app->env->plugins as $name=>$plugin) {
            $spec = new \Pomander\Wordpress\PluginSpecification($app,$name,$plugin);
            info("plugin","{$spec->name} at {$spec->url}");
            $spec->run();
        }
        info("plugins","Successfully deployed.");
    });

    task('finalize', 'deploy:wordpress','deploy:plugins','wp_config','htaccess');
});

after('deploy:finalize', function($app) {
    $app->invoke('compatibility_mode');
});

task('compatibility_mode', function($app) {
    if(!$app->env->wordpress["compat"]) return;
    if(empty($app->env->wordpress["install_dir"])) return;
    info("Create Symlinks", "adding symlinks");
    // Note: This removes the TwentyTen theme if it exists. User will need to include it in their repo if they rely on it.
    $install_dir = $app->env->release_dir."/".rtrim("/", $app->env->wordpress["install_dir"]);
    $uploads_dir = $app->env->releases === false ? "{$app->env->release_dir}/public/uploads" : "{$app->env->shared_dir}/uploads";
    $cmd = array(
        "rm -rf {$install_dir}/wp-content/themes",
        "ln -s {$app->env->release_dir}/public/themes {$install_dir}/wp-content/themes",
        "rm -rf {$install_dir}/wp-content/plugins",
        "ln -s {$app->env->release_dir}/vendor/plugins {$install_dir}/wp-content/plugins",
        "ln -s {$app->env->release_dir}/vendor/plugins {$app->env->release_dir}/public/plugins",
        "ln -s {$uploads_dir} {$install_dir}/wp-content/uploads"
    );
    run($cmd);
});

//db merging
before('db:merge', function($app) {
    if(!file_exists("./tmpdump.sql")) return;
    if(!$app->env->db_swap_url) return;

    $handle = fopen("./tmpdump.sql", 'rb');
    $sql = fread($handle, filesize("./tmpdump.sql"));
    fclose($handle);

    if(isset($app->old_url) || isset($app['merge_url'])) {
        if(isset($app['merge_url'])) $app->old_url = $app['merge_url'];
        info("premerge","replace {$app->old_url} with {$app->env->url}");
        $sql = preg_replace("|http://{$app->old_url}|", "http://{$app->env->url}", $sql);
        $sql = preg_replace_callback('|s:(\d+):\\\?"(.*?)\\\?";|', function($matches) {
            return "s:" . strlen(stripslashes($matches[2])) . ":\"" . $matches[2] . "\";";
        }, $sql);
    }
    $sql = $sql."\nUPDATE {$app->env->wordpress["db_prefix"]}options SET option_value=\"http://{$app->env->url}\" WHERE option_name=\"siteurl\" OR option_name=\"home\";\n";

    $handle = fopen("./tmpdump.sql", 'w');
    fwrite($handle, $sql);
    fclose($handle);
    unset($app->old_url);
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

//wordpress
desc("Create and deploy wp-config.php for environment");
task('wp_config','app', function($app) {
    info("config","creating wp-config.php");
    file_put_contents("./tmp-wp-config", include(__DIR__."/generators/wp-config.php"));
    put("./tmp-wp-config", "{$app->env->release_dir}/wp-config.php");
    unlink("./tmp-wp-config");
});

desc("Create and deploy .htaccess for environments");
task('htaccess','app', function($app) {
    info("htaccess","creating .htaccess");
    file_put_contents("./tmp-htaccess", include(__DIR__."/generators/htaccess.php"));
    $install_dir = $app->env->release_dir."/".rtrim("/", $app->env->wordpress["install_dir"]);
    put("./tmp-htaccess", "{$install_dir}/.htaccess");
    unlink("./tmp-htaccess");
});

desc("Wordpress task stack for local machine (1 and done)");
task('wpify','environment','deploy:wordpress','deploy:plugins','db:create','wp_config','htaccess','compatibility_mode', function($app) {
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
