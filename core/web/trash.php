<?php 

namespace Monkey\Web;

use Monkey\Storage\Config;
use Monkey\Router;

/**
 * This class is here to handle errors,
 * when something is wrong, yeet it in the `Trash` and that's it !
 * 
 * To define a custom error handling, call self::on(errorCode, callback);
 * Then, everytimes self::send(errorCode) is called, you callback will be called too
 */
class Trash 
{
    static $error_callbacks = [];

    public static function on(string $error, callable|string $callback){
        self::$error_callbacks[$error] = $callback;
    }

    public static function send(string $error, $arg=null): Response
	{
        if (isset(self::$error_callbacks[$error]))
		{
            $callback = self::$error_callbacks[$error];
            if (is_string($callback))
			{
                return Router::execute_route_callback($callback, $arg);
            } 
            else
            {
                return $callback($arg);
            }
        }
    }

    /**
     * Display an error message and die.
     * 
     * @param string $message Message to display
     */
    public static function fatal(string $message, bool $force_display=false): Response
    {
        $res = self::send("fatal", $message);
		if ($force_display)
		{
			$res->reveal();
			die();
		}
		return $res;
    }
}





Trash::on("fatal", function (string $message): Response
{
    if (Config::get("safe_error_display", false) !== true)
	{
        $message = "Fatal Error In Monkey : $message";
    } 
    $message = "<h1>Error 500</h1> $message";

    return Response::html($message);
});

Trash::on("404", function ($request_path) : Response
{
    $message = "<h1>Error 404</h1>Page Not found !<br>" ;
    if (Config::get("safe_error_display", false) !== true)
	{
        $message .= "\"".$request_path."\" route not found";
    }     

	return Response::html($message);
});