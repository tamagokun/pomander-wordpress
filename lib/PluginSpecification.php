<?php

class PluginSpecification
{
	public $version = "latest";
	public $name, $command
	private $app;
	
	public function __construct($app, $name, $options=array())
	{
		$this->app = $app;
		$this->name = $name;
		foreach($options as $key=>$value)
		{
			switch($key)
			{
				case "version":
					$this->version = $value;
					break;
				case "branch":
					$this->version = $value;
					break;
				case "svn":
					$this->command = $this->command_svn($value);
					break;
				case "git":
					$this->command = $this->command_git($value);
					break;
				case "dir":
					$this->command = $this->command_dir($value);
					break;
			}
		}
		if(is_null($this->command)) $this->command = $this->command_svn("http://plugins.svn.wordpress.org/");
	}
	
	public function command_svn($uri)
	{
		if(strpos($uri,"plugins.svn.wordpress.org") > -1 && strpos($uri,$this->name) === false)
			$uri.=$this->name;
		$uri.=($this->version == "latest")? "/trunk" : "/tags/{$this->version}";
		return "svn export $uri {$this->app->env->deploy_to}/vendor/plugins/{$this->name} --force --quiet";
	}
	
	public function command_git($uri)
	{
		if($this->version == "latest") $this->version = "origin/master";
		return array(
			"git clone $uri {$this->app->env->deploy_to}/vendor/plugins/{$this->name}",
			"git fetch origin && git reset --hard {$this->version}"
		);
	}
	
	public function command_dir($path)
	{
		return "cp -R $path {$this->app->env->deploy_to}/vendor/plugins/{$this->name}";
	}
}