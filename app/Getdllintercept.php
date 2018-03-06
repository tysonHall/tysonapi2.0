<?php
namespace app;

class Getdllintercept extends Common
{
	function __construct()
	{
		if(!$this->macDao)
		{
			$this->macDao = new \model\Mac();
		}
		if(!$this->dllinterceptDao)
		{
			$this->dllinterceptDao = new \model\Dllintercept();
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
		if(!isset($param_arr['TACKLEDDLS']))
		{
			$this->send404();
			return false;
		}
		$inject = $param_arr['TACKLEDDLS'];
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

		$mac = $this->macDao->find_by_mac($hardwareid);
		if(!$mac)
		{
			$this->send404();
			return false;
		}
		$data = $this->dllinterceptDao->select("channel='".$mac['outletsnum']."'");
		$result = array('dll' => '[]', 'process' => '[]');
		foreach ($data as $key => $o) {
			$systype_arr = explode(',', $o['systype']);
			$mac_systype = trim($mac['systype']);

			if(!in_array($mac_systype, $systype_arr))
			{
				$error_text .= '系统版本不符合';
				unset($data[$key]);
				continue;
			}
			$result = $data[$key];
			break;
		}
		$return_param = array(
			'dll' => json_decode($result['dll'], true),
			'process' => json_decode($result['process'], true));

		$this->return_json($return_param);
	}
}
?>