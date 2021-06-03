<?php 

namespace Monkey\Storage;

use Monkey\Storage\Storage;

class Cache 
{
    static $dir_name = "cache";
    static $cache = [];

    /**
     * Load the .json files in self::$store into the global variable
     */
    public static function load_files() : void
    {
        $files_glob_path = Storage::get_path(self::$dir_name . "*.obj") ;
        $files_to_load = glob($files_glob_path);
        foreach($files_to_load as $file) 
        {
            $key = preg_replace("/\.obj$/", "", basename($file));
            self::$cache[$key] = unserialize(Storage::read($file));
        }
    }


    public static function get(string $key, mixed $default=null) : mixed
    {
        return self::$cache[$key] ?? $default;
    }


    public static function exists(string $key) : bool
    {
        return isset(self::$cache[$key]);
    }

    
    public static function set(string $key, mixed $value) : void
    {
        self::$cache[$key] = $value;

        $file_path = self::$dir_name . $key .".obj";
        Storage::write($file_path, serialize($value));
    }


    public static function init() : void
    {        
        self::$dir_name = Config::get("cache_directory", "cache");
        if (!str_ends_with(self::$dir_name, "/")) self::$dir_name .= "/";

        $full_path = Storage::get_path(self::$dir_name);
        if (!is_dir($full_path)) mkdir($full_path, 0777, true);

        self::load_files();
    }
}