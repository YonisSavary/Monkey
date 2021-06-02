<?php 

namespace Monkey\Framework;

use Monkey\Storage\Config;
use Monkey\Storage\Cache;

/**
 * This class can load "applications", an application can be made of some directories :
 * 
 * Thoses directories will be added to the autoloader :
 * - models
 * - controllers
 * - middlewares
 * - others
 * - routes
 * 
 * see `AppLoader::AUTOLOAD_DIRECTORIES_NAMES` for more
 */
class AppLoader
{
    const CACHE_FILE_NAME = "apploader";
    const VIEWS_DIRECTORY_NAME = "views";
    const CONFIG_FILE_NAME = "monkey.json";
	const AUTOLOAD_DIRECTORIES_NAMES =  [
		"models", 
		"controllers", 
		"middlewares",
		"others", 
		"routes"
	];

	
    public static $app_directories = [];
    public static $config_paths = [];
    public static $views_directories = [];
    public static $autoload_list = [];
    public static $loaded_directories = false;


    public static function get_app_directories()
    {
        return self::$app_directories;
    }

    public static function get_config_paths()
    {
        return self::$config_paths;
    }

    public static function get_views_directories()
    {
        return self::$views_directories;
    }

    public static function get_autoload_list()
    {
        return self::$autoload_list;
    }



	/**
	 * Gives every paths of every files in a directory
	 * - Recursive Function
	 * - Does not returns Directories Path (only files)
	 * @param string $path
	 */
    public static function explore_full_dir(string $path) : mixed
	{
        if (!is_dir($path)) return false;
        if (!str_ends_with($path, "/")) $path .= "/";

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
                if (!str_ends_with($file, ".php")) continue;
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
     * @param string $path Path of the application to load
     * @return array PHP Files to load
     */
    public static function load_application_path(string $path) : array
    {
        if (!is_dir($path)) return false;
        if (substr($path, -1) != "/") $path .= "/";

        $results = [];
        foreach (scandir($path) as $file)
        {
            $file_path = $path . $file;
            if (is_dir($file_path))
            {
                if ($file === AppLoader::VIEWS_DIRECTORY_NAME)
                {
                    array_push(AppLoader::$views_directories, $file_path);
                } 
                else if (in_array($file, AppLoader::AUTOLOAD_DIRECTORIES_NAMES))
                {
                    $results = array_merge($results, AppLoader::explore_full_dir($file_path));
                }
            }
            else if ($file === AppLoader::CONFIG_FILE_NAME)
            {
				array_push(AppLoader::$config_paths, $file_path);
            }
        }

        return $results;
    }



    /**
     * Loads the applications in AppLoader::$app_directories
     */
    public static function load_all_applications() : bool
    {
        if (self::$loaded_directories === true) return false;

        $to_loads = [];
        foreach(AppLoader::$app_directories as $dir)
        {
            if (!str_ends_with("/", $dir)) $dir .= "/";
            $to_loads = array_merge($to_loads, AppLoader::load_application_path($dir));
        }
        AppLoader::$autoload_list = $to_loads; 

        self::write_to_Cache();
        self::$loaded_directories = true;

        return true;
    }



    /**
     * Write the current AppLoader configuration 
     * to the Cache (only if the configuration
     * allow it)
     */
    public static function write_to_Cache()
    {
        if (Config::get("cached_apploader") === true)
        {
            Cache::set(AppLoader::CACHE_FILE_NAME, [
                "views_directories" => AppLoader::$views_directories,
                "autoload_list"     => AppLoader::$autoload_list,
                "config_paths"      => AppLoader::$config_paths
            ]);
        }  
    }


    /**
     * Read the AppLoader config from the Cache
     * (only if the configuration allow it)
     */
    public static function read_from_Cache()
    {
		// If "cached_apploader" is true
		// And "cached_apploader.json" exists 
        if ( Config::get("cached_apploader", false) === true 
        &&   Cache::exists(AppLoader::CACHE_FILE_NAME) )
        {
            $data = Cache::get(AppLoader::CACHE_FILE_NAME);
            AppLoader::$views_directories   = $data["views_directories"];
            AppLoader::$autoload_list       = $data["autoload_list"];
            AppLoader::$config_paths        = $data["config_paths"];
            self::$loaded_directories = true;
        } 
    }




    /**
     * This function :
     * - Initialize the component
     * - Find the applications paths
     * - Load the php files with `spl_autoload_Cache`
     * 
     */
    public static function init() : void
    {
        $app_directories = Config::get("app_directories", []);
        if (is_string($app_directories)) $app_directories = [$app_directories];

        self::$app_directories = $app_directories;

        self::read_from_Cache();
        self::load_all_applications();    

        foreach (AppLoader::$autoload_list as $dir)
        {
            require_once $dir;
        }

		Config::read_file(AppLoader::$config_paths);
    }
}