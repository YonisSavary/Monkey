<?php 

namespace Controllers;

use Monkey\Register;
use Monkey\Router;
use Monkey\Web\Request;
use Monkey\Web\Response;
use Monkey\Web\API;

class m_api_route
{
    public static function clean_a_route(&$route)
    {
        if ($route["middlewares"] === [""]) $route["middlewares"] = [];
        if ($route["methods"] === [""]) $route["methods"] = [];
        if ($route["name"] === "") $route["name"] = null;
    }

    public static function check_route_integrity(array &$route_to_check, bool $check_existant=false): mixed
    {
        $needed = ["path", "callback", "name", "methods", "middlewares"];
        foreach($needed as $key)
        {
            if (!isset($route_to_check[$key])) API::error("Needed keys are " . print_r($needed, true) . "Stuck on $key");
        }

        if ($check_existant === true)
        {
            if (Router::exists($route_to_check["path"]))  {
                return API::error("Route with path = '".$route_to_check["path"]."' already exists !");
            }
            if (Router::exists($route_to_check["name"]))  {
                return API::error("Route with name = '".$route_to_check["name"]."' already exists !");
            }
        }
        return null;
    }



    /**
     * @api
     */
    public function create(Request $req): Response
    { 
        $params = API::retrieve($req, ["route"]);
        $new_route = (array) json_decode($params["route"]);
        
        $error = m_api_route::check_route_integrity($new_route);
        if ($error instanceof Response) return $error;
        m_api_route::clean_a_route($new_route);
        Router::add($new_route["path"], $new_route["callback"], $new_route["name"], $new_route["middlewares"], $new_route["methods"]); 
        return API::ok();
    }




    /**
     * @api
     */
    public function read(): Response
    {
        return Response::json(Router::$list); 
    }
    


    /**
     * @api
     */
    public function update(Request $req): Response
    {
        $params = API::retrieve($req, ["index", "route"]);

        $route_index = intval($params["index"]);
        $new_route = (array) json_decode($params["route"]);
        
        m_api_route::clean_a_route($new_route);
        m_api_route::check_route_integrity($new_route);

        if (!isset(Router::$list[$route_index])) API::error("inexistant route !");
        $new_route = Router::get_route($new_route["path"], $new_route["callback"], $new_route["name"], $new_route["middlewares"], $new_route["methods"]);
        Router::$list[$route_index] = $new_route;
        Router::save();
        return API::ok();
    }


    /**
     * @api
     */
    public function delete(Request $req): Response
    {
        $params = API::retrieve($req, ["index"]);
        $route_index = intval($params["index"]);
        if (!isset(Router::$list[$route_index])) API::error("inexistant route !");
        unset(Router::$list[$route_index]);
        Router::save();
        return API::ok();
    }


    public static function got_everything(&$routeObject)
    {
        if (!isset($routeObject->middlewares)) $routeObject->middlewares = [];
        if (!isset($routeObject->methods)) $routeObject->methods = [];
        if (!isset($routeObject->name)) $routeObject->name = null;
        return (isset($routeObject->path) && isset($routeObject->callback));
    }

    public function import(Request $req)
    {
        $content = file_get_contents($req->files["routesfiles"]["tmp_name"]);
        $content = json_decode($content);
        
        if (isset($content->path))
        {
            // only one route is sent
            if (m_api_route::got_everything($content))
            {
                $r = $content;
                if (!Router::exists($r->path)) Router::add($r->path, $r->callback, $r->name, $r->middlewares, $r->methods);
            }
        } else if ( is_array($content)) {
            // multiples routes were sent
            foreach($content as $r)
            {
                if (m_api_route::got_everything($r))
                {
                    if (!Router::exists($r->path)) Router::add($r->path, $r->callback, $r->name, $r->middlewares, $r->methods);
                }
            }
        }

        Router::redirect(router("m_route"));
    }

    public function export()
    {
        if (!is_dir("tmp")) mkdir("tmp");
        $routes = Router::$list;
        foreach ($routes as &$r ) { 
            if ($r["name"] === null) unset($r["name"]);
            if ($r["middlewares"] === []) unset($r["middlewares"]);
            if ($r["methods"] === []) unset($r["methods"]);
            unset($r["regex"]); 
        }
        $content = json_encode($routes, JSON_PRETTY_PRINT);
        $content = str_replace("\\/", "/", $content);
        file_put_contents("tmp/export_routes.json", $content);
        Response::send_file("tmp/export_routes.json", true);
    }

}