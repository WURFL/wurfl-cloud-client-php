<?php
namespace ScientiaMobile\WurflCloud\HttpClient;

use ScientiaMobile\WurflCloud\Config;

class MockHttpClient extends Fsock {

    public $calls = 0;

    protected $mock_file;

    public function call(Config $config, $request_path) {
        $this->calls++;
        $this->processResponse(file_get_contents($this->mock_file));
    }

    public function mockResponse($file) {
        $this->mock_file = __DIR__.'/../../../resources/'.$file;

        if (!is_readable($this->mock_file)) {
            throw new \Exception("Unable to read mock response file: $this->mock_file");
        }
    }

    public function getRequestHeaders() {
        return $this->request_headers;
    }
}
