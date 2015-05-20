<?php
namespace ScientiaMobile\WurflCloud\HttpClient;
use ScientiaMobile\WurflCloud\Exception;
/**
 * Copyright (c) 2015 ScientiaMobile, Inc.
 *
 * Please refer to the COPYING.txt file distributed with the software for licensing information.
 * 
 * @package ScientiaMobile\WurflCloud
 * @subpackage HttpClient
 */

class HttpException extends Exception {
	protected $default_message;
	protected $http_status;
	public function __construct($message, $http_status, $previous=null) {
		$this->http_status = $http_status;
		parent::__construct($message, null, $previous);
	}
	public function getHttpStatus() {
		return $this->http_status;
	}
	public function getHttpStatusCode() {
		if ($this->http_status === null) {
			return 0;
		}
		list($protocol, $http_status_code, $reason_code) = explode(' ', $this->http_status, 3);
		return (int)$http_status_code;
	}
}