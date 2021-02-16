<?php 

require_once "core/web/web.php";
require_once "core/web/renderer.php";
require_once "core/web/request.php";
require_once "core/web/response.php";
require_once "core/web/trash.php";
require_once "core/web/api.php";

require_once "core/config/config.php";
require_once "core/config/register.php";

require_once "core/app_loader.php";
require_once "core/router.php";

require_once "core/dist/database.php";
require_once "core/dist/query.php";

require_once "core/models/model.php";
require_once "core/models/model_parser.php";

require_once "core/services/auth.php";

Monkey\Register::init();
Monkey\Config::init();
Monkey\Router::init();

Monkey\AppLoader::init();

Monkey\Dist\DB::init();
Monkey\Services\Auth::init();


if (file_exists("vendor/autoload.php")){
    require_once "vendor/autoload.php";
}