<?php
namespace ScientiaMobile\WurflCloud\Cache;

abstract class CacheTestCase extends \PHPUnit_Framework_TestCase {
	
	/**
	 * @var CacheInterface
	 */
	protected $cache;
	
	public function testGetSetDevice() {
		$id = $this->getRandomId();
		$caps = $this->getRandomCapabilities();
		
		$ok = $this->cache->setDevice($id, $caps);
		$this->assertTrue($ok);
		
		$actual = $this->cache->getDevice($id);
		$this->assertEquals($caps, $actual);
	}
	
	public function testGetSetDeviceRandomCacheExpire() {
		$this->cache->cache_expiration_rand_max = 20;
		
		$id = $this->getRandomId();
		$caps = $this->getRandomCapabilities();
	
		$ok = $this->cache->setDevice($id, $caps);
		$this->assertTrue($ok);
	
		$actual = $this->cache->getDevice($id);
		$this->assertEquals($caps, $actual);
	}
	
	public function testGetSetDeviceFromId() {
		$id = $this->getRandomId();
		$caps = $this->getRandomCapabilities();
		
		$ok = $this->cache->setDeviceFromID($id, $caps);
		$this->assertTrue($ok);
		
		$actual = $this->cache->getDeviceFromID($id);
		$this->assertEquals($caps, $actual);
	}
	
	public function testGetSetDeviceFromIdRandomCacheExpire() {
		$this->cache->cache_expiration_rand_max = 20;
		
		$id = $this->getRandomId();
		$caps = $this->getRandomCapabilities();
	
		$ok = $this->cache->setDeviceFromID($id, $caps);
		$this->assertTrue($ok);
	
		$actual = $this->cache->getDeviceFromID($id);
		$this->assertEquals($caps, $actual);
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
	
	public function testCacheExpireIsHonored() {
		$id = $this->getRandomId();
		$caps = $this->getRandomCapabilities();
		
		// Change the cache expiration, save the entry, then sleep to invalidate cache
		$this->cache->setCacheExpiration(1);
		
		$ok = $this->cache->setDeviceFromID($id, $caps);
		$this->assertTrue($ok);
		
		$start_time = microtime(true);
		$actual = true;
		
		$i = 0;
		while ($i < 50 && $actual = $this->cache->getDeviceFromID($id)) {
			// Check every 200ms for 10 seconds, then fail
			usleep(200 * 1000);
			$i++;
		}
		
		$total_time = round((microtime(true) - $start_time) * 1000);
		$this->assertFalse($actual, "Entry did not expire after $i attempts in {$total_time}ms");
	}
	
	public function tearDown() {
		if ($this->cache instanceof CacheInterface) {
			$this->cache->close();
		}
	}
	
	protected function getRandomId() {
		return uniqid(md5(mt_rand()), true);
	}
	
	protected function getRandomCapabilities() {
		return array(
			'id' => $this->getRandomId(),
			'brand_name' => $this->getRandomId(),
			'model_name' => $this->getRandomId(),
		);
	}
	
}