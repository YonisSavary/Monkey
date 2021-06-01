<?php 

namespace Middlewares;

use Monkey\Framework\Router;
use Monkey\Services\Auth;
use Monkey\Web\Response;
use Monkey\Web\Trash;

class AuthMiddleware 
{
    const REDIRECT_REQUEST = false;
    const NOT_LOGGED_REDIRECT = "/";

    public function handle()
    {
        if (!Auth::is_logged())
        {
            if (self::REDIRECT_REQUEST==true) return Response::redirect(AuthMiddleware::NOT_LOGGED_REDIRECT);
            return Trash::send("401");
        }
    }
}