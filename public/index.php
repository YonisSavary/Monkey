<?php

use Models\User;
use Monkey\Framework\Router;
use Monkey\Model\ModelFetcher;
use Monkey\Web\Request;
use Monkey\Web\Response;

/**
 * Load Monkey and it components
 * but also load vendor.php if existants
 * 
 * Also, we change the directory to the project directory
 * (which is REALLY IMPORTANT, don't worry it shouldn't 
 * allow access to local files)
 */
require_once "../core/monkey.php";


/**
 * It is advised to create a file to store your routes :
 * (for example something like ./app/routes/routes.php)
 * and to put your routes in it
 * 
 * AppLoader::AUTOLOAD_DIRECTORIES_NAMES contains every 
 * directories names that will be loaded automatically 
 */


/* Basic Route Example */
Router::add("/", function(Request $req){
    ModelFetcher::fetch("user", null, true);
    
    return Response::json(["status"=>"It's does works !"]);
});

/* Route with Slug Example ! */
Router::add("/foo/{some_sentence}", function(Request $req, string $some_sentence){
    // You can also get $req->slugs["some_sentence"]
    return Response::json(["status"=>"Here is what you typed : $some_sentence !"]);
});



/**
 * As this function is called, Monkey\Framework\Router
 * look into the given Request and begin it lifecycle
 */
$current_request = Request::build();
Router::route($current_request);