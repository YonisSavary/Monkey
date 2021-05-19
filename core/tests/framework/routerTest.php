<?php

use Monkey\Framework\Router;
use Monkey\Web\Request;
use Monkey\Web\Response;
use PHPUnit\Framework\TestCase;

class RouterTest extends TestCase
{
	public function test_init(){
		Router::init();
		$this->assertIsArray(Router::$list);
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
