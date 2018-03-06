<?php
namespace app;

class Getdllconfig extends Common
{
	function __construct()
	{
		if(!$this->macDao)
		{
			$this->macDao = new \model\Mac();
		}
		if(!$this->dllconfigDao)
		{
			$this->dllconfigDao = new \model\Dllconfig();
		}
	}
	function index($param = '')
	{
		$param_arr = $this->get_param_arr($param);
		if(!isset($param_arr['GET']))
		{
			//如果上报内容不符合要求，就返回404
			$this->send404();
			return false;
		}
		$param_arr = $param_arr['GET'];
		if(!isset($param_arr['DLLCONFIG']))
		{
			$this->send404();
			return false;
		}
		$config = $param_arr['DLLCONFIG'];
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
		if(!isset($config['GROUP']))
		{
			$this->send404();
			return false;
		}
		$group = $config['GROUP'];
		$error_text = '';

		//获取本硬件ID的信息
		// $mac = $this->db->find("dx_mac", "mac='$hardwareid'");
		$mac = $this->macDao->find_by_mac($hardwareid);
		$data = array();
		if($mac)
		{
			//获取所有该渠道号下的配置信息
			// $datas = $this->db->select("dx_softconfig","state=0 AND channel='".$mac['outletsnum']."'");
			$datas = $this->dllconfigDao->select_by_channel($mac['outletsnum']);

			if(!empty($datas))
			{
				$ip = long2ip($this->getIP());
				foreach ($datas as $key => $o) {
					//如果配置信息中有IP限制
					if($o['ips'] && $o['ips'] != '')
					{
						if($this->is_ip_ok($o['ips'], $ip))
						{
							$data = $o;
						}
					}
					else
					{
						$data = $o;
					}
				}
			}
		}
		
		//如果有数据则返回
		if($data)
		{
			$urlreplace = $data['urlreplace'];
			$defaultsearch = $data['defaultsearch'];
			$sign = $data['sign'];
		}
		$return_param = array();
		if(!empty($data))
		{
			$return_param['UrlReplace'] = json_decode($urlreplace);
			$return_param['DefaultSearch'] = json_decode($defaultsearch);
			$return_param['sign'] = json_decode($sign);
		}
		// $redis_key = isset($data['id'])?'softconfig_'.$data['id']:'';

		$this->return_json($return_param);
	}
}
?>