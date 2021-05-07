<?php

use Monkey\Config;
use PHPUnit\Framework\TestCase;

class configTest extends TestCase
{
	public function test_read_file(){
		$this->assertTrue(true, "TODO");
	}
	
	public function test_save(){
		$this->assertTrue(true, "TODO");
	}

	public function test_init(){
		Config::init();
		$this->assertIsArray($GLOBALS["monkey"]["config"]);
	}
	
	public function test_exists(){
		Config::init();
		Config::set("someTest", 42);
		Config::set("someSecond", 52);
		$this->assertTrue(Config::exists("someTest"));
		$this->assertTrue(Config::exists(["someTest", "someSecond"]));
	}
	
	public function test_multiple_exists(){
		Config::init();
		Config::set("someTest", 42);
		Config::set("someSecond", 52);
		$this->assertTrue(Config::multiple_exists(["someTest", "someSecond"]));
	}
	
	public function test_get(){
		Config::init();
		Config::set("someTest", 42);
		$this->assertEquals(42, Config::get("someTest"));
		$this->assertEquals(null, Config::get("inexistantOne"));
		$this->assertEquals("yay!", Config::get("withDefault", "yay!"));
	}
	
	public function test_set(){
		Config::init();
		Config::set("someTest", 42);
		$this->assertEquals(42, $GLOBALS["monkey"]["config"]["someTest"]);
	}
	
	
}