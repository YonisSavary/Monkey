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

    public static $views_directories = [];
    public static $app_directories = [];
    public static $config_paths = [];

    public static $autoload_list = [];


    public static function explore_full_dir(string $path){
        if (!is_dir($path)) return false;
        if (substr($path, -1) != "/") $path .= "/";
        $results = [];
        foreach (scandir($path) as $file)
        {
            if ($file === "." || $file === "..") continue;
            $file_path = $path . $file;
            if (is_dir($file_path))
            {
                $results = array_merge($results, AppLoader::explore_full_dir($file_path));
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
     * Explore a directory and retrieves return classes PHP files,
     * if a directory is named `views` it is ignored and its path 
     * is added to `AppLoader::$views_directories`
     * 
     * @param string $path Path of the directory to explore
     * @param bool $recursive Do the function call itself for subdirectories ?
     * @return array Classes Files inside the explored directory
     */
    public static function load_application(string $path) : array
    {
        if (!is_dir($path)) return false;
        if (substr($path, -1) != "/") $path .= "/";
        $results = [];
        foreach (scandir($path) as $file)
        {
            if ($file === "." || $file === "..") continue;
            $file_path = $path . $file;
            if (is_dir($file_path))
            {
                if ($file === "views")
                {
                    array_push(AppLoader::$views_directories, $file_path);
                } 
                else if (in_array($file, ["others", "controllers", "models"]))
                {
                    $results = array_merge($results, AppLoader::explore_full_dir($file_path));
                }
            }
            else 
            {
                if ($file === "monkey.json"){
                    array_push(AppLoader::$config_paths, $file_path);
                    continue;
                }
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
            $to_loads = array_merge($to_loads, AppLoader::load_application($dir));
        }
        AppLoader::$autoload_list = $to_loads;      
        if (Config::get(AppLoader::CACHE_FILE_NAME) === true)
        {
            Register::set(AppLoader::CACHE_FILE_NAME, [
                "views_directories"=> AppLoader::$views_directories,
                "autoload_list"=> AppLoader::$autoload_list,
                "config_paths" => AppLoader::$config_paths
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
            AppLoader::$config_paths        = $data["config_paths"];
        } 
        else
        {
            AppLoader::load_applications();
        }


        Config::set("views-directory", AppLoader::$views_directories);
        spl_autoload_register(function()
        {
            foreach (AppLoader::$autoload_list as $dir)
            {
                include($dir);
            }
        });

        foreach (AppLoader::$config_paths as $path){
            Config::read_file($path);
        }
    }
}