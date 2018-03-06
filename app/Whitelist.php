<?php
namespace app;

class Whitelist extends Common
{
	function __construct()
	{
		if(!$this->macDao)
		{
			$this->macDao = new \model\Mac();
		}
		if(!$this->whitelDao)
		{
			$this->whitelDao = new \model\Whitelist();
		}
		if(!$this->urlrDao)
		{
			$this->urlrDao = new \model\Urlreplace();
		}
	}
	function index($param = '')
	{
		$param_arr = $this->get_param_arr($param);
		if(!isset($param_arr['GET']))
		{
			$this->send404();
			return false;
		}
		$param_arr = $param_arr['GET'];
		if(!isset($param_arr['URLREPLACE']))
		{
			$this->send404();
			return false;
		}
		$config = $param_arr['URLREPLACE'];
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
		$hardeareid = $config['HardWareID'];
		$error_text = '';

		$mac = $this->macDao->find_by_mac($hardeareid);
		if($mac)
		{
			// $data = $softconfig->where("state=0 AND version='$version' AND channel='".$mac['outletsnum']."'")->find();
			$whitelist_data = $this->whitelDao->get_all();
			$urlr_data = $this->urlrDao->get_all();
		}
		
		$whitelist_param = array();
		if(isset($whitelist_data))
		{
			foreach ($whitelist_data as $key => $o) {
				$whitelist_param[] = array('URL'=>$o['url']);
			}
		}
		$urlr_param = array();
		if(isset($urlr_data))
		{
			foreach ($urlr_data as $key => $o) {
				$urlr_param[] = array('EXPRESSION'=>$o['expression'],'URL'=>$o['url']);
			}
		}
		$return_param = array();
		$return_param['WHITELIST'] = $whitelist_param;
		$return_param['URLREPLACE'] = $urlr_param;
		
		if(empty($return_param))
		{
			// $this->add_error_log('getconfig', $hardeareid, get_client_ip(), json_encode($param_arr), json_encode($return_param), $error_text);
		}
		// var_dump($return_param);
		// die();
		$this->return_json($return_param);
	}
}
?>