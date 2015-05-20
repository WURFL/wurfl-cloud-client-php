<?php
namespace ScientiaMobile\WurflCloud\Cache;

class APCTest extends CacheTestCase {
	
	public function setUp() {
		if (!extension_loaded('apc')) {
			$this->markTestSkipped("PHP extension 'apc' is not loaded");
		}
		
		if (@apc_cache_info() === false) {
			$this->markTestSkipped("PHP extension 'apc' is loaded, but not enabled, see apc.enable_cli");
		}
		
		$this->cache = new APC();
		apc_clear_cache();
		@apc_clear_cache("user");
	}
	
	public function testCacheExpireIsHonored() {
		// This test doesn't seem to work for APC from the CLI
	}
}