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

    /**
     * Called function as another function of `DB` is called,
     * basically is you try to call `query` but your connection
     * isn't established, then an error message will appear
     */
    public static function check_connection() : void
    {
        if (DB::$connection === null) Trash::fatal("You tried to use a Function from the DB component but db_enabled is set to false :(");
    }


    public static function load_configuration() : void
    {
        DB::$configuration = [
            "driver" => Config::get("db_driver"),
            "host"   => Config::get("db_host"),
            "port"   => Config::get("db_port"),
            "name"   => Config::get("db_name"),
            "user"   => Config::get("db_user"),
            "pass"   => Config::get("db_pass")
        ];
    }

    public static function get_dsn() : string
    {
        $dsn = DB::$configuration["driver"] . ":";
        $dsn .= "host=".DB::$configuration["host"].";";
        $dsn .= "port=".DB::$configuration["port"].";";
        $dsn .= "dbname=".DB::$configuration["name"].";";
        return $dsn;
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
        DB::load_configuration();

        DB::$connection = DB::get_connection(DB::$configuration["user"], DB::$configuration["pass"]);
        return true;
    }

    public static function get_connection(string $user, string $password, string $custom_dsn=null){
        try
        {
            $dsn = $custom_dsn ?? DB::get_dsn();
            $connection = new PDO($dsn, $user, $password);
        } 
        catch (PDOException $e)
        {
            Trash::fatal("Can't initialize PDO (Usually Bad DB Credentials) : ". $e->getMessage());
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
        DB::check_connection();
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
        DB::check_connection();
        DB::$connection->bindParam($bind, $value);
    }


    

    /**
     * Execute the PDO prepared request and return the results
     * or an empty array
     */
    public static function execute() : array
    {
        DB::check_connection();
        $statement = DB::$connection->execute();
        DB::$last_insert_id = DB::$connection->lastInsertId() ?? null;
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
    public static function query(string $query, int $mode=PDO::FETCH_ASSOC)
    {
        DB::check_connection();
        $statement = DB::$connection->query($query);
        DB::$last_insert_id = DB::$connection->lastInsertId() ?? null;
        if ($statement->rowCount() > 0)
        {
            return $statement->fetchAll($mode);
        }
        return [];
    }
}