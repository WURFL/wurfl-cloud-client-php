<?php
namespace ScientiaMobile\WurflCloud\Cache;

class FileWithoutLinksTest extends CacheTestCase {
	
	public function setUp() {
		$this->cache = new File();
		$this->cache->cache_dir = File::getSystemTempDir().'wurfl_cloud/';
		$this->cache->use_links = false;
	}
	
	public function testSetCachePrefix() {
		// Not implemented in the File cache
	}

}