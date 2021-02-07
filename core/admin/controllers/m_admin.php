<?php 

namespace Controllers;

use Monkey\Router;
use Monkey\Web\Renderer;

class m_admin
{
    public function redirect()      { Router::redirect(router("m_index")); }
    public function configuration() { return Renderer::render("m_configuration"); }
    public function documentation() { return Renderer::render("m_documentation"); }
    public function index()         { return Renderer::render("m_index"); }
    public function model()         { return Renderer::render("m_model"); }
    public function route()         { return Renderer::render("m_route"); }
}