<?php

use Monkey\Storage\Config;
use Monkey\Dist\DB;
use PHPUnit\Framework\TestCase;

class databaseTest extends TestCase
{
	public function test_init(){
		$initialized = DB::init("sqlite::memory:", true);
		$this->assertTrue($initialized);
	}

	public function test_check_connection(){
		DB::init("sqlite::memory:", true);
		$this->assertTrue(DB::check_connection());
	}
	
	public function test_load_configuration(){
		DB::init("sqlite::memory:", true);
		DB::load_configuration();
		$this->assertCount(7, DB::$configuration);
	}
	
	public function test_get_dsn(){
        DB::$configuration = [
            "driver" => "mysql",
            "host"   => "127.0.0.1",
            "port"   => "3306",
            "name"   => "local"
        ];
		$this->assertEquals("mysql:host=127.0.0.1;port=3306;dbname=local", DB::get_dsn());

		DB::$configuration = [
			"driver" => "sqlite",
			"file" => "./some/path"
		];
		$this->assertEquals("sqlite:./some/path", DB::get_dsn());
	}
	
	
	public function test_get_connection(){
		$connection = DB::get_connection("", "", "sqlite::memory:");
		$this->assertInstanceOf("PDO", $connection);
	}
	
	public function test_query(){
		DB::init("sqlite::memory:", true);
		DB::$force_fetch_all = true;

		$res = DB::query("SELECT 12 as A");
		$expected_obj = [["A" => 12]];
		
		$this->assertEquals(
			$expected_obj[0]["A"], 
			$res[0]["A"]
		);
	}
	
	public function test_quick_prepare(){
		$this->assertEquals("fk_user = 'Admin'", DB::quick_prepare("fk_user = {}", "Admin"));
		$this->assertEquals("fk_user = 'Admin'", DB::quick_prepare("fk_user = '{}'", "Admin"));

		$this->assertEquals("fk_user = '5'", DB::quick_prepare("fk_user = '{}'", "5"));
		$this->assertEquals("fk_user = '5'", DB::quick_prepare("fk_user = '{}'", 5));
		$this->assertEquals("fk_user = 5", DB::quick_prepare("fk_user = {}", 5));
	}
	
}