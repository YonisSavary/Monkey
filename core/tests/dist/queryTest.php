<?php

use Monkey\Dist\Query;
use PHPUnit\Framework\TestCase;

class queryTest extends TestCase
{
	public function test_clean_data(){
		$query = new Query("placeholder");
		
		$someVal = "'someDangerousString!'";
		$query->clean_data($someVal);
		$this->assertEquals("'\'someDangerousString!\''", $someVal);

		$someArray = [
			"'someDangerousString!'",
			"'AnotherOne!'"
		];
		$query->clean_data($someArray);
		$this->assertEquals([
			"'\'someDangerousString!\''",
			"'\'AnotherOne!\''",
		], $someArray);

	}
	
	public function test_or(){ $this->assertTrue(true, "TODO"); }
	public function test_and(){ $this->assertTrue(true, "TODO"); }
	public function test_set(){ $this->assertTrue(true, "TODO"); }
	public function test_values(){ $this->assertTrue(true, "TODO"); }
	public function test_where(){ $this->assertTrue(true, "TODO"); }
	public function test_raw_where(){ $this->assertTrue(true, "TODO"); }
	public function test_order_by(){ $this->assertTrue(true, "TODO"); }
	public function test_limit(){ $this->assertTrue(true, "TODO"); }
	public function test_offset(){ $this->assertTrue(true, "TODO"); }
	public function test_build(){ $this->assertTrue(true, "TODO"); }
	public function test_execute(){ $this->assertTrue(true, "TODO"); }
	
}