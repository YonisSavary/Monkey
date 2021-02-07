<?php 

namespace Controllers;

use Monkey\Config;
use Monkey\Register;
use Monkey\Web\Request;
use Monkey\Web\Response;
use Monkey\Web\Trash;
use Monkey\Web\API;

class m_api_configuration
{
    /**
     * @api
     */
    public function create(Request $req): Response
    {
        $params = API::retrieve($req, ["key", "value"]);
        if (Config::exists($params["key"])) API::error($params["key"]." Key already exists !");
        Config::set($params["key"], $params["value"]);
        Config::save();
        return API::ok();
    }

    /**
     * @api
     */
    public function read(): Response
    {
        return Response::json($GLOBALS["monkey"]["config"]);
    }

    /**
     * @api
     */
    public function update(Request $req): Response
    {
        $params = API::retrieve($req, ["key", "value"]);
        if (!Config::exists($params["key"])) API::error("Inexistant Key !");
        $key = $params["key"];
        $value = $params["value"];
        Config::set($key, $value);
        Config::save();
        return API::ok();
    }

    /**
     * @api
     */
    public function delete(Request $req): Response
    {
        $params = API::retrieve($req, ["deletions"]);
        Config::save(explode(",", $params["deletions"]));
        return API::ok();
    }
}