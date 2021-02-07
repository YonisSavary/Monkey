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
     * Transform those values :
     * true => "true"
     * false => "false"
     * 
     * @param mixed $value Object to check
     */
    public static function str_if_bool(mixed &$value) : void
    {
        if (is_bool($value)) 
        {
            if ($value === true)
            {
                $value = "true";
            } else {
                $value = "false";
            }
        }
    }



    /**
     * Strings need to be quoted in INI files to be correctly parsed
     * in any situations
     * 
     * @param mixed &$value Value to Check
     */
    public static function quote_if_str(mixed &$value) : void 
    {
        if (is_string($value))
        {
            $value = '"'.$value.'"';
        }
    }

    /**
     * Save the current configuration in monkey.ini
     * 
     * @param array $to_delete Keys to exclude from the configuration
     * @note This function is designed to keep your comments !
     */
    public static function save(array $to_delete=[]) : void
    {
        $config = $GLOBALS["monkey"]["config"];
        $config_fields = array_keys($GLOBALS["monkey"]["config"]);
        $original_config = Config::init(true); // Read the current file config and return it

        $written_fields = [];
        $cfg_file = explode("\n", file_get_contents("monkey.ini"));
        foreach($cfg_file as &$line)
        {
            if (substr($line, 0, 1) === ";") continue;
            if (!preg_match("/[^;]+=.+/", $line)) continue;
            $key = preg_replace("/=.+/", "", $line);
            if (!isset($config[$key])) continue;

            if (in_array($key, $to_delete))
            {
                $line = "";
                continue;
            }

            Config::quote_if_str($original_config[$key]);
            Config::quote_if_str($config[$key]);

            Config::str_if_bool($original_config[$key]);
            Config::str_if_bool($config[$key]);

            $line = str_replace("=".$original_config[$key], "=".$config[$key], $line);
            array_push($written_fields, $key);
        }
        foreach (array_diff($config_fields, $written_fields, $to_delete) as $field)
        {
            array_push($cfg_file, $field."=".$config[$field]);
        }

        file_put_contents("monkey.ini", join("\n", $cfg_file));
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
     * Initialize the component, read the monkey.ini file 
     * and parse it with `INI_SCANNER_TYPED`, to allow you
     * to write "true" or "false" values
     * 
     * @param bool $return Set it to true to only return the read config
     * and not store it in `$GLOBALS`
     * @return array The read configuration
     */
    public static function init(bool $return=false) : array
    {
        if (!file_exists("monkey.ini")) {
            Trash::handle("monkey.ini doesn't exists !");
        };
        /* 
        From php.net :
        << As of PHP 5.6.1 can also be specified as INI_SCANNER_TYPED.
        In this mode boolean, null and integer types are preserved when possible.
        String values "true", "on" and "yes" are converted to true. "false", "off",
        "no" and "none" are considered false. 
        "null" is converted to null in typed mode.
        Also, all numeric strings are converted to integer type if it is possible. >>
        */
        $cfg = parse_ini_file("monkey.ini", true, INI_SCANNER_TYPED);
        if ($return !== true) 
        {
            $GLOBALS["monkey"]["config"] = $cfg;
        }
        return $cfg;
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