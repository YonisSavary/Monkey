<?php 

namespace Monkey\Web;

use Closure;
use Error;
use Monkey\Framework\AppLoader;
use Monkey\Framework\Hooks;
use Monkey\Storage\Config;
use Monkey\Framework\Router;
use Monkey\Services\Logger;

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

    public static function on(string $error_code, Closure $callback): void
    {
        self::$error_callbacks[$error_code] = $callback;
    }

    public static function send(string $error, ...$args): Response|null
	{
        Logger::text("event error \"$error\" called !", Logger::FRAMEWORK);
        $callback = self::$error_callbacks[$error] ?? null;
        $res = $callback(...$args);
		
        if ($res instanceof Response){
            $res->reveal();
            exit(1);
        }
        return $res;
    }

    /**
     * Display an error message and die.
     * 
     * @param string $message Message to display
     */
    public static function fatal(string $message=null): void
    {
        Trash::send("500", $message);
    }


    public static function get_error_page(string $error_title, string $error_message="No error message specified")
    {
        $safe_display = Config::get("safe_error_display", false);

        $message = "
            <head>
                <title>$error_title</title>
            </head>
            <h1>$error_title</h1>
        ";
        $message .= ($safe_display === false)? "<p>$error_message</p>" : ""; 
    
        return Response::html($message);
    }
}


Trash::on("400",    
fn($other_error)=> Trash::get_error_page("Error 400 : Fatal Error", "Bad parameters or Syntax Error : $other_error"));

Trash::on("401",    
fn()=> Trash::get_error_page("Error 401 : Unauthorized"));

Trash::on("404",    
fn($request_path)=> Trash::get_error_page("Error 404 : Page Not found !", "\"".$request_path."\" route not found"));

Trash::on("405",    
fn($request_path, $method)=> Trash::get_error_page("Error 405 : Bad Method !", "$method method is not allowed for \"".$request_path."\" route"));

Trash::on("500", 
function($custom_message = null) {
    Logger::text("Received fatal error : $custom_message !", Logger::ERROR, true);
    ob_start();
    ?>
    <style>
        table { border-collapse: collapse; }
        td, th { border: solid 1px black; padding: .5rem; }
        details, summary { padding: .5rem 0;}
    </style>
    <body>
        <p>
            <?= str_replace("\n", "<br>", $fatal_error["message"] ?? "") ?>
        </p>
        <p>
            <?= $custom_message ?? ""  ?>
        </p>

        <details>
            <summary>Routes Debug</summary> 
            <table>
                <tr>
                    <th>Path</th>
                    <th>Callback</th>
                    <th>Name</th>
                    <th>Methods</th>
                    <th>Middlewares</th>
                </tr>
                <?php foreach (Router::$routes as $route) { ?>
                    <tr>
                        <td><?= $route->path ?></td>
                        <td><?= print_r($route->callback, true) ?></td>
                        <td><?= $route->name ?></td>
                        <td><?= print_r($route->methods, true) ?></td>
                        <td><?= print_r($route->middlewares, true) ?></td>
                    </tr>
                <?php } ?>
            </table>
        </details>

        <details>
            <summary>Autoload Debug</summary> 
            <table>
                <tr><th>File</th></tr>
                <?php foreach (AppLoader::$autoload_list as $file) { ?>
                    <tr>
                        <td><?= $file ?></td>
                    </tr>
                <?php } ?>
            </table>
        </details>

    <?php 
    Trash::get_error_page("Error 500 : Internal Server Error !", ob_get_clean())->reveal();
    register_shutdown_function(fn()=>false);
    exit();
} 
);