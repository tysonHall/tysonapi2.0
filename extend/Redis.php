<?php
namespace extend;

class Redis
{
	public $redis = null;

	function __construct()
	{
		$this->init();
	}

	function init()
	{
		if(!$this->redis)
		{
			$this->redis = new \Redis();
			$this->redis->connect('127.0.0.1',6379);
		}
	}

	function get($key)
	{
		$result = $this->redis->get($key);
		if(substr($result, 0, 11) == 'redisarray_')
		{
			$result = substr($result, 10);
			$result = json_decode($result, true);
		}
		return $result;
	}

	function set($key, $data, $time = 0)
	{
		if(is_array($data))
		{
			$data = 'redisarray_'.json_encode($data);
		}
		$result = $this->redis->set($key, $data);
		if($time>0)
		{
			$this->redis->expire($key,$time);
		}
	}

	function delete($key)
	{
		$result = $this->redis->delete($key);
	}
}
?>