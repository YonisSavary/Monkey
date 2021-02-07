<?php

namespace Middlewares;

use Monkey\Config;
use Monkey\Web\Request;
use Monkey\Web\API;

class m_middle_password
{
    public function handle(Request &$req)
    {
        $params = API::retrieve($req, ["password"]);
        $password = Config::get("admin_api_password");
        if ($params["password"] !== $password) return API::error("API Password is Invalid");
        return null;
        // Everything's fine !
    }
}