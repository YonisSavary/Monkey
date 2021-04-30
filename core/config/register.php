<?php 

namespace Monkey;

/**
 * Register store object into files, it store essentials data
 * of the framework like routes, autoload files...
 * Object are stored insides *.json files
 */
class Register 
{
    // Directory where the .json files are stored
    public static $store = "./config";

    /**
     * Load the .json files in Register::$store into the global variable
     */
    public static function load_files() : void
    {
        $ser_glob_str = Register::$store . "*.json";
        $ser_glob = glob($ser_glob_str);
        foreach($ser_glob as $path) {
            //echo "Loading $path <br>"; // DEBUG
            $file_name = preg_replace("/.+\//", "", $path);
            $key = substr($file_name, 0, strlen($file_name)-5);
            $GLOBALS["monkey"][$key] = (array) json_decode(file_get_contents($path));
        }
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
        if (isset($GLOBALS["monkey"][$key])) return $GLOBALS["monkey"][$key];
        return $default;
    }




    /**
     * Set a value in the register and write its file
     * 
     * @param string $key Name of the new key
     * @param mixed $value Content of the new key
     */
    public static function set(string $key, mixed $value) : void
    {
        $GLOBALS["monkey"][$key] = $value;
        Register::write($key);
    }




    /**
     * Retrieve a key Register and write a file containing the 
     * key content
     * 
     * @param string $key Key register to write
     * @return bool Was the writing successful ?
     */
    public static function write(string $key) : bool
    {
        if (isset($GLOBALS["monkey"][$key]))
        {
            file_put_contents(Register::$store . "$key.json", json_encode($GLOBALS["monkey"][$key], JSON_PRETTY_PRINT));
            return true;
        }
        return false;
    }
    



    /**
     * Initialize the component :
     * - Correct the store path if needed
     * - Create a store repertory if inexistant
     * - Load .json files inside the store dir
     */
    public static function init()
    {
        if (Config::exists("register_store")) Register::$store = Config::get("register_store");
        if (substr(Register::$store, -1) !== "/") Register::$store .= "/";
        if (!is_dir(Register::$store)) mkdir(Register::$store);
        Register::load_files();
    }



}