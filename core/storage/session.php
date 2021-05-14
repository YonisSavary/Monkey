<?php 

namespace Monkey\Storage;

class Session {
	static $initialized = false;

	/**
	 * Return if the session is initialized
	 */
	public static function is_initialized() : bool  
	{
		return self::$initialized;
	}


	/**
	 * initialized the session, a cookie lifetime 
	 * can be set as first parameter
	 * (2 Hours cookies by default)
	 */
	public static function init(int $lifetime=7200){
		self::$initialized = true;
		if (session_status() !== PHP_SESSION_ACTIVE) session_start(['cookie_lifetime' => $lifetime]);
	}


	/**
	 * get a session element, a default value can be given
	 */
	public static function get(string $key, mixed $default=null){
		return $_SESSION[$key] ?? $default;
	}


	/**
	 * set a session element, return if the value already existed
	 */
	public static function set(string $key, mixed $value): bool
	{
		$res = isset($_SESSION[$key]);
		$_SESSION[$key] = $value;
		return $res;
	}


	/**
	 * Unset a session element, return if the key
	 * was already set
	 */
	public static function unset(string $key): bool
	{
		if (!isset($_SESSION[$key])) return false;
		unset($_SESSION[$key]);
		return true;
	}


	/**
	 * destroy the session
	 */
	public static function destroy() {
		session_destroy();
	}
}