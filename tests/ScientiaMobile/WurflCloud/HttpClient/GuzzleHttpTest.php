<?php

namespace ScientiaMobile\WurflCloud\HttpClient;

class GuzzleHttpTest extends HttpClientTestCase {
	
	public function setUp() {
		$this->http_client = new GuzzleHttp();
		parent::setUp();
	}

	public function testCallNoCompression() {
		$this->http_client->setUseCompression(false);
		$this->http_client->call($this->config, $this->request_path);
		$this->assertNotContains('x-encoded-content-encoding: gzip', $this->http_client->getResponseHeaders());
	}
	
	public function testCallCompression() {
		$this->http_client->setUseCompression(true);
		$this->http_client->call($this->config, $this->request_path);
		$this->assertContains('x-encoded-content-encoding: gzip', $this->http_client->getResponseHeaders());
	}
}
