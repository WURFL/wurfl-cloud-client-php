<?php
namespace ScientiaMobile\WurflCloud;
/**
 * Copyright (c) 2015 ScientiaMobile, Inc.
 *
 * Please refer to the COPYING.txt file distributed with the software for licensing information.
 * 
 * @package ScientiaMobile\WurflCloud
 */
/**
 * Configuration class for the Client
 * 
 * A usage example of ScientiaMobile\WurflCloud\Client\Config:
 * <code>
 * // Create a configuration object 
 * $config = new ScientiaMobile\WurflCloud\Client\Config(); 
 * // Paste your API Key below 
 * $config->api_key = 'xxxxxx:xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx';
 * </code>
 * 
 * @package ScientiaMobile\WurflCloud
 */
class Config {
	
	/**
	 * Enables or disables the use of compression in the WURFL Cloud response.  Using compression
	 * can increase CPU usage in very high traffic environments, but will decrease network traffic
	 * and latency.
	 * @var boolean
	 */
	public $compression = true;
	
	/**
	 * WURFL Cloud Service API Key
	 * 
	 * The API Key is used to authenticate with the WURFL Cloud Service.  It can be found at in your account
	 * at http://www.scientiamobile.com/myaccount
	 * The API Key is 39 characters in with the format: nnnnnn:xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
	 * where 'n' is a number and 'x' is a letter or number
	 * 
	 * @var string
	 */
	public $api_key;
	
	/**
	 * WURFL Cloud servers to use for uncached requests.  The "weight" field can contain any positive number,
	 * the weights are relative to each other.  
	 * @var array WURFL Cloud Servers
	 */
	public $wcloud_servers = array(
	//	'nickname'   	=> array(host, weight),
		'wurfl_cloud' 	=> array('api.wurflcloud.com', 80),
	);
	
	/**
	 * The WURFL Cloud Server that is currently in use, formatted like:
	 * 'server_nickname' => array('url', 'weight')
	 * @var array
	 */
	private $current_server = array();
	
	/**
	 * Adds the specified WURFL Cloud Server
	 * @param string $nickname Unique identifier for this server
	 * @param string $url URL to this server's API
	 * @param int $weight Specifies the chances that this server will be chosen over
	 * the other servers in the pool.  This number is relative to the other servers' weights.
	 */
	public function addCloudServer($nickname, $url, $weight=100) {
		$this->wcloud_servers[$nickname] = array($url, $weight);
	}
	
	/**
	 * Removes the WURFL Cloud Servers
	 */
	public function clearServers() {
		$this->wcloud_servers = array();
	}
	
	/**
	 * Determines the WURFL Cloud Server that will be used and returns its URL.
	 * @return string WURFL Cloud Server URL
	 */
	public function getCloudHost() {
		$server = $this->getWeightedServer();
		return $server[0];
	}
	
	/**
	 * Uses a weighted-random algorithm to chose a server from the pool
	 * @return array Server in the form array('host', 'weight')
	 */
	public function getWeightedServer() {
		if (count($this->current_server) === 1) {
			return $this->current_server;
		}
		if (count($this->wcloud_servers) === 1) {
			return $this->wcloud_servers[key($this->wcloud_servers)];
		}
		$max = $rcount = 0;
		foreach ($this->wcloud_servers as $k => $v) {
			$max += $v[1];
		}
		$wrand = mt_rand(0, $max);
		$k = 0;
		foreach ($this->wcloud_servers as $k => $v) {
			if ($wrand <= ($rcount += $v[1])) {
				break;
			}
		}
		$this->current_server = $this->wcloud_servers[$k];
		return $this->current_server;
	}
	
	
}