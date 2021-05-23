<?php

use Monkey\Framework\Router;
use Monkey\Storage\Config;
use Monkey\Web\Request;
use Monkey\Web\Response;
use Monkey\Web\Trash;

/**
 * Load Monkey and it components
 * but also load vendor.php if existants
 * 
 * Also, we change the directory to the project directory
 * (which is REALLY IMPORTANT, don't worry it shouldn't 
 * allow access to local files)
 */

// Don't put this line in core/monkey.php, as we need it for 
// Phpunit tests, you don't want to change directory in your tests
chdir("..");

require_once "core/monkey.php";


/**
 * If your application contains only a few
 * routes, you can add them here with Monkey\Framework\Router::add()
 */


/* Basic Route Example */
Router::add("/", function(Request $req){
    return Response::json(["status"=>"It's does works !"]);
});



/**
 * As this function is called, Monkey\Framework\Router
 * look into the given Request and begin it lifecycle
 */
Router::route(Request::build());