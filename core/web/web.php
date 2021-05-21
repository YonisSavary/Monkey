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
 * @deprecated use render() now
 */
function include_file(string $template_name)
{
    render($template_name);
}

/**
 * Render another template where this function is called
 */
function render(string $template_name){
    if (!isset($GLOBALS["render"])) $GLOBALS["render"] = [];
    return Renderer::render($template_name, $GLOBALS["render"], true);
}


function router(string $name_or_route) : string
{
    return Router::find($name_or_route)->path ?? "/".$name_or_route;
}