<?php
namespace Pomander
{
	class Wordpress
	{
		public static function load()
		{
			require_once dirname(__DIR__).'/tasks.php';
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
	}
}
