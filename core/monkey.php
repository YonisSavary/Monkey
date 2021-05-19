<?php 

$basefiles = [
	"core/framework/router.php",
	"core/framework/app_loader.php",
	"core/framework/middlewares.php",
	"core/framework/route.php",

	"core/web/web.php",
	"core/web/renderer.php",
	"core/web/request.php",
	"core/web/response.php",
	"core/web/trash.php",

	"core/storage/config.php",
	"core/storage/register.php",
	"core/storage/session.php",

	"core/dist/database.php",
	"core/dist/query.php",

	"core/models/model.php",
	"core/models/model_parser.php",

	"core/services/auth.php",
	
	"vendor/autoload.php"
];

foreach ($basefiles as $intern_file){
	if (file_exists($intern_file)) require_once $intern_file;
}


// Load Configuration And Caches
Monkey\Storage\Config::init();
Monkey\Storage\Register::init();

// Load Applications
Monkey\Framework\AppLoader::init();

// Session is loaded after your application, so 
// you can avoid PHP incomplete classes in your session
Monkey\Storage\Session::init();

Monkey\Framework\Router::init();

// Services
Monkey\Dist\DB::init();
Monkey\Services\Auth::init();
