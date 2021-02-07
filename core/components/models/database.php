<?php 

namespace Monkey;

use Monkey\Web\Trash;
use PDO;
use PDOException;

class DB {

    static $connection;
    static $configuration = [];

    /**
     * Called function as another function of `DB` is called,
     * basically is you try to call `query` but your connection
     * isn't established, then an error message will appear
     */
    public static function check_connection() : void
    {
        if (DB::$connection === false) Trash::handle("Tried to use DB Function ! db_enabled is set to false ");
    }



    /**
     * Initialize the DB service if `db_enabled` is set to `true`
     * Create a PDO connection a store it in `DB::$connection`
     * 
     * @return bool Was the connection successful ?
     */
    public static function init() : bool
    {
        if (Config::get("db_enabled") !== true) return false;

        DB::$configuration = [
            "driver"=>Config::get("db_driver"),
            "host"=>Config::get("db_host"),
            "port"=>Config::get("db_port"),
            "name"=>Config::get("db_name"),
            "user"=>Config::get("db_user"),
            "pass"=>Config::get("db_pass")
        ];

        $dsn = DB::$configuration["driver"] . ":";
        $dsn .= "host=".DB::$configuration["host"].";";
        $dsn .= "port=".DB::$configuration["port"].";";
        $dsn .= "dbname=".DB::$configuration["name"].";";
        
        try
        {
            $connection = new PDO($dsn, DB::$configuration["user"], DB::$configuration["pass"]);
        } catch (PDOException $e)
        {
            Trash::handle("Bad PDO parameters");
        }
        DB::$connection = $connection;
        return true;
    }



    /**
     * Link to PDO::prepare function
     * 
     * @param string $request Request with bindings
     */
    public static function prepare(string $request) : void
    {
        DB::$connection->prepare($request);
    }



    /**
     * Link to PDO::bindParam
     * 
     * @param string $bind Bind Name
     * @param mixed $value Bind value
     */
    public static function bind(string $bind, mixed $value) : void
    {
        DB::$connection->bindParam($bind, $value);
    }


    

    /**
     * Execute the PDO prepared request and return the results
     * or an empty array
     */
    public static function execute() : array
    {
        $statement = DB::$connection->execute();
        if ($statement->rowCount() > 0)
        {
            return $statement->fetchAll();
        } else {
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
    public static function query(string $query, int $mode=PDO::FETCH_ASSOC)
    {
        $statement = DB::$connection->query($query);
        if ($statement->rowCount() > 0)
        {
            return $statement->fetchAll($mode);
        } else {
            return [];
        }
    }
}