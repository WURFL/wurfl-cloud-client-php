<?php

namespace ScientiaMobile\WurflCloud\HttpClient;

class FileGetContentsTest extends HttpClientTestCase {
	
	public function setUp() {
		$this->http_client = new FileGetContents();
		parent::setUp();
	}
	
}
