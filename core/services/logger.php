<?php 

namespace Monkey\Services;

use Monkey\Storage\Config;
use Monkey\Web\Request;

class Logger 
{
    static $config = [];

    const INFO = "INFO";
    const DEBUG = "DEBUG";
    const ERROR = "ERROR";
    const GRAVE = "GRAVE";

    public static function load_config()
    {
        self::$config = Config::get("logger", ["enabled" => false]); 
    }

    public static function init()
    {
        self::load_config();
        if (self::$config["enabled"] !== true) return false;
        self::$config["file"] = self::$config["file"] ?? "monkey-logs.txt";
        if (!file_exists(self::$config["file"])){
            file_put_contents(self::$config["file"], 
            "#Software: Monkey Framework for PHP8\n#Fields: date time method type backtrace message\n");
        }
    }

    public static function get_start(string $type=Logger::INFO) : string 
    {
        $calling_functions = array_reverse(debug_backtrace());
        $calling_functions = array_filter($calling_functions, fn($elem)=>key_exists("file", $elem));
        $calling_functions = array_filter($calling_functions, fn($elem)=>basename($elem["file"])!=="logger.php" );
        $calling_functions = array_map( fn($elem)=> basename($elem["file"] ?? "unknown")."@".$elem["line"]??"unknown", $calling_functions);
        $calling_functions = join('->', $calling_functions);

        $method = Request::current()->method;

        return date("Y-m-d H:i:s") . " $method $type $calling_functions ";
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
        $to_write = self::get_start($type) . "\"".$to_write . "\"\n";
        if ($double_jump) $to_write .= "\n";
        file_put_contents(self::$config["file"], $to_write , FILE_APPEND);
    }
}