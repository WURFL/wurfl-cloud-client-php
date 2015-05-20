<?php
namespace ScientiaMobile\WurflCloud\Cache;
/**
 * Copyright (c) 2015 ScientiaMobile, Inc.
 *
 * Please refer to the COPYING.txt file distributed with the software for licensing information.
 * 
 * @package ScientiaMobile\WurflCloud
 * @subpackage Cache
 */
/**
 * Cookie cache provider
 * @package ScientiaMobile\WurflCloud
 * @subpackage Cache
 */
class Cookie implements CacheInterface {
	public $cookie_name = 'WurflCloud_Client';
	public $cache_expiration = 86400;
	private $cookie_sent = false;
	
	public function getDevice($user_agent) {
		if (!isset($_COOKIE[$this->cookie_name])) {
			return false;
		}
		$cookie_data = @json_decode($_COOKIE[$this->cookie_name], true, 5);
		if (!is_array($cookie_data) || empty($cookie_data)) {
			return false;
		}
		if (!isset($cookie_data['date_set']) || ((int)$cookie_data['date_set'] + $this->cache_expiration) < time()) {
			return false;
		}
		if (!isset($cookie_data['capabilities']) || !is_array($cookie_data['capabilities']) || empty($cookie_data['capabilities'])) {
			return false;
		}
		return $cookie_data['capabilities'];
	}
	
	public function getDeviceFromID($device_id) {
		return false;
	}
	
	public function setDevice($user_agent, $capabilities) {
		if ($this->cookie_sent === true) {
			return true;
		}
		
		$cookie_data = array(
			'date_set' => time(),
			'capabilities' => $capabilities,
		);
		$this->setCookie($this->cookie_name, json_encode($cookie_data, JSON_FORCE_OBJECT), $cookie_data['date_set'] + $this->cache_expiration);
		$this->cookie_sent = true;
		
		return true;
	}
	
	// Required by interface but not used for this provider
	public function setDeviceFromID($device_id, $capabilities) {
		return true;
	}
	
	public function setCacheExpiration($time) {
		$this->cache_expiration = $time;
	}
	
	public function setCachePrefix($prefix) {}
	public function close() {}
	
	protected function setCookie($name, $value = null, $expire = null, $path = null, $domain = null, $secure = null, $httponly = null) {
		return setcookie($name, $value, $expire, $path, $domain, $secure, $httponly);
	}
}