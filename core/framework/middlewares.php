<?php 

namespace Monkey\Framework;

use Monkey\Web\Request;
use Monkey\Web\Response;

/**
 * Interface for Middlewares
 * **It is not mandatory !**
 * You can use it to have a cleaner code
 */
interface Middleware {
    public function handle(Request $req) : Request|Response ;
}