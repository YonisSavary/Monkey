<?php 

namespace Monkey\Services;

use Monkey\Storage\Config;
use Monkey\Storage\Storage;
use Monkey\Web\Request;

class Logger 
{
    static $config = [];

    const INFO = "INFO";
    const DEBUG = "DEBUG";
    const ERROR = "ERROR";
    const GRAVE = "GRAVE";
    const FRAMEWORK = "FRAMEWORK";

    public static function load_config()
    {
        self::$config = Config::get("logger", ["enabled" => false]); 
    }

    public static function init()
    {
        self::load_config();
        if (self::$config["enabled"] !== true) return false;
        self::$config["file"] = self::$config["file"] ?? "monkey.log";
        if (!Storage::exists(self::$config["file"])){
            Storage::write(self::$config["file"], 
            "#Software: Monkey Framework for PHP8\n#Fields: date\ttime\tclient\tmethod\ttype\tmessage\tbacktrace\n");
        }

        $ignore = self::$config["ignore"] ?? [];
        if (!is_array($ignore)) $ignore = [$ignore];
        self::$config["ignore"] = $ignore;
    }


    public static function text(string|array $to_write, string $type=Logger::INFO, bool $double_jump=false) 
    {
        if (self::$config === []) self::load_config();
        if (self::$config["enabled"] !== true) return false;
        if (is_array($to_write))
        {
            foreach ($to_write as $line) self::text($line, $type, (end($to_write) === $line) & $double_jump);
            return false;
        }        
        
        if (in_array($type, self::$config["ignore"] ?? []))
        {
            return false;
        }

        $calling_functions = array_reverse(debug_backtrace());
        $calling_functions = array_filter($calling_functions, fn($elem)=>key_exists("file", $elem));
        $calling_functions = array_filter($calling_functions, fn($elem)=>basename($elem["file"])!=="logger.php" );
        $calling_functions = array_map( fn($elem)=> basename($elem["file"] ?? "unknown")."@".$elem["line"]??"unknown", $calling_functions);
        $calling_functions = join('->', $calling_functions);

        $method = Request::current()->method;

        $ip = $_SERVER['REMOTE_ADDR'] ?? "unknown";

        $to_write = date("Y-m-d\tH:i:s") . "\t$ip\t$method\t$type\t'$to_write'\t$calling_functions\n";
        
        if ($double_jump) $to_write .= "\n";
        Storage::write(self::$config["file"], $to_write , FILE_APPEND);
    }

    public static function object($objects) 
    {
        if (self::$config === []) self::load_config();
        if (self::$config["enabled"] !== true) return false;
        if (is_array($objects))
        {
            foreach ($objects as $obj) self::object($obj);
            return false;
        }
        self::text(json_encode($objects));
    }
}