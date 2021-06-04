<?php 

namespace Monkey\Web;

use Closure;
use Exception;
use Monkey\Framework\Route;
use Monkey\Storage\Storage;

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

	const BODY = 0b10000000;
	const AUTO = 0b11111111;

    public $path;
    public $method;

    public $slugs;

    public $get;
    public $post;
    public $files;
	public $body;

	public $errors = [];

	static $current = null;


    // In case you want to store more informations
    public $storage = [];
	public $uploaded_files = [];

    public function __construct(string $path=null, string $method=null)
    {
		$this->session 	= $_SESSION;
        
		$this->get 		= $_GET;
        $this->post 	= $_POST;
        $this->body		= $_REQUEST;

		$this->files 	= $_FILES;
		$this->discompose_files_data();

		$this->cookie 	= $_COOKIE;
		
		$this->path = $path;
		$this->method = $method;
    }


	public function discompose_files_data()
	{
		if (count($this->files["name"] ?? []) > 0) return false;

		foreach (array_keys($this->files) as $form_name)
		{
			$keys = array_keys($this->files[$form_name]);
			$file_number = count($this->files[$form_name]["name"]);
	
			$new_objects = [];
	
			for ($i=0; $i<$file_number; $i++)
			{
				$file = [];
				foreach ($keys as $key)
				{
					$file[$key] = $this->files[$form_name][$key][$i];
				}
				array_push($new_objects, $file);
			}
	
			$this->files[$form_name] = $new_objects;
		}

		return true;
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
			$req->path = preg_replace("/\?.{0,}/", "", $_SERVER["REQUEST_URI"]);
			$req->method = $_SERVER["REQUEST_METHOD"];
		} 
		catch (Exception $e)
		{
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

			if (preg_match("/{.+:.+}/", $pattern))
			{
				$slug_name = explode(":", $slug_name)[1];
				foreach (array_keys(Route::SLUGS_TYPES) as $type)
				{
					if (!preg_match($type, $pattern)) continue;
					$slugs[$slug_name] = self::get_slug_right_type($slug_value);
				}
			} 
			else 
			{
				$slugs[$slug_name] = self::get_slug_right_type($slug_value);
			}
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
     * @return mixed Values from the request data, null if nothing was found (so you can use the ?? operator for default value)
     */
    public function retrieve(array|string $keys, int $requested_mode=self::AUTO, bool $secure=true) : mixed
    {
		$one_param = (!is_array($keys));
        if ($one_param) $keys = [$keys];

		// Mode and their storages
		$storages = [
			self::GET  => &$this->get, 
			self::POST => &$this->post,
			self::BODY => &$this->body
		];

        $values = [];
        foreach ($keys as $k)
        {
			foreach ($storages as $storage_mode => $the_storage)
			{
				if ((($requested_mode & $storage_mode) !== 0 ) 
				&&  (isset($the_storage[$k])) )
				{
					$values[$k] = ($secure) ? htmlspecialchars($the_storage[$k]) : $the_storage[$k];
					break;
				}
			}
        }
		if ($one_param === true) $values = $values[$keys[0]] ?? [];
		if ($values === []) return null;
        return $values;
    }


	public function get_files_names() : array
	{
		return array_keys($this->files);
	}


	public function get_uploaded_files() : array 
	{
		return $this->uploaded_files;
	}


	public function move_upload(string $filename, string $path_to, Closure $name_editor=null)
	{
		if (!isset($this->files[$filename])) Trash::fatal("'$filename' File was not uploaded");
		if (!isset($this->files[$filename][0])) $this->files[$filename] = [$this->files[$filename]];
	
		foreach ($this->files[$filename] as $file)
		{
			$new_filename = $name_editor($file["name"]) ?? $file["name"];
	
			Storage::fix_path($path_to);
			$full_path_to = Storage::get_path($path_to);
			if (!is_dir($full_path_to)) mkdir($full_path_to, 0777, true);
	
			$new_path = $full_path_to . $new_filename;
			array_push($this->uploaded_files, $path_to . $new_filename);
	
			move_uploaded_file($file["tmp_name"], $new_path);
		}

	}


	public function move_all_uploads_to(string $path, Closure $name_editor=null)
	{
		foreach ($this->files as $name => $file)
		{
			$this->move_upload($name, $path, $name_editor);
		}
	}
}