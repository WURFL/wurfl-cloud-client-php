<?php
namespace ScientiaMobile\WurflCloud\Cache;

class NullTest extends CacheTestCase {
	
	public function setUp() {
		$this->cache = new Null();
	}
	
	public function testGetSetDevice() {
		$id = $this->getRandomId();
		$caps = $this->getRandomCapabilities();
		
		$ok = $this->cache->setDevice($id, $caps);
		$this->assertTrue($ok);
		
		$actual = $this->cache->getDevice($id);
		$this->assertFalse($actual);
	}
	
	public function testGetSetDeviceFromId() {
		$id = $this->getRandomId();
		$caps = $this->getRandomCapabilities();
		
		$ok = $this->cache->setDeviceFromID($id, $caps);
		$this->assertTrue($ok);
		
		$actual = $this->cache->getDeviceFromID($id);
		$this->assertFalse($actual);
	}
	
	public function testSetCachePrefix() {
		$id = $this->getRandomId();
		$caps = $this->getRandomCapabilities();
		
		$ok = $this->cache->setDeviceFromID($id, $caps);
		$this->assertTrue($ok);
		
		// Change the cache prefix to invalidate the next lookup
		$this->cache->setCachePrefix('_unit_test_'.mt_rand(100000, 999999));
		
		$actual = $this->cache->getDeviceFromID($id);
		$this->assertFalse($actual);
	}
	
	public function testGetSetDeviceRandomCacheExpire() {
		// Not supported on this cache provider
	}

	public function testGetSetDeviceFromIdRandomCacheExpire() {
		// Not supported on this cache provider
	}
	
	public function testCacheExpireIsHonored() {
		// Impossible to test, but at least we can ensure the method is present
		$this->cache->setCacheExpiration(1);
	}

}