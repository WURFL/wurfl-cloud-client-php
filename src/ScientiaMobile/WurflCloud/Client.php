<?php
namespace ScientiaMobile\WurflCloud;
use ScientiaMobile\WurflCloud\Cache\CacheInterface;
use ScientiaMobile\WurflCloud\Cache\Cookie;
use ScientiaMobile\WurflCloud\HttpClient\AbstractHttpClient;
use ScientiaMobile\WurflCloud\HttpClient\HttpException;
use ScientiaMobile\WurflCloud\HttpClient\Curl;
use ScientiaMobile\WurflCloud\HttpClient\Fsock;
/**
 * Copyright (c) 2015 ScientiaMobile, Inc.
 *
 * Please refer to the COPYING.txt file distributed with the software for licensing information.
 * 
 * @package ScientiaMobile\WurflCloud
 */
/**
 * WURFL Cloud Client for PHP.
 * @package ScientiaMobile\WurflCloud
 */
class Client {
	
	/**
	 * @var string Minimum supported PHP version
	 */
	const PHP_MIN_VERSION = '5.3.0';
	
	/**
	 * @var string No detection was performed
	 */
	const SOURCE_NONE = 'none';
	
	/**
	 * @var string Response was returned from cloud
	 */
	const SOURCE_CLOUD = 'cloud';
	
	/**
	 * @var string Response was returned from cache
	 */
	const SOURCE_CACHE = 'cache';
	
	/**
	 * Flat capabilities array containing 'key'=>'value' pairs.
	 * Since it is 'flattened', there are no groups in this array, just individual capabilities.
	 * @var array
	 */
	public $capabilities = array();
	
	/**
	 * Errors that were returned in the response body
	 * @var array
	 */
	private $errors = array();
	
	/**
	 * The capabilities that will be searched for
	 * @var array
	 */
	private $search_capabilities = array();
	
	/**
	 * The HTTP Headers that will be examined to find the best User Agent, if one is not specified
	 * @var array
	 */
	private $user_agent_headers = array(
		'HTTP_DEVICE_STOCK_UA',
		'HTTP_X_DEVICE_USER_AGENT',
		'HTTP_X_ORIGINAL_USER_AGENT',
		'HTTP_X_OPERAMINI_PHONE_UA',
		'HTTP_X_SKYFIRE_PHONE',
		'HTTP_X_BOLT_PHONE_UA',
		'HTTP_USER_AGENT'
	);
	
	/**
	 * The HTTP User-Agent that is being evaluated
	 * @var string
	 */
	private $user_agent;
	
	/**
	 * The HTTP Request that is being evaluated
	 * @var array
	 */
	private $http_request;
	
	/**
	 * The raw json response from the server
	 * @var string
	 */
	private $json;
	
	/**
	 * The version of this client
	 * @var string
	 */
	private $client_version = '2.0.1';
	
	/**
	 * Client configuration object
	 * @var Config
	 */
	private $config;
	
	/**
	 * Client cache object
	 * @var CacheInterface
	 */
	private $cache;
	
	/**
	 * The source of the last detection
	 * @var string
	 */
	private $source = self::SOURCE_NONE;
	
	/**
	 * The HTTP Client that will be used to call WURFL Cloud
	 * @var AbstractHttpClient
	 */
	private $http_client;

	/**
	 * Creates a new Client instance
	 * @param Config $config Client configuration object
	 * @param CacheInterface $cache Client caching object
	 * @param \ScientiaMobile\WurflCloud\HttpClient\AbstractHttpClient $http_client HTTP Client object
	 * @see Config
	 * @see \ScientiaMobile\WurflCloud\Cache\APC
	 * @see \ScientiaMobile\WurflCloud\Cache\Memcache
	 * @see \ScientiaMobile\WurflCloud\Cache\Memcached
	 * @see \ScientiaMobile\WurflCloud\Cache\File
	 * @see \ScientiaMobile\WurflCloud\Cache\Null
	 */
	public function __construct(Config $config, $cache=null, $http_client=null) {
		$this->config = $config;
		$this->cache = ($cache instanceof CacheInterface)? $cache: new Cookie();
		$this->http_client = ($http_client instanceof AbstractHttpClient)? $http_client: self::getDefaultHttpClient();
	}
	
	/**
	 * Get the requested capabilities from the WURFL Cloud for the given HTTP Request (normally $_SERVER)
	 * @param array $http_request HTTP Request of the device being detected
	 * @param array $search_capabilities Array of capabilities that you would like to retrieve
	 */
	public function detectDevice($http_request=null, $search_capabilities=null) {
		$this->source = self::SOURCE_NONE;
		$this->http_request = ($http_request === null)? $_SERVER: $http_request;
		$this->search_capabilities = ($search_capabilities === null)? array(): $search_capabilities;
		$this->user_agent = $this->getUserAgent($http_request);
		$result = $this->cache->getDevice($this->user_agent);
		if (!$result) {
			$this->source = self::SOURCE_CLOUD;
			$this->callWurflCloud();
			if ($this->source == self::SOURCE_CLOUD) {
				$this->cache->setDevice($this->user_agent, $this->capabilities);
			}
		} else {
			$this->source = self::SOURCE_CACHE;
			$this->capabilities = $result;
			// The user requested capabilities that don't exist in the cached copy.  Retrieve and cache the missing capabilities
			if (!$this->allCapabilitiesPresent()) {
				$this->source = self::SOURCE_CLOUD;
				$initial_capabilities = $this->capabilities;
				$this->callWurflCloud();
				$this->capabilities = array_merge($this->capabilities, $initial_capabilities);
				if ($this->source == self::SOURCE_CLOUD) {
					$this->cache->setDevice($this->user_agent, $this->capabilities);
				}
			}
		}
	}
	
	/**
	 * Gets the source of the result.  Possible values:
	 *  - cache:  from local cache
	 *  - cloud:  from WURFL Cloud Service
	 *  - none:   no detection was performed
	 *  @return string 'cache', 'cloud' or 'none'
	 */
	public function getSource() {
		return $this->source;
	}

	/**
	 * Initializes the WURFL Cloud request
	 */
	private function initializeRequest() {
		// Add HTTP Headers to pending request
		$this->http_client->addHttpRequestHeader('User-Agent', $this->user_agent);
		$this->http_client->addHttpRequestHeader('X-Cloud-Client', 'WurflCloudClient/PHP_'.$this->client_version);
		
		// Add X-Forwarded-For
		$ip = isset($this->http_request['REMOTE_ADDR'])? $this->http_request['REMOTE_ADDR']: null;
		$fwd = isset($this->http_request['HTTP_X_FORWARDED_FOR'])? $this->http_request['HTTP_X_FORWARDED_FOR']: null;
		if ($ip && $fwd) {
			$this->http_client->addHttpRequestHeader('X-Forwarded-For', $ip.', '.$fwd);
		} else if ($ip) {
			$this->http_client->addHttpRequestHeader('X-Forwarded-For', $ip);
		}
		
		// We use 'X-Accept' so it doesn't stomp on our deflate/gzip header
		$this->http_client->addHttpRequestHeaderIfExists($this->http_request, 'HTTP_ACCEPT', 'X-Accept');
		if (!$this->http_client->addHttpRequestHeaderIfExists($this->http_request, 'HTTP_X_WAP_PROFILE', 'X-Wap-Profile')) {
			$this->http_client->addHttpRequestHeaderIfExists($this->http_request, 'HTTP_PROFILE', 'X-Wap-Profile');
		}
	}
	
	private function getRequestPath() {
		if (count($this->search_capabilities) === 0) {
			$request_path = '/v1/json/';
		} else {
			$request_path = '/v1/json/search:('.implode(',', $this->search_capabilities).')';
		}
		return $request_path;
	}
	
	/**
	 * Returns true if all of the search_capabilities are present in the capabilities
	 * array that was returned from the WURFL Cloud Server
	 * @return boolean
	 * @see Client::capabilities
	 */
	private function allCapabilitiesPresent() {
		foreach ($this->search_capabilities as $key) {
			if (!array_key_exists($key, $this->capabilities)) {
				return false;
			}
		}
		return true;
	}
	
	/**
	 * Returns the value of the requested capability.  If the capability does not exist, returns null.
	 * @param string $capability The WURFL capability (e.g. "is_wireless_device")
	 * @return mixed Value of requested $capability or null if not found
	 * @throws Exception The requested capability is invalid or unavailable
	 */
	public function getDeviceCapability($capability) {
		$capability = strtolower($capability);
		if (array_key_exists($capability, $this->capabilities)) {
			return $this->capabilities[$capability];
		} else {
			if (!$this->http_client->wasCalled()) {
				// The capability is not in the cache (http_client was not called) - query the Cloud
				// to see if we even have the capability
				$this->source = 'cloud';
				$this->callWurflCloud();
				if ($this->source == 'cloud') {
					$this->cache->setDevice($this->user_agent, $this->capabilities);
				}
				if (array_key_exists($capability, $this->capabilities)) {
					return $this->capabilities[$capability];
				}
			}
			// The Cloud was queried and did not return a result for the this capability
			throw new Exception('The requested capability ('.$capability.') is invalid or you are not subscribed to it.');
		}
	}
	
	/**
	 * Get the version of the WURFL Cloud Client (this file)
	 * @return string
	 */
	public function getClientVersion() {
		return $this->client_version;
	}
	
	/**
	 * Returns a supported HttpClient based on your loaded PHP extensions
	 * @return \ScientiaMobile\WurflCloud\HttpClient\AbstractHttpClient
	 */
	public static function getDefaultHttpClient() {
		if (extension_loaded('curl')) {
			return new Curl();
		} else {
			return new Fsock();
		}
	}
	
	/**
	 * Make the webservice call to the server using the GET method and load the response.
	 * @throws  HttpException Unable to process server response
	 */
	private function callWurflCloud() {
		$this->initializeRequest();
		$this->http_client->call($this->config, $this->getRequestPath());
		$this->json = @json_decode($this->http_client->getResponseBody(), true);
		if ($this->json === null) {
			$msg = 'Unable to parse JSON response from server.';
			throw new HttpException($msg, 500);
		}
		$this->processResponse();
		unset($data);
	}
	
	/**
	 * Parses the response into the capabilities array
	 */
	private function processResponse() {
		$this->errors = $this->json['errors'];
		$this->capabilities['id'] = isset($this->json['id'])? $this->json['id']: '';
		$this->capabilities = array_merge($this->capabilities, $this->json['capabilities']);
	}
	
	/**
	 * Return the requesting client's User Agent
	 * @param $source
	 * @return string
	 */
	private function getUserAgent($source=null) {
		if (is_null($source) || !is_array($source)) {
			$source = $_SERVER;
		}
		$user_agent = '';
		foreach ($this->user_agent_headers as $header) {
			if (array_key_exists($header, $source) && $source[$header]) {
				$user_agent = $source[$header];
				break;
			}
		}
		if (strlen($user_agent) > 512) {
			return substr($user_agent, 0, 512);
		}
		return $user_agent;
	}
}
