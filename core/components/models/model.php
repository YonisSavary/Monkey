<?php 

namespace Monkey;

use Kernel\ModelParser;
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
     * Create a SELECT Query Object with the Model information
     * and return it, you can put as arguments the names of the 
     * fields you are trying to fetch
     * 
     * @return Query the SELECT SQL Query Object
     * @example base SomeModel->get("login", "password", "salt")->execute()
     */
    public function get(): Query
    {
        $fields = func_get_args();
        return new Query($this->table, $fields, $this->parser, Query::READ);
    }



    
    /**
     * Create a SELECT Query Object with the Model information
     * and return it, this function fetch All the fields of the 
     * model (not all the fields inside the table, but the all of 
     * the public fields you've declared in your model)
     * 
     * @return Query the SELECT SQL Query Object
     */
    public function get_all(): Query
    {
        $fields = $this->parser->get_model_fields();
        return new Query($this->table, $fields, $this->parser, Query::READ);
    }



    
    /**
     * Create a UPDATE Query Object with the Model information
     * and return it
     * 
     * @return Query the UPDATE SQL Query Object
     */
    public function update(): Query
    {
        return new Query($this->table, [], $this->parser, Query::UPDATE);
    }



    
    /**
     * Create a INSERT Query Object with the Model information
     * and return it
     * 
     * @return Query the INSERT SQL Query Object
     */
    public function insert(): Query
    {
        $fields = func_get_args();
        return new Query($this->table, $fields, $this->parser, Query::CREATE);
    }



    
    /**
     * Create a DELETE Query Object with the Model information
     * and return it
     * 
     * @return Query the DELETE SQL Query Object
     */
    public function delete_from(): Query
    {
        return new Query($this->table, [], $this->parser, Query::DELETE);
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