<?php 

namespace Monkey\Dist;

use Monkey\Config;
use Monkey\Web\Trash;
use PDO;
use PDOException;

class DB {
    static $connection = null;
    static $configuration = [];
    static $last_insert_id = null;

	static $fetch_mode = PDO::FETCH_ASSOC;

    /**
     * Called function as another function of `DB` is called,
     * basically is you try to call `query` but your connection
     * isn't established, then an error message will appear
     */
    public static function check_connection() : void
    {
        if (self::$connection === null){
			Trash::fatal("You tried to use a Function from the DB component but db_enabled is set to false :(");
		} 
    }


    public static function load_configuration() : void
    {
        self::$configuration = [
            "driver" => Config::get("db_driver"),
            "host"   => Config::get("db_host"),
            "port"   => Config::get("db_port"),
            "name"   => Config::get("db_name"),
            "user"   => Config::get("db_user"),
            "pass"   => Config::get("db_pass"),
			"file"	 => Config::get("db_file")
        ];
    }

    public static function get_dsn() : string
    {
		$conf = &self::$configuration;
        $dsn = $conf["driver"] . ":";
		if ($conf["driver"] === "sqlite") 
		{
			$dsn .= $conf["file"];
		} 
		else 
		{
			$dsn .= "host=".$conf["host"].";";
			$dsn .= "port=".$conf["port"].";";
			$dsn .= "dbname=".$conf["name"].";";
		}
        return $dsn;
    }


    /**
     * Initialize the DB service if `db_enabled` is set to `true`
     * Create a PDO connection a store it in `self::$connection`
     * 
     * @return bool Was the connection successful ?
     */
    public static function init() : bool
    {
        if (Config::get("db_enabled") !== true) return false;
        self::load_configuration();
	    self::$connection = self::get_connection(self::$configuration["user"], self::$configuration["pass"]);

        return true;
    }


    public static function get_connection(string $user, string $password, string $custom_dsn=null) : PDO
	{
        try
        {
            $dsn = $custom_dsn ?? self::get_dsn();
            $connection = new PDO($dsn, $user, $password);
        } 
        catch (PDOException $e)
        {
            Trash::fatal("Can't initialize PDO (Usually Bad DB Credentials) <br>PDO Exception : ". $e->getMessage());
        }
        return $connection;
    }


    /**
     * Link to PDO::prepare function
     * 
     * @param string $request Request with bindings
     */
    public static function prepare(string $request) : void
    {
        self::check_connection();
        self::$connection->prepare($request);
    }


    /**
     * Link to PDO::bindParam
     * 
     * @param string $bind Bind Name
     * @param mixed $value Bind value
     */
    public static function bind(string $bind, mixed $value) : void
    {
        self::check_connection();
        self::$connection->bindParam($bind, $value);
    }


    /**
     * Execute the PDO prepared request and return the results
     * or an empty array
     */
    public static function execute() : array
    {
        self::check_connection();
        $statement = self::$connection->execute();
        self::$last_insert_id = self::$connection->lastInsertId() ?? null;
        if ($statement->rowCount() > 0)
        {
            return $statement->fetchAll();
        } 
        else
        {
            return [];
        }
    }



    /**
     * Direct Query Execution, execute the given query
     * and return the results of an empty array
     * 
     * @param string $query SQL Query to execute
     * @param int $mode PDO mode for fetchAll function (`FETCH_ASSOC` by default)
     */
    public static function query(string $query, mixed ...$params) : array
    {
        self::check_connection();
		$query = self::quick_prepare($query, ...$params);

        $statement = self::$connection->query($query);
        self::$last_insert_id = self::$connection->lastInsertId() ?? null;

        if ($statement->rowCount() > 0)
        {
            return $statement->fetchAll(self::$fetch_mode);
        }
        return [];
    }



	/**
	 * This function is simply a homemade prepare function
	 * It does escapes quotes and put string between new quotes if needed
	 * 
	 * @example already_quoted 	quick_prepare("SELECT ... id = {}", "blah") => SELECT ... id = 'blah'
	 * @example no_quoted		quick_prepare("SELECT ... id = '{}'", "blah") => SELECT ... id = 'blah'
	 */
	public static function quick_prepare(string $sql, mixed ...$params) : string
	{
		$index = 0;
	
		do {
			$add_quotes = true;
	
			preg_match("/\'[^\']{0,}\{\}[^\']{0,}\'/", $sql, $matches, PREG_OFFSET_CAPTURE);
			$quoted_slot_pos = -1;
			if (count($matches) > 0){
				$quoted_slot_pos = $matches[0][1] ?? -1;
				$quoted_slot_pos += strpos($matches[0][0], "{}");
			}
			
			$next_pos = strpos($sql, "{}");
			if ($next_pos === false) return $sql;
	
			if ($quoted_slot_pos === $next_pos){
				$add_quotes = false;
			}
	
			$params[$index] = addslashes($params[$index]);
			$param_str = ($add_quotes && !is_numeric($params[$index]))? "'$params[$index]'" : $params[$index];
	
			$sql = preg_replace("/\{\}/", $param_str, $sql, 1);
	
			$index++;
	
		} while ($next_pos !== false);
	
		return $sql;
	}
}