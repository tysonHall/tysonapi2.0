<?php
namespace app;

class Getdllinject extends Common
{
	function __construct()
	{
		if(!$this->macDao)
		{
			$this->macDao = new \model\Mac();
		}
		if(!$this->dllinjectDao)
		{
			$this->dllinjectDao = new \model\Dllinject();
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
		$ids = isset($param_arr['id'])?$param_arr['id']:array();
		$param_arr = $param_arr['GET'];
		if(!isset($param_arr['DLLINJECT']))
		{
			$this->send404();
			return false;
		}
		$inject = $param_arr['DLLINJECT'];
		if(!isset($inject['CURVER']))
		{
			$this->send404();
			return false;
		}
		$version = $inject['CURVER'];
		if(!isset($inject['HardWareID']))
		{
			$this->send404();
			return false;
		}
		$hardwareid = $inject['HardWareID'];
		if(!isset($inject['GROUP']))
		{
			$this->send404();
			return false;
		}
		$group = $inject['GROUP'];
		$error_text = '';

		$hardwareid = $inject['HardWareID'];
		$mac = $this->macDao->find_by_mac($hardwareid);
		$condition = " sysbit=".$mac['sysbit']." OR sysbit=0";
		$datas = $this->dllinjectDao->select_except_ids($ids, $condition);

		$return_param = array('dll'=>array());
		if(!empty($datas))
		{
			$ip = long2ip($this->getIP());
			foreach ($datas as $key => $o) {
				//如果配置信息中有IP限制
				if($o['ips'] && $o['ips'] != '')
				{
					if($this->is_ip_ok($o['ips'], $ip))
					{
						$return_param['dll'][] = array(
							'id' => (int)$o['id'],
							'host' => (string)$o['host'],
							'path' => (string)'/'.$this->get_download_path($o['path']),
							'port' => (string)$o['port'],
							'md5' => (string)$o['md5'],
							'key' => (string)$o['key'],
							'Architecture' => (string)$o['architecture'],
							'condition' => (string)$o['condition'],
							);
					}
				}
				else
				{
					$return_param['dll'][] = array(
						'id' => (int)$o['id'],
						'host' => (string)$o['host'],
						'path' => (string)'/'.$this->get_download_path($o['path']),
						'port' => (string)$o['port'],
						'md5' => (string)$o['md5'],
						'key' => (string)$o['key'],
						'Architecture' => (string)$o['architecture'],
						'condition' => (string)$o['condition'],
						);
				}
			}
		}

		$this->return_json($return_param);
	}
}
?>