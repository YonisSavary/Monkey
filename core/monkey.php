<?php

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

register_shutdown_function( function(){
    Monkey\Framework\Hooks::execute_event("shutdown");

    $fatal_error = error_get_last();
    // If a custom_message is given, it means a fatal error was manually called, so we display it
    // If no error happenned, we don't have something to debug then (it means everything went fine)
    if (!is_null($fatal_error))
	{
		Monkey\Web\Trash::fatal($fatal_error["message"]);
	}
});

/*       _________________________________________
	* _____/   INTITIALISING FRAMEWORKS COMPONENTS   \_____ */

// Load Configuration And Caches
Monkey\Storage\Config::init();
Monkey\Storage\Storage::init();
Monkey\Storage\Cache::init();

// Load Application(s)
Monkey\Framework\AppLoader::init();

// Session is loaded after your application files, so 
// you can avoid PHP incomplete classes in your session
Monkey\Storage\Session::init();

Monkey\Framework\Router::init();
Monkey\Dist\DB::init();
Monkey\Services\Auth::init();

Monkey\Services\Logger::init();

Monkey\Framework\Hooks::execute_event("loaded");
Monkey\Framework\Hooks::execute_event("initialized");