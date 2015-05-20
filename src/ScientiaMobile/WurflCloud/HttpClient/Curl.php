<?php
namespace ScientiaMobile\WurflCloud\HttpClient;
use ScientiaMobile\WurflCloud\Config;
/**
 * Copyright (c) 2015 ScientiaMobile, Inc.
 *
 * Please refer to the COPYING.txt file distributed with the software for licensing information.
 * 
 * @package ScientiaMobile\WurflCloud
 * @subpackage HttpClient
 */
/**
 * An HTTP Client that uses the curl extension
 */
class Curl extends AbstractHttpClient {
	private $curl_supports_ms = false;
	private $curl_supports_encoding = false;
	private $curl_handle;
	
	public function __construct() {
		$this->initializeCurl();
	}
	
	private function initializeCurl() {
		$this->curl_handle = curl_init();
		// CURLOPT_TIMEOUT_MS was introduced in libcurl version 7.16.2, PHP version 5.2.3
		// cURL v7.16.2 converted to a 24-bit number (7 << 16 | 16 << 8 | 2) == 462850
		$version_info = curl_version();
		$this->curl_supports_ms = ($version_info['version_number'] >= 462850 && version_compare(PHP_VERSION, '5.2.3') >= 0);
		// Introduced in curl 7.10.0 (461312)
		$this->curl_supports_encoding = ($version_info['version_number'] >= 461312);
	}
	
	/**
	 * Returns the response body using the PHP cURL Extension
	 * @param Config $config
	 * @param string $request_path Request Path/URI
	 * @throws HttpException Unable to query server
	 */
	public function call(Config $config, $request_path) {
		// Setup CURL
		$this->option(CURLOPT_URL, 'http://'.$config->getCloudHost().$request_path);
		$this->option(CURLOPT_RETURNTRANSFER, true);
		$this->option(CURLOPT_FORBID_REUSE, true);
		$this->option(CURLOPT_HEADER, true);
		$this->option(CURLOPT_HTTPHEADER, $this->getCurlHeaders());
		$this->option(CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		$this->option(CURLOPT_USERPWD, $config->api_key);
		
		// Proxy
		if ($this->proxy) {
			$this->option(CURLOPT_PROXY, $this->proxy);
		}
		
		// Compression
		if ($this->use_compression === true && $this->curl_supports_encoding === true) {
			$this->option(CURLOPT_ENCODING, '');
		}
		
		// Timeout
		if ($this->curl_supports_ms) {
			// Required for CURLOPT_TIMEOUT_MS to play nice on most Unix/Linux systems
			// http://www.php.net/manual/en/function.curl-setopt.php#104597
			$this->option(CURLOPT_NOSIGNAL, 1);
			$this->option(CURLOPT_TIMEOUT_MS, $this->timeout_ms);
		} else {
			$timeout = ($this->timeout_ms < 1000)? 1000: $this->timeout_ms;
			$this->option(CURLOPT_TIMEOUT, ($timeout / 1000));
		}
		
		// Execute
		$response = curl_exec($this->curl_handle);
		$curl_errno = curl_errno($this->curl_handle);
		$curl_error = curl_error($this->curl_handle);
		
		if ($curl_errno !== 0) {
			throw new HttpException("Unable to contact server: cURL Error: $curl_error", null);
		}
		$this->processResponse($response);
	}
	
	/**
	 * Set CURL option
	 * @param int $option CURL constant, like CURLOPT_TIMEOUT
	 * @param string $value
	 */
	public function option($option, $value) {
		curl_setopt($this->curl_handle, $option, $value);
	}
	
	/**
	 * Gets the HTTP request headers in CURL format
	 * @return array
	 */
	private function getCurlHeaders() {
		$headers = array();
		foreach ($this->request_headers as $key => $value) {
			$headers[] = "$key: $value";
		}
		return $headers;
	}

	public function __destruct() {
		if (is_resource($this->curl_handle)) {
			curl_close($this->curl_handle);
		}
	}
}
