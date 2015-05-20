<?php
namespace ScientiaMobile\WurflCloud\Cache;

class FileWithLinksTest extends CacheTestCase {
	
	public function setUp() {
		if (DIRECTORY_SEPARATOR !== '/') {
			$this->markTestSkipped("Hard links are only available in UNIX-like environments");
		}
		
		$this->cache = new File();
		$this->cache->cache_dir = File::getSystemTempDir().'wurfl_cloud/';
	}
	
	public function testSetCachePrefix() {
		// Not implemented in the File cache
	}

}