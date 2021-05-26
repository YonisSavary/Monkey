<?php 

namespace Kernel\Model;


/**
 * This class is kind of anecdotal, it is mainly used
 * to know which field a Model contains, by playing with the variable scope
 * the `get_class_vars` only return the public ones
 * 
 * Also this class is used to parse SQL results to put them into model objects
 */
class ModelParser {
	
    public $model;

    /**
     * A Model must be given when creating a ModelParser
     * so each Model can have its own ModelParser
     */
    public function __construct(string $modelClassName) 
    {
        $this->model = $modelClassName;
    }



    /**
     * This function can parse PDO results to transform 
     * rows into model objects, every fields in the results 
     * that are not in the model fields are set into the `unparsed` 
     * variable of the model object
     * 
     * @param array $rows_results PDO Query Results (`FETCH_ASSOC` is needed)
     * @return array An array of model object (the model is the one given when the ModelParser was created)
     */
    public function parse(array $rows_results) : array
    {
        $model_name = $this->model ;
        $fields = $model_name::get_fields();
        $results = [];
        $model = $this->model;
        foreach ($rows_results as $row)
        {
            $obj = new $model();
            foreach ($row as $field => $value)
            {
                if (in_array($field, $fields))
                {
                    $obj->$field = $value;
                } 
            }
            array_push($results, $obj);
        }
        return $results;
    }
}