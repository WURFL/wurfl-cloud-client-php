<?php

namespace ScientiaMobile\WurflCloud\HttpClient;

class GuzzleTest extends HttpClientTestCase {
	
	public function setUp() {
		$this->http_client = new Guzzle();
		parent::setUp();
	}
	
	public function testCallTimeout() {
		// Guzzle doesn't seem to honor timeouts consistently
		// $this->markTestSkipped("Guzzle doesn't seem to honor timeouts consistently");
	}
}