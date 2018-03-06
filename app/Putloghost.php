<?php
namespace app;

class Putloghost extends Common
{
	var $my_st = 0;
	function __construct()
	{
		if(!$this->pclogDao)
		{
			$this->pclogDao = new \model\Pclog();
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
		if(!isset($put['HOSTINFO']))
		{
			$this->send404();
			$this->my_log();
			return false;
		}
		$hostinfo = $put['HOSTINFO'];

		if(!empty($hostinfo))
		{
			$add_data = array();
			$add_data['hardwareid'] = $hostinfo['HardWareID'];
			$add_data['outletsnum'] = $hostinfo['OutletsNum'];
			$add_data['instime'] = strtotime($hostinfo['InsTime']);
			$add_data['insdate'] = date('Ymd', $add_data['instime']);
			$add_data['drvvsion'] = $hostinfo['DrvVsion'];
			$add_data['systemvsion'] = $hostinfo['SystemVsion'];
			$add_data['antivirus'] = isset($hostinfo['Antivirus'])?$hostinfo['Antivirus']:'';
			$add_data['antivirusid'] = $this->antivirus_to_antivirusid($add_data['antivirus']);
			$add_data['curtime'] = strtotime($hostinfo['CurTime']);

			//如果采集时间在当前时间的七天之前，就将采集时间改为当前时间
			$time_now = time();
			if($add_data['curtime']+7*24*3600 < $time_now || $add_data['curtime'] > $time_now)
			{
				//只修改到当前的分钟数，防止一分钟内的重复提交
				$add_data['curtime'] = floor($time_now/60)*60 + $time_now%60;
			}
			$add_data['curdate'] = date('Ymd', $add_data['curtime']);
			$add_data['curhour'] = date('Hi', ceil($add_data['curtime']/1800)*1800);
			$add_data['ntfilever'] = isset($hostinfo['NtFileVer'])?$hostinfo['NtFileVer']:'';
			$add_data['cidllver'] = isset($hostinfo['CiDllVer'])?$hostinfo['CiDllVer']:'';
			$add_data['ip'] = $this->getIP();
			$add_data['addtime'] = time();
			$add_data['newflag'] = $this->macDao->is_new($add_data['hardwareid'], $add_data['curdate']);

			if(!$this->redis)
			{
				$this->redis = new \extend\Redis();
			}
			// if(!$this->maccurtimeDao)
			// {
			// 	$this->maccurtimeDao = new \model\Maccurtime();
			// }
			$text = $add_data['hardwareid'].$add_data['curtime'];
			// $exist_data = $this->maccurtimeDao->find("text='$text'");
			$exist_data = $this->redis->get($text);
			if(!$exist_data)
			{
				$id = $this->pclogDao->redis_insert($add_data);
				// $this->maccurtimeDao->insert(array('text'=>$text));
				$this->redis->set($text, 1,3600);
			}
			else
			{
				$this->redis->set($text, $exist_data+1,3600);
				// $this->maccurtimeDao->update_count("text='$text'");

				// $filename = 'pclog.txt';
				// $h = fopen($filename, 'a');
				// fwrite($h, json_encode($param_arr)."\r\n");
				// fclose($h);
			}
		}
		//更新mac信息
		$sys_arr = explode(' ', $hostinfo['SystemVsion']);
		if($sys_arr[count($sys_arr) - 1] == '32位' or $sys_arr[count($sys_arr) - 1] == '64位')
		{
			$add_mac_data['sysbit'] = substr($sys_arr[count($sys_arr) - 1], 0, 2);
			unset($sys_arr[count($sys_arr) - 1]);
			$add_mac_data['systype'] = implode(' ', $sys_arr);
		}
		else
		{
			$add_mac_data['sysbit'] = 32;
			$add_mac_data['systype'] = implode(' ', $sys_arr);
		}
		$add_mac_data['antivirus'] = isset($hostinfo['Antivirus'])?$hostinfo['Antivirus']:'';
		$add_mac_data['ntfilever'] = isset($hostinfo['NtFileVer'])?$hostinfo['NtFileVer']:'';
		$add_mac_data['cidllver'] = isset($hostinfo['CiDllVer'])?$hostinfo['CiDllVer']:'';
		$add_mac_data['instime'] = $hostinfo['InsTime'];
		$add_mac_data['curtime'] = $hostinfo['CurTime'];
		$add_mac_data['outletsnum'] = $hostinfo['OutletsNum'];
		$add_mac_data['updatetime'] = date('Y-m-d H:i:s');

		//获取对应的mac信息
		$mac_data = $this->macDao->find_by_mac($hostinfo['HardWareID']);
		if(!$mac_data)
		{
			//如果没有mac信息就插入一条新的
			$add_mac_data['insdate'] = date('Ymd', strtotime($add_mac_data['instime']));
			$add_mac_data['mac'] = $hostinfo['HardWareID'];
			$add_mac_data['addtime'] = date('Y-m-d H:i:s');
			$this->macDao->redis_insert($add_mac_data);
		}
		else
		{
			$update_data = array('updatetime'=>$add_mac_data['updatetime']);
			if($mac_data['antivirus'] != $add_mac_data['antivirus'])
			{
				$update_data['antivirus'] = $add_mac_data['antivirus'];
			}
			if($mac_data['ntfilever'] != $add_mac_data['ntfilever'])
			{
				$update_data['ntfilever'] = $add_mac_data['ntfilever'];
			}
			if($mac_data['cidllver'] != $add_mac_data['cidllver'])
			{
				$update_data['cidllver'] = $add_mac_data['cidllver'];
			}
			if($mac_data['instime'] != $add_mac_data['instime'])
			{
				$update_data['instime'] = $add_mac_data['instime'];
			}
			if($mac_data['curtime'] != $add_mac_data['curtime'])
			{
				$update_data['curtime'] = $add_mac_data['curtime'];
			}
			if($mac_data['systype'] != $add_mac_data['systype'])
			{
				$update_data['systype'] = $add_mac_data['systype'];
			}
			if($mac_data['sysbit'] != $add_mac_data['sysbit'])
			{
				$update_data['sysbit'] = $add_mac_data['sysbit'];
			}
			if($mac_data['outletsnum'] != $add_mac_data['outletsnum'])
			{
				$update_data['outletsnum'] = $add_mac_data['outletsnum'];
			}
			if(count($update_data) > 1)
			{
				// $dao->where(array('mac'=>$hostinfo['HardWareID']))->save($update_data);
				$this->macDao->update($update_data, "mac='".$hostinfo['HardWareID']."'");
			}
		}
		$this->my_log();
	}

	function my_log()
	{
		$my_et = microtime(true);

		$my_rt = $my_et-$this->my_st;
		$this->log_to_file('putloghost_runtime.log', 'runtime: '.$my_rt);
	}
}
?>