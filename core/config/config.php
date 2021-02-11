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
     * Save the current configuration in monkey.json
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
     * Read monkey.json (and the extra ones if there's any)
     * and return the configuration
     * 
     * @return array Read configuration
     */
    public static function read_config() : array 
    {
        $cfg = (array) json_decode(file_get_contents("monkey.json"));

        if (isset($cfg["extra_config"]))
        {
            $more_files = $cfg["extra_config"];
            if (is_string($more_files)) $more_files = [$more_files];
            if (is_array($more_files)){
                foreach($more_files as $f){
                    if (!is_file($f)) continue;
                    $extra = (array) json_decode(file_get_contents($f));
                    $cfg = array_merge($cfg, $extra);
                }
            }

        }
        return $cfg;
    }


    /**
     * Initialize the component, read the
     * configuration from either the json file(s)
     */
    public static function init()
    {
        if (!file_exists("monkey.json")) {
            Trash::handle("monkey.json doesn't exists !");
        };
        
        $cfg = Config::read_config();

        $GLOBALS["monkey"]["config"] = $cfg;
    }




    /**
     * Check if a key exists in the config
     * 
     * @param string $key Key name to check
     * @return bool Do the given key exists
     * @note discretes keys are not stored in `monkey.ini`
     */
    public static function exists_discrete(string $key) : bool
    {
        return isset($GLOBALS["monkey"]["config_discrete"][$key]);
    }




    /**
     * Set a new key/value pair in the configuration
     * 
     * @param string $key New key to define
     * @param mixed $value The value for the new key
     * @note discretes keys are not stored in `monkey.ini`
     */
    public static function set_discrete(string $key, mixed $value)
    {
        $GLOBALS["monkey"]["config_discrete"][$key] = $value;
    }




    /**
     * Get a key from the register, $default can be given to 
     * replace the value if inexistant
     * 
     * @param string $key Key to reach
     * @param mixed $default Default value if the given key isn't found
     * @note discretes keys are not stored in `monkey.ini`
     */
    public static function get_discrete(string $key, mixed $default=null)
    {
        if (Config::exists_discrete($key)) return $GLOBALS["monkey"]["config_discrete"][$key];
        return $default;
    }
}