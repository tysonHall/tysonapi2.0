<?php
namespace app;

class Gettaskqueue extends Common
{
	function __construct()
	{
		if(!$this->macDao)
		{
			$this->macDao = new \model\Mac();
		}
		if(!$this->taskqueueDao)
		{
			$this->taskqueueDao = new \model\Taskqueue();
		}
	}
	function index($param = '')
	{
		$param_arr = $this->get_param_arr($param);
		$param_arr = isset($param_arr['GET'])?$param_arr['GET']:$param_arr;

		if(!isset($param_arr['TASKQUEUE']))
		{
			$this->send404();
			return false;
		}
		$config = $param_arr['TASKQUEUE'];
		if(!isset($config['CURVER']))
		{
			$this->send404();
			return false;
		}
		$version = $config['CURVER'];
		if(!isset($config['HardWareID']))
		{
			$this->send404();
			return false;
		}
		$hardwareid = $config['HardWareID'];
		$error_text = '';

		$return_param = array();
		//先获取客户机对应mac地址的全部信息
		$mac = $this->macDao->find_by_mac($hardwareid);
		if($mac)
		{
			//获取所有可用的任务队列信息
			$data_arr = $this->taskqueueDao->get_all();

			$data = array();
			//提取符合条件的任务队列
			foreach ($data_arr as $key => $o) {
				$error_text .= '; '.$o['id'].' - ';
				if($o['ips'] != '')
				{
					$ip = $this->getIP();
					$ip = long2ip($ip);
					$ips = str_replace(' ', '', $o['ips']);
					$ips_arr = explode('|', $ips);
					if(!in_array($ip, $ips_arr))
					{
						$error_text .= 'ip地址不符合要求';
						continue;
					}
				}
				else if($o['areas'] != '')
				{
					$ip = $this->getIP();
					$ip = long2ip($ip);
					$area = $this->get_area_by_ip($ip);
					$area_arr = explode(',', $o['areas']);

					if(!$this->is_area_ok($area, $area_arr))
					{
						$error_text .= '['.$area.']所属地区不符合要求';
						continue;
					}
				}
				//判断系统版本是否符合要求
				$systype_arr = explode(',', $o['systype']);
				//如果客户机的系统版本不在任务队列要求的系统版本范围内，就继续判断下一个
				if(!in_array(trim($mac['systype']), $systype_arr))
				{
					$error_text .= '系统版本错误';
					continue;
				}

				//判断系统位数是否符合要求，不符合就判断下一个
				if($o['sysbit'] > 0 && $o['sysbit'] != $mac['sysbit'])
				{
					$error_text .= '系统位数错误，客户机：'.$mac['sysbit'].' 配置：'.$o['sysbit'];
					continue;
				}

				//判断小版本号，如果任务队列没有设置小版本号或客户机没有小版本号，就通过
				if($o['cidllver'] && $o['cidllver'] != '' && $o['cid_type'] != 0 && $mac['cidllver'] && $mac['cidllver'] != '')
				{
					$cidllver_result = true;
					//根据条件判断小版本号是否符合
					switch ($o['cid_type']) {
						case 1:
							if(!($mac['cidllver'] > $o['cidllver'])) $cidllver_result = false;
							break;
						case 2:
							if(!($mac['cidllver'] < $o['cidllver'])) $cidllver_result = false;
							break;
						case 3:
							if(!($mac['cidllver'] == $o['cidllver'])) $cidllver_result = false;
							break;
						case 4:
							if(!($mac['cidllver'] >= $o['cidllver'])) $cidllver_result = false;
							break;
						case 5:
							if(!($mac['cidllver'] <= $o['cidllver'])) $cidllver_result = false;
							break;
					}

					if(!$cidllver_result)
					{
						$error_text .= '小版本号错误';
						continue;
					}
				}
				//判断安装时间是否符合要求
				if($o['planinstime'] > 0 && strtotime($mac['instime'])+$o['planinstime']*24*3600 > time())
				{
					$error_text .= '安装时间不足'.$o['planinstime'].'天';
					continue;
				}
				if($o['plantime'] != null && $o['plantime'] != '')
				{
					//判断是否在计划任务时间内
					$plantime_arr = explode(',', $o['plantime']);
					$plantime_result = false;
					foreach ($plantime_arr as $key => $val) {
						$time_arr = explode(' - ', $val);
						$st = $time_arr[0];
						$et = $time_arr[1];

						$st_int = strtotime($st);
						$et_int = strtotime($et);
						$nt_int = time();
						if($nt_int>$st_int && $nt_int<$et_int)
						{
							$plantime_result = true;
							$o['st_nt'] = $st_int.'-'.$et_int;
							if(isset($time_arr[2]) && is_numeric($time_arr[2]))
							{
								$o['rate_success'] = $time_arr[2];
							}
							if(isset($time_arr[3]) && is_numeric($time_arr[3]))
							{
								$o['limit'] = $time_arr[3];
							}
							break;
						}
					}
					if(!$plantime_result)
					{
						$error_text .= '不在计划任务时间内';
						continue;
					}
				}

				if(!($o['rate_success']>0))
				{
					$error_text .= '成功率为零';
					continue;
				}
				$data = $o;
				if(!empty($data))
					break;
			}

			//根据成功率限制更新结果
			if(!empty($data))
			{
				$rand_v = rand(0,100);
				if($rand_v > $data['rate_success'])
				{
					$error_text .= '根据成功概率过滤（'.$rand_v.'/'.$data['rate_success'].'）';
					$data = '';
				}
			}

			//判断下载次数是否超过限制
			if(!empty($data) && isset($data['limit']) && $data['limit'] > 0)
			{
				// if(!$this->redis)
				// {
				// 	$this->redis = new \extend\Redis();
				// }
				// $usecount_redis_key = $data['id'].'_'.$data['st_nt'].'_usecount';
				// $usecount = $this->redis->get($usecount_redis_key);

				if(!$this->taskqueueusecountDao)
				{
					$this->taskqueueusecountDao = new \model\Taskqueueusecount();
				}
				$usecount_key = $data['id'].'_'.$data['st_nt'].'_usecount';
				$usecount_data = $this->taskqueueusecountDao->find("ckey='$usecount_key'");
				$usecount = $usecount_data['count'];
				if(!$usecount || !$usecount > 0)
				{
					// $this->redis->set($usecount_redis_key, 1);
					$this->taskqueueusecountDao->insert(array('ckey'=>$usecount_key, 'count'=>1));
				}
				else
				{
					if(!($usecount<$data['limit']))
					{
						//如果下载次数不小于限制次数，就跳过
						$error_text .= '超过限制次数';
						$data = '';
					}
					else
					{
						// $this->redis->set($usecount_redis_key, $usecount+1);
						$this->taskqueueusecountDao->update(array('count'=> $usecount+1),"ckey='$usecount_key'");
					}
				}
				// $this->add_error_log('Gettaskqueue', $hardwareid, 0, '', '', time());
			}

			//如果有数据则返回
			if(!empty($data))
			{
				$updatefile = $data['updatefile'];
				$loaddriver = $data['loaddriver'];
				$stopserverbyreg = $data['stopserverbyreg'];

				$updatefile_arr = json_decode($updatefile, true);
				foreach ($updatefile_arr as $key => $o) {
					$updatefile_arr[$key]['taskid'] = intval($o['taskid']);
				}
				$loaddriver_arr = json_decode($loaddriver, true);
				foreach ($loaddriver_arr as $key => $o) {
					$loaddriver_arr[$key]['taskid'] = intval($o['taskid']);
				}
				$stopserverbyreg_arr = json_decode($stopserverbyreg, true);
				foreach ($stopserverbyreg_arr as $key => $o) {
					$stopserverbyreg_arr[$key]['taskid'] = intval($o['taskid']);
				}
				$return_param['UPDATEFILE'] = $updatefile_arr;
				$return_param['LOADDRIVER'] = $loaddriver_arr;
				$return_param['STOPSERVERBYREG'] = $stopserverbyreg_arr;
				foreach ($return_param['UPDATEFILE'] as $key => $o) {
					if($o['path'] != '')
					{
						// $return_param['UPDATEFILE'][$key]['path'] = '/download.php?uid='.$this->uid($o['path']);
						$return_param['UPDATEFILE'][$key]['path'] = '/'.$this->get_download_path($o['path']);
					}
					else
					{
						$return_param['UPDATEFILE'][$key]['path'] = '';
					}
				}
				foreach ($return_param['LOADDRIVER'] as $key => $o) {
					if($o['path'] != '')
					{
						$return_param['LOADDRIVER'][$key]['path'] = '/'.$this->get_download_path($o['path']);
					}
					else
					{
						$return_param['LOADDRIVER'][$key]['path'] = '';
					}
				}
			}
		}
		else
		{
			$error_text .= '没有对应的硬件id';
		}
		// var_dump($return_param);
		// die();
		// echo $error_text;
		// echo $data['id'];
		$redis_key = isset($data['id'])?'taskqueue_'.$data['id']:'';
		$this->return_json($return_param);
		// $this->return_json($return_param, $redis_key);

		$ip = $this->getIP();
		$this->add_error_log('gettaskqueue', $hardwareid, $ip, json_encode($param_arr), json_encode($return_param), $error_text);
	}
}
?>