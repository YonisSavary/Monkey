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
     * @param string $content Content to display
     */
    public static function html(string $content): Response
    {
        $r = new Response();
        $r->content = $content;
        return $r;
    }
    
    

    /**
     * This function display objects in JSON, but also send
     * a `application/json` header for browsers like Mozilla
     * which have a special layout for JSON content
     * 
     * @param mixed $content Content to adapt in json and display
     */
    public static function json(mixed $content): Response
    {
        $r = new Response();
        $r->header = "Content-Type: application/json";
        $r->content = json_encode($content);
        return $r;
    }



    /**
     * Show the content of the `Response`
     * with the appropriate header
     */
    public function reveal() : void 
    {
        header($this->header);
        echo $this->content;
    }



    /**
     * Send a file as a Response
     * 
     * @param string $path Path of the file to send
     * @param bool $delete Do we delete the file after it was sent
     * @param bool $secure Ignore the function if the path includes some forbidden terms
     */
    public static function send_file(string $path, bool $delete=false, bool $secure=false)
    {
        if ($secure === true){
            foreach(["/etc", "/opt", "/windows", ".."] as $word){
                if (strpos($path, $word) !== false){
                    return false;
                }
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
        if ($delete) unlink($path);
        die();
    }
}