<?php 

namespace Monkey\Framework;

use Closure;
use Monkey\Storage\Register;
use Monkey\Web\Request;
use Monkey\Web\Response;
use Monkey\Web\Trash;
use Monkey\Framework\Route;
use Monkey\Services\Logger;
use TypeError;

class Router 
{
	const DEFAULT_GROUPS =  [
		"path" => [],
		"middlewares" => [],
		"methods" => []
	];

    public static $groups = self::DEFAULT_GROUPS;
    public static $routes = []; // Current routes list


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
		foreach (array_keys(self::$groups) as $key)
        {
            if (!isset($groups[$key])) continue;
            if (!is_array($groups[$key])) $groups[$key] = [$groups[$key]];
			self::$groups[$key] = array_merge(self::$groups[$key], $groups[$key]);
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
		foreach (array_keys(self::$groups) as $key)
        {
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
        Logger::text("Redirecting to $path", Logger::FRAMEWORK);
        header("Location: $path");
        exit(0);
    }



    /**
     * Initialize the component :
     * - Create an empty routes list if inexistant
     * - Read the framework routes
     */
    public static function init() : void
    {
        self::$routes = array_merge(self::$routes,  Register::get("routes", []));
    }



	/**
     * Add a temporary route in `self::$routes`
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
        array_push(self::$routes, $new_route);
    }

    

    /**
     * Remove a route from the `Router::$routes` array
     * 
     * @param string $name_or_path Can be either a route name of 
     * a route path, every maching routes are unsets
     */
    public static function remove(string $name_or_path)
    {
        foreach (self::$routes as &$route)
        {
            if ($route->path === $name_or_path 
            ||  $route->name === $name_or_path )
            {
                unset($route);
            }
        }
    }


    /**
     * Return the first route that match a given name or path
     */
    public static function find(string $name_or_path): Route|bool
    {
        foreach (self::$routes as $r)
        {
            if ($r->path === $name_or_path) return $r;
            if ($r->name === $name_or_path) return $r;
        }
        return false;
    }


    /**
     * Check if a route exists (from the `self::$routes` and 'self::$routes' array)
     * @param string $name_or_path Can be either a route name of  a route path
     */
    public static function exists(string $name_or_path)
    {
        return (Router::find($name_or_path) !== false);
    }


    /**
     * Call a Route callback and return the value
     */
    public static function execute_route_callback(string|Closure|array $to_execute, ...$custom_args) : mixed
    {
        $current_request = Request::current();
        $parameters = ($custom_args !== [])? $custom_args : array_values($current_request->slugs);

		if (is_callable($to_execute)) return $to_execute($current_request, ...$parameters);

        // Discompose the `callback` attribute
		$callback_parts = (is_string($to_execute))?  explode("->", $to_execute) : $to_execute;
		$controller_class = $callback_parts[0];
		$method = $callback_parts[1];
		
        // Create the controller and execute the route callback
        if (!class_exists($controller_class, false)) $controller_class = "Controllers\\".$controller_class;
        if (!class_exists($controller_class, true)) Trash::fatal("\"$controller_class\" class does not exists !");
        $controller = new $controller_class();

		// Execute the controller callback
        if (!method_exists($controller, $method)) Trash::fatal("\"$controller_class->$method\" method does not exists !");
        return $controller->$method($current_request, ...$parameters);
    }


	/**
	 * Given a middleware name, this functions return what the middleware
	 * will return (every middleware handle function is called with 
	 * the current request as first parameter)
	 */
	public static function execute_middleware(string|Closure $middleware_name) : mixed
	{
		if (is_callable($middleware_name)) return $middleware_name(Request::$current);
		
        if (!class_exists($middleware_name, false)) $middleware_name = "Middlewares\\".$middleware_name;
        if (!class_exists($middleware_name, true )) Trash::fatal("$middleware_name does not exists!");
        $middleware = new $middleware_name();

        if (!method_exists($middleware, "handle")) Trash::fatal("$middleware_name does not have a 'handle' function");
        return $middleware->handle(Request::current());
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
        Request::set_current($req);
        Logger::text( $req->method . " " . $req->path, Logger::FRAMEWORK);

        $bad_method_route = null; // Store the latest bad method route for 405 error
        $type_error_route = null; // Store the laster route for bad slugs type
        $type_error_message = "";

        foreach(self::$routes as $route)
        {
			if (! $route instanceof Route ) continue;
            if (! $route->match($req) ) continue;
            
			// We can't just do the in_array condition, as we must 
			// check methods only if the route has a method constraint
            if (count($route->methods ?? []) > 0 
            &&  !in_array($req->method, $route->methods) )
            {
                $bad_method_route = $route;
                continue;
            }

			$req->build_slugs($route->path);


            Hooks::execute_event("pre_middlewares");
			// Executing Callbacks
			foreach ($route->middlewares ?? [] as $middleware_name)
			{
				$res = self::execute_middleware($middleware_name);

                // Middlewares can either return a response (that will be displayed)
                // Or a edited Request (that will replace the current one)
                Response::reveal_if_response($res);
                if ($res instanceof Request) Request::set_current($res);
			}

            Hooks::execute_event("post_middlewares");
            Hooks::execute_event("pre_controllers");

            try 
            {
                $response = self::execute_route_callback($route->callback);
                Hooks::execute_event("post_controllers");
                if ($return_response) return $response;
                Response::reveal_if_response($response); 
                exit(0);            
            }
            catch (TypeError $e)
            {
                $type_error_route = $route;
                $type_error_message = $e->getMessage();
            }
        }

        if ($bad_method_route !== null) Trash::send("405", $bad_method_route->path, $req->method);
        if ($type_error_route !== null) Trash::send("400", $type_error_message);
        Trash::send("404", $req->path);
    }
}