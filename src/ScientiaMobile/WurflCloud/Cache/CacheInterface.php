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
 * Interface that all Cache providers must implement to be compatible with ScientiaMobile\WurflCloud\Client
 * @package ScientiaMobile\WurflCloud
 * @subpackage Cache
 */
interface CacheInterface {
	
	/**
	 * Get the device capabilities for the given user agent from the cache provider
	 * @param string $key User Agent
	 * @return array|boolean Capabilities array or boolean false
	 */
	public function getDevice($key);
	
	/**
	 * Get the device capabilities for the given device ID from the cache provider
	 * @param string $key WURFL Device ID
	 * @return array|boolean Capabilities array or boolean false
	 */
	public function getDeviceFromID($key);
	
	/**
	 * Stores the given user agent with the given device capabilities in the cache provider for the given time period
	 * @param string $key User Agent
	 * @param array $value Capabilities
	 * @return boolean Success
	 */
	public function setDevice($key, $value);
	
	/**
	 * Stores the given user agent with the given device capabilities in the cache provider for the given time period
	 * @param string $key WURFL Device ID
	 * @param array $value Capabilities
	 * @return boolean Success
	 */
	public function setDeviceFromID($key, $value);
	
	/**
	 * Closes the connection to the cache provider
	 */
	public function close();
	
	/**
	 * Sets the string that is prefixed to the keys stored in this cache provider (to prevent collisions)
	 * @param string $prefix
	 */
	public function setCachePrefix($prefix);
	
	/**
	 * Sets the expiration time of the cached items
	 * @param int $time Expiration time in seconds
	 */
	public function setCacheExpiration($time);
}