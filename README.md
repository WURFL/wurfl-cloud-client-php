# ScientiaMobile WURFL Cloud Client for PHP

The WURFL Cloud Service by ScientiaMobile, Inc., is a cloud-based
mobile device detection service that can quickly and accurately
detect over 500 capabilities of visiting devices.  It can differentiate
between portable mobile devices, desktop devices, SmartTVs and any 
other types of devices that have a web browser.

This is the PHP Client for accessing the WURFL Cloud Service, and
it requires a free or paid WURFL Cloud account from ScientiaMobile:
http://www.scientiamobile.com/cloud 

## Installation
--------------
### Requirements

 - `PHP 5.3+` (Some HTTP adapters may require 5.4+)
 - `json` extension (almost always included)
 - `curl` extension is recommended

### Sign up for WURFL Cloud
First, you must go to http://www.scientiamobile.com/cloud and signup
for a free or paid WURFL Cloud account (see above).  When you've finished
creating your account, and have selected the WURFL Capabilities that you
would like to use, you must copy your API Key, as it will be needed in
the Client.

### Via Composer

    composer require scientiamobile/wurflcloud

### Via Source

[Download the source code](https://github.com/WURFL/wurfl-cloud-client-php/zipball/master)

    require_once '/path/to/cloud/client/src/autoload.php'; 

### Example WURFL Cloud Client
From your web browser, you should go to the WURFL Cloud Client's
examples/ folder.  You will see the Compatibility Test Script,
which will verify that your configuration is compatible with
the WURFL Cloud Client.  

You should test your API Key from this page by pasting it in the
input box, then clicking "Test API Key".  If successful, you will
see "Your server is able to access WURFL Cloud and your API Key was 
accepted."  If there was a problem, the error message will be 
displayed instead.  Please note that it may take a few minutes from
the time that you signup for your WURFL Cloud API Key to become active.

### Integration
You should review the included examples (`example.php`, `MyWurfl.php`,
`show_capabilities.php`) to get a feel for the Client API, and how
best to use it in your application.

Here's a quick example of how to get up and running quickly:

```php
// Include the autoloader - edit this path! 
require_once '../src/autoload.php'; 
// Create a configuration object  
$config = new ScientiaMobile\WurflCloud\Config();  
// Set your WURFL Cloud API Key  
$config->api_key = 'xxxxxx:xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx';   
// Create the WURFL Cloud Client  
$client = new ScientiaMobile\WurflCloud\Client($config);  
// Detect your device  
$client->detectDevice();  
// Use the capabilities  
if ($client->getDeviceCapability('is_wireless_device')) {  
    echo "This is a mobile device";  
} else {  
    echo "This is a desktop device";  
}
```

### Upgrading from v1.x
-----------
**IMPORTANT**: Since version 2.0.0, the WURFL Cloud Client for PHP uses
namespaces.  In addition, things were moved around a bit to allow
for easier configuration.

To upgrade your existing v1 code, you will need to replace calls to
the following classes:

    old:
    require_once '../Client/Client.php';
    WurflCloud_Client_Config
    WurflCloud_Client_Client
    WurflCloud_Cache_Null
    WurflCloud_Cache_Cookie

    new:
    require_once '../src/autoload.php';
    ScientiaMobile\WurflCloud\Config
    ScientiaMobile\WurflCloud\Client
    ScientiaMobile\WurflCloud\Cache\Null
    ScientiaMobile\WurflCloud\Cache\Cookie

Every other class was also renamed/namespaced, so if you are using them
directly, you can follow the mapping above to figure out the new name.

If you were using the `MyWurfl.php` singleton, you can use the new one 
and no code changes will be required in your application.

Note that in v2, support for proxy servers was added as well as better
support for timeouts.  More on these options is found below.

## Configuration
---------------
The WURFL Cloud Client object `ScientiaMobile\WurflCloud\Client` takes three
arguments: `Config`, `Cache`, `HttpClient`.

### Config
`ScientiaMobile\WurflCloud\Config`  
Used for setting the WURFL Cloud API Key `api_key` and adding
`addCloudServer()` / removing `clearServers()` WURFL Cloud Servers.

### Cache (optional)
Caching classes:

 - `ScientiaMobile\WurflCloud\Cache\Null`: Disables caching completely
 - `ScientiaMobile\WurflCloud\Cache\Cookie`: Cookie-based caching
 - `ScientiaMobile\WurflCloud\Cache\File`: Filesystem-based caching
 - `ScientiaMobile\WurflCloud\Cache\APC`: APC memory-based caching
 - `ScientiaMobile\WurflCloud\Cache\Memcache`: Memcached distributed memory-based
 caching using the PHP `memcache` extension
 - `ScientiaMobile\WurflCloud\Cache\Memcached`: Memcached distributed memory-based
 caching using the PHP `memcached` extension

### HttpClient  (optional)
 - `ScientiaMobile\WurflCloud\HttpClient\Fsock`: Uses native PHP `fsock` calls
 - `ScientiaMobile\WurflCloud\HttpClient\Curl`: Uses the PHP extension `curl`
 - `ScientiaMobile\WurflCloud\HttpClient\Guzzle`: Uses the [Guzzle HTTP client](http://guzzlephp.org/)

Note: to use `Guzzle`, you must have the Guzzle library loaded.  To load it locally,
you can run the following command from the root of the WURFL Cloud Client folder:

    composer update

Then make sure to use the composer autoloader `vendor/autoload.php` instead of the
built in one (`src/sutoload.php`).

#### Proxy Server Configuration
The `Curl` and `Guzzle` HTTP Clients support a proxy server configuration via the
`setProxy()` method:

```php
// Common proxy examples
$http_client = new ScientiaMobile\WurflCloud\HttpClient\Curl();
 
// Socks 4 Proxy
$http_client->setProxy("socks4://192.168.1.1:1080");

// Socks 4a Proxy
$http_client->setProxy("socks4a://192.168.1.1:1080");

// Socks 5 Proxy
$http_client->setProxy("socks5://192.168.1.1:1080");

// Socks 5 Proxy + Authentication
$http_client->setProxy("socks5://someuser:somepass@192.168.1.1:1080");

// HTTP Proxy
$http_client->setProxy("http://192.168.1.1:8080");

// HTTP Proxy + Authentication
$http_client->setProxy("http://someuser:somepass@192.168.1.1:8080");

// Pass $http_client in to the Client to use it
$client = new ScientiaMobile\WurflCloud\Client($config, $cache, $http_client);
```

#### HTTP Timeout
The WURFL Cloud Client is set to forcefully terminate the WURFL Cloud request
after 1 second if a response has not been received.  In some cases you may want
to increase this timeout to account for high latency.  All of the HTTP Clients
support the `setTimeout()` method:

```php
$http_client = new ScientiaMobile\WurflCloud\HttpClient\Curl();

// Timeout is in milliseconds (10000 == 10 seconds)
$http_client->setTimeout(10000);

// Pass $http_client in to the Client to use it
$client = new ScientiaMobile\WurflCloud\Client($config, $cache, $http_client);
```

# Unit Testing
Unit tests are included with the client and can be run with PHPUnit.
Before you can run the unit tests, you must install the dependencies
via Composer from the root directory:

    cd WurflCloudClient-PHP*
    curl -sS https://getcomposer.org/installer | php
    php composer.phar install

Before you run the PHPUnit tests, you must set your WURFL Cloud API Key
in an environment variable so it can be used in the tests.  To run the
tests, run `phpunit` from the root directory (where `phpunit.xml.dist`
is located):

    export WURFL_CLOUD_API_KEY="123456:xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
    php vendor/phpunit/phpunit/phpunit.php -v

Note that in order to get all the tests to pass, you will need to use
the API Key from a Premium WURFL Cloud account with all capabilities
enabled.  This is because small responses from the WURFL Cloud system
are never compressed if they fit within one network packet, and the
unit tests that cover compression see this as a failure.  If this is
the case, you will see failures like this:

    There were 3 failures:
    
    1) ScientiaMobile\WurflCloud\HttpClient\CurlTest::testCallCompression
    Failed asserting that an array contains 'Content-Encoding: gzip'.
    
    2) ScientiaMobile\WurflCloud\HttpClient\FsockTest::testCallCompression
    Failed asserting that an array contains 'Content-Encoding: gzip'.
    
    3) ScientiaMobile\WurflCloud\HttpClient\GuzzleTest::testCallCompression
    Failed asserting that an array contains 'Content-Encoding: gzip'.

You will also need to have the extensions `apc`, `memcache`, `memcached`,
`json`, and `curl` enabled.  By default, `apc` is not enabled in CLI mode,
so you may need to launch phpunit like this to force it on:

    php -d apc.enable_cli=1 vendor/phpunit/phpunit/phpunit.php -v

**2015 ScientiaMobile Incorporated**

**All Rights Reserved.**

**NOTICE**:  All information contained herein is, and remains the property of
ScientiaMobile Incorporated and its suppliers, if any.  The intellectual
and technical concepts contained herein are proprietary to ScientiaMobile
Incorporated and its suppliers and may be covered by U.S. and Foreign
Patents, patents in process, and are protected by trade secret or copyright
law. Dissemination of this information or reproduction of this material is
strictly forbidden unless prior written permission is obtained from 
ScientiaMobile Incorporated.
