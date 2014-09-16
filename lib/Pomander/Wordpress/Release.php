<?php
namespace Pomander\Wordpress;

class Release
{
    public $version;

    public function __construct($version = "latest")
    {
        if($version == "latest") $version = $this->latest();
        $this->version = $version;
    }

    public function deploy($env)
    {
        $cmd = array();
        $install_location = isset($env->wordpress["install_dir"]) ? $env->wordpress["install_dir"] : "wordpress";
        $root_install = empty($install_location);
        $install_dir = $env->release_dir."/".rtrim($install_location, "/");

        // make sure install location exists
        $cmd[] = "cd {$env->release_dir}";
        $cmd[] = "mkdir -p $install_dir";
        // download release
        $cmd[] = "curl -sL {$this->release_url()} > {$env->release_dir}/wordpress.tar";
        // extract
        if( $root_install ) {
            $cmd[] = "tar --strip-components=1 -xzf {$env->release_dir}/wordpress.tar";
        } else {
            $cmd[] = "mkdir -p {$install_dir}";
            $cmd[] = "tar --strip-components=1 -xzf {$env->release_dir}/wordpress.tar -C {$install_dir}";
            $cmd[] = "rm -rf $install_dir/public";
            $cmd[] = "ln -s {$env->release_dir}/public $install_dir/public";
            $cmd[] = "rm -rf $install_dir/vendor";
            $cmd[] = "ln -s {$env->release_dir}/vendor $install_dir/vendor";
        }

        // cleanup
        $cmd[] = "rm -rf {$env->release_dir}/wordpress.tar";

        // make sure plugin dir and .htaccess exist
        $cmd[] = "mkdir -p {$env->release_dir}/vendor/plugins";
        $cmd[] = "touch $install_dir/.htaccess";
        return $cmd;
    }

    public function latest()
    {
        $data = json_decode(file_get_contents("http://api.wordpress.org/core/version-check/1.7/"));
        return $data->offers[0]->current;
    }

    public function release_url()
    {
        // This is a smaller download, but we have no uniform way of extracting a zip
        // http://wordpress.org/wordpress-{$this->version}-no-content.zip
        return "http://wordpress.org/wordpress-{$this->version}.tar.gz";
    }
}
