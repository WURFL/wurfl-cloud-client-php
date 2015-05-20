<?php
namespace ScientiaMobile\WurflCloud\HttpClient;
use ScientiaMobile\WurflCloud\Config;
use ScientiaMobile\WurflCloud\ApiKeyException;
/**
 * Copyright (c) 2015 ScientiaMobile, Inc.
 *
 * Please refer to the COPYING.txt file distributed with the software for licensing information.
 * 
 * @package ScientiaMobile\WurflCloud
 * @subpackage HttpClient
 */
/**
 * This is the abstract class for HTTP Communications
 */
abstract class AbstractHttpClient {
	
	protected $use_compression = true;
	protected $timeout_ms = 1000;
	protected $request_headers = array();
	protected $response_headers;
	protected $response_http_status;
	protected $response_body;
	protected $success;
	protected $proxy;
	
	/**
	 * Use a proxy to access the WURFL Cloud service
	 * Proxy URL should be in the format:
	 *   protocol://[user:pass@]ip_or_hostname:port
	 * Ex:
	 *   http://192.168.1.10:8080
	 *   socks4://192.168.1.10:8080
	 *   socks4a://192.168.1.10:8080
	 *   socks5://myuser:mypass@192.168.1.10:8080
	 *   
	 * This option gets passed straight to CURL and whether or not it works
	 * for you depends on your version of PHP/CURL.
	 * 
	 * @see http://curl.haxx.se/libcurl/c/curl_easy_setopt.html#CURLOPTPROXY
	 * 
	 * @param string $proxy URL of proxy server
	 */
	public function setProxy($proxy) {
		$this->proxy = $proxy;
	}
	
	/**
	 * Set the timeout for HTTP requests
	 * @param int $milliseconds
	 */
	public function setTimeout($milliseconds) {
		$this->timeout_ms = $milliseconds;
	}
	
	/**
	 * Enable compression
	 * @param bool $use_compression
	 */
	public function setUseCompression($use_compression = true) {
		$this->use_compression = $use_compression;
	}
	
	/**
	 * Add an HTTP header to the outgoing request
	 * @param string $name
	 * @param string $value
	 */
	public function addHttpRequestHeader($name, $value) {
		$this->request_headers[$name] = $value;
	}
	
	/**
	 * Adds the HTTP Header specified by $source_name (if found) in the $http_request
	 * under $dest_name.  Example: addRequestHeaderIfExists($_SERVER, 'HTTP_USER_AGENT', 'User-Agent');
	 * @param array $http_request
	 * @param string $source_name
	 * @param string $dest_name
	 * @return boolean true if the header was found and added, otherwise false
	 */
	public function addHttpRequestHeaderIfExists(array $http_request, $source_name, $dest_name) {
		if (array_key_exists($source_name, $http_request)) {
			$this->addHttpRequestHeader($dest_name, $http_request[$source_name]);
			return true;
		}
		return false;
	}
	
	/**
	 * Get the response body
	 * @return string
	 */
	public function getResponseBody() {
		return $this->response_body;
	}
	
	/**
	 * Get the response HTTP headers
	 * @return array
	 */
	public function getResponseHeaders() {
		return $this->response_headers;
	}
	
	/**
	 * Returns true if the cloud service was already called
	 * @return boolean
	 */
	public function wasCalled() {
		return ($this->success !== null);
	}
	
	/**
	 * Returns the response body using the PHP cURL Extension
	 * @param Config $config
	 * @param string $request_path Request Path/URI
	 * @throws HttpException Unable to query server
	 */
	abstract public function call(Config $config, $request_path);
	
	protected function processResponse($response) {
		list($headers, $body) = explode("\r\n\r\n", $response, 2);
		$this->processResponseHeaders($headers);
		$this->processResponseBody($body);
	}
	
	protected function processResponseHeaders($headers) {
		$this->response_headers = explode("\r\n", $headers);
		$this->response_http_status = $this->response_headers[0];
		list($protocol, $http_status_code, $reason_code) = explode(' ', $this->response_http_status, 3);
		$http_status_code = (int)$http_status_code;
		if ($http_status_code >= 400 ) {
			$this->success = false;
			switch ($http_status_code) {
				case 401:
					throw new ApiKeyException("Invalid API key", $http_status_code);
					break;
				case 402:
					throw new ApiKeyException("No API key was provided", $http_status_code);
					break;
				case 403:
					throw new ApiKeyException("API key is expired or revoked", $http_status_code);
					break;
				default:
					throw new HttpException("The WURFL Cloud service returned an unexpected response: $this->response_http_status", $this->response_http_status);
					break;
			}
		}
		$this->success = true;
	}
	
	protected function processResponseBody($body) {
		$this->response_body = $body;
	}
}