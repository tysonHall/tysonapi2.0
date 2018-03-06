<?php
namespace app;

class Putextserstate extends Common
{
	var $my_st = 0;
	function __construct()
	{
		if(!$this->extserstateDao)
		{
			$this->extserstateDao = new \model\Extserstate();
		}
		if(!$this->macDao)
		{
			$this->macDao = new \model\Mac();
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
		if(!isset($put['EXTERNALSERVERSTATE']))
		{
			$this->send404();
			$this->my_log();
			return false;
		}
		$data = $put['EXTERNALSERVERSTATE'];
		if(!empty($data))
		{
			$add_data = array();
			$add_data['hardwareid'] = $data['HardWareID'];
			$add_data['softui'] = $data['SoftUI']?'true':'false';
			$add_data['softserver'] = $data['SoftServer']?'true':'false';
			$add_data['softdriver'] = $data['SoftDriver']?'true':'false';
			$add_data['curtime'] = strtotime($data['CurTime']);

			//如果采集时间在当前时间的七天之前，就将采集时间改为当前时间
			$time_now = time();
			if($add_data['curtime']+7*24*3600 < $time_now || $add_data['curtime'] > $time_now)
			{
				//只修改到当前的分钟数，防止一分钟内的重复提交
				$add_data['curtime'] = floor($time_now/60)*60 + $time_now%60;
			}
			$add_data['curdate'] = date('Ymd', $add_data['curtime']);
			// $add_data['curhour'] = date('Hi', ceil($add_data['curtime']/1800)*1800);
			$add_data['ip'] = $this->getIP();
			$add_data['addtime'] = time();
			$add_data['newflag'] = $this->macDao->is_new($add_data['hardwareid'], $add_data['curdate']);

			$id = $this->extserstateDao->insert($add_data);
		}
		$this->my_log();
	}

	function my_log()
	{
		$my_et = microtime(true);

		$my_rt = $my_et-$this->my_st;
		$this->log_to_file('putextserstate_runtime.log', 'runtime: '.$my_rt);
	}
}
?>