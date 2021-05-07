<?php 

$basefiles = [
	"core/web/web.php",
	"core/web/renderer.php",
	"core/web/request.php",
	"core/web/response.php",
	"core/web/trash.php",
	"core/config/config.php",
	"core/config/register.php",
	"core/config/session.php",
	"core/app_loader.php",
	"core/router.php",
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
Monkey\Session::init();
Monkey\Config::init();
Monkey\Register::init();

// Load Applications
Monkey\AppLoader::init();

Monkey\Router::init();

// Services
Monkey\Dist\DB::init();
Monkey\Services\Auth::init();
