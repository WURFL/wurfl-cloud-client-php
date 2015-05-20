<?php
namespace ScientiaMobile\WurflCloud;


use ScientiaMobile\WurflCloud\Cache\CacheInterface;
use ScientiaMobile\WurflCloud\Cache\Null;
use ScientiaMobile\WurflCloud\HttpClient\Fsock;
use ScientiaMobile\WurflCloud\HttpClient\HttpException;

class ClientTest extends \PHPUnit_Framework_TestCase {
	
	/**
	 * @var Config
	 */
	protected $config;
	
	/**
	 * @var CacheInterface
	 */
	protected $cache;
	
	/**
	 * @var MockHttpClient
	 */
	protected $http_client;
	
	/**
	 * @var Client
	 */
	protected $client;
	
	public static function setUpBeforeClass() {
		$_SERVER['HTTP_USER_AGENT'] = "ScientiaMobile/PHPUnit";
	}
	
	public function setUp() {
		// Config
		$this->config = new Config();

		// Cache
		$this->cache = new Null();
		
		// HTTP Client
		$this->http_client = new MockHttpClient();
		$this->http_client->mockResponse('cloud_success.bin');
		
		// WURFL Cloud Client
		$this->client = new Client($this->config, $this->cache, $this->http_client);
	}
	
	public function testGetUserAgentLongUaIsTruncated() {
		$user_agent = 'ScientiaMobile/PHPUnit_LongUa/';
		$user_agent .= implode('', array_fill(0, 1024, '-'));
		
		$this->assertGreaterThan(512, strlen($user_agent));
		$this->client->detectDevice(array('HTTP_USER_AGENT' => $user_agent));
		
		// Check the user agent string as it went out to the cloud
		$headers = $this->http_client->getRequestHeaders();
		$this->assertEquals(512, strlen($headers['User-Agent']));
	}
	
	/**
	 * @expectedException \ScientiaMobile\WurflCloud\ApiKeyException
	 * @expectedExceptionMessage Invalid API key
	 */
	public function testDetectDeviceInvalidKey() {
		$this->http_client->mockResponse('cloud_api_key_error.bin');
		$this->client->detectDevice();
	}
	
	/**
	 * @expectedException \ScientiaMobile\WurflCloud\Exception
	 * @expectedExceptionMessage is invalid or you are not subscribed to it
	 */
	public function testDetectDeviceInvalidCapability() {
		$this->http_client->mockResponse('cloud_cap_error.bin');
		$this->client->detectDevice();
		$this->client->getDeviceCapability('foobar');
	}
	
	public function testDetectDevicePostCacheSecondaryLookup() {
		// Get a real response from the cloud to play with
		$this->http_client->mockResponse('cloud_success.bin');
		$this->client->detectDevice();
		
		/*
		 * array (
		 *  'id' => 'generic_mobile',
		 *  'is_mobile' => true,
		 *  'is_tablet' => false,
		 *  'is_smartphone' => false,
		 * )
		 */
		$capabilities = $this->client->capabilities;
				
		// Reset everything
		$this->config = new Config();
		// Use MockCache instead of Null so we can force in a cache value
		$this->cache = new MockCache();
		$this->http_client = new MockHttpClient();
		$this->client = new Client($this->config, $this->cache, $this->http_client);
		
		// Remove is_smartphone from cache
		unset($capabilities['is_smartphone']);
		
		// Prime the cache without is_smartphone
		$this->cache->setDevice($_SERVER['HTTP_USER_AGENT'], $capabilities);
		
		// Prepare the response, which will contain is_smartphone
		// The client should check the cache first, then ask the cloud
		$this->http_client->mockResponse('cloud_success.bin');
		
		////////////////
		// Ready to go!
		////////////////
		
		$this->assertEquals(0, $this->http_client->calls);
		$this->assertEquals(Client::SOURCE_NONE, $this->client->getSource());
		
		// Detect device
		$this->client->detectDevice();
		$this->assertEquals(0, $this->http_client->calls);
		$this->assertEquals(Client::SOURCE_CACHE, $this->client->getSource());
		
		// Check for cached capability - no call should be made to cloud
		$this->client->getDeviceCapability('is_mobile');
		$this->assertEquals(0, $this->http_client->calls);
		$this->assertEquals(Client::SOURCE_CACHE, $this->client->getSource());
		
		// Check for uncached capability - this should trigger cloud call, which repopulates cache
		$this->client->getDeviceCapability('is_smartphone');
		$this->assertEquals(1, $this->http_client->calls);
		$this->assertEquals(Client::SOURCE_CLOUD, $this->client->getSource());
		
		
		
		
		// Prime the cache without is_smartphone
		$this->cache->setDevice($_SERVER['HTTP_USER_AGENT'], $capabilities);
		
		// Detect device again, this
		$this->client->detectDevice($_SERVER, array('is_tablet'));
		
	}
	
	public function testDetectDeviceInitialSecondaryLookup() {
		// Get a real response from the cloud to play with
		$this->http_client->mockResponse('cloud_success.bin');
		$this->client->detectDevice();
	
		/*
		 * array (
		 		*  'id' => 'generic_mobile',
		 		*  'is_mobile' => true,
		 		*  'is_tablet' => false,
		 		*  'is_smartphone' => false,
		 		* )
		*/
		$capabilities = $this->client->capabilities;
	
		// Reset everything
		$this->config = new Config();
		// Use MockCache instead of Null so we can force in a cache value
		$this->cache = new MockCache();
		$this->http_client = new MockHttpClient();
		$this->client = new Client($this->config, $this->cache, $this->http_client);
	
		// Remove is_smartphone from cache
		unset($capabilities['is_smartphone']);
	
		// Prime the cache without is_smartphone
		$this->cache->setDevice($_SERVER['HTTP_USER_AGENT'], $capabilities);
	
		// Prepare the response, which will contain is_smartphone
		// The client should check the cache first, then ask the cloud
		$this->http_client->mockResponse('cloud_success.bin');
	
		////////////////
		// Ready to go!
		////////////////
	
		$this->assertEquals(0, $this->http_client->calls);
		$this->assertEquals(Client::SOURCE_NONE, $this->client->getSource());
		
		// Detect device
		//  In this test, we are requesting a capability in detectDevice() that does not
		//  exist in the cache, which causes a cloud lookup during the initial detectDevice()
		$this->client->detectDevice($_SERVER, array('is_smartphone'));
		// Make sure the cache was checked
		$this->assertEquals(1, $this->cache->hits);
		// Make sure the cloud was called
		$this->assertEquals(1, $this->http_client->calls);
		// Make sure the cache was set with the: (1) initial result, then (2) secondary result
		$this->assertEquals(2, $this->cache->sets);
		$this->assertEquals(Client::SOURCE_CLOUD, $this->client->getSource());
	
		// Check for cached capability - no call should be made to cloud
		$this->client->getDeviceCapability('is_smartphone');
		$this->assertEquals(1, $this->http_client->calls);
	}
	
	public function testDetectDeviceSuccess() {
		$this->client->detectDevice();
		$this->assertFalse($this->client->getDeviceCapability('is_smartphone'));
		$this->assertEquals('generic_mobile', $this->client->getDeviceCapability('id'));
	}
	
	public function testDetectDeviceCompression() {
		$this->http_client->mockResponse('cloud_success_compressed.bin');
		$this->client->detectDevice();
		$this->assertFalse($this->client->getDeviceCapability('is_smartphone'));
		$this->assertEquals('generic_mobile', $this->client->getDeviceCapability('id'));
	}
	
	/**
	 * @expectedException \ScientiaMobile\WurflCloud\HttpClient\HttpException
	 * @expectedExceptionMessage Unable to decompress
	 */
	public function testDetectDeviceCompressionFailed() {
		$this->http_client->mockResponse('cloud_success_compressed_fail.bin');
		$this->client->detectDevice();
		$this->assertFalse($this->client->getDeviceCapability('is_smartphone'));
		$this->assertEquals('generic_mobile', $this->client->getDeviceCapability('id'));
	}
	
	/**
	 * @expectedException \ScientiaMobile\WurflCloud\HttpClient\HttpException
	 * @expectedExceptionMessage Unable to parse JSON
	 */
	public function testDetectDeviceInvalidJson() {
		$this->http_client->mockResponse('cloud_invalid_json.bin');
		$this->client->detectDevice();
	}
	
	public function testDetectDeviceInternalServerError() {
		$this->http_client->mockResponse('cloud_server_internal_error.bin');
		try {
			$this->client->detectDevice();
			$this->fail("Client did not throw exception on failed request");
		} catch (HttpException $e) {
			$this->assertEquals(500, $e->getHttpStatusCode());
			$this->assertEquals('HTTP/1.1 500 Internal Server Error', $e->getHttpStatus());
		}
	}
	
	public function testDetectDeviceHttpHeadersAreForwarded() {
		$_SERVER['REMOTE_ADDR'] = '10.1.2.3';
		$_SERVER['HTTP_X_FORWARDED_FOR'] = '2.2.2.2';
		$_SERVER['HTTP_DEVICE_STOCK_UA'] = 'FooBar/1.0';
		$_SERVER['HTTP_X_WAP_PROFILE'] = 'http://test.com/uaprof.rdf';
		$_SERVER['HTTP_ACCEPT'] = 'Stuff';

		$this->client = new Client($this->config, $this->cache, $this->http_client);
		$this->assertEmpty($this->http_client->getRequestHeaders());
		
		$this->client->detectDevice();
		
		$expected_headers = array (
			'User-Agent' => 'FooBar/1.0',
			'X-Cloud-Client' => 'WurflCloudClient/PHP_'.$this->client->getClientVersion(),
			'X-Forwarded-For' => '10.1.2.3, 2.2.2.2',
			'X-Accept' => 'Stuff',
			'X-Wap-Profile' => 'http://test.com/uaprof.rdf'
		);
		$headers = $this->http_client->getRequestHeaders();
		$this->assertEquals($expected_headers, $headers);
	}
	
	public function testGetClientVersion() {
		$this->assertContains('.', $this->client->getClientVersion());
	}
	
}

class MockHttpClient extends Fsock {
	
	public $calls = 0;
	
	protected $mock_file;
	
	public function call(Config $config, $request_path) {
		$this->calls++;
		$this->processResponse(file_get_contents($this->mock_file));
	}
	
	public function mockResponse($file) {
		$this->mock_file = __DIR__.'/../../resources/'.$file;
		
		if (!is_readable($this->mock_file)) {
			throw new \Exception("Unable to read mock response file: $this->mock_file");
		}
	}
	
	public function getRequestHeaders() {
		return $this->request_headers;
	}
}

class MockCache extends Null {
	
	public $misses = 0;
	public $hits = 0;
	public $sets = 0;
	
	private $devices = array();
	
	public function getDevice($user_agent) {
		if (!array_key_exists($user_agent, $this->devices)) {
			$this->misses++;
			return false;
		}
		$this->hits++;
		return $this->devices[$user_agent];
	}
	
	public function setDevice($user_agent, $capabilities) {
		$this->sets++;
		$this->devices[$user_agent] = $capabilities;
		return true;
	}

}