<?php 

namespace Monkey\Framework;

use Closure;

class Hooks
{
    const AFTER  = 0;
    const BEFORE = 1;

    static $callbacks = [];
    static $globals = [];
    

    public static function add_callback(array &$var, Closure $callback, int $mode)
    {
        switch ($mode)
        {
            case Hooks::BEFORE :
                array_unshift($var, $callback);
                break;
            case Hooks::AFTER :
                array_push($var, $callback);
                break;
        }
    }


    public static function add_for_event(string|array $event_name, Closure $callback, int $mode=Hooks::AFTER)
    {
        if (is_array($event_name))
        {   
            foreach ($event_name as $e) Hooks::add_for_event($e, $callback, $mode);
        } 
        else 
        {
            if (!isset(self::$callbacks[$event_name])) self::$callbacks[$event_name] = [];
            self::add_callback(self::$callbacks[$event_name], $callback, $mode);
        }
    }


    public static function add_for_all(Closure $callback, int $mode=Hooks::AFTER)
    {
        self::add_callback(self::$globals, $callback, $mode);
    }


    public static function execute_event(string $event_name)
    {
        $to_execute = array_merge(self::$callbacks[$event_name] ?? [], self::$globals);
        foreach ($to_execute as $callback)
        {
            if (is_callable($callback)) $callback($event_name);
        }
    }
}