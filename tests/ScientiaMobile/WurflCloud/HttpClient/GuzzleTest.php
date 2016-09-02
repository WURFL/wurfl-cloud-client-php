<?php

namespace ScientiaMobile\WurflCloud\HttpClient;

class GuzzleTest extends HttpClientTestCase {
	
	public function setUp() {
		$this->http_client = new Guzzle();
		parent::setUp();
	}
}
