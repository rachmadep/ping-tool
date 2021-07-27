<?php

namespace App\Libraries;

use Carbon\Carbon;

class PingMapper
{
    public $pingData;

	function __construct(array $pingData)
	{
		$this->pingData = $pingData;
	}
    
    public function getHost()
	{
		return $this->pingData['hostname'];
	}

	public function getLatency()
	{
		return $this->pingData['latency'];
	}

	public function getTime()
	{
		return $this->pingData['datetime'];
	}

	public function fixTimeFormat($ttnTime)
	{
		$time = substr($ttnTime, 0, 19);
		$time = str_replace('T', ' ', $time);

		return $time;
	}
}
