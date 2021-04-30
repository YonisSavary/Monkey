<?php 

namespace Monkey\Web;

/**
 * This class has no other purpose than store
 * `GET`, `POST`, `FILES` variables and the request path & slugs
 */
class Request
{
    const GET = 0;
    const POST = 1;

    public $path;
    public $method;

    public $slugs;

    public $get;
    public $post;
    public $files;



    // In case you want to store more informations
    public $storage = [];

    public function __construct()
    {
        $this->get = $_GET;
        $this->post = $_POST;
        $this->files = $_FILES;
    }





    /**
     * - Get one or multiples keys from the `Request` Object
     * - Returning an associative array with the needed keys and their values
     * - Replace any missing key by `null` !
     * 
     * @param array|string $keys Keys to retrieve
     * @param int $mode Request::[GET,POST], choose either from GET or POST data
     * @param bool $secure Should the function protect values with htmlspecialchars() ?
     * @return array Values from the request data
     */
    public function retrieve(array|string $keys, int $mode=Request::GET, bool $secure=true) : array
    {
        if (!is_array($keys)) $keys = [$keys];

        $storage = null;
        if ($mode === Request::GET) $storage = &$this->get;
        if ($mode === Request::POST) $storage = &$this->post;
        if ($storage === null) Trash::fatal("Bad API Mode for retrieve function !");

        $values = [];
        foreach ($keys as $k)
        {
            if (!key_exists($k, $storage)){
                $values[$k] = null;
            } else {
                $values[$k] = ($secure) ? htmlspecialchars($storage[$k]) : $storage[$k];
            } 
        }
        
        return $values;
    }



    /**
     * This function follow the same behavior as Request->retrieve but return the
     * value of a key directly instead of returning an array
     * 
     * @param string $keys Keys to retrieve
     * @param int $mode Request::[GET,POST], choose either from GET or POST data
     * @param bool $secure Should the function protect values with htmlspecialchars() ?
     */
    public function retrieveOne(string $key, mixed $default, int $mode=Request::GET, bool $secure=true) {
        return $this->retrieve($key, $mode, $secure)[$key] ?? $default;
    }


}