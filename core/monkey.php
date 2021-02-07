<?php 

require_once "core/components/web/web.php";
require_once "core/components/web/renderer.php";
require_once "core/components/web/request.php";
require_once "core/components/web/response.php";
require_once "core/components/web/trash.php";
require_once "core/components/web/api.php";

require_once "core/components/config/config.php";
require_once "core/components/config/register.php";

require_once "core/components/app_loader.php";
require_once "core/components/router.php";

require_once "core/components/models/database.php";
require_once "core/components/models/model.php";
require_once "core/components/models/model_parser.php";
require_once "core/components/models/query.php";

require_once "core/components/services/auth.php";

Monkey\Config::init();
Monkey\Register::init();
Monkey\AppLoader::init();
Monkey\DB::init();
Monkey\Router::init();

Monkey\Services\Auth::init();

if (file_exists("vendor/autoload.php")){
    require_once "vendor/autoload.php";
}