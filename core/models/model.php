<?php 

namespace Monkey\Model;

use Exception;
use Kernel\Model\ModelParser;
use Monkey\Dist\Query;
use Monkey\Web\Trash;

/**
 * *HAVING KNOWLEDGE ABOUT THE `Query` CLASS IS ADVISED BEFORE DIVING INTO THIS CLASS*
 * 
 * protected members are for internal purpose :
 * - protected $table : name of the SQL table
 * - protected $primary_key : unique column (used for deletion and saving)
 * 
 * public members are your table column
 */
abstract class Model
{
    // Table used by the model
    const table = null;
    // Primary key field name, pretty important, it is used by example 
    // by the delete function which use its values to delete from the model table
    const primary_key = "";



    /**
     * Pretty explicit, get the table name,
     * as $table is protected, a getter is necessary
     */
    public static function get_table() : string
    { 
        return (get_called_class())::table; 
    }

    
    /**
     * Pretty explicit, get the primary key field name,
     * as $primary_key is protected, a getter is necessary
     */
    public function get_primary_key() : string
    { 
        return (get_called_class())::primary_key; 
    }


	/**
	 * Get the field of a Model (SQL columns)
	 */
    public static function get_fields(array|string $ignores=[]): array 
    {
        if (!is_array($ignores)) $ignores = [$ignores];
        $fields = array_keys(get_class_vars(get_called_class()));
        return array_diff($fields, $ignores);
    }

    public static function get_insertable_fields(array|string $ignores=[]): array 
    {
        if (!is_array($ignores)) $ignores = [$ignores];
		$model = new (get_called_class());
        return array_diff($model->insertable ?? [], $ignores);
    }

	/**
	 * Check if your class has one or multiples fields,
	 * If multiple fields are given, we check if all of them exists
	 */
	public static function has_fields(array|string $fields) : bool 
	{
		if (is_array($fields))
		{
			foreach ($fields as $field)
			{
				if (self::has_fields($field) === false) return false;
			}
			return true;
		}
		else 
		{
			return in_array($fields, self::get_fields());
		}
	}


    /**
     * Create a ModelParser object and throw fatal errors if the model
     * isn't well-defined 
     */
    public function __construct()
    {
        if ($this::table === null) Trash::fatal("No 'protected \$table' defined for model " . $this::class, true);
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
    public static function build_query(array $fields=null, int $query_mode=Query::READ): Query
    {
        $model = new (get_called_class());
        $table = $model::get_table();

        if ($fields === null) $fields = $model->get_fields();
        $parser = new ModelParser(get_called_class());
        return new Query($table, $fields, $query_mode, $parser);
    }


    public static function get(...$fields): Query
    {
        return self::build_query($fields, Query::READ);
    }

    public static function get_all(): Query
    {
        return self::build_query(null, Query::READ);
    }

    public static function update(): Query
    {
        return self::build_query([], Query::UPDATE);
    }

    public static function insert(...$fields): Query
    {
        if (count($fields) == 0) $fields = null;
        return self::build_query($fields, Query::CREATE);
    }

    public static function delete_from(): Query
    {
        return self::build_query([], Query::DELETE);
    }


    /**
     * Delete the current object in the database
     * this function base its behavior on the primary key value
     * so be aware to use it and declare your model properly !
     */
    public function delete(bool $return_query = false)
    {
        if ($this::primary_key === null) Trash::fatal('Object has no $primary_key field value');
        $primary = $this::primary_key;
        if (!isset($this->$primary)) Trash::fatal("Object has no '$primary' field");
        $query = $this::delete_from()->where($primary, $this->$primary);
        if ($return_query === true) return $query;
        $query->execute();
    }


    /**
     * Save the current object by setting all its fields with their values
     * this function base its behavior on the primary key value
     * so be aware to use it and declare your model properly !
     */
    public function save(bool $return_query = false)
    {
        $fields = $this::get_fields();

        if ($this::primary_key === null) Trash::fatal('Object has no $primary_key field value');
        $primary = $this::primary_key;

        if (!isset($this->$primary)) Trash::fatal("Object has no $primary field value");
        
		$query = self::update();
        foreach ($fields as $f) { $query->set($f, $this->$f); }

		$query->where($primary, $this->$primary);

        if ($return_query === true) return $query;
		$query->execute();
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
	 * ```php
     *  [
     *      "cola"=>"somevalue",
     *      "colb"=>"anotherone",
     *      "colc"=>"again"
     *  ]
     * ```
     * 
	 * with this method, you can give any column
     * you want (if we wanted to only give 'cola' and 'colb',
     * it is possible)
     * 
     * Also you can directly give
     * 
	 * ```php
     * [ "thefirstvalue", "thesecondone", "thethirdone" ]
     * ```
     * 
	 * **It is mandatory to give an array
     * with the same element number as the model fields (here: 3)
	 * If the given array is non-associative**
     * 
     * 
     */
    public static function magic_create(array $data)
	{
        $model_name = get_called_class();
        $new_object = new $model_name();
        $model_fields = $model_name::get_fields();

		// Build an associative array if the one given is "value-only"
		// This condition is true if $data is non-associative
		if ( array_keys($data) === range(0, count($data)-1) ) 
		{
			if ( count($model_fields) !== count($data) ) {
				throw new Exception("Non associative array size doesn't match the model column number ! (array=".count($model_fields).", model=".count($data).")");
			};
			foreach (range(0, count($data)-1) as $i)
			{
				$data[$model_fields[$i]] = $data[$i];
				unset($data[$i]);
			}
		}
		
		foreach ($data as $key => $value)
		{
			if (in_array($key, $model_fields)) $new_object->$key = $value;
		}

		return $new_object;
    }


    /**
     * See "Model::magic_create" for argument behavior
     * 
     * This model behave the same as magic_create but 
     * insert an item instead of returning is
     */
    public static function magic_insert(array $data) : Query 
	{
        $new_object = self::magic_create($data);
        if ($new_object === null) return null;

        $fields_to_insert = ( array_keys($data) !== range(0, count($data) - 1) ) ?
		array_keys($data) : $new_object::get_fields();

        $query = get_called_class()::build_query($fields_to_insert, Query::CREATE);
        $object_values = array_values($data);
		$query->values(...$object_values);

        return $query;
    }
}
