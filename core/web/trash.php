<?php 

namespace Monkey\Web;

use Monkey\Config;

/**
 * This class is here to handle errors,
 * when something is wrong, yeet it in the `Trash` and that's it !
 * 
 * To define a custom error handling, call Trash::on(errorCode, callback);
 * Then, everytimes Trash::send(errorCode) is called, you callback will be called too
 */
class Trash 
{
    static $error_callbacks = [];

    public static function on(string $error, callable $callback){
        Trash::$error_callbacks[$error] = $callback;
    }

    public static function send(string $error, $arg=null){
        if (isset(Trash::$error_callbacks[$error])){
            Trash::$error_callbacks[$error]($arg);
        }
    }

    /**
     * Display an error message and die.
     * 
     * @param string $message Message to display
     */
    public static function fatal(string $message): void
    {
        Trash::send("fatal", $message);
    }
}





Trash::on("fatal", function (string $message){
    if (Config::get("safe_error_display", false) !== true){
        $message = "Fatal Error In Monkey : $message";
    } 
    $message = "
        <h1>Error 500</h1>
        $message
    ";
    echo $message;
    die();
});

Trash::on("404", function ($request_path){
    echo "<h1>Error 404</h1>Page Not found !<br>" ;
    if (Config::get("safe_error_display", false) !== true){
        echo "\"".$request_path."\" route not found";
    }     
});