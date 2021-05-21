<?php 

namespace Monkey\Framework;

use Closure;
use Monkey\Storage\Register;
use Monkey\Web\Request;
use Monkey\Web\Response;
use Monkey\Web\Trash;
use Monkey\Framework\Route;

class Router 
{
	const DEFAULT_GROUPS =  [
		"path" => [],
		"middlewares" => [],
		"methods" => []
	];

    public static $groups = self::DEFAULT_GROUPS;
    public static $list = []; // Current routes list
    public static $temp = []; // Not saved routes, used in `route` function


    /**
	 * Enable routes groups 
	 * ___
	 * Router Groups
	 * There are 3 Types of groups :
	 * - path (prefixes)
	 * - middlewares
	 * - methods
	 * ___
	 * - path are the prefixes to add before new routes url
	 * - middlewares are the middlewares or closure to execute before executing the controller callback
	 * - methods are the **allowed** methods of your new routes (merged with the route methods)
	 * 
	 * Example :
	 * ```php
	 * 		[
	 * 			"path" => ["api"],
	 * 			"middlewares" => ["AuthMiddleware" , "AnotherOne", function(){...}],
	 * 			"methods" => ["POST"]
	 * 		]
	 * ```
	 * 
	 * With these groups, every new route 
	 * - will have a "/api" prefix on their paths
	 * - will need the execution of the "AuthMiddleware", "AnotherOne" and the Closure middlewares
	 * - and can be accessed with the POST methods
     */
    public static function groups(array $groups)
    {
		foreach (self::$groups as $key => $void){
			self::$groups[$key] = array_merge(self::$groups[$key], $groups[$key] ?? []);
		}
    }


	/**
	 * Given the same syntax as `Router::groups`,
	 * this function disable the given groups
	 * 
	 * (See `Router::groups` for syntax docs)
	 */
    public static function end_groups(array $groups)
    {
		foreach (self::$groups as $key => $void){
        	self::$groups[$key] = array_diff(self::$groups[$key], $groups[$key]);
		}
    }


	/**
	 * Reset the routes groups
	 */
    public static function end_all_groups()
    {
        self::$groups = self::DEFAULT_GROUPS;
    }


	/**
	 * Get current Router groups with the 
	 * same syntax as described in `Router::groups`
	 */
    public static function get_groups()
    {
        return self::$groups;
    }


    /**
     * Redirect the client to a route
     * 
     * @param string $path Path to redirect to
     */
    public static function redirect(string $path) : void
    {
        header("Location: $path");
        die();
    }



    /**
     * Save the routes with the `Register` component
	 * @deprecated 
     */
    public static function save() : void
    {
        // Array Values is here to avoid the index problem
        // For an array like ["A", "B", "C"]
        // don't using array_values will give this [0 => "A", 1 => "B", 2 => "C"]
        self::$list = array_values(self::$list);
        Register::set("routes", self::$list);
    }


    /**
     * Initialize the component :
     * - Create an empty routes list if inexistant
     * - Read the framework routes
     */
    public static function init() : void
    {
        self::$list = Register::get("routes", []);
    }



	/**
     * Add a temporary route in `self::$temp`
     * 
     * @param string $path Route's Path (URL)
     * @param mixed $callback Route Callback (controllerName->methodName)
     * @param string $name Route Name (optionnal)
     * @param array $middlewares Routes Middlewares (optionnal) (classnames)
     * @param array $methods Routes HTTP methods (optionnal) 
     */
    public static function add(string $path, mixed $callback, string $name=null, array $middlewares=[],  array $methods=[]) : void
    {
        $new_route = new Route($path, $callback, $name, $middlewares, $methods);
		$new_route->apply_groups(self::$groups);
        array_push(self::$temp, $new_route);
    }

    
    /**
     * Add a route in `self::$list` and save the framework's routes with `Register`
     * 
     * @param string $path Route's Path (URL)
     * @param mixed $callback Route Callback (controllerName->methodName)
     * @param string $name Route Name (optionnal)
     * @param array $middlewares Routes Middlewares (optionnal) (classnames)
     * @param array $methods Routes HTTP methods (optionnal) 
     */
    public static function add_to_register(string $path, mixed $callback, string $name=null, array $middlewares=[],  array $methods=[]) : void
    {
        $new_routes = new Route($path, $callback, $name, $middlewares, $methods);
        array_push(self::$list, $new_routes);
        Register::set("routes", self::$list);
    }
    

    /**
     * Remove a route from the `Router::$list` array
     * 
     * @param string $nameOrRoute Can be either a route name of 
     * a route path, every maching routes are unsets
     */
    public static function remove(string $nameOrRoute)
    {
        foreach (self::$list as &$route)
        {
            if ($route->path === $nameOrRoute || $route->name === $nameOrRoute)
            {
                unset($route);
            }
        }
    }


    /**
     * Check if a route exists (from the `self::$list` and 'self::$temp' array)
     * 
     * @param string $nameOrRoute Can be either a route name of 
     * a route path
     */
    public static function exists(string $nameOrRoute)
    {
        if ($nameOrRoute === '' || $nameOrRoute === null) return false;
		foreach (array_merge(self::$list, self::$temp) as $route)
		{
			if(($route->path === $nameOrRoute && $route->path!==null)
			|| ($route->name === $nameOrRoute && $route->name!==null))
			{
				return true;
			}
		}
        return false;
    }


    /**
     * Given an object, this function display its
     * content if it is a Response Object (and then die)
     * 
     * @param mixed $object Object to check
     */
    public static function display_if_response(mixed $object) : void
    {
        if ($object instanceof Response)
        {
            $object->reveal();
            die();
        }
    }


    /**
     * Call a Route callback and return the value
     */
    public static function execute_route_callback(string|Closure|array $to_execute, mixed $custom_args=null) : mixed
    {
		if (is_callable($to_execute)) return $to_execute(Request::current());
        // Discompose the `callback` attribute
		$callback_parts = (is_string($to_execute))?  explode("->", $to_execute) : $to_execute;
		$controller_class = $callback_parts[0];
		$method = $callback_parts[1];
		
        // Create the controller and execute the route callback
        if (!class_exists($controller_class, false)) $controller_class = "Controllers\\".$controller_class;
        if (!class_exists($controller_class, true)) return Trash::fatal("\"$controller_class\" class does not exists !");
        $controller = new $controller_class();
		// Execute the controller callback
        if (!method_exists($controller, $method)) return Trash::fatal("\"$controller_class->$method\" method does not exists !");
        return $controller->$method( $custom_args ?? Request::current());
    }


	/**
	 * Given a middleware name, this functions return what the middleware
	 * will return (every middleware handle function is called with 
	 * the current request as first parameter)
	 */
	public static function execute_middleware(string|Closure $middleware_name) : mixed
	{
		if (is_callable($middleware_name)) 
		{
			$res = $middleware_name(Request::$current);
		}
		else 
		{
			if (!class_exists($middleware_name, false)) $middleware_name = "Middlewares\\".$middleware_name;
			if (!class_exists($middleware_name, true )) return Trash::fatal("$middleware_name does not exists!");
			$middleware = new $middleware_name();
	
			if (!method_exists($middleware, "handle")) return Trash::fatal("$middleware_name does not have a 'handle' function");
			$res = $middleware->handle(Request::current());
		}
	
		// Middlewares can either return a response (that will be displayed)
		// Or a edited Request (that will replace the current one)
		self::display_if_response($res);
		if ($res instanceof Request) Request::$current = $res;

		return $res;
	}

    
    /**
     * When called, this function :
     * 1. get the Request parameter,
     * 2. check for a matching route
     * 3. create a `Request` object
     * 4. call a route callback if existing, with the `Request` object as an argument
     */
    public static function route(Request $req, bool $return_response=false)
    {
        $routes_all = array_merge(self::$list, self::$temp);
        foreach($routes_all as $route)
        {
			if (! $route instanceof Route ) continue;
            if (! $route->match($req) ) continue;
            
			// We can't just do the in_array condition, as we must 
			// check methods only if the route has a method constraint
            if (count($route->methods ?? []) > 0 && (!in_array($req->method, $route->methods))) continue;

			$req->build_slugs($route->path);
            Request::$current = $req;

			// Executing Callbacks
			foreach ($route->middlewares ?? [] as $middleware_name)
			{
				self::execute_middleware($middleware_name);
			}

			$response = self::execute_route_callback($route->callback);
			if ($return_response) return $response;
            self::display_if_response($response);
			
            die();
        }

		// Route not found
        Router::display_if_response(Trash::send("404", $req->path));
    }

    /**
     * Return the first route that match a given name or path
     */
    public static function find(string $name_or_path): Route|bool
    {
        $all_routes = array_merge(self::$list, self::$temp);
        foreach ($all_routes as $r)
        {
            if ($r->path === $name_or_path) return $r;
            if ($r->name === $name_or_path) return $r;
        }
        return false;
    }
}