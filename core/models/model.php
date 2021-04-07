<?php 

namespace Monkey\Model;

use Kernel\Model\ModelParser;
use Monkey\Dist\DB;
use Monkey\Dist\Query;
use Monkey\Web\Trash;

/**
 * *HAVING KNOWLEDGE ABOUT THE `Query` CLASS IS ADVISED BEFORE DIVING INTO THIS CLASS*
 */
abstract class Model
{
    // Those are the unparsed fields : when sql results fields don't match your model's one
    // non-matching fields are put into the unparsed variable
    // Note : it is not public, so naming a field 'unparsed' is still allowed
    protected $unparsed = [];

    // Table used by the model
    protected $table;
    // Primary key field name, pretty important, it is used by example 
    // by the delete function which use its values to delete from the model table
    protected $primary_key = "";

    // Class name, used by ModelParser constructors
    protected $class = "null";
    // ModelParser associated with the model, 
    // automatically created when a model object is created
    protected $parser = null;




    /**
     * Pretty explicit, get the table name,
     * as $table is protected, a getter is necessary
     */
    public function get_table() : string
    { 
        return $this->table; 
    }



    
    /**
     * Pretty explicit, get the primary key field name,
     * as $primary_key is protected, a getter is necessary
     */
    public function get_primary_key() : string
    { 
        return $this->primary_key; 
    }


    /**
     * Get the class name, not the most useful thing, but 
     * pretty useful when creating a ModelParser
     * 
     * @deprecated
     */
    public function get_class() : string
    {
        return $this->class;
    }





    /**
     * Create a ModelParser object and throw fatal errors if the model
     * isn't well-defined 
     */
    public function __construct()
    {
        if (is_null($this->table)) Trash::handle("No protected \$table defined for model " . $this->class);
        if (is_null($this->primary_key)) Trash::handle("No protected \$primary_key defined for model " . $this->class);

        $this->parser = new ModelParser(get_called_class());
    }




    /**
     * Add a unparsed value
     * 
     * @param key Unparsed Field name
     * @param mixed $value Field value 
     */
    public function set_unparsed(string $key, mixed $value)
    {
        $this->unparsed[$key] = $value;
    }


    /**
     * Query Building: I had some problem with these.
     * Let's assume we have a model called User
     * 
     * With our old methods we had to do this to perform a SELECT query
     * $model = new User();
     * $rows = $model->getAll()->execute()
     * 
     * 
     * But I don't like at all this syntax so I changed de system
     * to have this one
     * 
     * $rows = User::getAll()->execute()
     * 
     * Which respect some idea or generality for the model
     */


    /**
     * This function is important for all the query-building ones
     * It is a static function that can build a `Query` Object 
     * with the model informations 
     * 
     * @param array $fields Fields edited by the new Query
     * @param int $query_mode one of the 4 modes of Query (Query::READ, Query::DELETE...etc)
     */
    public static function build_query(array $fields=null, int $query_mode): Query
    {
        $model = new (get_called_class());
        $table = $model->get_table();
        if ($fields === null) $fields = $model->parser->get_model_fields();
        return new Query($table, $fields, $model->parser, $query_mode);
    }


    public static function get(): Query
    {
        return get_called_class()::build_query(func_get_args(), Query::READ);
    }

    public static function get_all(): Query
    {
        return get_called_class()::build_query(null, Query::READ);
    }

    public static function update(): Query
    {
        return get_called_class()::build_query([], Query::UPDATE);
    }

    public static function insert(): Query
    {
        return get_called_class()::build_query(func_get_args(), Query::CREATE);
    }

    public static function delete_from(): Query
    {
        return get_called_class()::build_query([], Query::DELETE);
    }

    /**
     * Delete the current object in the database
     * this function base its behavior on the primary key value
     * so be aware to use it and declare your model properly !
     */
    public function delete(): void
    {
        $primary = $this->primary_key;
        if (!isset($this->$primary)) Trash::handle("Object has no '$primary' field");
        $this->delete_from()->where($primary, $this->$primary)->execute();
    }



    /**
     * Save the current object by setting all its fields with their values
     * this function base its behavior on the primary key value
     * so be aware to use it and declare your model properly !
     */
    public function save()
    {
        $fields = $this->parser->get_model_fields();
        $primary = $this->primary_key;
        if (!isset($this->$primary)) Trash::handle("Object has no '$primary' field");
        if ($this->primary_key === "") Trash::handle($this::class . " has no \$primary_key defined !");
        $fields_str = [];
        foreach ($fields as $f)
        {
            if ($f === $primary) continue;
            array_push($fields_str, "$f='".$this->$f."'");
        }
        $query = "UPDATE " . $this->table . " SET ". join(",", $fields_str) . " WHERE $primary='" . $this->$primary . "';";
        DB::query($query);
    }

}
