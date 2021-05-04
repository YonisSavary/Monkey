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


    public static function get_fields(array|string $ignores=[]): array 
    {
        if (!is_array($ignores)) $ignores = [$ignores];
        $class_name = get_called_class();
        $model = new $class_name();
        return array_diff($model->parser->get_model_fields(), $ignores);
    }


    /**
     * Create a ModelParser object and throw fatal errors if the model
     * isn't well-defined 
     */
    public function __construct()
    {
        if (is_null($this->table)) Trash::fatal("No 'protected \$table' defined for model " . $this->class);

        // This line makes the primary_key a mandatory property
        //if (is_null($this->primary_key)) Trash::fatal("No 'protected \$primary_key' defined for model " . $this->class);

        $this->parser = new ModelParser(get_called_class());
    }


    /**
     * Add a unparsed value
     * 
     * @param key Unparsed Field name
     * @param mixed $value Field value 
     */
    public function set_unparsed(string $key, mixed $value) : void
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


    public static function get(...$fields): Query
    {
        return get_called_class()::build_query($fields, Query::READ);
    }

    public static function get_all(): Query
    {
        return get_called_class()::build_query(null, Query::READ);
    }

    public static function update(): Query
    {
        return get_called_class()::build_query([], Query::UPDATE);
    }

    public static function insert(...$fields): Query
    {
        return get_called_class()::build_query($fields, Query::CREATE);
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
        if (!isset($this->$primary)) Trash::fatal("Object has no '$primary' field");
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
        if (!isset($this->$primary)) Trash::fatal('Object has no $primary field');
        if ($this->primary_key === "") Trash::fatal($this::class . " has no \$primary_key defined !");
        $fields_str = [];
        foreach ($fields as $f)
        {
            if ($f === $primary) continue;
            array_push($fields_str, "$f='".$this->$f."'");
        }
        $query = "UPDATE " . $this->table . " SET ". join(",", $fields_str) . " WHERE $primary='" . $this->$primary . "';";
        DB::query($query);
    }


    /**
     * Given an array respecting the model structure,
     * this function create a new model instance and return it
     * (or null if failed)
     * 
     * Let's say our model look like :
     * Table 1 :
     *  - cola
     *  - colb
     *  - colc
     * 
     * You can either give :
     *  
     *  [
     *      "cola"=>"somevalue",
     *      "colb"=>"anotherone",
     *      "colc"=>"again"
     *  ]
     * 
     * with this method, you can give any column
     * you want (if we wanted to only give 'cola' and 'colb',
     * it is possible)
     * 
     * Also you can directly give
     * 
     * [ "thefirstvalue", "thesecondone", "thethirdone" ]
     * 
     * with this method, you it is mandatory to give an array
     * with the same element number as the model fields (here: 3)
     * 
     * 
     */
    public static function magic_create(array $data)
	{
        $model_name = get_called_class();
        $new_object = new $model_name();
        $model_fields = $model_name::get_fields();

        if ( array_keys($data) !== range(0, count($data) - 1) ) 
		{
            // Is array associative ?
            foreach ($data as $key => $value)
			{
                if (in_array($key, $model_fields))
				{
                    $new_object->$key = $value;
                }
            }
            return $new_object;
        } 
		else if ( count($model_fields) !== count($data) )
		{
            // Non-associative array, is the array the same size 
            // as the declared model fields
            for ($i=0; $i<count($model_fields); $i++)
			{
                $current_field = $model_fields[$i];
                $value = $data[$i];
                $new_object->$current_field = $value;
            }
            return $new_object;
        } 
		else 
		{
            return null;
        }
    }


    /**
     * See "Model::magic_create" for argument behavior
     * 
     * This model behave the same as magic_create but 
     * insert an item instead of returning is
     */
    public static function magic_insert(array $data) : Query 
	{
        $model_name = get_called_class();
        $new_object = $model_name::magic_create($data);
        if ($new_object === null) return null;

        $fields_to_insert = $model_name::get_fields();
        if ( array_keys($data) !== range(0, count($data) - 1) ) 
		{
            $fields_to_insert = array_keys($data);
        }

        $query = get_called_class()::build_query($fields_to_insert, Query::CREATE);
        $object_values = array_values($data);
        foreach ($object_values as &$val)
		{
            if (preg_match("/^[0-9.]+$/", $val)) continue;
            $val = "'".addslashes($val)."'";
        }
        $values = "(" .  join(", ", $object_values) .  ")";
        array_push($query->values, $values);

        return $query;
    }
}
