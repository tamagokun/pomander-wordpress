<?php
namespace Pomander
{
    class Wordpress
    {
        public static function load($env)
        {
            $env->wordpress = self::defaults();
            require dirname(__DIR__).'/tasks.php';
        }

        public static function unescape_mysql($value)
        {
            return str_replace(array("\\\\", "\\0", "\\n", "\\r", "\Z",  "\'", '\"'),
                                                    array("\\",   "\0",  "\n",  "\r",  "\x1a", "'", '"'),
                                                    $value);
        }

        public static function unescape_quotes($value)
        {
            return str_replace('\"', '"', $value);
        }

        protected static function defaults()
        {
            return array(
                'cache'                => true,
                'disallow_file_edit'   => false,
                'disallow_file_mods'   => false,
                'force_ssl_login'      => false,
                'force_ssl_admin'      => false,
                'debug'                => false,
                'lang'                 => '',
                'compat'               => false,
                'version'              => 'latest',
                'db_prefix'            => 'wp_',
                'base_uri'             => '',
                'install_dir'          => 'wordpress'
            );
        }
    }
}
