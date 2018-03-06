<?php
namespace app;

class Selfupdate extends Common
{
	function __construct()
	{
		if(!$this->macDao)
		{
			$this->macDao = new \model\Mac();
		}
		if(!$this->channelDao)
		{
			$this->channelDao = new \model\Channel();
		}
		if(!$this->softDao)
		{
			$this->softDao = new \model\Soft();
		}
	}
	function index($param = '')
	{
		$param_arr = $this->get_param_arr($param);
		if(!isset($param_arr["GET"]["VERSION"]["CURVER"]))
		{
			$this->send404();
			return false;
		}
		$t_version = $param_arr["GET"]["VERSION"]["CURVER"];
//		$version='16.10.8.1';
		if(!isset($param_arr["GET"]["VERSION"]["HardWareID"]))
		{
			$this->send404();
			return false;
		}
		$hardwareid = $param_arr["GET"]["VERSION"]["HardWareID"];
		// if($hardwareid == 'B8975A2AFC5A')
		// {
		// 	$ip = $this->getIP();
		// 	$this->add_error_log('Selfupdate', $hardwareid, $ip, json_encode($param_arr), '', '1');
		// }
		$error_text = '';
		$rate_success = 0;
		if(empty($t_version)){
			$error_text = '未提供版本号';
			$rate_success = 0;
			$detail = '';
		}else{
			//根据mac地址获取用户系统配置信息
			$mac = $this->macDao->find_by_mac($hardwareid);
			if($mac)
			{
				//如果mac对应的渠道号不在数据库内，就设置为0，去匹配对应“其他”渠道的更新文件
				if(!$mac['outletsnum'])
				{
					$mac['outletsnum'] = '0';
				}
				else
				{
					$channel = $this->channelDao->find_by_channel($mac['outletsnum']);
					if(!$channel)
					{
						$mac['outletsnum'] = '0';
					}
				}
				$details = $this->softDao->select("channel='".$mac['outletsnum']."' AND state=0");
				
				$rate_success = 0;
				$detail = '';
				$count_find = 0;
				//在所有的更新文件中匹配符合当前用户配置信息的结果
				foreach ($details as $key => $d) 
				{
					$error_text .= '; '.$d['id'].' - ';
					//如果更新包有设置可更新的IP，就判断当前用户的IP是否可更新
					if(!empty($d) && $d['ips'] && $d['ips']!='' )
					{
						//获取设置的可更新IP
						$ips_arr = explode('|', $d['ips']);
						$ip = $this->getIP();
						$ip = long2ip($ip);
						if(!$ip || !in_array($ip, $ips_arr))
						{
							$error_text .= $d['id'].' IP不被允许';
							continue;
						}
					}
					else if($d['areas'] != '')
					{
						$ip = $this->getIP();
						$ip = long2ip($ip);
						$area = $this->get_area_by_ip($ip);
						$area_arr = explode(',', $d['areas']);

						if(!$this->is_area_ok($area, $area_arr))
						{
							$error_text .= '['.$area.']所属地区不符合要求';
							continue;
						}
					}

					//判断系统版本和位数
					$systype_arr = explode(',', $d['systype']);
					$mac_systype = trim($mac['systype']);

					if(!in_array($mac_systype, $systype_arr))
					{
						$error_text .= '系统版本不符合';
						continue;
					}
					if($d['sysbit'] != 0 && $d['sysbit'] != $mac['sysbit'])
					{
						$error_text .= count($details).' '.$mac['sysbit'].' 系统位数不符合';
						continue;
					}
					//判断版本条件是否符合
					if(!empty($d) && $d['t_version'] != '' && $d['t_version_type'] > 0)
					{
						$t_version_result = true;
						switch ($d['t_version_type']) {
							case 1:
								if(!version_compare($t_version, $d['t_version'], '>'))
								{
									$t_version_result = false;
								}
								break;
							case 2:
								if(!version_compare($t_version, $d['t_version'], '<'))
								{
									$t_version_result = false;
								}
								break;
							case 3:
								if(!version_compare($t_version, $d['t_version'], '=='))
								{
									$t_version_result = false;
								}
								break;
							case 4:
								if(!version_compare($t_version, $d['t_version'], '>='))
								{
									$t_version_result = false;
								}
								break;
							case 5:
								if(!version_compare($t_version, $d['t_version'], '<='))
								{
									$t_version_result = false;
								}
								break;
						}
						if(!$t_version_result)
						{
							$error_text .= '版本条件不符合';
							continue;
						}
					}

					//如果有设置小版本号，而且用户机有小版本号，就进行判断
					if(!empty($d) && $d['cidllver'] && $d['cidllver'] != '' && $mac['cidllver'] && $mac['cidllver'] != '' && $d['cid_type']>0)
					{
						$cidllver_result = true;
						switch ($d['cid_type']) {
							case 1:
								if(!version_compare($mac['cidllver'], $d['cidllver'], '>'))
								{
									$cidllver_result = false;
								}
								break;
							case 2:
								if(!version_compare($mac['cidllver'], $d['cidllver'], '<'))
								{
									$cidllver_result = false;
								}
								break;
							case 3:
								if(!version_compare($mac['cidllver'], $d['cidllver'], '=='))
								{
									$cidllver_result = false;
								}
								break;
							case 4:
								if(!version_compare($mac['cidllver'], $d['cidllver'], '>='))
								{
									$cidllver_result = false;
								}
								break;
							case 5:
								if(!version_compare($mac['cidllver'], $d['cidllver'], '<='))
								{
									$cidllver_result = false;
								}
								break;
						}
						if(!$t_version_result)
						{
							$error_text .= '小版本号不符合';
							continue;
						}
					}
					//如果上面条件都符合
					$detail = $d;
					$rate_success = $d['rate_success'];
					$count_find++;
				}
				//如匹配得到多个符合的结果则返回空值（宁愿不更新也不要错误更新）
				if($count_find > 1)
				{
					$error_text .= '获取到多个符合的结果';
					$detail = '';
				}
			}
			else
			{
				$error_text .= '硬件ID不存在';
				$detail = '';
			}
		}

		//根据成功率限制更新结果
		if(!empty($detail))
		{
			$rand_v = rand(0,100);
			if($rand_v > $rate_success)
			{
				$error_text .= '根据成功概率过滤（'.$rand_v.'/'.$rate_success.'）';
				$detail = '';
			}
		}

		$data = array();
		if(!empty($detail))
		{
			//将下载连接加密
			$data['HOST'] = $detail['host'];
			$data['PATH'] = '/'.$this->get_download_path($detail['path']);
			$data['PORT'] = '80';
			$data['VERSION'] = $detail['n_version'];
			$data['MD5'] = $detail['md5'];
			$data['KEY'] = $detail['key'];
		}
		// if($hardwareid == 'B8975A2AFC5A')
		// {
		// 	$ip = $this->getIP();
		// 	$this->add_error_log('Selfupdate', $hardwareid, $ip, json_encode($param_arr), json_encode($data), '2');
		// }
		$this->return_json($data);
	}
}
?>