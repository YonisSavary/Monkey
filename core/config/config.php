<?php 

namespace Monkey;

use Monkey\Web\Trash;

/**
 * The Config component store the FRAMEWORK configuration,
 * which is written in the monkey.ini file
 */
class Config
{
    /**
     * Read a file if it exists
     * If an error happend while reading json, Trash will handle a fatal error
     * Merge the read json and the already existing configuration
     */
    public static function read_file(string $path){
        if (!file_exists($path)) return false;
        
        $content = (array) json_decode(file_get_contents($path));

        $last_error = json_last_error();
        if ($last_error !== JSON_ERROR_NONE){
            Trash::fatal("JSON Syntax Error while reading '$path' (json_last_error() == $last_error) !");
        }

        foreach ($content as $key => $value){
            Config::set($key, $value);
        }
    }



    /**
     * Save the current configuration in monkey.json
     * 
     * @deprecated This function save the whole configuration in `./monkey.json` 
     * But now, configuration can be set in multiples files
     */
    public static function save() : void
    {
        $config = $GLOBALS["monkey"]["config"];
        file_put_contents("monkey.json", json_encode($config, JSON_PRETTY_PRINT));
        Config::init();
    }



    /**
     * Check if a key exists in the config
     * 
     * @param string $key Key name to check
     * @return bool Do the given key exists
     */
    public static function exists(string $key) : bool
    {
        return isset($GLOBALS["monkey"]["config"][$key]);
    }



    /**
     * Check if an array of keys exists at the same time
     * 
     * @param array $keys Keys list to check
     * @return bool Do the given keys exists at the same time ?
     */
    public static function multiple_exists(array $keys) : bool
    {
        $exists = true;
        foreach ($keys as $key) $exists &= Config::exists($key);
        return $exists;
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
        if (Config::exists($key)) return $GLOBALS["monkey"]["config"][$key];
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
        Config::read_file("./monkey.json");
    }

}