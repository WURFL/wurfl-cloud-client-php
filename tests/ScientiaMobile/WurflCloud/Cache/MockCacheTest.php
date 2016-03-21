<?php
namespace ScientiaMobile\WurflCloud\Cache;

class MockCacheTest extends CacheTestCase {

    public function setUp() {
        $this->cache = new MockCache();
    }

    public function testGetSetDeviceFromId() {
        $id = $this->getRandomId();
        $caps = $this->getRandomCapabilities();

        $ok = $this->cache->setDeviceFromID($id, $caps);
        $this->assertTrue($ok);

        $actual = $this->cache->getDeviceFromID($id);
        $this->assertFalse($actual);
    }

    public function testGetSetDeviceFromIdRandomCacheExpire() {
        $this->cache->cache_expiration_rand_max = 20;

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

    public function testCacheExpireIsHonored() {
        // Impossible to test, but at least we can ensure the method is present
        $this->cache->setCacheExpiration(1);
    }
}
