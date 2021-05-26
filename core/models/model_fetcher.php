<?php

namespace Monkey\Model;

use Exception;
use Monkey\Dist\DB;
use Monkey\Storage\Config;
use Monkey\Web\Trash;

class ModelFetcher
{
    public static function get_camel_case_of(string $value)
    {
        $new_string = "";
        $uppercase_next = true;
        foreach (str_split($value) as $letter)
        {
            if ($uppercase_next === true) {
                $letter = strtoupper($letter);
                $uppercase_next = false;
            }
            if (preg_match("/[0-9_]/", $letter)) {
                $uppercase_next = true;
                if ($letter === "_") continue;
            }
            $new_string .= $letter;
        }
        return $new_string;
    }


    public static function check_for_db_connection()
    {
        if (! DB::is_connected()) {
            Trash::fatal("DB is not connected ! Ending.\n");
            die();
        }
    }


    public static function check_for_existing_table(string $table_name) 
    {
        try 
        {
            $table_test = DB::query("SELECT 1 FROM $table_name");
        } catch (Exception $e) {
            Trash::fatal("There is a problem with the '$table_name' table ! Ending.\n".$e->getMessage()."\n");
            die();
        }
    }


    public static function get_model_directory($app_directory) : string
    {
        if (is_null($app_directory))
        {
            $app_directory = Config::get("app_directories");
            if (is_array($app_directory)) $app_directory = $app_directory[0];
            if (!str_ends_with($app_directory, "/")) $app_directory .= "/";
        }
        return $app_directory . "models/" ;
    }



    public static function ask_for_overwriting_file(string $path)
    {
        $opts = getopt("y", ["yes"]);
        $overwrite_option = in_array("y", array_keys($opts)) || in_array("yes", array_keys($opts));

        if (file_exists($path) && !$overwrite_option)
        {
            $overwrite = readline("'$path' already exists ! Do you want to overwrite it ? (y/n) [n] : ");
            $overwrite = strtolower($overwrite);
            if ( $overwrite !== "y" && $overwrite !== "yes"){
                print("Keeping '$path' ! Ending.\n");
                die();
            } 
        } 
        if (file_exists($path)) print("Overwriting '$path'.\n");
    }

    public static function get_table_description(string $table_name)
    {
        return DB::query("DESCRIBE $table_name");
    }




    public static function get_table_name(array $description)
    {

    }


    public static function get_primary_key(array $description)
    {
        foreach ($description as $field){
            if ($field["Key"] === "PRI") return "\tconst primary_key = '" . $field["Field"] . "';";
        }
        return "";
    }


    public static function get_public_fields(array $description)
    {
        return join("\n", 
            array_map(fn($value)=> "\tpublic \t\$".$value["Field"].";", $description)
        );
    }





    public static function build_class_string(string $table_name,  array $description)
    {
        $class_name = self::get_camel_case_of($table_name);
        return "<?php 

namespace Models;

use Monkey\Model\Model;

class $class_name extends Model 
{
\tconst table = '$table_name';
".self::get_primary_key($description)."

".self::get_public_fields($description)."
}

        ";
    }

    public static function fetch(string $table_name, string $app_directory=null, bool $force_overwrite=false)
    {
        $model_directory = self::get_model_directory($app_directory);

        self::check_for_db_connection();
        self::check_for_existing_table($table_name);

        $model_name = ModelFetcher::get_camel_case_of($table_name);
        $path = $model_directory . $model_name . ".php";

        if (!$force_overwrite) self::ask_for_overwriting_file($path);

        $description = self::get_table_description($table_name);

        $class_str = self::build_class_string($table_name, $description);
        file_put_contents($path, $class_str);

        return true;
    }
}