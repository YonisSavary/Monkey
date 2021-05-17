<?php 

namespace Monkey\Web;

use Exception;
use Monkey\Framework\Router;

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
	// 0b00000010 & 0b00000001 === 0b00000001
	// 0b00000010 === 0b0000001

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


	public static function current() : Request|null
	{
		return self::$current;
	}


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
			foreach ($storages as $theMode => $theStorage)
			{
				if ( (($mode & $theMode) !== $theMode)   
				||   (!isset($theStorage[$k]))   ){
					// If the asked mode doesn't match or the storage doesn't hold any value
					// We skip it
					$values[$k] = null;
					continue;
				}
				$values[$k] = ($secure) ? htmlspecialchars($theStorage[$k]) : $theStorage[$k];
				break;
			}
        }
		if ($one_param === true) $values = $values[$keys[0]] ?? null;
		
        return ($values === []) ? null : $values;
    }
}