<?php 

namespace Monkey\Web;

/**
 * This class is here to handle fatal error,
 * when something is wrong, yeet it in the `Trash` and that's it !
 */
class Trash 
{
    /**
     * Display an error message and die.
     * 
     * @param string $message Message to display
     */
    public static function handle(string $message): void
    {
        echo $message;
        die();
    }
}