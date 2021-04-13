<?php

use Monkey\AppLoader;
use Monkey\Router;
use Monkey\Web\API;
use Monkey\Web\Request;
use Monkey\Web\Response;

/**
 * Load Monkey and its components
 * but also load vendor.php if existants
 * 
 * Also, monkey.php place you at your project dir ('..')
 */
require_once "../core/monkey.php";



/**
 * If your application contains only a few
 * routes, you can add them here with Monkey\Router::add_temp()
 * (don't forget 'add()' has a permanent effect, don't use it !)
 */

/* Route Example 
Router::add_temp("/", function(Request $req){
    return Response::json(["status"=>"It's does works"]);
});
//*/

Router::add_temp("/oui", function(Request $req){
    return Response::html($req->retrieveOne("page", Request::GET, true));
});

/**
 * As this function is called, Monkey\Router
 * analyse your Request and begin it lifecycle
 */
Router::route_current();