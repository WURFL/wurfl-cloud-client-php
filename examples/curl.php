<?php

require_once __DIR__.'/../src/autoload.php';

use ScientiaMobile\WurflCloud\Config;
use ScientiaMobile\WurflCloud\Client;
use ScientiaMobile\WurflCloud\Cache\Cookie;
use ScientiaMobile\WurflCloud\HttpClient\Curl;

try {
    // Create a WURFL Cloud Config
    $config = new Config();

    // Set your API Key here
    $config->api_key = '861359:K0P1rBDX8yfk9gAvtSlsOCIUmTL5uwQJ';

    // Create an HTTP Client
    $http_client = new Curl();

    // Increase the timeout if there are issues connecting
    $http_client->setTimeout(3000);

    // Create a WURFL Cloud Client
    $client = new Client($config, new Cookie(), $http_client);

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
