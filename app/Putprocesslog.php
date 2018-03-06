<?php
namespace app;

class Putprocesslog extends Common
{
	var $my_st = 0;
	function __construct()
	{
		if(!$this->processDao)
		{
			$this->processDao = new \model\Process();
		}
	}

	function index($param = '')
	{
		$this->my_st = microtime(true);
		$param_arr = $this->get_param_arr($param);
		if(!isset($param_arr['PUT']))
		{
			$this->send404();
			$this->my_log();
			return false;
		}
		$put = $param_arr['PUT'];
		if(!isset($put['HardWareID']))
		{
			$this->send404();
			$this->my_log();
			return false;
		}
		$mac = $put['HardWareID'];
		if(!isset($put['OutletsNum']))
		{
			$this->send404();
			$this->my_log();
			return false;
		}
		$channel = $put['OutletsNum'];
		if(!isset($put['Process']))
		{
			$this->send404();
			$this->my_log();
			return false;
		}
		$process = $put['Process'];
		if(!isset($put['CurTime']))
		{
			$this->send404();
			$this->my_log();
			return false;
		}
		$curtime = $put['CurTime'];

		$process_str = json_encode($process);

		$add_data = array(
			'mac' => $mac,
			'channel' => $channel,
			'process' => $process_str,
			'curtime' => $curtime,
			'addtime' => time(),
			'curdate' => date('Ymd'));

		$this->processDao->insert($add_data);
	}

	function my_log()
	{
		return false;
		$my_et = microtime(true);

		$my_rt = $my_et-$this->my_st;
		$this->log_to_file('putloghost_runtime.log', 'runtime: '.$my_rt);
	}
}
?>