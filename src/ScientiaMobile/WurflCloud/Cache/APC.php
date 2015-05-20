<?php
namespace ScientiaMobile\WurflCloud\Cache;
use ScientiaMobile\WurflCloud\Exception;
/**
 * Copyright (c) 2015 ScientiaMobile, Inc.
 *
 * Please refer to the COPYING.txt file distributed with the software for licensing information.
 * 
 * @package ScientiaMobile\WurflCloud
 * @subpackage Cache
 */
/**
 * The APC Cache Provider
 *
 * An example of using APC for caching:
 * <code>
 * // Create Configuration object
 * $config = new ScientiaMobile\WurflCloud\Config();
 * // Set API Key
 * $config->api_key = 'xxxxxx:xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx';
 * // Use APC Caching
 * $cache = new ScientiaMobile\WurflCloud\Cache\APC();
 * // Create Client
 * $client = new ScientiaMobile\WurflCloud\Client($config, $cache);
 * </code>
 *
 * @throws \ScientiaMobile\WurflCloud\Exception Required module does not exist
 * @package ScientiaMobile\WurflCloud
 * @subpackage Cache
 */
class APC implements CacheInterface {

	/**
	 * Number of seconds to keep device cached in memory.  Default: 0 - forever.
	 * Note: the device will eventually be pushed out of memory if the memcached
	 * process runs out of memory.
	 * @var Int Seconds to cache the device in memory
	 */
	public $cache_expiration = 86400;
	
	/**
	 * Used to add randomness to the cache expiration.  If this value is 0, no 
	 * randomness will be added, if it's above 0, a random value between 0..value
	 * will be added to the cache_expiration to prevent massive simultaneous expiry
	 * @var int
	 */
	public $cache_expiration_rand_max = 0;
	
	/**
	 * @var string
	 * @access private
	 */
	protected $prefix = 'wc2_';

	public function __construct() {
		if (!function_exists('apc_store')) {
			throw new Exception("The 'apc' extension is not loaded.");
		}
	}

	public function getDevice($user_agent){
		$device_id = apc_fetch($this->prefix.hash('md5', $user_agent));
		if ($device_id !== false) {
			$caps = apc_fetch($this->prefix.$device_id);
			if ($caps !== false) {
				return $caps;
			}
		}
		return false;
	}
	
	public function getDeviceFromID($device_id) {
		$result = apc_fetch($this->prefix.$device_id);
		return ($result === false)? false: $result;
	}
	
	public function setDevice($user_agent, $capabilities) {
		$ttl = $this->cache_expiration;
		if ($this->cache_expiration_rand_max !== 0) {
			$ttl += mt_rand(0, $this->cache_expiration_rand_max);
		}
		apc_add($this->prefix.hash('md5', $user_agent), $capabilities['id'], $ttl);
		apc_add($this->prefix.$capabilities['id'], $capabilities, $ttl);
		return true;
	}
	
	public function setDeviceFromID($device_id, $capabilities) {
		$ttl = $this->cache_expiration;
		if ($this->cache_expiration_rand_max !== 0) {
			$ttl += mt_rand(0, $this->cache_expiration_rand_max);
		}

		apc_add($this->prefix.$device_id, $capabilities, $ttl);
		return true;
	}
	
	public function setCachePrefix($prefix) {
		$this->prefix = $prefix.'_';
	}
	
	public function setCacheExpiration($time) {
		$this->cache_expiration = $time;
	}
	
	public function close(){}
}
