<?php 

namespace Monkey\Dist;

use Exception;
use Monkey\Storage\Config;
use Monkey\Web\Trash;
use PDO;
use PDOException;

class DB {
    static $connection = null;
    static $configuration = [];
    static $last_insert_id = null;

	static $fetch_mode = PDO::FETCH_ASSOC;


	public static function last_insert_id() 
	{
		return self::$last_insert_id ?? null;
	}


	/**
	 * Return true if a table exists (or false if inexistant)
	 */
	public static function table_exists(string $table_name)
	{
		return DB::field_exists($table_name, "1");
	}


	/**
	 * Return true if a field exists inside a table
	 * Return false if either the table or field doesn't exists
	 */
	public static function field_exists(string $table_name, string $field_name)
	{
		try
		{
			DB::query("SELECT $field_name FROM $table_name");
			return true;
		}
		catch (Exception $e)
		{
			return false;
		}
	}


    /**
     * Is a connection created ?
     */
    public static function is_connected(): bool
    {
        return (self::$connection !== null);
    }


    /**
     * Initialize the DB service if `db.enabled` is set to `true`
     * Create a PDO connection a store it in `self::$connection`
     * 
     * @return bool Was the connection successful ?
     */
    public static function init(string $custom_dsn=null) : bool
    {
        self::$configuration = Config::get("db") ?? ["enabled" => false];
        if (self::$configuration["enabled"] !== true) return false;
	    self::$connection = self::get_connection(self::$configuration["user"], self::$configuration["pass"], $custom_dsn);

        return true;
    }


    /**
     * Called function as another function of `DB` is called,
     * basically is you try to call `query` but your connection
     * isn't established, then an error message will appear
     */
    public static function check_connection() : bool
    {
        if (self::$connection === null)
		{
			Trash::fatal("You tried to use a Function from the DB component but db.enabled is set to false :(");
			return false;
		} 
		return true;
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
			$dsn .= "dbname=".$conf["name"];
		}
        return $dsn;
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
     * Direct Query Execution, execute the given query
     * and return the results of an empty array
     * 
     * @param string $query SQL Query to execute
     * @param int $mode PDO mode for fetchAll function (`FETCH_ASSOC` by default)
     */
    public static function query(string $query, mixed ...$params) : array
    {
        self::check_connection();
		if (count($params) > 0)
		{
			$query = self::quick_prepare($query, ...$params);
		}
	
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