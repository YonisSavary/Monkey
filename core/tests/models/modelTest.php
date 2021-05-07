<?php

namespace Models;

use Monkey\Dist\Query;
use Monkey\Model\Model;
use PHPUnit\Framework\TestCase;

class modelTest extends TestCase
{
	public function test_get_table(){
		$fake = new FakeUser();
		$this->assertEquals("fake_user", $fake->get_table());
	}

	public function test_get_primary_key(){
		$fake = new FakeUser();
		$this->assertEquals("id", $fake->get_primary_key());
	}

	public function test_get_fields(){
		$this->assertTrue( FakeUser::get_fields() === ["user", "password", "created_at"] );
	}

	public function test_get_unparsed(){
		$fake = new FakeUser();
		$this->assertIsArray($fake->get_unparsed());
	}

	public function test_set_unparsed(){
		$fake = new FakeUser();
		$fake->set_unparsed("someKey", "someUser");
		$this->assertCount(1, $fake->get_unparsed());
	}

	public function test_build_query(){
		$query = FakeUser::build_query(["user", "password"], Query::CREATE);
		$this->assertInstanceOf("Monkey\Dist\Query", $query);
	}

	public function test_get(){
		$query = FakeUser::get("user", "password");
		$this->assertInstanceOf("Monkey\Dist\Query", $query);
	}

	public function test_get_all(){
		$query = FakeUser::get_all();
		$this->assertEquals(Query::READ, $query->mode);
		$this->assertInstanceOf("Monkey\Dist\Query", $query);
	}

	public function test_update(){
		$query = FakeUser::update();
		$this->assertEquals(Query::UPDATE, $query->mode);
		$this->assertInstanceOf("Monkey\Dist\Query", $query);
	}

	public function test_insert(){
		$query = FakeUser::insert();
		$this->assertEquals(Query::CREATE, $query->mode);
		$this->assertInstanceOf("Monkey\Dist\Query", $query);
	}

	public function test_delete_from(){
		$query = FakeUser::delete_from();
		$this->assertEquals(Query::DELETE, $query->mode);
		$this->assertInstanceOf("Monkey\Dist\Query", $query);
	}

	public function test_delete(){
		$this->assertTrue(true, "TODO");
	}

	public function test_save(){
		$this->assertTrue(true, "TODO");
	}

	public function test_magic_create(){

		$badFakeUser = FakeUser::magic_create([ "Admin", "1995-06-08" ]);
		$fakeUser = FakeUser::magic_create([ "Admin", "Password", "1995-06-08" ]);
		$fakeUserWithKeys = FakeUser::magic_create([
			"user"=>"Admin",
			"password"=>"Password",
			"created_at"=>"1995-06-08"
		]);
		
		$this->assertNull($badFakeUser);

		$this->assertEquals("Admin", $fakeUser->user);
		$this->assertEquals("Admin", $fakeUserWithKeys->user);

		$this->assertEquals("Password", $fakeUser->password);
		$this->assertEquals("Password", $fakeUserWithKeys->password);

		$this->assertEquals("1995-06-08", $fakeUser->created_at);
		$this->assertEquals("1995-06-08", $fakeUserWithKeys->created_at);

	}

	public function test_magic_insert(){
		$this->assertTrue(true, "TODO");
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