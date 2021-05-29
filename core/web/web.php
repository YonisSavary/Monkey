<?php

use Monkey\Storage\Config;
use Monkey\Framework\Router;
use Monkey\Web\Renderer;

/**
 * Put the app_url_prefix config key 
 * before the first parameter
 */
function url(string $target) : string
{
    $prefix = Config::get("app_url_prefix", "/");
    return str_replace("//", "/", $prefix."/".$target);
}


/**
 * Render another template where this function is called
 */
function render(string $template_name, array $vars=[]) : string
{
    return Renderer::render($template_name, $vars, true);
}


function router(string $name_or_path) : string
{
    return Router::find($name_or_path)->path ?? "/".$name_or_path;
}