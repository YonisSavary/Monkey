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

    const ALLOWED_UNPROTECTED_TERMS = [
        "/^(NOT)? NULL$/i",
        "/[0-9]+ AND [0-9]+/"
    ];

    // CRUD Mode
    protected $mode;

    // Particulary Used when working with models
    // the execute function can call the ModelParser to parse 
    // the results and return a bunch of Model objects
    protected $parser = null;

    // Variable where the final SQL query is stored 
    protected $sql_string = null;
    protected $sql_parts = [];

    protected $given_transition = false;
    protected $has_condition = false;

    // --------- QUERY PROPERTIES --------- 

    // Initial Selector :
    // can be :
    // INSERT INTO...
    // SELECT FROM *
    // UPDATE *
    // DELETE FROM * 
    protected $selector;

    // common
    protected $where = [];     // for READ | UPDATE | DELETE modes

    // CREATE mode only
    protected $values = [];

    // READ mode only
    protected $order = [];
    protected $limit = null;
    protected $offset = null;

    // UPDATE mode only
    protected $set = [];



    /**
     * Class constructor, really important !
     * 
     * @param string $table Table name to work with
     * @param mixed $fields Can be either one field name of an array of fields names
     * @param ModelParser $parser A ModelParser can be given to parse the results of `execute`
     * @param int $mode CRUD mode (self::CREATE|READ|UPDATE|DELETE)
     */
    public function __construct(string|array $table, string|array $fields=[], int $mode=self::READ, ModelParser $parser=null)
    {
        if (!is_array($fields)) $fields = [$fields];
        if (is_array($table)) $table = join(",", $table);

        $this->mode = $mode;
        $this->parser = $parser;
        switch ($mode)
        {
            case self::CREATE:
                $fields = ($fields == [])? "" : "(". join(", ", $fields) . ")";
                $this->selector = "INSERT INTO $table $fields";
                break;
            case self::READ:
                $this->selector = "SELECT ". join(", ", $fields) . " FROM $table";
                break;
            case self::UPDATE:
                $this->selector = "UPDATE $table";
                break;
            case self::DELETE:
                $this->selector = "DELETE FROM $table";
                break;
            default:
                Trash::fatal("Bad Query Mode !");
                break;
        }
        return $this;
    }

    /**
     * Constructor "Shortcuts" (more intuitive)
     */
    
    /**
     * Start a "SELECT ... FROM ..." Query
     * You can also use the constructor with Query::READ mode
     */
    public static function insert(string|array $table, string|array $field=[]): Query 
    {
        return new Query($table, $field, Query::CREATE);
    }


    /**
     * Start a "INSERT INTO ... (...)" Query
     * You can also use the constructor with Query::READ mode
     */
    public static function select(string|array $table, string|array $field="*"): Query 
    {
        return new Query($table, $field, Query::READ);
    }


    /**
     * Start a "UPDATE ..." Query
     * You can also use the constructor with Query::READ mode
     */
    public static function update(string|array $table): Query 
    {
        return new Query($table, [], Query::UPDATE);
    }


    /**
     * Start a "SELECT ... FROM ..." Query
     * You can also use the constructor with Query::READ mode
     */
    public static function delete_from(string|array $table): Query 
    {
        return new Query($table, [], Query::READ);
    }





    /**
     * Add slashes to values (as SQL can interpret 
     * number between quotes as number is it not blocking)
     * 
     * @param string|array $values Can be either one value or a array of ones
     * @note $values is passed by references so be aware 
     */
    public function clean_data(mixed &$values): void
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
            // Don't need to clean 59 or 1 for example, 
            // but we do need words
            if (is_bool($values))
            {
                $values = ($values === true) ? "TRUE" : "FALSE";
            } 
            else if ($values === null)
            {
                $values = "NULL";
            }
            else if (!is_numeric($values))
            {
                $skip = false;
                foreach (Query::ALLOWED_UNPROTECTED_TERMS as $allowed)
                {
                    if (preg_match($allowed, $values)){
                        $skip = true;
                        break;
                    }
                }
                if (!$skip) $values = "'".addslashes($values)."'";
            } 
        }
    }


    public function set_parser(ModelParser $parser): Query
    {
        $this->parser = $parser;
        return $this;
    }






    /**
     * Reset all the query actions excepts its selector,
     * so you can re-use the same query multiple time
     */
    public function reset(): Query
    {
        $this->where = [];     // for READ | UPDATE | DELETE modes

        $this->values = [];

        $this->order = [];
        $this->limit = "";
        $this->offset = "";

        $this->set = [];

        $this->given_transition = false;
        $this->has_condition = false;

        return $this;
    }


    /**
     * Add a OR condition to the WHERE array
     * 
     * @return Query return $this so you can chain up quick functions like this one
     */
    public function or(): Query
    {
        if ($this->given_transition === false){
            array_push($this->where, "OR");
            $this->given_transition = true;
        }
        return $this;
    }

    
    /**
     * Add a AND condition to the WHERE array
     * 
     * @return Query return $this so you can chain up quick functions like this one
     */
    public function and(): Query
    {
        if ($this->given_transition === false){
            array_push($this->where, "AND");
            $this->given_transition = true;
        }
        return $this;
    }


    /**
     * SET statement part for UPDATE queries
     * 
     * @param string $field Field to change
     * @param mixed $value New Value to the field
     */
    public function set(string $field, mixed $value, bool $do_clean_value=true): Query
    {
        if ($do_clean_value === true) $this->clean_data($value);
        return $this->raw_set("`".$field . "` = " . $value);
    }

    public function raw_set(string $expression) : Query 
    {
        array_push($this->set, $expression);
        return $this;
    }


    /**
     * VALUES statement part for INSERT queries
     * 
     * @param string Values
     */
    public function values(...$values): Query
    {
        $this->clean_data($values);
        array_push($this->values, "(". join(", ", $values ) .")");
        return $this;
    }


    /**
     * Add a condition to the where array
     * 
     * You can chain function like this :
     * where()->and()->where()->or()
     * 
     * If you chain where() it will be treated as a AND condition
     * where()->where() <=> where()->and()->where()
     * 
     * @param string $field Field to check
     * @param string $value Value you're looking for
     * @param string $comparator Value comparator ('=' by default)
     */
    public function where(string $field, mixed $value, string $comparator="=", bool $do_clean_value = true): Query
    {
		if ($do_clean_value === true){
            $this->clean_data($value);
        } 
		return $this->raw_where("`$field` $comparator $value");
    }


	/**
	 * Add a condition to the Query conditions
	 * 
	 * @param string $condition condition to add
	 */
	public function raw_where(string $condition): Query {
        // If no condition join were given, we automatically add a and() condition
        if ($this->has_condition === true && $this->given_transition === false )
        {
            $this->and();
        }

        array_push($this->where, $condition);

        $this->has_condition = true;
        $this->given_transition = false;
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
     * Add a limit section to the query
     * You can also set the offset with this function
     * 
     * @param int $limit Limit to set
     * @param int $offset (optionnal) you can put the offset value directly
     */
    public function limit(int $limit, int $offset=null): Query
    {
        $this->limit = $limit;
        if ($offset !== null) $this->offset($offset);
        return $this;
    }


    /**
     * You can also define the offset with `limit`
     */
    public function offset(int $offset): Query
    {
        $this->offset = $offset;
        return $this;
    }



    private function build_part(string $prefix, string $glue, array $pieces)
    {
        if (count($pieces) === 0) return null;
        return $prefix. join($glue, $pieces);
    }

    private function build_order(){
        return $this->build_part("ORDER BY ", ", ", $this->order);
    }

    private function build_wheres(){
        return $this->build_part("WHERE ", " ", $this->where);
    }

    private function build_set(){
        return $this->build_part("SET ", ", ", $this->set );
    }

    private function build_values(){
        return $this->build_part("VALUES ", ", ", $this->values );
    }

    private function build_limit(){
        return ($this->limit === null) ? null : "LIMIT $this->limit";
    }

    private function build_offset(){
        return ($this->offset === null) ? null : "OFFSET $this->offset";
    }


    private function build_read() 
    {
        $this->sql_parts = [
            $this->build_wheres(),
            $this->build_order(),
            $this->build_limit(),
            $this->build_offset()
        ];
    }

    
    private function build_create()
    {
        $this->sql_parts  = [
            $this->build_values()
        ];
    }

    
    private function build_update() 
    {
        $this->sql_parts  = [
            $this->build_set(),
            $this->build_wheres(),
            $this->build_order(),
            $this->build_limit()
        ];
    }

    
    private function build_delete()
    {
        $this->sql_parts  = [
            $this->build_wheres(),
            $this->build_order(),
            $this->build_limit()
        ];
    }


    /**
     * Build the final query and return it as a String
     * @return string $query Your SQL Query
     */
    public function build(): string
    {
        switch ($this->mode)
        {
            case self::CREATE:
                $this->build_create();
                break;
            case self::READ:
                $this->build_read();
                break;
            case self::UPDATE:
                $this->build_update();
                break;
            case self::DELETE:
                $this->build_delete();
                break;
            default:
                Trash::fatal("Bad Query Mode !");
                break;
        }
        $this->sql_parts = array_merge([$this->selector], $this->sql_parts);
        $this->sql_parts = array_filter($this->sql_parts, fn($value)=> !is_null($value) && $value !== "" );
        return join(" ", $this->sql_parts);
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