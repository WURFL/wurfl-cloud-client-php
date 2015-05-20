<?php
namespace ScientiaMobile\WurflCloud\HttpClient;
use ScientiaMobile\WurflCloud\Config;
use ScientiaMobile\WurflCloud\Exception;
/**
 * Copyright (c) 2015 ScientiaMobile, Inc.
 *
 * Please refer to the COPYING.txt file distributed with the software for licensing information.
 * 
 * @package ScientiaMobile\WurflCloud
 * @subpackage HttpClient
 */
/**
 * An HTTP Client that uses the builtin PHP fsock commands
 */
class Fsock extends AbstractHttpClient {
	
	/**
	 * Fsock proxy support is not implemented
	 * @see AbstractHttpClient::setProxy()
	 */
	public function setProxy($proxy) {
		throw new Exception(__CLASS__." does not support proxies");
	}

	
	/**
	 * Returns the response body using the PHP cURL Extension
	 * @param Config $config
	 * @param string $request_path Request Path/URI
	 * @throws HttpException Unable to query server
	 */
	public function call(Config $config, $request_path) {
		$host = $config->getCloudHost();
		
		if (strpos(':', $host) !== false) {
			list($host, $port) = explode(':', $host);
		} else {
			$port = '80';
		}
		
		// Open connection
		$fh = @fsockopen($host, $port, $errno, $error, ($this->timeout_ms / 1000));
		if (!$fh) {
			throw new HttpException("Unable to contact server: fsock Error: $error", null);
		}
		
		// Setup HTTP Request headers
		$http_header = "GET $request_path HTTP/1.1\r\n";
		$http_header.= "Host: $host\r\n";
		if ($this->use_compression === true) {
			$http_header.= "Accept-Encoding: gzip\r\n";
		}
		$http_header.= "Accept: */*\r\n";
		$http_header.= "Authorization: Basic ".base64_encode($config->api_key)."\r\n";
		foreach ($this->request_headers as $key => $value) {
			$http_header .= "$key: $value\r\n";
		}
		$http_header.= "Connection: Close\r\n";
		$http_header.= "\r\n";
//die('<pre>'.nl2br($http_header).'</pre>');
		// Setup timeout
		stream_set_timeout($fh, 0, $this->timeout_ms * 1000);
		
		// Send Request headers
		fwrite($fh, $http_header);
		
		// Get Response
		$response = '';
		while ($line = fgets($fh)) {
			$response .= $line;
		}
		$stream_info = stream_get_meta_data($fh);
		fclose($fh);
		
		// Check for Timeout
		if ($stream_info['timed_out']) {
			throw new HttpException("HTTP Request timed out", null);
		}
		
		$this->processResponse($response);
	}
	
	protected function processResponseBody($body) {
		if ($this->responseIsCompressed()) {
			$this->response_body = $this->decompressBody($body);
		} else {
			$this->response_body = $body;
		}
	}
	
	protected function decompressBody($body) {
		$data = @gzinflate(substr($body, 10));
		if (!is_string($data)) {
			throw new HttpException("Unable to decompress the WURFL Cloud Server response", $this->response_http_status);
		}
		return $data;
	}
	
	protected function responseIsCompressed() {
		// Decompress if necessary
		foreach ($this->response_headers as $header) {
			if (stripos($header, 'Content-Encoding: gzip') !== false) {
				return true;
			}
		}
		return false;
	}
}