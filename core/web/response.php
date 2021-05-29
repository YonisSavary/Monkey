<?php 

namespace Monkey\Web;

/**
 * This class is what a controller should return
 */
class Response 
{
    public $header = "Content-type: text/html";
    public $content = "";


    /**
     * Given an object, this function display its
     * content if it is a Response Object (and then exit)
     * 
     * @param mixed $object Object to check
     */
    public static function reveal_if_response(mixed $object) : void
    {
        if ($object instanceof Response)
        {
            $object->reveal();
            exit(0);
        }
    }


    /**
     * @param string $content HTML/String content of the query
     */
    public static function html(string $content): Response
    {
        $r = new Response();
        $r->content = $content;
        return $r;
    }
    
    
    /**
     * @param mixed $content Content to adapt in json and display
     */
    public static function json(mixed $content, int $flags=0): Response
    {
        $r = new Response();
        $r->header = "Content-Type: application/json";
        $r->content = json_encode($content, $flags);
        return $r;
    }


    /**
     * Show the content of the `Response`
     * with the appropriate header
     */
    public function reveal(bool $skip_header=false) : void 
    {
        if (!$skip_header) header($this->header);
        echo $this->content;
    }


    /**
     * Send a file as a Response
     * 
     * @param string $path Path of the file to send
     * @param bool $delete Do we delete the file after it was sent
     * @param bool $secure Ignore the function if the path includes some forbidden terms
     */
    public static function send_file(string $path, bool $secure=true, array $forbidden_keywords=null)
    {
        if ($secure === true)
		{
            $forbidden_keywords = $forbidden_keywords ?? ["/etc", "/opt", "/windows", ".."];
            foreach($forbidden_keywords as $word)
			{
                if (strpos($path, $word) !== false) Trash::fatal("Can't send $path file, Forbidden Access by Framework");
            }
        }

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename='.basename($path));
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . filesize($path));
        ob_clean();
        flush();
        readfile($path);
        
        die();
    }
}