<?php

namespace ScientiaMobile\WurflCloud\HttpClient;

class FsockTest extends HttpClientTestCase
{
    public function setUp()
    {
        $this->http_client = new Fsock();
        parent::setUp();
    }
    
    /**
     * @expectedException \ScientiaMobile\WurflCloud\Exception
     * @expectedExceptionMessage does not support proxies
     */
    public function testSetProxy()
    {
        parent::testSetProxy();
    }

    public function testCallBadPath()
    {
        $this->http_client->call($this->config, '/foo/bar');
        $this->assertAttributeNotEquals('HTTP/1.1 200 OK', 'response_http_status', $this->http_client);
    }
}
