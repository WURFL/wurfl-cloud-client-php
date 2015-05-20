<?php
namespace ScientiaMobile\WurflCloud\Cache;

class MemcacheTest extends CacheTestCase {
	
	public function setUp() {
		if (!extension_loaded('memcache')) {
			$this->markTestSkipped("PHP extension 'memcache' is not loaded");
		}
		
		$host = '127.0.0.1';
		$port = 11211;
		
		$this->cache = new Memcache();
		$this->cache->addServer($host, $port);
		
		$memcache = $this->cache->getMemcache();
		if (@$memcache->getVersion() === false) {
			$this->markTestSkipped("Cannot connect to local memcached server");
		}
	}
}