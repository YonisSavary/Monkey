<?php

use Monkey\Framework\Route;
use Monkey\Framework\Router;
use Monkey\Web\Request;
use Monkey\Web\Response;
use PHPUnit\Framework\TestCase;

class RouteTest extends TestCase
{
	public function test_init(){
		Router::init();
		$this->assertIsArray(Router::$list);
	}

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

	public function test_add(){
		Router::init();
		Router::add("/somePath", function(){});
		$this->assertNotEmpty(Router::$temp);
	}

	public function test_add_to_register(){
		$this->assertTrue(true, "TODO : test_add_to_register");
	}

	public function test_remove(){
		$this->assertTrue(true, "TODO : test_remove");
	}

	public function test_exists(){
		Router::init();
		Router::add("/somePath", function(){});
		$exists = Router::exists("/somePath");
		$this->assertTrue($exists);
	}


	public function test_execute_route_callback(){
		Request::$current = Request::build();
		$res = Router::execute_route_callback(function(){
			return Response::json("someResponse");
		});
		$this->assertInstanceOf("Monkey\Web\Response", $res);
	}

	public function test_route(){
		// Manually creating a Request and a Route,
		// Then forcing the Router to Request the created one
		$req = new Request("/auto", "GET");
		Router::init();
		Router::add("/auto", function(){ return Response::json("oui"); });
		$res = Router::route($req, true);
		$this->assertInstanceOf("Monkey\Web\Response", $res);
	}
}
