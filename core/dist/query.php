<?php 

namespace Monkey\Dist;

use Kernel\Model\ModelParser;
use Monkey\Web\Trash;


/**
 * This class can build CRUD queries (Create/Insert, Read/Select, Update, Delete/Delete from)
 * It is made to be the more readable possible and to work in synergy with the Monkey models
 */
class Query
{
    // Constants used for Query Constructor
    const CREATE    = 0;
    const READ      = 1;
    const UPDATE    = 2;
    const DELETE    = 3;

    public $get_results = true;
    // CRUD Mode
    public $mode;

    // Particulary Used when working with models
    // the execute function can call the ModelParser to parse 
    // the results and return a bunch of Model objects
    public $parser = null;
    
    // Variable where the final SQL query is stored 
    public $query;

    // Initial Selector :
    // can be :
    // INSERT INTO...
    // SELECT FROM *
    // UPDATE *
    // DELETE FROM * 
    public $selector;

    // --------- QUERY PROPERTIES --------- 

    // common
    public $where = [];     // for READ | UPDATE | DELETE modes

    // CREATE mode only
    public $values = [];

    // READ mode only
    public $order = [];
    public $limit = "";
    public $offset = "";

    // UPDATE mode only
    public $set = [];


    /**
     * Class constructor, really important !
     * 
     * @param string $table Table name to work with
     * @param mixed $fields Can be either one field name of an array of fields names
     * @param ModelParser $parser A ModelParser can be given to parse the results of `execute`
     * @param int $mode CRUD mode (self::CREATE|READ|UPDATE|DELETE)
     */
    public function __construct(string|array $table, mixed $fields=[],  ModelParser $parser=null, int $mode=self::READ)
    {
        if (is_array($table)){
            $table = join(",", $table);
        }
        $this->mode = $mode;
        $this->parser = $parser;
        switch ($mode)
        {
            case self::CREATE:
                $this->selector = "INSERT INTO $table (". join(", ", $fields) . ") VALUES ";
                $this->get_results = false;
                break;
            case self::READ:
                $this->selector = "SELECT ". join(", ", $fields) . " FROM $table ";
                $this->get_results = true;
                break;
            case self::UPDATE:
                $this->selector = "UPDATE $table SET ";
                $this->get_results = false;
                break;
            case self::DELETE:
                $this->selector = "DELETE FROM $table ";
                $this->get_results = false;
                break;
            default:
                Trash::fatal("Bad Query Mode !");
                break;
        }
        return $this;
    }


    /**
     * Add slashes to values (as SQL can interpret 
     * number between quotes as number is it not blocking)
     * 
     * @param string|array $values Can be either one value or a array of ones
     * @note $values is passed by references so be aware 
     */
    public function clean_data(string|array &$values) : void
    {
        if (is_array($values))
        {
            foreach ($values as &$v)
            {
                $this->clean_data($v);
            }
        } 
        else
        {
            $values = "'".addslashes($values)."'";
        }
    }


    /**
     * Add a OR condition to the WHERE array
     * 
     * @return Query return $this so you can chain up quick functions like this one
     */
    public function or() : Query
    {
        array_push($this->where, " OR ");
        return $this;
    }

    
    /**
     * Add a AND condition to the WHERE array
     * 
     * @return Query return $this so you can chain up quick functions like this one
     */
    public function and() : Query
    {
        array_push($this->where, " AND ");
        return $this;
    }


    /**
     * SET statement part for UPDATE queries
     * 
     * @param string $field Field to change
     * @param mixed $value New Value to the field
     */
    public function set(string $field, mixed $value) : Query
    {
        $this->clean_data($value);
        array_push($this->set, "`".$field . "` = " . $value);
        return $this;
    }


    /**
     * VALUES statement part for INSERT queries
     * 
     * @param string Values
     */
    public function values(...$values) : Query
    {
        $this->clean_data($values);
        array_push($this->values, "(". join(",", $values ) .")");
        return $this;
    }


    /**
     * Add a condition to the where array
     * 
     * @param string $field Field to check
     * @param string $value Value you're looking for
     * @param string $comparator Value comparator ('=' by default)
     */
    public function where(string $field, mixed $value, string $comparator="=") : Query
    {
		return $this->rawWhere(" `$field` $comparator $value ");
    }


	/**
	 * Add a condition to the Query conditions
	 * 
	 * @param string $condition condition to add
	 */
	public function rawWhere(string $condition) : Query {
		$this->clean_data($value);
        array_push($this->where, $condition);
        return $this;
	}


    /**
     * Order Statement for SELECT queries
     * 
     * @param string $field Field to sort
     * @param string $mode Ordering Mode (ASC by default)
     */
    public function order_by(string $field, string $mode="ASC"): Query
    {
        array_push($this->order, "$field $mode");
        return $this;
    }


    /**
     * Add a limit to the query
     * 
     * @param int $limit Limit to set
     * @param int $offset (optionnal) you can put the offset value directly
     */
    public function limit(int $limit, int $offset=0): Query
    {
        $this->limit = " LIMIT $limit ";
        if ($offset !== 0) $this->offset = " OFFSET $offset";
        return $this;
    }


    /**
     * Offset part 
     * 
     * @param int $offset limit offset
     * @note You can also define the offset with `limit`
     */
    public function offset(int $offset): Query
    {
        $this->offset = " OFFSET $offset";
        return $this;
    }


    public function build_order() : string 
	{
        if (count($this->order) > 0){
            return " ORDER BY ". join(", ", $this->order);
        }
    }


    /**
     * Build the WHERE statement part 
     * 
     * @return string Either the WHERE if the query has conditions or an empty string
     */
    public function build_wheres() : string
    {
        if (count($this->where) > 0)
        {
            return " WHERE " . join("", $this->where);
        }
        return "";
    }


    /**
     * Build the final Query for READ mode
     * 
     * @note this function should be used by `build` only !
     */
    private function build_read() : string
    {
        $this->query = $this->selector ;
        $this->query .= $this->build_wheres();
        $this->query .= $this->build_order();
        $this->query .= $this->limit;
        $this->query .= $this->offset;
        return $this->query;
    }

    
    /**
     * Build the final Query for CREATE mode
     * 
     * @note this function should be used by `build` only !
     */
    private function build_create(): string
    {
        $this->query = $this->selector;
        $this->query .= join(", ", $this->values);
        return $this->query;
    }

    
    /**
     * Build the final Query for UPDATE mode
     * 
     * @note this function should be used by `build` only !
     */
    private function build_update() : string
    {
        $this->query = $this->selector;
        $this->query .= join(", ", $this->set);
        $this->query .= $this->build_wheres();
        $this->query .= $this->build_order();
        $this->query .= $this->limit;
        return $this->query;
    }

    
    /**
     * Build the final Query for DELETE mode
     * 
     * @note this function should be used by `build` only !
     */
    private function build_delete(): string
    {
        $this->query = $this->selector;
        $this->query .= $this->build_wheres();
        $this->query .= $this->build_order();
        $this->query .= $this->limit;
        return $this->query;
    }


    /**
     * Build the final query and return it
     */
    public function build(): string
    {
        switch ($this->mode)
        {
            case self::CREATE:
                return $this->build_create();
                break;
            case self::READ:
                return $this->build_read();
                break;
            case self::UPDATE:
                return $this->build_update();
                break;
            case self::DELETE:
                return $this->build_delete();
                break;
            default:
                Trash::fatal("Bad Query Mode !");
                break;
        }
    }


    /**
     * Build the query, execute it (and parse it into model objects if a 
     * `ModelParse` was given in the constructor)
     * 
     * @return array An array of either raw SQL results or Model Objects
     */
    public function execute(): array
    {
        $results = DB::query($this->build());
        
        if (is_null($this->parser)) return $results;
        return $this->parser->parse($results);
    }
}