<?php 

namespace Monkey\Services;

use Monkey\Framework\Router;
use Monkey\Web\Request;
use Monkey\Web\Response;
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
        [AutoCRUD::MODE_UPDATE, "update", ["PUT", "PATCH"]],
        [AutoCRUD::MODE_DELETE, "delete", ["DELETE"]],
    ];

    const ROUTE_NAME_MODEL = 1;
    const ROUTE_NAME_LOWER = 2;
    const ROUTE_NAME_TABLE = 3;

    static $middlewares = [];
    static $methods = null;

    public static function create(Request $req, string $model): Response
    {
        AutoCRUD::fix_model_classname($model);
        $fields = $model::get_insertable();
        $values = $req->retrieve($fields);
        $model::magic_insert($values)->execute();
        return Response::json(["status"=>"done"]);
    }

    public static function read(Request $req, string $model): Response
    {
        AutoCRUD::fix_model_classname($model);
        $params = array_merge($req->get, $req->post);
        $query = $model::get_all();
        
        if (count($params) == 0) return Response::json(["status"=>"error", "message"=>"No parameter"]); 

        foreach ($params as $field => $value)
        {
            $query->where($field, $value);
        }

        return Response::json($query->execute());
    }

    public static function update(Request $req, string $model): Response
    {
        AutoCRUD::fix_model_classname($model);
        $primary = $model::get_primary_key();
        $primary_value = $req->retrieve($primary);
        if ($primary_value === null) return Response::json(["status"=>"error", "message"=>"No $primary specified !", "details"=>$req->body]);

        $subject = $model::get_all()->where($primary, $primary_value)->limit(1)->execute();
        if (count($subject) == 0) return Response::json(["status"=>"error", "message"=>"No subject found ! Probably a bad $primary value"]);
        $subject = $subject[0];

        $params = array_merge($req->get, $req->post);
        
        if (count($params) == 1) return Response::json(["status"=>"error", "message"=>"No parameter"]);

        foreach ($params as $field => $value)
        {
            if ($field == $primary) continue;
            if (!$subject::has_fields($field)) continue;
            $subject->$field = $value;
        }
        
        $subject->save(true)->execute();

        return Response::json(["status"=>"done"]);
    }


    public static function delete(Request $req, string $model): Response
    {
        AutoCRUD::fix_model_classname($model);
        
        $query = $model::delete_from();
        $params = array_merge($req->get, $req->post);
        
        if (count($params) == 0) return Response::json(["status"=>"error", "message"=>"No parameter"]);
        
        foreach ($params as $field => $value){
            $query->where($field, $value);
        }
        
        $query->execute();

        return Response::json(["status"=>"done"]);
    }


    public static function fix_model_classname(string &$model_class)
    {
        if (!class_exists($model_class)) $model_class = "Models\\" . $model_class;
        if (!class_exists($model_class)) Trash::fatal("Inexistant model class name ! ($model_class)");
    }


    public static function set_middlewares(array|string $middlewares){
        if (!is_array($middlewares)) $middlewares = [$middlewares];
        self::$middlewares = $middlewares;
    }


    public static function set_methods(array|string $methods){
        if (!is_array($methods)) $methods = [$methods];
        self::$methods = $methods;
    }


    public static function add_specific_route(
        string $url_model_name, 
        string $mode, 
        array $default_methods,
        string $class_name)
    {
        Router::add(
            "/$url_model_name/$mode/", 
            fn(Request $req)=> AutoCRUD::$mode($req, $class_name),
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
        $original_classname = $model_class;
        $model_sample = new $model_class();

        //$model_class = preg_replace("/^.+\\/", "", $model_class);
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

        foreach (AutoCRUD::CRUD_CONFIG as $mode)
        {
            if (($allowed_modes & $mode[0]) > 0) 
            {
                AutoCRUD::add_specific_route($model_class, $mode[1], $mode[2], $original_classname);
            }
        }
    }
}