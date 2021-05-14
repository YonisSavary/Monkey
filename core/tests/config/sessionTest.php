<?php

use Monkey\Storage\Session;
use PHPUnit\Framework\TestCase;

class sessionTest extends TestCase
{
	public function test_is_initialized(){
		Session::init();
		$this->assertEquals(true, Session::is_initialized());
	}
	
	public function test_init(){
		Session::init();
		$this->assertEquals(PHP_SESSION_ACTIVE, session_status());
	}
	
	public function test_get(){
		Session::init();
		Session::set("someTest", "someYay");
		$this->assertEquals("someYay", Session::get("someTest"));
		$this->assertEquals(null, Session::get("someInexistant"));
		$this->assertEquals("someDefault", Session::get("someInexistant", "someDefault"));
	}
	
	public function test_set(){
		Session::init();
		Session::set("someTest", "someYay");
		$this->assertTrue(isset($_SESSION["someTest"]));
		$this->assertEquals("someYay", $_SESSION["someTest"]);
	}
	
	public function test_unset(){

		Session::init();
		Session::set("someTest", "someYay");
		$this->assertTrue(isset($_SESSION["someTest"]));

		Session::unset("someTest");
		$this->assertFalse(isset($_SESSION["someTest"]));
	}
}