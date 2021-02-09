<?php 

namespace Controllers;

use Monkey\Config;
use Monkey\Router;
use Monkey\Web\API;
use Monkey\Web\Renderer;
use Monkey\Web\Request;
use Monkey\Web\Response;

class m_admin
{
    public function redirect()      { Router::redirect(router("m_index")); }
    public function configuration() { return Renderer::render("m_configuration"); }
    public function documentation() { return Renderer::render("m_documentation"); }
    public function index()         { return Renderer::render("m_index"); }
    public function model()         { return Renderer::render("m_model"); }
    public function route()         { return Renderer::render("m_route"); }
    public function guard()         { return Renderer::render("m_guard"); }

    public function guard_attempt(Request $req) {
        $params = API::retrieve($req, ["password"], API::POST);
        $password = $params["password"];
        if ($password === "" || $password === null){
            $this->disconnect();
        }

        if ($password === Config::get("admin_password")){
            session_start();
            $_SESSION["m_admin_logged"] = true;
            return Router::redirect(router("m_index"));
        } else {
            $this->disconnect();
        }
    }

    public function disconnect(){
        session_start();
        $_SESSION["m_admin_logged"] = false;
        return Router::redirect(router("m_guard_page"));
    }
}