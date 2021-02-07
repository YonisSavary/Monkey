<?php 

namespace Monkey\Web;

class API 
{
    const GET = 0;
    const POST = 1;


    /**
     * Get one or multiples keys from a `Request` Object
     * Returning an associative array with the needed keys and their values
     * Raise and Return a JSON error if any key is missing !
     * 
     * @param Request $req Request Object containing the request data
     * @param array $keys Keys to retrieve
     * @param int $mode API::[GET,POST], choose either from GET or POST data
     * @return array Values from the request data
     */
    public static function retrieve(Request $req, array $keys, int $mode=API::GET) : array 
    {
        $storage = null;
        if ($mode === API::GET) $storage = &$req->get;
        if ($mode === API::POST) $storage = &$req->post;
        if ($storage === null) Trash::handle("Bad API Mode for retrieve function !");

        $values = [];
        foreach ($keys as $k)
        {
            if (!isset($storage[$k])) API::error("$k Key is needed !");
            $values[$k] = $storage[$k];
        }
        
        return $values;
    }




    /**
     * Give the client a JSON error message
     * 
     * @param string $message Message to display
     */
    public static function error(string $message): Response
    {
        return Response::json(["status"=>"error", "message"=>$message]);
    }



    /**
     * Give the client a JSON "ok" message
     * 
     * @param string $message Message to display
     */
    public static function ok(string $message="done") : Response
    {
        return Response::json(["status"=>"ok", "message"=>$message]);
    }




    /**
     * Give the client a JSON custom message
     * 
     * @param string $status Usually "ok" or "error"
     * @param string $message Message to display
     */
    public static function custom(string $status, string $message) : Response
    {
        return Response::json(["status"=>"$status", "message"=>$message]);
    }

}