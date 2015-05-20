<?php

namespace ScientiaMobile\WurflCloud\HttpClient;

use ScientiaMobile\WurflCloud\HttpClient\AbstractHttpClient;
use ScientiaMobile\WurflCloud\Config;
use ScientiaMobile\WurflCloud\Client;

abstract class HttpClientTestCase extends \PHPUnit_Framework_TestCase {
	
	/**
	 * @var AbstractHttpClient
	 */
	protected $http_client;
	
	/**
	 * @var Config
	 */
	protected $config;
	
	/**
	 * Request path for the WURFL Cloud service
	 * @var string
	 */
	protected $request_path = '/v1/json';
	
	public function setUp() {
		$this->config = new Config();
		$this->config->api_key = WURFL_CLOUD_API_KEY;
		$this->config->clearServers();
		$this->config->addCloudServer('phpunit', WURFL_CLOUD_SERVER);
	}
	
	public function testAddHttpRequestHeader() {
		$this->http_client->addHttpRequestHeader('foo', 'bar');
		$this->assertAttributeEquals(array('foo'=>'bar'), 'request_headers', $this->http_client);
	}
	
	public function testAddHttpRequestHeaderIfExists() {
		$headers = array('HTTP_X_FORWARDED_FOR' => '127.0.0.1');
		
		// This header should get added with the new name
		$this->http_client->addHttpRequestHeaderIfExists($headers, 'HTTP_X_FORWARDED_FOR', 'X-Forwarded-For');
		$this->assertAttributeEquals(array('X-Forwarded-For'=>'127.0.0.1'), 'request_headers', $this->http_client);
		
		// Make sure it doesn't add non-existent headers
		$this->http_client->addHttpRequestHeaderIfExists($headers, 'HTTP_FOO', 'Foo');
		$this->assertAttributeEquals(array('X-Forwarded-For'=>'127.0.0.1'), 'request_headers', $this->http_client);
	}
	
	public function testSetUseCompression() {
		$this->assertAttributeEquals(true, 'use_compression', $this->http_client);
		
		$this->http_client->setUseCompression(false);
		
		$this->assertAttributeEquals(false, 'use_compression', $this->http_client);
	}
	
	/**
	 * @expectedException \ScientiaMobile\WurflCloud\HttpClient\HttpException
	 */
	public function testCallBadPath() {
		$this->http_client->setTimeout(50);
		$this->http_client->call($this->config, '/foo/bar');
	}

	/**
	 * @expectedException \ScientiaMobile\WurflCloud\HttpClient\HttpException
	 */
	public function testCallBadHost() {
		$this->http_client->setTimeout(50);
		$this->config->clearServers();
		$this->config->addCloudServer('foo', 'localhost:12345');
		$this->http_client->call($this->config, '/foo/bar');
	}

	/**
	 * @expectedException \ScientiaMobile\WurflCloud\ApiKeyException
	 * @expectedExceptionMessage No API key was provided
	 */
	public function testCallMangledApiKey() {
		$this->config->api_key = 'foobar';
		$this->http_client->call($this->config, $this->request_path);
	}
	
	/**
	 * @expectedException \ScientiaMobile\WurflCloud\ApiKeyException
	 * @expectedExceptionMessage Invalid API key
	 */
	public function testCallInvalidApiKey() {
		$this->config->api_key = '654321:xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx';
		$this->http_client->call($this->config, $this->request_path);
	}
	
	public function testSetProxy() {
		$proxy = 'socks5://foo:bar@127.0.0.1:12345';
		$this->http_client->setProxy($proxy);
		$this->assertAttributeEquals($proxy, 'proxy', $this->http_client);
	}
	
	public function testCall() {
		$this->assertAttributeEmpty('response_http_status', $this->http_client);
		$this->assertAttributeEmpty('response_headers', $this->http_client);
		$this->assertAttributeEmpty('response_body', $this->http_client);
		
		$this->http_client->call($this->config, $this->request_path);
		
		$this->assertTrue($this->http_client->wasCalled());
		
		$this->assertAttributeNotEmpty('response_http_status', $this->http_client);
		$this->assertAttributeNotEmpty('response_headers', $this->http_client);
		$this->assertAttributeNotEmpty('response_body', $this->http_client);
	}
	
	public function testCallNoCompression() {
		$this->http_client->setUseCompression(false);
		$this->http_client->call($this->config, $this->request_path);
		$this->assertNotContains('Content-Encoding: gzip', $this->http_client->getResponseHeaders());
	}
	
	public function testCallCompression() {
		$this->http_client->setUseCompression(true);
		$this->http_client->call($this->config, $this->request_path);
		$this->assertContains('Content-Encoding: gzip', $this->http_client->getResponseHeaders());
	}
	
	public function testCallMultipleTimes() {
		$this->http_client->call($this->config, $this->request_path);
		$this->http_client->call($this->config, $this->request_path);
		$this->http_client->call($this->config, $this->request_path);
		$this->http_client->call($this->config, $this->request_path);
		$response = $this->http_client->getResponseBody();
		$this->assertNotEmpty($response);
		
		$response_data = @json_decode($response, true);
		$this->assertInternalType('array', $response_data);
	}
	
	public function testCallReturnsValidResponse() {
		$this->http_client->call($this->config, $this->request_path);
		$response = $this->http_client->getResponseBody();
		$this->assertNotEmpty($response);
		
		$response_data = @json_decode($response, true);
		$this->assertInternalType('array', $response_data);
		
		// Check response data
		$this->assertArrayHasKey('apiVersion', $response_data);
		$this->assertArrayHasKey('id', $response_data);
		$this->assertArrayHasKey('capabilities', $response_data);
		$this->assertArrayHasKey('errors', $response_data);
		$this->assertInternalType('array', $response_data['capabilities']);
		$this->assertInternalType('array', $response_data['errors']);
	}
	
	public function testCallTimeout() {
		$timeout = 5;
		$fail_after = 80;
		
		$this->http_client->setTimeout($timeout);
		$this->config->clearServers();
		$this->config->addCloudServer('foo', 'node01-za.wurflcloud.com:12345');
		
		$start_time = microtime(true);
		
		try {
			$this->http_client->call($this->config, '/foo/bar');
			$this->fail("HTTP Client did not fail as expected");
			return;
		} catch (HttpException $e) {
			// These tests cover the HttpException class
			$this->assertEmpty($e->getHttpStatus());
			$this->assertEquals(0, $e->getHttpStatusCode());
		}
		
		$total_time = (microtime(true) - $start_time) * 1000;
		
		$this->assertLessThan($fail_after, $total_time);
	}
	
	public function testGetDefaultHttpClient() {
		$client = Client::getDefaultHttpClient();
		
		$this->assertInstanceOf('ScientiaMobile\\WurflCloud\\HttpClient\\AbstractHttpClient', $client);
		
		if (function_exists('curl_init')) {
			$this->assertInstanceOf('ScientiaMobile\\WurflCloud\\HttpClient\\Curl', $client);
		} else {
			$this->assertInstanceOf('ScientiaMobile\\WurflCloud\\HttpClient\\Fsock', $client);
		}
	}
}
