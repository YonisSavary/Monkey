<?php 

namespace Monkey\Web;

use Monkey\Services\Logger;

/**
 * This class is what a controller should return
 */
class Response 
{
    const HTML = 1;
    const JSON = 2;
    const FILE = 3;

    public $headers = null;
    public $content = null;
    public $type = null;

    public $file_path = null;


    public function __construct(string $content=null, string|array $headers=[], int $type=Response::HTML) 
    {
        if (!is_array($headers)) $headers = [$headers];
        $this->headers = $headers;
        $this->content = $content;
        $this->type = $type;
    }


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
     * Show the content of the `Response`
     * with the appropriate headers
     */
    public function reveal(bool $skip_headers=false) : void 
    {
        if (!$skip_headers) 
        {
            if (!is_array($this->headers)) $this->headers = [$this->headers];
            foreach($this->headers as $h) header($h);
        }
        if ($this->content !== null) echo $this->content;
        if ($this->type === Response::FILE)
        {
            ob_clean();
            flush();
            readfile($this->file_path);
            die();
        }
    }

    /**
     * @param string $content HTML/String content of the query
     */
    public static function html(string $content): Response
    {
        return new Response($content, "Content-type: text/html", Response::HTML);
    }
    
    
    /**
     * @param mixed $content Content to adapt in json and display
     */
    public static function json(mixed $content, int $flags=0): Response
    {
        return new Response(json_encode($content, $flags), "Content-Type: application/json", Response::JSON);
    }


    /**
     * Send a file as a Response
     * 
     * @param string $path Path of the file to send
     * @param bool $delete Do we delete the file after it was sent
     * @param bool $secure Ignore the function if the path includes some forbidden terms
     */
    public static function file(string $path, bool $secure=true, array $forbidden_keywords=null)
    {
        if ($secure === true)
		{
            $forbidden_keywords = $forbidden_keywords ?? ["/etc", "/opt", "/windows", ".."];
            foreach($forbidden_keywords as $word)
			{
                if (strpos($path, $word) !== false) Trash::fatal("Can't send $path file, Forbidden Access by Framework");
            }
        }

        $r = new Response();
        $r->headers = [
            'Content-Description: File Transfer',
            'Content-Type: application/octet-stream',
            'Content-Disposition: attachment; filename='.basename($path),
            'Content-Transfer-Encoding: binary',
            'Expires: 0',
            'Cache-Control: must-revalidate, post-check=0, pre-check=0',
            'Pragma: public',
            'Content-Length: ' . filesize($path)
        ];
        $r->content = null;
        $r->type = Response::FILE;
        $r->file_path = $path;
        return $r;
    }



    /**
     * Send a file as a Response
     * 
     * @deprecated Use Response::file now !
     * @param string $path Path of the file to send
     * @param bool $delete Do we delete the file after it was sent
     * @param bool $secure Ignore the function if the path includes some forbidden terms
     */
    public static function send_file(string $path, bool $secure=true, array $forbidden_keywords=null)
    {
        return Response::file($path, $secure, $forbidden_keywords);
    }


    /**
     * Return a Response that will redirect the client
     * 
     * @param string $path Path to redirect to
     */
    public static function redirect(string $path) : Response
    {
        Logger::text("Redirecting to $path", Logger::FRAMEWORK);
        $r = new Response();
        $r->headers = "Location: $path";
        $r->content = null;
        return $r;
    }

}