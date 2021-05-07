<?php

namespace Monkey\Web;

use Monkey\Config;
use Monkey\Web\Response;
use Monkey\Web\Trash;

/**
 * This function allow you to render a view
 */
class Renderer
{
    /**
     * Find a template in a directory
     * 
     * @param string $dir Directory to look into
     * @param string $template_name Template name we are looking for
     * @return string the path of the template if found, null if not found
     */
    public static function find_recursive(string $dir, string $template_name, bool $in_dir = false) : mixed
    {
        if (!is_dir($dir)) return false;
        if (substr($dir, -1) !== "/") $dir .= "/";
        if (substr($template_name, -4) !== ".php") $template_name .= ".php";
        foreach (scandir($dir) as $file)
        {
            if ($file === "." || $file === "..") continue;
            $file_path = $dir . $file ;
            if (is_dir($file_path))
            {
                $value = self::find_recursive($file_path, $template_name, $in_dir);
                if ($value !== null) return $value;
            }
            else 
            {
				if ($in_dir && strpos($file_path, $template_name)) return $file_path;
                if ($template_name === $file) return $file_path;
            }
        }
        return null;
    }


    /**
     * Given a template name, this function try to find a template path
     * in the views-directories
     * 
     * @param string $template_name template name we are looking for
     * @return string the path of the template if found, null if not found
     */
    public static function find(string $template_name) : mixed
    {
        foreach (Config::get("views-directory", []) as $dir)
        {
			$in_dir = (strpos($template_name, "/") !== false);
            $result = self::find_recursive($dir, $template_name, $in_dir);
            if ($result !== null) return $result ;
        }
        return null;
    }

    
    /**
     * Render a PHP template
     * 
     * @param string $template_name template name we are looking for
     * @param bool $flush If set to `true' the function return a `Response` object
     */
    public static function render(string $template_name, mixed $vars=null, bool $return_raw=false) : mixed
    {
        $GLOBALS["render"] = $vars;
        $path = self::find($template_name);
        if ($path === null) return Trash::fatal($template_name . " template does not exists !");
        ob_start();
        require_once $path;
        $content = ob_get_clean();
        if ($return_raw === true) return $content;
        return Response::html($content);
    }
}