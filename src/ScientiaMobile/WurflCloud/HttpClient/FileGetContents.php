<?php
namespace ScientiaMobile\WurflCloud\HttpClient;
use ScientiaMobile\WurflCloud\Config;
use ScientiaMobile\WurflCloud\Exception;
/**
 * Copyright (c) 2016 ScientiaMobile, Inc.
 *
 * Please refer to the COPYING.txt file distributed with the software for licensing information.
 * 
 * @package ScientiaMobile\WurflCloud
 * @subpackage HttpClient
 */
/**
 * An HTTP Client that uses the builtin PHP file_get_contents command
 */
class FileGetContents extends Fsock {

	protected $stream_options = array();

	/**
	 * Use a proxy to access the WURFL Cloud service
	 * Proxy URL should be in the format:
	 *   protocol://[user:pass@]ip_or_hostname:port
	 * Ex:
	 *   tcp://proxy.example.com:5100
	 *   
	 * This option gets passed straight to stream_context_create() and whether or not it works
	 * for you depends on your version of PHP and your proxy server.
	 * 
	 * @see http://php.net/manual/en/context.http.php#context.http.proxy
	 * 
	 * @param string $proxy URL of proxy server
	 */
	public function setProxy($proxy) {
		$this->proxy = $proxy;
	}

	/**
	 * Manually set stream options that will be used in stream_context_create().
	 * The options provided here are set last, so they will overwrite any existing options set
	 * by the WURFL Cloud Client.
	 * 
	 * Ex:
	 *   setStreamOptions([ 'http' => ['request_fulluri' => true] ])
	 * 
	 * @param array $options The stream options
	 */
	public function setStreamOptions(array $options) {
		$this->stream_options = $options;
	}

	/**
	 * Returns the response body using the PHP file_get_contents() command
	 * @param Config $config
	 * @param string $request_path Request Path/URI
	 * @throws HttpException Unable to query server
	 */
	public function call(Config $config, $request_path) {
		$host = $config->getCloudHost();
		
		$url = "http://${host}${request_path}";

		// Setup HTTP Request headers
		$http_headers = array();
		$http_headers[] = "Host: $host";
		if ($this->use_compression === true) {
			$http_headers[] = "Accept-Encoding: gzip";
		}
		$http_headers[] = "Accept: */*";
		$http_headers[] = "Authorization: Basic ".base64_encode($config->api_key);
		foreach ($this->request_headers as $key => $value) {
			$http_headers[] = "$key: $value";
		}
		$http_headers[] = "Connection: Close";

		$context_options = array(
			'http' => array(
				'method' => "GET",
				'timeout' => ($this->timeout_ms / 1000),
				'ignore_errors' => true,
				'header' => implode("\r\n", $http_headers)."\r\n",
			),
		);

		if ($this->proxy != '') {
			$context_options['http']['proxy'] = $this->proxy;
		}

		$context = stream_context_create($context_options);

		if (function_exists('error_clear_last')) {
			// In PHP 7+ we can clear all previous errors so we're sure we capture the correct one
			error_clear_last();
		}

		// Note: $http_response_header is a magic variable created by file_get_contents()
		//  we create it here to tell code sniffers that the variable exists
		$http_response_header = array();

		// Make actual HTTP request
		$response = @file_get_contents($url, false, $context);

		if ($response === false) {
			$error = error_get_last();
			$error_message = $error['message'];
			throw new HttpException("Unable to contact server: $error_message", null);
		}

		$this->processResponseHeaders(implode("\r\n", $http_response_header));
		$this->processResponseBody($response);
	}
}
