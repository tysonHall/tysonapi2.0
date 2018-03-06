<?php
namespace app;

class Putloghostid extends Common
{
	function index($param = '')
	{
		if(strlen($param) != 12)
		{
			// $this->add_error_log('putloghostid', $this->json_param, get_client_ip(), json_encode($this->json_param), '', '长度错误');
			exit;
		}
		$data = array(
			'mac' => $param,
			'addtime' => time()
			);

		$pcloghostidDao = new \model\Pcloghostid();
		$id = $pcloghostidDao->insert($data);
	}
}
?>