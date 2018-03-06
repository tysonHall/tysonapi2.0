<?php
namespace app;

class Getconfig extends Common
{
	function __construct()
	{
		if(!$this->macDao)
		{
			$this->macDao = new \model\Mac();
		}
		if(!$this->softconfigDao)
		{
			$this->softconfigDao = new \model\Softconfig();
		}
	}
	function index($param = '')
	{
		$param_arr = $this->get_param_arr($param);
		if(isset($param_arr['GET']))
		{
			//如果上报内容不符合要求，就返回404
			// $this->send404();
			$param_arr = $param_arr['GET'];
		}
		if(!isset($param_arr['CONFIG']))
		{
			$this->send404();
			return false;
		}
		$config = $param_arr['CONFIG'];
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

		//获取本硬件ID的信息
		// $mac = $this->db->find("dx_mac", "mac='$hardwareid'");
		$mac = $this->macDao->find_by_mac($hardwareid);
		$data = array();
		if($mac)
		{
			//获取所有该渠道号下的配置信息
			// $datas = $this->db->select("dx_softconfig","state=0 AND channel='".$mac['outletsnum']."'");
			$datas = $this->softconfigDao->select_by_channel($mac['outletsnum']);

			if(!empty($datas))
			{
				$ip = $this->getIP();
				$ip = long2ip($ip);
				foreach ($datas as $key => $o) {
					//如果配置信息中有IP限制
					if($o['ips'] && $o['ips'] != '')
					{
						if($this->is_ip_ok($o['ips'], $ip))
						{
							$data = $o;
						}
					}
					//如果配置信息中没有限制IP，但有地区限制
					else if($o['areas'] && $o['areas'] != '')
					{
						$area = $this->get_area_by_ip($ip);
						$area_arr = explode(',', $o['areas']);
						if($this->is_area_ok($area, $area_arr))
						{
							$data = $o;
						}
					}
					else
					{
						$data = $o;
					}
					if(!empty($data))
					{
						break;
					}
				}
			}
		}
		
		//如果有数据则返回
		if($data)
		{
			$sethomepage = $data['sethomepage'];
			$reportbsr = $data['reportbsr'];
			$desktopshortcut = $data['desktopshortcut'];
			$iefavorites = $data['iefavorites'];
			$nosethomepagepro = $data['nosethomepagepro'];
			$dlltackled = $data['dlltackled'];
			$dlltackled1 = $data['dlltackled1'];
			$bsrreport = $data['bsrreport']?true:false;
			$processreport = $data['processreport']?true:false;
			$clientreport = $data['clientreport']?true:false;
			$dde = $data['dde']?true:false;
			// $nochangeparent = $data['nochangeparent']==1?"TRUE":"FALSE";
		}
		$return_param = array();
		if(!empty($data))
		{
			$return_param['SETHOMEPAGE'] = json_decode($sethomepage);
			$return_param['REPORTBSR'] = json_decode($reportbsr);
			$return_param['DESKTOPSHORTCUT'] = json_decode($desktopshortcut);
			$return_param['IEFAVORITES'] = json_decode($iefavorites);
			$return_param['NOSETHOMEPAGEPRO'] = json_decode($nosethomepagepro);
			$return_param['DLLTACKLED'] = json_decode($dlltackled);
			$return_param['TACKLEDDLL1'] = json_decode($dlltackled1);
			// $return_param['NOCHANGEPARENT'] = $nochangeparent;
			$return_param['BSRREPORT'] = $bsrreport;
			$return_param['PROCESSREPORT'] = $processreport;
			$return_param['CLIENTREPORT'] = $clientreport;
			$return_param['DDE'] = $dde;
		}
		$redis_key = isset($data['id'])?'softconfig_'.$data['id']:'';
		// $redis_key = '';
		// $ip = $this->getIP();
		// $this->add_error_log('getconfig', $hardwareid, $ip, json_encode($param_arr), json_encode($return_param), '');
		// echo json_encode($return_param);
		$this->return_json($return_param,$redis_key);
	}
}
?>