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
    public static $required_files = [];
    public static $views_directory = [];
    public static $app_directory = [];

    public static $app_loader_blacklist = [];
    public static $autoload_list = [];



    /**
     * Explore a directory and retrieves return classes PHP files,
     * if a directory is named `views` it is ignored and its path 
     * is added to `AppLoader::$views_directory`
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
                    array_push(AppLoader::$views_directory, $file_path);
                    continue;
                }
                if ($file === "requires")
                {
                    AppLoader::$required_files = array_merge(AppLoader::$required_files, AppLoader::explore_dir($file_path));
                    continue;
                }
                if (in_array($file, AppLoader::$app_loader_blacklist)) continue;
                $results = array_merge($results, AppLoader::explore_dir($file_path));
            }
            else 
            {
                if (in_array($file, AppLoader::$app_loader_blacklist)) continue;
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
        foreach(AppLoader::$app_directory as $dir)
        {
            if (!str_ends_with("/", $dir)) $dir .= "/";
            $to_loads = array_merge($to_loads, AppLoader::explore_dir($dir));
        }
        AppLoader::$autoload_list = $to_loads;      
        if (Config::get("cached_apploader") === true)
        {
            Register::set("cached_apploader", [AppLoader::$views_directory, AppLoader::$autoload_list]);
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
        $cfg_app = Config::get("app_directory", ["./app"]);
        if (is_string($cfg_app)) $cfg_app = [$cfg_app];
        AppLoader::$app_directory = $cfg_app;
        

        
        if (Config::exists("app_loader_blacklist")) { 
            AppLoader::$app_loader_blacklist = Config::get("app_loader_blacklist");
        }


        if (Config::get("cached_apploader") === true && Register::get("apploader") !== null)
        {
            $data = Register::get("cached_apploader");
            AppLoader::$views_directory = $data[0];
            AppLoader::$autoload_list = $data[1];
        } 
        else
        {
            AppLoader::load_applications();
        }


        Config::set_discrete("views-directory", AppLoader::$views_directory);
        spl_autoload_register(function()
        {
            foreach(AppLoader::$autoload_list as $dir)
            {
                include($dir);
            }
        });
        foreach (AppLoader::$required_files as $f ){
            if (is_file($f)){
                require_once $f;
            }
        }
    }
}