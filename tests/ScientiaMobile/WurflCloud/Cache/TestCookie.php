<?php
namespace ScientiaMobile\WurflCloud\Cache;

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
