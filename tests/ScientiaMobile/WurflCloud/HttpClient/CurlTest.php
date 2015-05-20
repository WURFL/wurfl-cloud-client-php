<?php

namespace ScientiaMobile\WurflCloud\HttpClient;

class CurlTest extends HttpClientTestCase {
	
	public function setUp() {
		if (!extension_loaded('curl')) {
			$this->markTestSkipped("PHP extension 'curl' is not loaded");
			return;
		}
		
		$this->http_client = new Curl();
		parent::setUp();
	}
	
	
	
}