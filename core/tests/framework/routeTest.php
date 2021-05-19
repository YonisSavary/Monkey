<?php

use Monkey\Framework\Route;
use Monkey\Framework\Router;
use Monkey\Web\Request;
use Monkey\Web\Response;
use PHPUnit\Framework\TestCase;

class RouteTest extends TestCase
{
	public function test_get_regex(){
		$regex = Route::get_regex("/api/some/{user}");
		$this->assertEquals("/^\/api\/some\/.+$/", $regex);
	}

	public function test___construct(){
		$route = new Route(
			"/path",
			function(){}, 
			"someName", 
			["middle1"],
			["GET"]
		);

		$this->assertEquals("/path", $route->path);
		$this->assertIsCallable($route->callback);
		$this->assertEquals("someName", $route->name);
		$this->assertEquals(["middle1"], $route->middlewares);
		$this->assertEquals(["GET"], $route->methods);
	}
}
