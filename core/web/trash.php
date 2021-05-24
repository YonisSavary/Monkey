<?php 

namespace Monkey\Web;

use Closure;
use Monkey\Storage\Config;
use Monkey\Framework\Router;

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

    public static function on(string $error_code, string|Closure|array $callback): void
    {
        self::$error_callbacks[$error_code] = $callback;
    }

    public static function send(string $error, ...$arg): Response
	{
        $callback = self::$error_callbacks[$error] ?? null;

        if (is_callable($callback)) return $callback(...$arg);
        return Router::execute_route_callback($callback, $arg);
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


    public static function get_error_page(string $error_title, string $error_message="No error message specified")
    {
        $safe_display = Config::get("safe_error_display", false);

        $message = "<h1>$error_title</h1>";
        $message .= ($safe_display === false)? "<p>$error_message</p>" : ""; 
    
        return Response::html($message);
    }
}


Trash::on("fatal",  
fn($message)=> Trash::get_error_page("Error 500 : Internal Error !", "Internal Error from Monkey : $message"));

Trash::on("400",    
fn($other_error)=> Trash::get_error_page("Error 404 : Bad Query", "Bad parameters or Syntax Error : $other_error"));

Trash::on("401",    
fn()=> Trash::get_error_page("Error 401 : Unauthorized"));

Trash::on("404",    
fn($request_path)=> Trash::get_error_page("Error 404 : Page Not found !", "\"".$request_path."\" route not found"));

Trash::on("405",    
fn($request_path, $method)=> Trash::get_error_page("Error 403 : Bad Method !", "$method method is not allowed for \"".$request_path."\" route"));
