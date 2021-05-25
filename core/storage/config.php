<?php 

namespace Monkey\Storage;

use Monkey\Web\Trash;

/**
 * The Config component store the FRAMEWORK configuration,
 * which is written in the monkey.ini file
 */
class Config
{
    /**
     * - Read a file if it exists
     * - If an error happend while reading json, Trash will handle a fatal error
     * - Merge the read json and the already existing configuration
	 * 
	 * @param string|array $path A file Path or an array of
     */
    public static function read_file(string|array $path)
	{
		if (is_array($path)){
			$res = true;
			foreach ($path as $p){
				$res &= self::read_file($p);
			}
			return $res;
		}

        if (!file_exists($path)) return false;
        
        $content = json_decode(file_get_contents($path), true);

        $last_error = json_last_error();
        if ($last_error !== JSON_ERROR_NONE){
            Trash::fatal("JSON Syntax Error while reading '$path' (json_last_error() == $last_error) !", true);
        }

        foreach ($content as $key => $value){
            self::set($key, $value);
        }

		return true;
    }



    /**
     * Check if a key exists in the config
     * 
     * @param string $key Key name to check
     * @return bool Do the given key exists
     */
    public static function exists(string|array $key) : bool
    {
		if (is_array($key))
        {
			foreach ($key as $k)
            {
                if (!self::exists($k)) return false;
            } 
			return true;
		}
        return isset($GLOBALS["monkey"]["config"][$key]);
    }


    /**
     * Get a key from the register, $default can be given to 
     * replace the value if inexistant
     * 
     * @param string $key Key to reach
     * @param mixed $default Default value if the given key isn't found
     */
    public static function get(string $key, mixed $default=null) : mixed
    {
        if (self::exists($key)) return $GLOBALS["monkey"]["config"][$key];
        return $default;
    }


    /**
     * Set a new key/value pair in the configuration
     * 
     * @param string $key New key to define
     * @param mixed $value The value for the new key
     */
    public static function set(string $key, mixed $value) : void
    {
        $GLOBALS["monkey"]["config"][$key] = $value;
    }


    /**
     * Initialize the component, read the
     * configuration from either the json file(s)
     */
    public static function init()
    {
        $GLOBALS["monkey"]["config"] = [];
        $read = self::read_file("./monkey.json");
        if ($read === false){
            Trash::fatal("monkey.json does not exists !", true);
        }
    }
}