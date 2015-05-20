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

class TestCookie extends Cookie {
	
	private $expire;
	
	public function getDevice($user_agent) {
		// Simulate cookie data expiration before each get
		if ($this->expire < time()) {
			$_COOKIE = array();
		}
		return parent::getDevice($user_agent);
	}
	
	protected function setCookie($name, $value = null, $expire = null, $path = null, $domain = null, $secure = null, $httponly = null) {
		$_COOKIE[$name] = $value;
		$this->expire = time() + $expire;
	}
	
}