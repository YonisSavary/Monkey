<?php

use Monkey\Config;
use Monkey\Router;
use Monkey\Web\Renderer;

function url(string $file)
{
    $prefix = Config::get("app_prefix");
    if ($prefix === null) $prefix = "/";
    if (substr($prefix, -1) !== "/") $prefix .= "/";
    return $prefix.$file;
}

function include_file(string $template_name)
{
    return Renderer::render($template_name, $GLOBALS["m_render_vars"], false);
}

function router(string $routeName)
{
    $all_route = array_merge(Router::$list, Router::$temp);
    foreach ($all_route as $r)
    {
        if ($routeName === $r["name"]) return $r["path"];
    }
    return "/". $r["path"];
}