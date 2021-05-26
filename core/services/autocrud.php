<?php 

namespace Monkey\Services;

use Monkey\Framework\AppLoader;
use Monkey\Framework\Router;
use Monkey\Web\Request;
use Monkey\Web\Trash;

class AutoCRUD 
{
    const MODE_ALL    = 0b11111111;
    const MODE_CREATE = 0b00000001;
    const MODE_READ   = 0b00000010;
    const MODE_UPDATE = 0b00000100;
    const MODE_DELETE = 0b00001000;

    const CRUD_CONFIG = [
        [AutoCRUD::MODE_CREATE, "create", ["POST"]],
        [AutoCRUD::MODE_READ  , "read"  , ["GET"]],
        [AutoCRUD::MODE_UPDATE, "update", ["PUT", ["PATCH"]]],
        [AutoCRUD::MODE_DELETE, "delete", ["DELETE"]],
    ];

    const ROUTE_NAME_MODEL = 1;
    const ROUTE_NAME_LOWER = 2;
    const ROUTE_NAME_TABLE = 3;

    static $middlewares = [];
    static $methods = null;

    public static function create(Request $req, string $model)
    {
        AutoCRUD::fix_model_classname($model);
    }

    public static function read(Request $req, string $model)
    {
        AutoCRUD::fix_model_classname($model);
    }

    public static function update(Request $req, string $model)
    {
        AutoCRUD::fix_model_classname($model);
    }

    public static function delete(Request $req, string $model)
    {
        AutoCRUD::fix_model_classname($model);
    }


    public static function fix_model_classname(string &$model_class)
    {
        if (class_exists($model_class, false)) $model_class = "Models\\" . $model_class;
        if (class_exists($model_class)) Trash::fatal("Inexistant model class name ! ($model_class)");
    }

    public static function set_middlewares(array $middlewares){
        self::$middlewares = $middlewares;
    }

    public static function set_methods(array $methods){
        self::$methods = $methods;
    }


    public static function add_route(string $url_model_name, string $mode, array $default_methods=["GET"])
    {
        Router::add(
            "/api/$url_model_name/{mode}/", 
            [AutoCRUD::class, $mode],
            "autocrud_".$url_model_name."_".$mode,
            AutoCRUD::$middlewares,
            AutoCRUD::$methods ?? $default_methods
        );
    }

    public static function add(
        string $model_class, 
        int $allowed_modes=AutoCRUD::MODE_ALL, 
        int $route_name_mode=AutoCRUD::ROUTE_NAME_TABLE
    )
    {
        AutoCRUD::fix_model_classname($model_class);
        $model_sample = new $model_class();

        $model_class = preg_replace("/.+\\/", "", $model_class);
        switch ($route_name_mode)
        {
            case self::ROUTE_NAME_LOWER:
                $model_class = strtolower($model_class);
                break;
            case self::ROUTE_NAME_TABLE:
                $model_class = $model_sample->get_table();
                break;
            case self::ROUTE_NAME_MODEL:
            default : break;
        }

        foreach (AutoCRUD::CRUD_CONFIG as $mode){
            if ($mode[0] & $allowed_modes > 0) {
                AutoCRUD::add_route($model_class, $mode[1], $mode[2]);
            }
        }
    }
}