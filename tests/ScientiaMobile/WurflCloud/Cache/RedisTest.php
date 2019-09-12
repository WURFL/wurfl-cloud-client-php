<?php
namespace ScientiaMobile\WurflCloud\Cache;

class RedisTest extends CacheTestCase
{
    public function setUp()
    {
        if (!extension_loaded('redis')) {
            $this->markTestSkipped("PHP extension 'redis' is not loaded");
        }
        
        $this->cache = new Redis();
        $this->cache->addServer('127.0.0.1');
    }
}
