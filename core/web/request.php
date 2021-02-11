<?php 

namespace Monkey\Web;

/**
 * This class has no other purpose than store
 * `GET`, `POST`, `FILES` variables and the request path & slugs
 */
class Request
{
    public $path;
    public $method;

    public $slugs;

    public $get;
    public $post;
    public $files;

    // In case you want to store more informations
    public $storage = [];

    public function __construct()
    {
        $this->get = $_GET;
        $this->post = $_POST;
        $this->files = $_FILES;
    }
}