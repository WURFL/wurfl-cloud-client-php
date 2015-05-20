<?php
namespace ScientiaMobile\WurflCloud;

class ConfigTest extends \PHPUnit_Framework_TestCase {
	
	public function testApiKey() {
		$config = new Config();
		$this->assertNull($config->api_key);
	}
	
	public function testAddCloudServer() {
		$config = new Config();
		$config->addCloudServer('foo', 'bar', 50);
		$this->assertArrayHasKey('foo', $config->wcloud_servers);
		$this->assertEquals(array('bar', 50), $config->wcloud_servers['foo']);
	}
	
	public function testClearServers() {
		$config = new Config();
		$this->assertNotEmpty($config->wcloud_servers);
		$config->clearServers();
		$this->assertEmpty($config->wcloud_servers);
	}
	
	public function testGetCloudHost() {
		$config = new Config();
		$config->clearServers();
		$config->addCloudServer('foo', 'bar', 50);
		$this->assertEquals('bar', $config->getCloudHost());
	}
	
	public function testGetWeightedServer() {
		$config = new Config();
		$config->clearServers();
		
		$likely_real = 1000;
		$unlikely_real = 8;
		$runs = 50000;
		$allowed_deviation = 0.01; // 1% ratio deviation
		
		$config->addCloudServer('foo', 'likely', $likely_real);
		$config->addCloudServer('nota', 'unlikely', $unlikely_real);
		
		$likely = 0;
		$unlikely = 0;
		
		// Test the weighting algorithm
		for ($i = 0; $i < $runs; $i++) {
			$server = $config->getWeightedServer();
			if ($server[0] === 'likely') {
				$likely++;
			} else {
				$unlikely++;
			}
		}
		
		$ratio_real = $unlikely_real / $likely_real;
		$ratio = $unlikely / $likely;
		$deviation = abs($ratio_real - $ratio);
		
		$deviation_nice = round($deviation * 100, 2);
		$allowed_deviation_nice = round($allowed_deviation * 100, 2);

		$message = "Weighted server algorithm choose $unlikely:$likely which is $deviation_nice% outside of ideal (must be within $allowed_deviation_nice%)";
		$this->assertLessThan($allowed_deviation, $deviation, $message);
	}
	
}