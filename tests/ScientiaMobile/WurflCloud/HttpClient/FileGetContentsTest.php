<?php

namespace ScientiaMobile\WurflCloud\HttpClient;

class FileGetContentsTest extends HttpClientTestCase
{
    public function setUp()
    {
        $this->http_client = new FileGetContents();
        parent::setUp();
    }
    
    public function testCallBadPath()
    {
        $this->http_client->call($this->config, '/foo/bar');
        $this->assertAttributeNotEquals('HTTP/1.1 200 OK', 'response_http_status', $this->http_client);
    }
}
