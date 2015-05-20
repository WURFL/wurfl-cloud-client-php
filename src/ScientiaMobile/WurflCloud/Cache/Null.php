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
 * The Null Cache Provider.  This exists only to disable caching and
 * should not be used for production installations
 * @package ScientiaMobile\WurflCloud
 * @subpackage Cache
 */
class Null implements CacheInterface {

	public $cache_expiration = 0;
	public $cache_expiration_rand_max = 0;
	public function getDevice($user_agent) {
		return false;
	}
	public function getDeviceFromID($device_id) {
		return false;
	}
	public function setDevice($user_agent, $capabilities) {
		return true;
	}
	public function setDeviceFromID($device_id, $capabilities) {
		return true;
	}
	public function setCacheExpiration($time) {}
	public function setCachePrefix($prefix) {}
	public function close(){}
}
