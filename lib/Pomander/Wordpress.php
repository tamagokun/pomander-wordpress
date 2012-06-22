<?php
namespace Pomander
{
	class Wordpress
	{
		public static function load()
		{
			require dirname(__DIR__).'/tasks.php';
		}
	}
}
