<?php

use Monkey\Web\Response;
use Monkey\Web\Trash;
use PHPUnit\Framework\TestCase;

class TrashTest extends TestCase
{
	public function test_on(){
		Trash::$error_callbacks = [];
		Trash::on("someErrorCode", function(){});
		$this->assertCount(1, Trash::$error_callbacks);
	}

	public function test_send(){
		Trash::on("someError", function(){ return Response::html("oui"); });
		$res = Trash::send("someError");
		$this->assertInstanceOf("Monkey\Web\Response", $res);
	}

	public function test_fatal(){
		Trash::on("fatal", function($message){ return Response::html("Fatal Error ! $message"); });
		$res = Trash::fatal("some error");
		$this->assertInstanceOf("Monkey\Web\Response", $res);
	}

}