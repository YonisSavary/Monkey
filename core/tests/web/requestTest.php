<?php

use Monkey\Web\Request;
use PHPUnit\Framework\TestCase;

class RequestTest extends TestCase
{
	public function test_current(){
		$req = Request::current();
		$this->assertTrue(  $req instanceof Request || is_null($req) );
	}

	public function test_build(){
		$req = Request::build();
		$this->assertInstanceOf("Monkey\Web\Request", $req);
	}

	public function test_retrieve(){
		$fake_request = new Request();
		$fake_request->get = ["paramOne" => 35];
		$fake_request->post = ["paramTwo" => 45];

		$this->assertEquals		(35, $fake_request->retrieve("paramOne"));
		$this->assertNotEquals	(35, $fake_request->retrieve("paramOne", Request::POST));

		$this->assertEquals		(45, $fake_request->retrieve("paramTwo"));
		$this->assertNotEquals	(45, $fake_request->retrieve("paramTwo", Request::GET));
		
		$this->assertCount(2, $fake_request->retrieve(["paramOne", "paramTwo"]));
	}

}