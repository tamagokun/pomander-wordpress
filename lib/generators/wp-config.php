<?php
    $app = builder()->get_application();
    $secret_keys = file_get_contents("https://api.wordpress.org/secret-key/1.1/salt/");
    $cache = isset($app->env->wordpress["cache"]) ? "define('WP_CACHE', {$app->env->wordpress["cache"]});" : "";
    $siteurl = isset($app->env->wordpress["url"]) ? "'{$app->env->wordpress["url"]}'" : "'http://'.\$_SERVER['SERVER_NAME']";
    $siteurl .= isset($app->env->wordpress["base_uri"]) ? ".'{$app->env->wordpress["base_uri"]}'" : "";
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
define ('WPLANG', '');

    /**
     * For developers: WordPress debugging mode.
     *
     * Change this to true to enable the display of notices during development.
     * It is strongly recommended that plugin and theme developers use WP_DEBUG
     * in their development environments.
     */
    define('WP_DEBUG', false);

// Define new paths in config
if( !defined('ABSPATH') ) define('ABSPATH', dirname(__FILE__).'/wordpress/');
define('WP_SITEURL', {$siteurl});
define('WP_CONTENT_DIR', ABSPATH.'public');
define('WP_CONTENT_URL', WP_SITEURL.'/public');
define('WP_PLUGIN_DIR', dirname(__FILE__.'../').'/public/plugins');
define('WP_PLUGIN_URL', WP_SITEURL.'/public/plugins');
define('PLUGINDIR', WP_PLUGIN_DIR);
{$cache}
require_once(ABSPATH . 'wp-settings.php');
?>
EOT;
?>
