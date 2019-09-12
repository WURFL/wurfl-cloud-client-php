<?php

namespace ScientiaMobile\WurflCloud\HttpClient;

class CurlTest extends HttpClientTestCase
{
    public function setUp()
    {
        if (!extension_loaded('curl')) {
            $this->markTestSkipped("PHP extension 'curl' is not loaded");
            return;
        }
        
        $this->http_client = new Curl();
        parent::setUp();
    }

    public function testCallBadPath()
    {
        $this->http_client->call($this->config, '/foo/bar');
        $this->assertAttributeNotEquals('HTTP/1.1 200 OK', 'response_http_status', $this->http_client);
    }
}
