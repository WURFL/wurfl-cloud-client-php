<?php
namespace ScientiaMobile\WurflCloud\Cache;

use ScientiaMobile\WurflCloud\Exception;

/**
 * Copyright (c) 2017 ScientiaMobile, Inc.
 *
 * Please refer to the COPYING.txt file distributed with the software for licensing information.
 *
 * @package ScientiaMobile\WurflCloud
 * @subpackage Cache
 */
/**
 * The Redis cache provider
 *
 * An example of using Redis for caching:
 * <code>
 * // Create Configuration object
 * $config = new ScientiaMobile\WurflCloud\Config();
 * // Set API Key
 * $config->api_key = 'xxxxxx:xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx';
 * // Use Redis Caching
 * $cache = new ScientiaMobile\WurflCloud\Cache\Redis();
 * // Set the redis server
 * $cache->addServer('localhost');
 * // Create Client
 * $client = new ScientiaMobile\WurflCloud\Client($config, $cache);
 * </code>
 *
 * You can also specify multiple servers with different ports and weights:
 * <code>
 * // Create Cache object
 * $cache = new ScientiaMobile\WurflCloud\Cache\Redis();
 * // Add a local redis server at port 6379
 * $cache->addServer('localhost', 6379);
 * </code>
 *
 * If you have unusual traffic patterns, you may want to add some randomness to your
 * cache expiration, so you don't get a bunch of entries expiring at the same time:
 * <code>
 * // Create Cache object
 * $cache = new ScientiaMobile\WurflCloud\Cache\Redis();
 * // Add up to 10 minutes (600 seconds) to the cache expiration
 * $cache->cache_expiration_rand_max = 600;
 * </code>
 *
 * @throws \ScientiaMobile\WurflCloud\Exception Required module does not exist
 * @package ScientiaMobile\WurflCloud
 * @subpackage Cache
 */
class Redis implements CacheInterface
{
    /**
     * Number of seconds to keep device cached in memory.  Default: 0 - forever.
     * Note: behaviour of the redis server depends on the eviction policy set
     * volatile-lru is used by default, if 0 is used redis wont evict the keys
     * allkeys-lru is likely a better choice
     *
     * @see https://redis.io/topics/lru-cache
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
     * @var \Redis
     */
    protected $cache;
    
    /**
     * Redis Key prefix
     * @var string
     * @access private
     */
    protected $prefix = 'wc2mcd_';
    
    /**
     * Create a new Redis object
     * @param \Redis $redis If included, this instance of Redis is used instead of using an existing one
     * @throws \ScientiaMobile\WurflCloud\Exception
     */
    public function __construct($redis=null)
    {
        if (!extension_loaded('redis')) {
            throw new Exception('Please verify the Redis extension is loaded');
        }
        
        if ($redis instanceof \Redis) {
            $this->cache = $redis;
            $this->cache->setOption(\Redis::OPT_SERIALIZER, \Redis::SERIALIZER_PHP);
            return;
        }

        $this->cache = new \Redis();
    }
    
    /**
     * Update the redis connection settings
     * @param string $host Server hostname
     * @param int $port Server port number
     */
    public function addServer($host, $port = 6379)
    {
        $this->cache->connect($host, $port);
        $this->cache->setOption(\Redis::OPT_SERIALIZER, \Redis::SERIALIZER_PHP);
    }
    
    public function getDevice($user_agent)
    {
        $device_id = $this->cache->get($this->prefix.hash('md5', $user_agent));
        if ($device_id !== false) {
            $caps = $this->cache->get($this->prefix.$device_id);
            if ($caps !== false) {
                return $caps;
            }
        }
        return false;
    }
    
    public function getDeviceFromID($device_id)
    {
        return $this->cache->get($this->prefix.$device_id);
    }
    
    public function setDevice($user_agent, $capabilities)
    {
        $ttl = $this->getTtl();
        return
            $this->cache->set($this->prefix.hash('md5', $user_agent), $capabilities['id'], $ttl) &&
            $this->cache->set($this->prefix.$capabilities['id'], $capabilities, $ttl);
    }
    
    public function setDeviceFromID($device_id, $capabilities)
    {
        $ttl = $this->getTtl();
        return $this->cache->set($this->prefix.$device_id, $capabilities, $ttl);
    }
    
    public function setCachePrefix($prefix)
    {
        $this->prefix = $prefix.'_';
    }
    
    public function setCacheExpiration($time)
    {
        $this->cache_expiration = $time;
    }
    
    public function close()
    {
        $this->cache->close();
        $this->cache = null;
    }
    
    public function getRedis()
    {
        return $this->cache;
    }

    /**
     * @return int
     */
    private function getTtl()
    {
        $ttl = $this->cache_expiration;
        if ($this->cache_expiration_rand_max !== 0) {
            $ttl += mt_rand(0, $this->cache_expiration_rand_max);
        }
        return $ttl;
    }
}
