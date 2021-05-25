<?php

use Monkey\Web\Trash;


register_shutdown_function( fn()=> Trash::fatal());



if (str_ends_with(getcwd(), "public")) chdir("..");

// Requiring AppLoader to load the Framework
require_once "core/framework/app_loader.php";

// Load the Framework Files except for Test Files
$basefiles = Monkey\Framework\AppLoader::explore_full_dir("./core");
foreach ($basefiles as $intern_file)
{
	if(strpos($intern_file, "core/tests") == false) require_once $intern_file;
} 

// Loading Vendor Files
if (file_exists("vendor/autoload.php")) require_once "vendor/autoload.php";



/*       _________________________________________
	* _____/   INTITIALISING FRAMEWORKS COMPONENTS   \_____ */

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