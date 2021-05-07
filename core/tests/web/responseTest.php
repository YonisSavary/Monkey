<?php

use Monkey\Web\Response;
use PHPUnit\Framework\TestCase;

class ResponseTest extends TestCase
{
	public function test_html(){
		$new_content = "<h1>Ahah ! YES.</h1>";
		$res = Response::html($new_content);
		$this->assertEquals($new_content, $res->content);
	}

	public function test_json(){
		$new_content = ["someKey" => "someValue"];
		$res = Response::json($new_content);
		$this->assertEquals('{"someKey":"someValue"}', $res->content);
	}

	public function test_reveal(){
		ob_start();
		Response::html("someContent")->reveal(true);
		$res = ob_get_clean();
		$this->assertEquals($res, "someContent");
		$this->assertTrue(true);
	}
}