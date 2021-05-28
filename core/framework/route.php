<?php 

namespace Monkey\Framework;

use Closure;
use Monkey\Web\Request;

/**
 * This class is here to store informations
 * about framework Routes, this class has a
 * few useful functions 
 * 
 * 
 * (static) Build a route regex from a path
 * 
 * `Route::get_regex(string $path) : string`
 * 
 * 
 * (public) Apply Router groups to a Route, 
 * see Router::groups for more !
 * 
 * `Route->apply_groups(array $groups)`
 * 
 * (public) Check if this route path match a 
 * Request Object or a URI String
 * 
 * `Route->match(string|Request $request) : bool `
 */
class Route
{
	public $path;
	public $callback;
	public $name;
	public $methods;
	public $middlewares;
	public $regex;

	const SLUGS_TYPES = [
		"/\{int:.+\}/" => "[0-9]+", 
		"/\{float:.+\}/" => "[0-9]+\.?[0-9]{0,}", 
		"/\{bool:.+\}/" => "(true|false)",
		"/\{string:.+\}/" => ".+",
		"/\{[A-Za-z0-9:]+\}/" => ".+"
	];

    /**
     * Given a route path, this function return
     * a regex to reach it
     * 
     * @param string $path Route Path 
     * @return string Route Regex 
     */
    public static function get_regex(string $path) : string
    {
        $regex = $path;
        $regex = str_replace("/", "\\/", $regex);

		foreach (self::SLUGS_TYPES as $declaration => $type_regex){
			$regex = preg_replace($declaration, $type_regex, $regex);
		}

        $regex = "/^$regex\/?$/i";
        return $regex;
    }


	/**
	 * Apply Router groups to a Route, 
 	 * see Router::groups for more !
	 */
	public function apply_groups(array $groups)
	{
		if (count($groups["path"] ?? []) > 0)
		{
			$this->path = "/". join("/", $groups["path"] ?? []) ."/". $this->path;
			$this->path = preg_replace("/\/{2,}/", "/", $this->path);
			$this->regex = self::get_regex($this->path);
		}
		$this->middlewares = array_merge($this->middlewares, $groups["middlewares"] ?? []);
		$this->methods = array_merge($this->methods, $groups["methods"]);
	}


	/**
	 * Check if this route path match a Request Object or a URI String
	 */
	public function match(string|Request $request) : bool 
	{
		if ($request instanceof Request) $request = $request->path;
		return (preg_match($this->regex, $request) !== 0);
	}

	/**
	 * You can see the docs of `Route` class for more informations
	 */
	public function __construct(
		string $path, 
		string|array|Closure $callback, 
		string $name=null, 
		array $middlewares=[],  
		array $methods=null)
	{
		if (str_ends_with($path, "/") && (strlen($path) > 1) ) $path = substr($path, 0, -1);
        $this->path = $path;
        $this->callback = $callback;
        $this->name = $name;
        $this->methods = $methods;
        $this->middlewares = $middlewares;
        $this->regex = self::get_regex($path);
        return $this;
	}
}