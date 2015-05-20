<?php
namespace ScientiaMobile\WurflCloud\HttpClient;
use ScientiaMobile\WurflCloud\Config;
use Guzzle\Http\Exception\BadResponseException;
use Guzzle\Http\Message\Response;
use Guzzle\Http\Client as GuzzleClient;
/**
 * Copyright (c) 2015 ScientiaMobile, Inc.
 *
 * Please refer to the COPYING.txt file distributed with the software for licensing information.
 * 
 * @package ScientiaMobile\WurflCloud
 * @subpackage HttpClient
 */
/**
 * An HTTP Client that wraps the Guzzle HTTP client
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
class Guzzle extends AbstractHttpClient {
	
	/**
	 * @var \Guzzle\Http\Client
	 */
	private $guzzle;
	
	/**
	 * Create a Guzzle-wrapping HTTP Client for WURFL Cloud
	 * @param \Guzzle\Http\Client|null $guzzle
	 */
	public function __construct($guzzle=null) {
		if ($guzzle) {
			$this->guzzle = $guzzle;
		} else {
			$this->guzzle = new GuzzleClient();
		}
	}
	
	/**
	 * Returns the response body using the PHP cURL Extension
	 * @param Config $config
	 * @param string $request_path Request Path/URI
	 * @throws HttpException Unable to query server
	 */
	public function call(Config $config, $request_path) {
		// Setup
		$this->guzzle->setBaseUrl('http://'.$config->getCloudHost());
		
		$options = array(
			'auth' => explode(':', $config->api_key, 2),
			'timeout' => ($this->timeout_ms / 1000),
			'connect_timeout' => ($this->timeout_ms / 1000),
			'proxy' => $this->proxy,
		);
		
		// Compression
		if ($this->use_compression === true) {
			$this->request_headers['Accept-Encoding'] = 'gzip';
		}
		
		// Execute
		try {
			$request = $this->guzzle->get($request_path, $this->request_headers, $options);
			$response = $request->send();
			
		} catch (BadResponseException $e) {
			return $this->processGuzzleResponse($e->getResponse());
			
		} catch (\Exception $e) {
			throw new HttpException("Unable to contact server: Guzzle Error: ".$e->getMessage(), null, $e);
			
		}
		
		return $this->processGuzzleResponse($response);
	}
	
	protected function processGuzzleResponse(Response $response) {
		$this->processResponseHeaders($response->getRawHeaders());
		$this->processResponseBody($response->getBody(true));
	}
}