<?php

use Monkey\Web\Renderer;
use PHPUnit\Framework\TestCase;

/**
 * This function allow you to render a view
 */
class RendererTest extends TestCase
{
    public function test_find_recursive()
    {
		// Dependant of App Views !
		$path = Renderer::find_recursive("./app/views", "someInexistant");
		$this->assertTrue(
			is_string($path)||
			is_null($path)||
			is_bool($path)
		);
    }


    public function test_find()
    {
		$path = Renderer::find("inexistantsOne");
		$this->assertTrue( is_string($path) || is_null($path) );
    }

	
    public function test_render()
    {
		$res = Renderer::render("inexistantOne");
		$this->assertInstanceOf("Monkey\Web\Response", $res);
    }
}