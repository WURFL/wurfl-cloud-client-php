<?php
namespace ScientiaMobile\WurflCloud\HttpClient;
use ScientiaMobile\WurflCloud\Config;

use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Psr7\Response;
/**
 * Copyright (c) 2016 ScientiaMobile, Inc.
 *
 * Please refer to the COPYING.txt file distributed with the software for licensing information.
 * 
 * @package ScientiaMobile\WurflCloud
 * @subpackage HttpClient
 */
/**
 * An HTTP Client that wraps the GuzzleHttp/Guzzle HTTP client.
 * Note that this differs from the Guzzle client in that it supports Guzzle 6+
 * 
 * You can re-use an existing Guzzle object like this:
 * 
 * // Create a real Guzzle object
 * $guzzle = new \Guzzle\Http\Client();
 * 
 * // Get a WURFL Cloud compatible Guzzle wrapper
 * $guzzle_wrapper = new ScientiaMobile\WurflCloud\HttpClient\Guzzle($guzzle);
 *
 * // Create the WURFL Cloud Client 
 * $client = new ScientiaMobile\WurflCloud\Client($config, $cache, $guzzle_wrapper); 
 * 
 */
class GuzzleHttp extends AbstractHttpClient {
	
	/**
	 * @var \GuzzleHttp\Client
	 */
	private $guzzle;
	
	/**
	 * Create a Guzzle-wrapping HTTP Client for WURFL Cloud
	 * @param \GuzzleHttp\Client|null $guzzle
	 */
	public function __construct($guzzle=null) {
		if ($guzzle) {
			$this->guzzle = $guzzle;
		} else {
			$this->guzzle = new \GuzzleHttp\Client();
		}
	}
	
	/**
	 * Returns the response body using the GuzzleHttp/Guzzle package
	 * @param Config $config
	 * @param string $request_path Request Path/URI
	 * @throws HttpException Unable to query server
	 */
	public function call(Config $config, $request_path) {
		$url = 'http://'.$config->getCloudHost().$request_path;
		
		$http_headers = $this->request_headers;
		$http_headers["Accept"] = "*/*";
		$http_headers["Connection"] = "Close";

		// Compression
		if ($this->use_compression === true) {
			$http_headers['Accept-Encoding'] = 'gzip';
		}

		$options = array(
			'auth' => explode(':', $config->api_key, 2),
			'timeout' => ($this->timeout_ms / 1000),
			'connect_timeout' => ($this->timeout_ms / 1000),
			'proxy' => $this->proxy,
			'headers' => $http_headers,
			'version' => 1.0,
		);
		
		// Execute
		try {
			$response = $this->guzzle->get($url, $options);
			
		} catch (BadResponseException $e) {
			return $this->processGuzzleResponse($e->getResponse());
			
		} catch (\Exception $e) {
			throw new HttpException("Unable to contact server: Guzzle Error: ".$e->getMessage(), null, $e);
		}
		
		return $this->processGuzzleResponse($response);
	}
	
	protected function processGuzzleResponse(Response $response) {
		// Rebuild status line
		$status_line = sprintf("HTTP/%s %s %s",
			$response->getProtocolVersion(),
			$response->getStatusCode(),
			$response->getReasonPhrase()
		);

		// Add status to headers
		$headers = [$status_line];

		// Add other headers in normal format
		foreach ($response->getHeaders() as $header => $value) {
			if (is_array($value)) {
				$value = implode(',', $value);
			}
			$headers[] = "$header: $value";
		}

		$this->processResponseHeaders(implode("\r\n", $headers));
		$this->processResponseBody((string)$response->getBody());
	}
}
