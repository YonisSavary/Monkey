<?php

use Monkey\Storage\Config;
use Monkey\Framework\Router;
use Monkey\Web\Renderer;

function url(string $file) : string
{
    $prefix = Config::get("app_prefix", "/");
    return str_replace("//", "/", $prefix."/".$file);
}


/**
 * Render another template where this function is called
 */
function render(string $template_name, array $vars=[]) : string
{
    return Renderer::render($template_name, $vars, true);
}


function router(string $name_or_route) : string
{
    return Router::find($name_or_route)->path ?? "/".$name_or_route;
}