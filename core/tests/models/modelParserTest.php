<?php

namespace Models;

use Kernel\Model\ModelParser;
use Monkey\Model\Model;
use PHPUnit\Framework\TestCase;
use stdClass;

class ModelParserTest extends TestCase
{
	public function test_get_model_fields(){
		$parser = new ModelParser("Models\FakeUser");
		$this->assertTrue($parser->get_model_fields() === ["user", "password", "created_at"]);
	}
	
	public function test_parse(){
		$someRows = [];
		array_push($someRows, new stdClass());
		$someRows[0]->user = "Admin";
		$someRows[0]->password = "Password";
		$someRows[0]->created_at = "1995-06-08";
		array_push($someRows, new stdClass());
		$someRows[1]->user = "NewAdmin";
		$someRows[1]->password = "Password";
		$someRows[1]->created_at = "2020-11-26";

		$parser = new ModelParser("Models\FakeUser");
		$user = $parser->parse($someRows);

		$this->assertInstanceOf("Models\FakeUser", $user[0]);
		$this->assertEquals("Admin"		, $user[0]->user);
		$this->assertEquals("Password"	, $user[0]->password);
		$this->assertEquals("1995-06-08", $user[0]->created_at);

		$this->assertInstanceOf("Models\FakeUser", $user[1]);
		$this->assertEquals("NewAdmin"	, $user[1]->user);
		$this->assertEquals("Password"	, $user[1]->password);
		$this->assertEquals("2020-11-26", $user[1]->created_at);
	}
	
}

if (!class_exists("Models\FakeUser")){
	class FakeUser extends Model
	{
		protected $table = "fake_user";
		protected $primary_key = "id";
		public $user;
		public $password;
		public $created_at;
	}
}