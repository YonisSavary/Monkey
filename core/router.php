<?php 

namespace Monkey;

use Monkey\Web\Request;
use Monkey\Web\Response;
use Monkey\Web\Trash;
use stdClass;

class Router 
{
    public static $list = []; // Current routes of the framework
    public static $temp = []; // Not saved routes, used in `route_current` function



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
        Router::$list = array_values(Router::$list);
        Register::set("routes", Router::$list);
    }



    /**
     * Initialize the component :
     * - Create an empty routes list if inexistant
     * - Read the framework routes
     * - Add the admin interfaces route if enabled 
     */
    public static function init() : void
    {
        if (Register::get("routes") === null) Register::set("routes", []);
        Router::$list = Register::get("routes");
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
        $regex = preg_replace("/\{[A-Za-z0-9]\}/", ".+", $regex);
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
    public static function get_route(string $path, mixed $callback, string $name=null, array $middlewares=[],  array $methods=[]) : stdClass
    {
        $new_route = new stdClass();
        $new_route->path = $path;
        $new_route->callback = $callback;
        $new_route->name = $name;
        $new_route->methods = $methods;
        $new_route->middlewares = $middlewares;
        $new_route->regex = Router::get_regex($path);
        return $new_route;
    }
    



    /**
     * Add a temporary route in `Router::$temp`
     * 
     * @param string $path Route's Path (URL)
     * @param mixed $callback Route Callback (controllerName->methodName)
     * @param string $name Route Name (optionnal)
     * @param array $middlewares Routes Middlewares (optionnal) (classnames)
     * @param array $methods Routes HTTP methods (optionnal) 
     */
    public static function add_temp(string $path, mixed $callback, string $name=null, array $middlewares=[],  array $methods=[]) : void
    {
        $new_routes = Router::get_route($path, $callback, $name, $middlewares, $methods);
        array_push(Router::$temp, $new_routes);
    }



    
    /**
     * Add a route in `Router::$list` and save the framework's routes with `Register`
     * 
     * @param string $path Route's Path (URL)
     * @param mixed $callback Route Callback (controllerName->methodName)
     * @param string $name Route Name (optionnal)
     * @param array $middlewares Routes Middlewares (optionnal) (classnames)
     * @param array $methods Routes HTTP methods (optionnal) 
     */
    public static function add(string $path, mixed $callback, string $name=null, array $middlewares=[],  array $methods=[]) : void
    {
        $new_routes = Router::get_route($path, $callback, $name, $middlewares, $methods);
        array_push(Router::$list, $new_routes);
        Register::set("routes", Router::$list);
    }
    


    /**
     * Remove a route from the `Router::$list` array
     * 
     * @param string $nameOrRoute Can be either a route name of 
     * a route path, every maching routes are unsets
     */
    public static function remove(string $nameOrRoute)
    {
        foreach (Router::$list as &$route)
        {
            if ($route->path === $nameOrRoute || $route->name === $nameOrRoute)
            {
                unset($route);
            }
        }
    }

    /**
     * Check if a route exists (from the `Router::$list` array)
     * 
     * @param string $nameOrRoute Can be either a route name of 
     * a route path
     */
    public static function exists(string $nameOrRoute)
    {
        if ($nameOrRoute === '' || $nameOrRoute === null) return false;
        foreach (Router::$list as &$route)
        {
            if(($route->path === $nameOrRoute && $route->path!==null)
              ||($route->name === $nameOrRoute && $route->name!==null))
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
     * @example ``` Router::build_slugs("/example/{user_id}", "/example/123") => Array( "user_id"=>123 )
     */
    public static function build_slugs(string $route_path, string $request_path) : array
    {
        $route_parts = explode("/", $route_path);
        $request_parts = explode("/", $request_path);
        $slugs = [];

        for ($i=0; $i<count($route_parts); $i++)
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
     * When called, this function :
     * 1. get the Request parameter,
     * 2. check for a matching route
     * 3. create a `Request` object
     * 4. call a route callback if existing, with the `Request` object as an argument
     */
    public static function route_current() : void
    {
        $routes_all = array_merge(Router::$list, Router::$temp);
        $req_path = $_SERVER["REQUEST_URI"];
        $req_path = preg_replace("/\?.+/", "", $req_path);
        $req_method = $_SERVER["REQUEST_METHOD"];
        foreach($routes_all as $route)
        {
            if (!isset($route->path)) continue;
            if (!isset($route->regex)){
                $route->regex = Router::get_regex($route->path);
            }
            if (preg_match($route->regex, $req_path) === 0) continue;
            
            $to_execute = $route->callback;
                
            if (isset($route->methods) && count($route->methods) > 0)
            {
                if (!in_array($req_method, $route->methods)) continue;
            }

            $req = new Request();
            $req->path = $req_path;
            $req->method = $req_method;
            $req->slugs = Router::build_slugs($route->path, $req_path);
            
            if (isset($route->middlewares))
            {
                foreach ($route->middlewares as $middleware_str)
                {
                    $middleware_str = "Middlewares\\".$middleware_str;
                    if (!class_exists($middleware_str)) continue;
                    $middleware = new $middleware_str();
                    if (!method_exists($middleware, "handle")) continue;
                    $res = $middleware->handle($req);
                    if ($res instanceof Response)
                    {
                        $res->reveal();
                        die();
                    }
                }
            }

            $class = "Controllers\\".explode("->", $to_execute)[0];
            $method = explode("->", $to_execute)[1];
            if (!class_exists($class)) {
                //print_r(get_declared_classes());
                Trash::handle("$class does not exists !");
            }
            $controller = new $class();
            if (!method_exists($controller, $method)) Trash::handle("$method method does not exists !");
            $response = $controller->$method($req);
            if ($response instanceof Response)
            {
                $response->reveal();
            }
            die();
        }
        Trash::handle("\"".$req_path."\" route not found");
        //Response::json(Router::$list)->reveal();
    }
}