<?php 

namespace Monkey;

/**
 * This class can load "applications", an application is made of 
 * 4 directories :
 * - controllers
 * - middlewares
 * - models
 * - views
 */
class AppLoader
{
    const CACHE_FILE_NAME = "cached_apploader";

    public static $others = [];
    public static $views_directories = [];
    public static $app_directories = [];

    public static $autoload_list = [];



    /**
     * Explore a directory and retrieves return classes PHP files,
     * if a directory is named `views` it is ignored and its path 
     * is added to `AppLoader::$views_directories`
     * 
     * @param string $path Path of the directory to explore
     * @param bool $recursive Do the function call itself for subdirectories ?
     * @return array Classes Files inside the explored directory
     */
    public static function explore_dir(string $path, bool $recursive=true) : array
    {
        if (!is_dir($path)) return false;
        if (substr($path, -1) != "/") $path .= "/";
        $results = [];
        foreach (scandir($path) as $file)
        {
            if ($file === "." || $file === "..") continue;
            $file_path = $path . $file;
            if (is_dir($file_path) && $recursive)
            {
                if ($file === "views")
                {
                    array_push(AppLoader::$views_directories, $file_path);
                } 
                else if ($file === "others") 
                {
                    AppLoader::$others = array_merge(AppLoader::$others, AppLoader::explore_dir($file_path, true));
                } 
                else 
                {
                    $results = array_merge($results, AppLoader::explore_dir($file_path));
                }
            }
            else 
            {
                if (substr($file, -4) !== ".php") continue;
                array_push($results, $file_path);
            }
        }
        return $results;
    }



    /**
     * Loads the given applications paths
     * 
     * @param array $apps Array of applications paths
     * @note If the `cached_apploader` is set to true in monkey.ini, it will use the `Register component`
     */
    public static function load_applications() : void
    {
        $to_loads = [];
        foreach(AppLoader::$app_directories as $dir)
        {
            if (!str_ends_with("/", $dir)) $dir .= "/";
            $to_loads = array_merge($to_loads, AppLoader::explore_dir($dir));
        }
        AppLoader::$autoload_list = $to_loads;      
        if (Config::get(AppLoader::CACHE_FILE_NAME) === true)
        {
            Register::set(AppLoader::CACHE_FILE_NAME, [
                "views_directories"=> AppLoader::$views_directories,
                "autoload_list"=> AppLoader::$autoload_list
            ]);
        }  
    }



    /**
     * This function :
     * - Initialize the component
     * - Find the applications paths
     * - Load the php files with `spl_autoload_register`
     * 
     * @note If the `cached_apploader` is set to true in monkey.ini, it will use the `Register component`
     */
    public static function init() : void
    {
        $cfg_app = Config::get("app_directories", []);
        if (is_string($cfg_app)) $cfg_app = [$cfg_app];
        AppLoader::$app_directories = $cfg_app;

        if ( Config::get(AppLoader::CACHE_FILE_NAME)    === true 
        &&   Register::get(AppLoader::CACHE_FILE_NAME)  !== null )
        {
            $data = Register::get(AppLoader::CACHE_FILE_NAME);
            AppLoader::$views_directories   = $data["views_directories"];
            AppLoader::$autoload_list       = $data["autoload_list"];
        } 
        else
        {
            AppLoader::load_applications();
        }


        Config::set_discrete("views-directory", AppLoader::$views_directories);
        spl_autoload_register(function()
        {
            $to_loads = array_merge(AppLoader::$autoload_list, AppLoader::$others);
            foreach(array_unique($to_loads) as $dir)
            {
                include($dir);
            }
        });
    }
}