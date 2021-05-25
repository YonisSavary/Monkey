<?php 

namespace Monkey\Web;

use Closure;
use Monkey\Framework\AppLoader;
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

    public static function send(string $error, ...$args): Response|null
	{
        $callback = self::$error_callbacks[$error] ?? null;

        if (is_callable($callback)) return $callback(...$args);
        return Router::execute_route_callback($callback, $args);
    }

    /**
     * Display an error message and die.
     * 
     * @param string $message Message to display
     */
    public static function fatal(...$args): void
    {
        self::send("fatal", ...$args);
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
fn($request_path, $method)=> Trash::get_error_page("Error 403 : Bad Method !", "$method method is not allowed for \"".$request_path."\" route"));

/*
Trash::on("fatal", 
fn($fatal_error)=> Trash::get_error_page("Error 500 : Internal Server Error !", 
"
<p>Error : <b>".$fatal_error->getMessage()." </b></p>
<p>
    Error Trace : 
    <ul>".
    join("", 
        array_map( fn($error_str)=>"<li>$error_str</li>",
        explode("\n", $fatal_error->getTraceAsString()))
    )
    ."</ul>
</p>" 
));
*/


Trash::on("fatal", 
function($custom_message = null) {
    $fatal_error = error_get_last();
    // If a custom_message is given, it means a fatal error was manually called, so we display it
    // If no error happenned, we don't have something to debug then (it means everything went fine)
    if ($custom_message === null && is_null($fatal_error)){
        return null;
    }
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
                <?php foreach (Router::$temp as $route) { ?>
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

        <details>
            <summary>Configuration Debug</summary> 
            <pre><?= json_encode(Config::$config_ref, JSON_PRETTY_PRINT) ?></pre>
        </details>
    <?php 
    Trash::get_error_page("Error 500 : Internal Server Error !", ob_get_clean())->reveal();
    register_shutdown_function(fn()=>false);
    exit();
} 
);