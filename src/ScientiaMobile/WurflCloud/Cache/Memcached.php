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
 * The Memcached Cache Provider
 * 
 * NOTE: PHP has two very common extensions for communicating with Memcache:
 * 'memcache' and 'memcached'.  Either will work, but you must use the correct
 * CacheInterface class!
 * 
 * An example of using Memcached for caching:
 * <code>
 * // Create Configuration object
 * $config = new ScientiaMobile\WurflCloud\Config();
 * // Set API Key
 * $config->api_key = 'xxxxxx:xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx';
 * // Use Memcached Caching
 * $cache = new ScientiaMobile\WurflCloud\Cache\Memcached();
 * // Set the Memcache server
 * $cache->addServer('localhost');
 * // Create Client
 * $client = new ScientiaMobile\WurflCloud\Client($config, $cache);
 * </code>
 * 
 * You can also specify multiple servers with different ports and weights:
 * <code>
 * // Create Cache object
 * $cache = new ScientiaMobile\WurflCloud\Cache\Memcached();
 * // Add a local memcache server at port 11211 and weight 60
 * $cache->addServer('localhost', 11211, 60);
 * // Add another server with a weight of 40 - it will be used about 40% of the time
 * $cache->addServer('192.168.1.10', 11211, 40);
 * </code>
 * 
 * If you have unusual traffic patterns, you may want to add some randomness to your
 * cache expiration, so you don't get a bunch of entries expiring at the same time:
 * <code>
 * // Create Cache object
 * $cache = new ScientiaMobile\WurflCloud\Cache\Memcached();
 * // Add up to 10 minutes (600 seconds) to the cache expiration
 * $cache->cache_expiration_rand_max = 600;
 * </code>
 * 
 * @throws \ScientiaMobile\WurflCloud\Exception Required module does not exist
 * @package ScientiaMobile\WurflCloud
 * @subpackage Cache
 */
class Memcached implements CacheInterface {
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
	 * @var \Memcached
	 */
	protected $memcache;
	
	/**
	 * Memcache Key prefix
	 * @var string
	 * @access private
	 */
	protected $prefix = 'wc2mcd_';
	
	/**
	 * If true, data stored in Memcache will be compressed
	 * @var boolean
	 */
	public $compression = false;
	
	/**
	 * true if the pool of memcache connections is already set via persistence
	 * @var boolean 
	 */
	private $persistent_pool_restored = false;
	
	/**
	 * Create a new Memcached object
	 * @param \Memcached $memcache If included, this instance of Memcached is used instead of using an existing one
	 * @throws \ScientiaMobile\WurflCloud\Exception
	 */
	public function __construct($memcache=null) {
		if (!class_exists('Memcached', false)) {
			throw new Exception("The class 'Memcached' does not exist.  Please verify the extension is loaded");
		}
		
		$this->memcache = ($memcache instanceof \Memcached)? $memcache: new \Memcached($this->prefix.'persist');
		
		// Check if we have an existing persistent connection, or if we must reconnect
		if (!count($this->memcache->getServerList())) {
			$this->memcache->setOption(\Memcached::OPT_COMPRESSION, $this->compression);
		} else {
			$this->persistent_pool_restored = true;
		}
	}
	
	/**
	 * Add Memcache server
	 * @param string $host Server hostname
	 * @param int $port Server port number
	 * @param int $weight Probability that this server will be used, relative to other added servers
	 */
	public function addServer($host, $port = 11211, $weight = 1) {
		// The memcache connection pool is already established.  This prevents duplicate memcache connections. 
		if ($this->persistent_pool_restored === true) return;
		$this->memcache->addServer($host, $port, $weight);
	}
	
	public function getDevice($user_agent) {
		$device_id = $this->memcache->get($this->prefix.hash('md5', $user_agent));
		if ($device_id !== false) {
			$caps = $this->memcache->get($this->prefix.$device_id);
			if ($caps !== false) {
				return $caps;
			}
		}
		return false;
	}
	
	public function getDeviceFromID($device_id) {
		return $this->memcache->get($this->prefix.$device_id);
	}
	
	public function setDevice($user_agent, $capabilities) {
		$ttl = $this->cache_expiration;
		if ($this->cache_expiration_rand_max !== 0) {
			$ttl += mt_rand(0, $this->cache_expiration_rand_max);
		}
		// Set user_agent => device_id
		$this->memcache->add($this->prefix.hash('md5', $user_agent), $capabilities['id'], $ttl);
		// Set device_id => (array)capabilities
		$this->memcache->add($this->prefix.$capabilities['id'], $capabilities, $ttl);
		return true;
	}
	
	public function setDeviceFromID($device_id, $capabilities) {
		$ttl = $this->cache_expiration;
		if ($this->cache_expiration_rand_max !== 0) {
			$ttl += mt_rand(0, $this->cache_expiration_rand_max);
		}
		$this->memcache->add($this->prefix.$device_id, $capabilities, $ttl);
		return true;
	}
	
	public function setCachePrefix($prefix) {
		$this->prefix = $prefix.'_';
	}
	
	public function setCacheExpiration($time) {
		$this->cache_expiration = $time;
	}
	
	public function close() {
		$this->memcache = null;
	}
	
	public function getMemcache() {
		return $this->memcache;
	}
}
