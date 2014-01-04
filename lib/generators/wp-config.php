<?php
$app = builder()->get_application();
$secret_keys = file_get_contents("https://api.wordpress.org/secret-key/1.1/salt/");

// Wordpress constants
$define = function($key, $options_key) use($app) {
	$value = $app->env->wordpress[$options_key];
	if(is_bool($value)) $value = $value ? 'true' : 'false';
	return "define('{$key}', {$value});";
};

$cache = isset($app->env->wordpress["cache"])? $define('WP_CACHE', "cache") : "";
$multisite = isset($app->env->wordpress["allow_multisite"])? $define('WP_ALLOW_MULTISITE', "allow_multisite") : "";
$debug = $define('WP_DEBUG', "debug");
$edits = $define('DISALLOW_FILE_EDIT', "disallow_file_edit");
$mods = $define('DISALLOW_FILE_MODS', "disallow_file_mods");
$ssl_login = $define('FORCE_SSL_LOGIN', "force_ssl_login");
$ssl_admin = $define('FORCE_SSL_ADMIN', "force_ssl_admin");

$siteurl = isset($app->env->wordpress["url"])? "'{$app->env->wordpress["url"]}'":"'http://'.\$_SERVER['SERVER_NAME']";
if($app->env->wordpress["base_uri"]) $siteurl .= ".'{$app->env->wordpress["base_uri"]}'";

return <<<EOT
<?php
define('DB_NAME', '{$app->env->database["name"]}');
define('DB_USER', '{$app->env->database["user"]}');
define('DB_PASSWORD', '{$app->env->database["password"]}');
define('DB_HOST', '{$app->env->database["host"]}');
define('DB_CHARSET', '{$app->env->database["charset"]}');
define('DB_COLLATE', '');
{$secret_keys}
\$table_prefix = '{$app->env->wordpress["db_prefix"]}';
define ('WPLANG', '{$app->env->wordpress["lang"]}');
if( !defined('ABSPATH') ) define('ABSPATH', dirname(__FILE__).'/wordpress/');
define('WP_SITEURL', {$siteurl});
define('WP_CONTENT_DIR', ABSPATH.'public');
define('WP_CONTENT_URL', WP_SITEURL.'/public');
define('WP_PLUGIN_DIR', dirname(__FILE__.'../').'/vendor/plugins');
define('WP_PLUGIN_URL', WP_SITEURL.'/vendor/plugins');
define('PLUGINDIR', WP_PLUGIN_DIR);
{$cache}
{$debug}
{$multisite}
{$edits}
{$mods}
{$ssl_login}
{$ssl_admin}
require_once(ABSPATH . 'wp-settings.php');
?>
EOT;
?>
