<?php 

namespace Controllers;

use Model\Users;
use Monkey\AppLoader;
use Monkey\Router;
use Monkey\Web\Response;

class m_api_index
{
    /**
     * @api
     */
    public function read(): Response
    {
        return Response::json([
            "Route Number" => count(Router::$list),
            "Temporary Routes" => count(Router::$temp),
            "Views Directories" => AppLoader::$views_directory,
            "Apploader Blacklist"=>AppLoader::$app_loader_blacklist,
            "Apploader Autoload List"=>AppLoader::$autoload_list
        ]);
    }

}