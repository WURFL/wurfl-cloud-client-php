<?php
namespace ScientiaMobile\WurflCloud\Cache;

class CookieTest extends CacheTestCase {

    public function setUp() {
        $_COOKIE = array();
        $this->cache = new TestCookie();
    }

    public function testGetSetDeviceFromId() {
        $this->assertTrue($this->cache->setDeviceFromID('foo', 'bar'));
        $this->assertFalse($this->cache->getDeviceFromID('foo'));
    }

    public function testGetSetDeviceRandomCacheExpire() {
        // Not supported on this cache provider
    }

    public function testGetSetDeviceFromIdRandomCacheExpire() {
        // Not supported on this cache provider
    }
}
