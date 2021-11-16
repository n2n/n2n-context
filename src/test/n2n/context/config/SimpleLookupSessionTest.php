<?php
namespace n2n\context\config;

use PHPUnit\Framework\TestCase;

class SimpleLookupSessionTest extends TestCase {
	private SimpleLookupSession $session;

	protected function setUp(): void {
		$this->session = new SimpleLookupSession();
	}

	function testNsData() {
		$testNs = 'test\ns';
		$key = 'test';
		$obj = array('hello');

		$this->session->set($testNs, $key, $obj);
		$this->assertTrue($this->session->has($testNs, $key));
		$this->assertEquals($this->session->get($testNs, $key), $obj);
	}
}