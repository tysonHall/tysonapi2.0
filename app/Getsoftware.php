<?php
namespace app;

class Getsoftware extends Common
{
	function __construct()
	{
		if(!$this->macDao)
		{
			$this->macDao = new \model\Mac();
		}
		if(!$this->appsoftwareDao)
		{
			$this->appsoftwareDao = new \model\Appsoftware();
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
		if(!isset($param_arr['SOFTWARE']))
		{
			$this->send404();
			return false;
		}
		$software = $param_arr['SOFTWARE'];
		if(!isset($software['CURVER']))
		{
			$this->send404();
			return false;
		}
		$version = $software['CURVER'];
		if(!isset($software['HardWareID']))
		{
			$this->send404();
			return false;
		}
		$hardwareid = $software['HardWareID'];
		if(!isset($software['GROUP']))
		{
			$this->send404();
			return false;
		}
		$group = $software['GROUP'];
		$error_text = '';

		$mac = $this->macDao->find_by_mac($hardwareid);
		$condition = " sysbit=".$mac['sysbit']." OR sysbit=0";
		$datas = $this->appsoftwareDao->select_except_ids($ids, $condition);

		$return_param = array('app'=>array());
		if(!empty($datas))
		{
			$ip = long2ip($this->getIP());
			foreach ($datas as $key => $o) {
				//如果配置信息中有IP限制
				if($o['ips'] && $o['ips'] != '')
				{
					if($this->is_ip_ok($o['ips'], $ip))
					{
						$return_param['app'][] = array(
							'id' => (int)$o['id'],
							'host' => (string)$o['host'],
							'path' => (string)'/'.$this->get_download_path($o['path']),
							'port' => (string)$o['port'],
							'md5' => (string)$o['md5'],
							'key' => (string)$o['key'],
							'filename' => (string)$o['filename'],
							'user' => (int)$o['user'],
							'param' => (string)$o['param'],
							'CmdShow' => (int)$o['cmdshow'],
							);
					}
				}
				else
				{
					$return_param['app'][] = array(
						'id' => (int)$o['id'],
						'host' => (string)$o['host'],
						'path' => (string)'/'.$this->get_download_path($o['path']),
						'port' => (string)$o['port'],
						'md5' => (string)$o['md5'],
						'key' => (string)$o['key'],
						'filename' => (string)$o['filename'],
						'user' => (int)$o['user'],
						'param' => (string)$o['param'],
						'CmdShow' => (int)$o['cmdshow'],
						);
				}
			}
		}

		$this->return_json($return_param);
	}
}
?>