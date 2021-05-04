<?php

use Monkey\Router;
use Monkey\Web\Request;
use Monkey\Web\Response;

/**
 * Load Monkey and it components
 * but also load vendor.php if existants
 * 
 * Also, monkey.php change the directory to the project directory
 */
require_once "../core/monkey.php";



/**
 * If your application contains only a few
 * routes, you can add them here with Monkey\Router::add()
 * (don't forget 'add()' has a permanent effect !)
 */

/* Basic Route Example */

Router::add("/", function(Request $req){
    return Response::json(["status"=>"It's does works"]);
});




/**
 * As this function is called, Monkey\Router
 * look into the Request and begin it lifecycle
 */
Router::route_current();