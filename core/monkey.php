<?php

/**
 *       _____________________________
 * _____/   LOADING FRAMEWORK FILES   \_____
 */


/**
 * Here's a little lesson in trickery !
 * We use the framework to load the framework
 * 
 * "AppLoader::explore_full_dir" gives us a full list of files
 * inside a directory (recursive function)
 * 
 * So we just have to load this single file, tell it 
 * to explore the core directory, reject tests directory and 
 * load every other framework files 
 */

chdir("..");
require_once "core/framework/app_loader.php";

$basefiles = Monkey\Framework\AppLoader::explore_full_dir("./core");

foreach ($basefiles as $intern_file){
	if(strpos($intern_file, "core/tests") == false) require_once $intern_file;
} 


/**
 *       ___________________________
 * _____/   LOADING VENDOR FILES   \_____
 */


if (file_exists("vendor/autoload.php")) require_once "vendor/autoload.php";


/**
 *       _________________________________________
 * _____/   INTITIALISING FRAMEWORKS COMPONENTS   \_____
 */

// Load Configuration And Caches
Monkey\Storage\Config::init();
Monkey\Storage\Register::init();

// Load Application(s)
Monkey\Framework\AppLoader::init();

// Session is loaded after your application files, so 
// you can avoid PHP incomplete classes in your session
Monkey\Storage\Session::init();

Monkey\Framework\Router::init();
Monkey\Dist\DB::init();
Monkey\Services\Auth::init();
