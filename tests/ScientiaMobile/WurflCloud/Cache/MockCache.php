<?php
namespace ScientiaMobile\WurflCloud\Cache;

class MockCache extends NullCache {

    public $misses = 0;
    public $hits = 0;
    public $sets = 0;

    private $devices = array();

    public function getDevice($user_agent) {
        if (!array_key_exists($user_agent, $this->devices)) {
            $this->misses++;
            return false;
        }
        $this->hits++;
        return $this->devices[$user_agent];
    }

    public function setDevice($user_agent, $capabilities) {
        $this->sets++;
        $this->devices[$user_agent] = $capabilities;
        return true;
    }
}
