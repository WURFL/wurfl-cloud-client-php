<?php
use ScientiaMobile\WurflCloud\Config;
use ScientiaMobile\WurflCloud\Cache\Null;
use ScientiaMobile\WurflCloud\Cache\Cookie;
use ScientiaMobile\WurflCloud\Client;
/**
 * WURFL Cloud Client - Simple example using MyWurfl
 * @package WurflCloud_Client
 * @subpackage Examples
 * 
 * This example uses the included MyWurfl class to get device capabilities.
 * If you prefer to use the WURFL Cloud Client directly, see show_capabilities.php
 * 
 * For this example to work properly, you must put your API Key in the script below.
 */
/**
 * Include the WURFL Cloud Client file
 */
require_once __DIR__.'/../src/autoload.php';

try {
	// Create a WURFL Cloud Config
	$config = new Config();
	
	// Set your API Key here
	$config->api_key = 'xxxxxx:xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx';
	
	// Create a WURFL Cloud Client
	$client = new Client($config, new Null());
	
	// Detect the visitor's device
	$client->detectDevice();

	// Show all the capabilities returned by the WURFL Cloud Service
	foreach ($client->capabilities as $name => $value) {
		echo "<strong>$name</strong>: ".(is_bool($value)? var_export($value, true): $value) ."<br/>";
	}
} catch (Exception $e) {
	// Show any errors
	echo "Error: ".$e->getMessage();
}
