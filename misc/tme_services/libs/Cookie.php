<?php

class Cookie
{
	public static function set($key, $value) {
		setcookie($key,$value);
	}
	
	public static function get($key) {
		if (isset($_COOKIE[$key]))
		return $_COOKIE[$key];
	}
}
