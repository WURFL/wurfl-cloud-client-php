<?php

$loader = require __DIR__ . "/../vendor/autoload.php";
$loader->add('ScientiaMobile\\', __DIR__);

if (!defined('WURFL_CLOUD_SERVER')) {
	define('WURFL_CLOUD_SERVER', isset($_SERVER['WURFL_CLOUD_SERVER'])? $_SERVER['WURFL_CLOUD_SERVER']: 'api.wurflcloud.com');
}

if (!defined('WURFL_CLOUD_API_KEY')) {
	define('WURFL_CLOUD_API_KEY', isset($_SERVER['WURFL_CLOUD_API_KEY'])? $_SERVER['WURFL_CLOUD_API_KEY']: '999999:xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx');
}
