<?php

namespace ScientiaMobile\WurflCloud\HttpClient;

class FsockTest extends HttpClientTestCase {
	
	public function setUp() {
		$this->http_client = new Fsock();
		parent::setUp();
	}
	
	/**
	 * @expectedException \ScientiaMobile\WurflCloud\Exception
	 * @expectedExceptionMessage does not support proxies
	 */
	public function testSetProxy() {
		parent::testSetProxy();
	}
	
}