<?php
namespace ScientiaMobile\WurflCloud\Cache;

class APCTest extends CacheTestCase {
	
	public function setUp() {
		if (!APC::isSupported()) {
			$this->markTestSkipped("PHP extension 'apc' is not loaded or enabled (see apc.enable_cli)");
		}

		$this->cache = new APC();
		$this->cache->clear();
	}
	
	public function testCacheExpireIsHonored() {
		// This test doesn't seem to work for APC from the CLI
	}
}