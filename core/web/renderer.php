<?php

namespace Monkey\Web;

use Monkey\Framework\AppLoader;
use Monkey\Storage\Config;
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
        foreach (AppLoader::get_views_directories() as $dir)
        {
			$in_dir = (strpos($template_name, "/") !== false);
            $result = self::find_recursive($dir, $template_name, $in_dir);
            if ($result !== null) return $result ;
        }
        return null;
    }



    /**
     * Test is a view exists 
     * (Shortcut to know if find() return null)
     */
    public static function exists(string $template_name): bool 
    {
        return (self::find($template_name) !== null);
    }
    
    
    /**
     * Render a PHP template
     * 
     * All theses keys in $vars will create
     * the same variable name ("product_one"),
     * As variable that start with a number are forbidden
     * and non-alpha-numeric characters replaced with underscore
     * 
     * ```php
     * $vars = [
     *      "4903product one" => [...]
     *      "product one" => [...]
     *      "product_one" => [...]
     * ]
     * ```
     * 
     * **Important**, reserved names are :
     *  - monkey_renderer_vars
     *  - monkey_renderer_template_name
     *  - monkey_renderer_return_raw
     *  - monkey_renderer_content
     * 
     * @param string $template_name template name we are looking for
     * @param mixed $vars Data for your template
     * @param bool $return_raw Set to true to retrieve the HTML string
     * @param bool $flush If set to `true' the function return a `Response` object
     */
    public static function render(string $template_name, mixed $vars=[], bool $return_raw=false) : Response|string
    {
        // We can declare a variable only if not already set
        // So we use $GLOBALS["render"] to reserves the minimal 
        // Number of variables
        $GLOBALS["render_vars"] = array_merge($GLOBALS["render_vars"]??[], $vars);

        $monkey_renderer_vars = $GLOBALS["render_vars"];
        $monkey_renderer_template_name = $template_name;
        $monkey_renderer_return_raw = $return_raw;
        $monkey_renderer_content = "";

        unset($vars);
        unset($template_name);
        unset($return_raw);


        foreach ($monkey_renderer_vars ?? [] as $key => $value)
        {
            // As a variable name can't start with numbers, we delete them
            $key = preg_replace("/^[0-9]{0,}/", "", $key);
            $key = preg_replace("/[^0-9A-Z]/i", "_", $key);
            if (! isset($$key)) $$key = $value; 
        }
        
        $monkey_renderer_path = self::find($monkey_renderer_template_name);
        if ($monkey_renderer_path === null) Trash::fatal($monkey_renderer_template_name . " template does not exists !");
        
        ob_start();
        require_once $monkey_renderer_path;
        $monkey_renderer_content = ob_get_clean();
        

        if ($monkey_renderer_return_raw === true) return $monkey_renderer_content;
        return Response::html($monkey_renderer_content);
    }
}