<?php 

namespace Monkey\Storage;

use Monkey\Storage\Config;

class Storage 
{
    static $path ;


    public static function exists(string $path)
    {
        $path = self::get_path($path);
        return file_exists($path);
    }


    public static function write(string $path, mixed $content, int $flags = 0)
    {
        if($path === null || $content === null) return false;
        $path = self::get_path($path);
        if (!is_dir(dirname($path))) mkdir(dirname($path), 0777, true);
        file_put_contents($path, $content, $flags);
    }


    public static function read(string $path)
    {
        $path = self::get_path($path);
        return file_get_contents($path);
    }


    public static function get_path(string $path)
    {
        if (strpos($path, self::$path) !== false) return $path;
        return self::$path . $path ;
    }


    public static function fix_path(string &$path)
    {
        if (!str_ends_with($path, "/")) $path .= "/";
        $path = preg_replace("/\.{1,}\//", "", $path);
    }


    public static function init()
    {
        self::$path = getcwd();
        self::fix_path(self::$path);
        self::$path .= Config::get("storage_directory", "storage");
        if (!is_dir(self::$path)) mkdir(self::$path, 0777, true);
        self::fix_path(self::$path);
    }
}