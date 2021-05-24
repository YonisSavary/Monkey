<?php 

namespace Monkey\Web;

use Exception;

/**
 * This class has no other purpose than store
 * `GET`, `POST`, `FILES` variables and the request path & slugs
 */
class Request
{
	// Retrieve() use a mask feature
	// To know which mode are we using
	// with this condition (with $mode = self::AUTO): 
	// ($mode & self::GET) === self::POST is 
	// is equal to :
	// <=> 0b00000010 & 0b00000001 	=== 0b00000001
	// <=> 0b00000010 				=== 0b0000001

    const GET  = 0b00000001;
    const POST = 0b00000010;
	const AUTO = 0b11111111;

    public $path;
    public $method;

    public $slugs;

    public $get;
    public $post;
    public $files;

	public $errors = [];

	public static $current = null;


    // In case you want to store more informations
    public $storage = [];

    public function __construct(string $path=null, string $method=null)
    {
		$this->request 	= $_REQUEST;
		$this->session 	= $_SESSION;
        $this->get 		= $_GET;
        $this->post 	= $_POST;
        $this->files 	= $_FILES;
		$this->cookie 	= $_COOKIE;

		$this->path = $path;
		$this->method = $method;
    }


	/**
	 * Build a Request object and return it,
	 * You can also use Request::current() to retrieve the global query
	 */
	public static function build() : Request 
	{
		$req = new Request();
		try 
		{
			$req->path = preg_replace("/\?.+/", "", $_SERVER["REQUEST_URI"]);
			$req->method = $_SERVER["REQUEST_METHOD"];
		} catch (Exception $e){
			$req->path = null;
			$req->method = null;
			array_push($req->errors, $e->getMessage());
		}

		return $req;
	}



	public static function current() : Request|null
	{
		return self::$current;
	}


	public static function set_current(Request $req): void 
	{
		self::$current = $req;
	}


	public static function get_slug_right_type(mixed $value) 
	{
		if (in_array(strtolower($value), ["true", "false"]))
		{
			return (strtolower($value) === "true")? true : false;
		} 
		else if (preg_match("/^[0-9]+$/", $value)) 
		{
			return intval($value);
		} 
		else if (preg_match("/^[0-9]+\.[0-9]+$/", $value)) 
		{
			return floatval($value);
		} 
		return $value;
	}

    /**
     * Given a route path, 
	 * this function build the slugs array for the 
	 * request object
     * 
     * @param string $route_path Path of the Route Object 
	 * @param bool $return Should the function return the built array ?
     */ 
    public function build_slugs(string $route_path, bool $return=false)
    {

        $route_parts = explode("/", $route_path);
        $request_parts = explode("/", $this->path);
        $slugs = [];

        for ($i=0; $i < count($route_parts); $i++)
        {
            $pattern = $route_parts[$i];
            if (!preg_match("/\{.+\}/", $pattern)) continue;
            $slug_name = preg_replace("/[\{\}]/", "", $pattern);
            $slug_value = $request_parts[$i];
            $slugs[$slug_name] = self::get_slug_right_type($slug_value);
        }
		
        if ($return === true) return $slugs;
		$this->slugs = $slugs;
    } 



    /**
     * - Get one or multiples keys from the `Request` Object
	 * - If you give a string in `$keys`, the function will return either the value or null
     * - Returning an associative array with the needed keys and their values
     * - Replace any missing key by `null` !
     * 
     * @param array|string $keys Keys to retrieve
     * @param int $mode self::[AUTO,GET,POST]
     * @param bool $secure Should the function protect values with htmlspecialchars() ?
     * @return mixed Values from the request data
     */
    public function retrieve(array|string $keys, int $mode=self::AUTO, bool $secure=true) : mixed
    {
		$one_param = (!is_array($keys));
        if ($one_param) $keys = [$keys];

		// Mode and their storages
		$storages = [
			self::GET  => &$this->get, 
			self::POST => &$this->post
		];

        $values = [];
        foreach ($keys as $k)
        {
			foreach ($storages as $the_mode => $the_storage)
			{
				if ( (($mode & $the_mode) !== $the_mode)   
				||   (!isset($the_storage[$k]))   ){
					// If the asked mode doesn't match or the storage doesn't hold any value
					// We skip it
					$values[$k] = null;
					continue;
				}
				$values[$k] = ($secure) ? htmlspecialchars($the_storage[$k]) : $the_storage[$k];
				break;
			}
        }
		if ($one_param === true) $values = $values[$keys[0]] ?? [];
		
        return $values;
    }
}