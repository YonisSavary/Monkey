<?php 

namespace Monkey\Framework;

use Monkey\Storage\Register;
use Monkey\Web\Request;
use Monkey\Web\Response;
use Monkey\Web\Trash;
use stdClass;

class Router 
{
    public static $enabled_groups = [];
    public static $list = []; // Current routes list
    public static $temp = []; // Not saved routes, used in `route_current` function


    /**
     * Enable a global middleware 
     * that will be put on every added routes
     * until it is disabled
     */
    public static function start_group(string|array $names)
    {
        if (is_array($names))
        {
            foreach ($names as $n) 
            {
                self::start_group($n);
            }
        }
        else 
        {
            array_push(self::$enabled_groups, $names);
        }
    }


    public static function end_group(string|array $names)
    {
        if (!is_array($names)) $names = [$names];
        self::$enabled_groups = array_diff(self::$enabled_groups, $names);
    }

    public static function end_all_groups()
    {
        self::$enabled_groups = [];
    }

    public static function get_groups()
    {
        return self::$enabled_groups;
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
        $regex = preg_replace("/\{[A-Za-z0-9_.\-]+\}/", ".+", $regex);
        $regex = "/^$regex$/";
        return $regex;
    }


    /**
     * Create a new route array and return it
     * 
     * @param string $path Route's Path (URL)
     * @param mixed $callback Route Callback (controllerName->methodName)
     * @param string $name Route Name (optionnal)
     * @param array $middlewares Routes Middlewares (optionnal) (classnames)
     * @param array $methods Routes HTTP methods (optionnal) 
     * @return stdClass the new route
     */
    public static function get_route(string $path, mixed $callback, string $name=null, array $middlewares=[],  array $methods=null) : stdClass
    {
        $new_route = new stdClass();
        $new_route->path = $path;
        $new_route->callback = $callback;
        $new_route->name = $name;
        $new_route->methods = $methods;
        $new_route->middlewares = $middlewares;
        $new_route->regex = self::get_regex($path);
        return $new_route;
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
        $middlewares = array_merge($middlewares, self::$enabled_groups);
        $new_routes = self::get_route($path, $callback, $name, $middlewares, $methods);
        array_push(self::$temp, $new_routes);
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
        $new_routes = self::get_route($path, $callback, $name, $middlewares, $methods);
        array_push(self::$list, $new_routes);
        Register::set("routes", self::$list);
    }
    

    /**
     * Remove a route from the `self::$list` array
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
     * Given a route path and a request path, this 
     * function return a slug array, slugs are defined
     * by a string between braces in a route path
     * 
     * @param string $route_path Path of the Route Object 
     * @param string $request_path Path of the HTTP Request
     * @return array Slugs array
     * @example simple_use self::build_slugs("/example/{user_id}", "/example/123") => Array( "user_id"=>123 )
     */
    public static function build_slugs(string $route_path, string $request_path) : array
    {
        $route_parts = explode("/", $route_path);
        $request_parts = explode("/", $request_path);
        $slugs = [];

        for ($i=0; $i < count($route_parts); $i++)
        {
            $pattern = $route_parts[$i];
            if (!preg_match("/\{.+\}/", $pattern)) continue;
            $slug_name = preg_replace("/[\{\}]/", "", $pattern);
            $slug_value = $request_parts[$i];
            $slugs[$slug_name] = $slug_value;
        }
        return $slugs;
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
    public static function execute_route_callback(string|callable $to_execute, mixed $custom_args=null) : mixed
    {
		if (is_callable($to_execute)) return $to_execute(Request::current());
        // Discompose the `callback` attribute
        $callback_parts = explode("->", $to_execute);
        $controller_class = $callback_parts[0];
        $method = $callback_parts[1];

        // Create the controller and execute the route callback
        if (!class_exists($controller_class, false)) $controller_class = "Controllers\\".$controller_class;
        if (!class_exists($controller_class, true)) return Trash::fatal("$controller_class does not exists !");
        $controller = new $controller_class();

        if (!method_exists($controller, $method)) return Trash::fatal("$method method does not exists !");
        return $controller->$method( $custom_args ?? Request::current());
    }


	/**
	 * Given a middleware name, this functions return what the middleware
	 * will return (every middleware handle function is called with 
	 * the current request as first parameter)
	 */
	public static function execute_middleware(string $middleware_name) : mixed
	{
		if (is_callable($middleware_name)) return $middleware_name(Request::$current);
	
        if (!class_exists($middleware_name, false)) $middleware_name = "Middlewares\\".$middleware_name;
		if (!class_exists($middleware_name, true)) return Trash::fatal("$middleware_name does not exists!");

		$middleware = new $middleware_name();
		if (!method_exists($middleware, "handle")) return Trash::fatal("$middleware_name does not have a 'handle' function");
		
		return $middleware->handle(Request::current());
	}

    
    /**
     * When called, this function :
     * 1. get the Request parameter,
     * 2. check for a matching route
     * 3. create a `Request` object
     * 4. call a route callback if existing, with the `Request` object as an argument
     */
    public static function route_current(bool $return_response=false, Request $forced_request=null)
    {
        $routes_all = array_merge(self::$list, self::$temp);
		$req = $forced_request ?? Request::build();

        foreach($routes_all as $route)
        {
            if (!isset($route->path)) continue;
            if (!isset($route->regex)) $route->regex = self::get_regex($route->path);
            if (preg_match($route->regex, $req->path) === 0) continue;
            
            if (count($route->methods ?? []) > 0)
            {
                if (!in_array($req->method, $route->methods)) continue;
            }

			$req->slugs = self::build_slugs($route->path, $req->path);
            Request::$current = $req;

			// Executing Callbacks
			foreach ($route->middlewares ?? [] as $middleware_name)
			{
				$res = self::execute_middleware($middleware_name);
				self::display_if_response($res);
                if ($res instanceof Request) Request::$current = $res;
			}

			$response = self::execute_route_callback($route->callback);
			if ($return_response) return $response;
            self::display_if_response($response);
			
            die();
        }
		// Route not found
        Trash::send("404", $req->path);
    }
}