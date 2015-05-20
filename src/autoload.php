<?php
/**
 * ScientiaMobile\WurflCloud\Client PHP class autoloader
 */

if (!class_exists('ScientiaMobile\WurflCloud\Client')) {
	if (!class_exists('SplClassLoader')) {
		require_once __DIR__.'/SplClassLoader.php';
	}
	$classLoader = new SplClassLoader('ScientiaMobile\WurflCloud', __DIR__);
	$classLoader->register();
}