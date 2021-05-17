<?php

use Monkey\Storage\Config;
use Monkey\Framework\Router;
use Monkey\Web\Renderer;
use Monkey\Web\Response;

function url(string $file) : string
{
    $prefix = Config::get("app_prefix");
    if ($prefix === null) $prefix = "/";
    if (substr($prefix, -1) !== "/") $prefix .= "/";
    return $prefix.$file;
}


function include_file(string $template_name)
{
    if (!isset($GLOBALS["render"])) $GLOBALS["render"] = [];
    return Renderer::render($template_name, $GLOBALS["render"], true);
}


function router(string $routeName) : string
{
    $all_route = array_merge(Router::$list, Router::$temp);
    foreach ($all_route as $r)
    {
        if (!isset($r->name)) continue;
        if ($routeName === $r->name) return $r->path;
    }
    return "/". $r->path;
}