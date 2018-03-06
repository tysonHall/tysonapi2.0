<?php
namespace app;

class Putlogbsr extends Common
{
	function __construct()
	{
		if(!$this->brlogDao)
		{
			$this->brlogDao = new \model\Brlog();
		}
		if(!$this->macDao)
		{
			$this->macDao = new \model\Mac();
		}
		if(!$this->mutiplebrlogDao)
		{
			$this->mutiplebrlogDao = new \model\Mutiplebrlog();
		}
	}
	function index($param = '')
	{
		$putlogbsr_st = microtime(true);
		$log_str = date('Y-m-d H:i:s')." : ";
		$param_arr = $this->get_param_arr($param);
		$this->log_decrypt($putlogbsr_st);
		$log_str .= " decript:".(microtime(true)-$putlogbsr_st);
		if(!$param_arr)
		{
			return false;
		}
		if(!isset($param_arr['PUT']))
		{
			$this->send404();
			return false;
		}
		$put = $param_arr['PUT'];
		if(!isset($put['LOG']))
		{
			$this->send404();
			return false;
		}
		$log = $put['LOG'];
		if(!isset($log['USEBSR']))
		{
			$this->send404();
			return false;
		}
		$usebsr = $log['USEBSR'];
		$error_text = '';

		if(!empty($usebsr))
		{
			$add_data = array();
			$add_data['hardwareid'] = $usebsr['HardWareID'];
			$add_data['outletsnum'] = $usebsr['OutletsNum'];
			$add_data['instime'] = strtotime($usebsr['InsTime']);
			$add_data['drvvsion'] = $usebsr['DrvVsion'];
			$add_data['systemvsion'] = $usebsr['SystemVsion'];
			$add_data['antivirus'] = isset($usebsr['Antivirus'])?$usebsr['Antivirus']:'';
			$add_data['browser'] = $usebsr['Browser'];
			$add_data['browserid'] = $this->get_browserid($usebsr['Browser']);
			$add_data['sethomepage'] = $usebsr['SetHomePage'];
			$add_data['realhomepage'] = $usebsr['RealHomePage'];
			$add_data['curtime'] = strtotime($usebsr['CurTime']);
			
			$add_data['realhomepage'] = strlen($add_data['realhomepage'])>40?substr($add_data['realhomepage'], 0, 40):$add_data['realhomepage'];
			//如果采集时间在当前时间的七天之前，就将采集时间改为当前时间
			$time_now = time();
			if($add_data['curtime']+7*24*3600 < $time_now || $add_data['curtime'] > $time_now)
			{
				//只修改到当前的分钟数，防止一分钟内的重复提交
				$add_data['curtime'] = floor($time_now/60)*60 + $time_now%60;
			}
			$add_data['curdate'] = date('Ymd', $add_data['curtime']);
			$add_data['ip'] = $this->getIP();
			$add_data['addtime'] = time();
			$add_data['newflag'] = $this->macDao->is_new($add_data['hardwareid'], $add_data['curdate']);
			// if($add_data['hardwareid'] == '00E04C4B2951')
			$log_str .= " checknew:".(microtime(true)-$putlogbsr_st);
				$id = $this->brlogDao->redis_insert($add_data);
			$log_str .= " insert_br:".(microtime(true)-$putlogbsr_st);
			// else
			// 	$id = $this->brlogDao->insert($add_data);
			if(!$id)
			{
				// $this->add_error_log('putlogbsr', $add_data['hardwareid'], $add_data['ip'], json_encode($param_arr), '', '保存失败');
			}

			// $mutiple_st = microtime(true);
			$this->add_mutiple_log($add_data);
			$log_str .= " add_mutiple:".(microtime(true)-$putlogbsr_st);
			// $mutiple_et = microtime(true);
			// $mutiple_rt = $mutiple_et-$mutiple_st;
			// file_put_contents('mutiple_time.log', date('Y-m-d H:i:s').' : '.$mutiple_rt."\r\n", FILE_APPEND);

			//更新mac信息
			$sys_arr = explode(' ', $usebsr['SystemVsion']);
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
			$add_mac_data['browser'] = $usebsr['Browser'];
			$add_mac_data['antivirus'] = isset($usebsr['Antivirus'])?$usebsr['Antivirus']:'';
			$add_mac_data['instime'] = $usebsr['InsTime'];
			$add_mac_data['curtime'] = $usebsr['CurTime'];
			$add_mac_data['outletsnum'] = $usebsr['OutletsNum'];
			$add_mac_data['updatetime'] = date('Y-m-d H:i:s');

			//获取对应硬件ID的主机信息
			$macdata = $this->macDao->find_by_mac($usebsr['HardWareID']);
			if($macdata)
			{
				$update_data = array('updatetime'=>$add_mac_data['updatetime']);
				//判断每个字段是否有变化
				if($macdata['systype'] != $add_mac_data['systype'])
				{
					$update_data['systype'] = $add_mac_data['systype'];
				}
				if($macdata['sysbit'] != $add_mac_data['sysbit'])
				{
					$update_data['sysbit'] = $add_mac_data['sysbit'];
				}
				if($macdata['outletsnum']!=$add_mac_data['outletsnum'])
				{
					$update_data['outletsnum'] = $add_mac_data['outletsnum'];
				}
				if($macdata['browser']!=$add_mac_data['browser'])
				{
					$update_data['browser'] = $add_mac_data['browser'];
				}
				if(count($update_data) > 1)
				{
					//如果有字段变化，就更新数据
					// $dao->where(array('mac'=>$usebsr['HardWareID']))->save($update_data);
					$this->macDao->update($update_data, " mac='".$usebsr['HardWareID']."'");
				}
			}
			else
			{
				//没有主机信息，就插入一条新的数据
				$add_mac_data['insdate'] = date('Ymd', strtotime($add_mac_data['instime']));
				$add_mac_data['mac'] = $usebsr['HardWareID'];
				$add_mac_data['addtime'] = date('Y-m-d H:i:s');
				$this->macDao->insert($add_mac_data);
			}

			$log_str .= " update_mac:".(microtime(true)-$putlogbsr_st);
		}

		// file_put_contents('putlogbsr_runtime.log', $log_str . "\r\n", FILE_APPEND);
	}

	function log_decrypt($putlogbsr_st)
	{
		$key2 = 'putlogbsr_decrypt_time';
		$key3 = 'putlogbsr_decrypt_count';

		$putlogbsr_et = microtime(true);

		$redis = new \Redis();
		$redis->connect('127.0.0.1', 6379);

		$hkey = $redis->get('apimonitor_start_time');
		if(!$hkey)
		{
			$hkey = $putlogbsr_et;
			$redis->set('apimonitor_start_time', $putlogbsr_et);
		}
		if($redis->hGet('apimonitor_'.$hkey, $key3))
		{
			$redis->hIncrBy('apimonitor_'.$hkey, $key3, 1);
		}
		else
		{
			$redis->hSet('apimonitor_'.$hkey, $key3, 1);
		}

		$runtime = $putlogbsr_et-$putlogbsr_st;
		if($redis->hGet('apimonitor_'.$hkey, $key2))
		{
			$redis->hSet('apimonitor_'.$hkey, $key2, $redis->hGet('apimonitor_'.$hkey, $key2)+$runtime);
		}
		else
		{
			$redis->hSet('apimonitor_'.$hkey, $key2, $runtime);
		}
	}

	function add_mutiple_log($data)
	{
		$hardwareid = $data['hardwareid'];
		$outletsnum = $data['outletsnum'];
		$drvvsion = $data['drvvsion'];
		$browser = $data['browser'];
		$browserid = $data['browserid'];
		$curdate = $data['curdate'];
		
		$exist_data = $this->mutiplebrlogDao->find("hardwareid='$hardwareid' and curdate=$curdate");

		$log_str = 'mutiple:';
		if($exist_data)
		{
			$browser_arr = explode('_', $exist_data['browsers']);
			if(!in_array($browser, $browser_arr))
			{
				$browser_arr[] = $browser;
			}
			else
			{
				return;
			}

			$browserid_arr = explode('_', $exist_data['browserids']);
			if(!in_array($browserid, $browserid_arr))
			{
				$browserid_arr[] = $browserid;
			}
			sort($browserid_arr);
			$update_data = array(
				'browsers' => is_array($browser_arr)?implode('_', $browser_arr):'',
				'browserids' => is_array($browserid_arr)?implode('_', $browserid_arr):'',
				);
			$log_str .= "update";
			$this->mutiplebrlogDao->update($update_data,"hardwareid='$hardwareid' and curdate=$curdate", $curdate);
		}
		else
		{
			$add_data = array(
				'hardwareid' => $hardwareid,
				'outletsnum' => $outletsnum,
				'drvvsion' => $drvvsion,
				'browsers' => $browser,
				'browserids' => $browserid,
				'curdate' => $curdate,
				);
			$log_str .= "insert------";
			$this->mutiplebrlogDao->redis_insert($add_data);
		}
		// file_put_contents('mutiple.log', $log_str."\r\n", FILE_APPEND);
	}
}
?>