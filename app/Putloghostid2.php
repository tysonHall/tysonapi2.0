<?php
namespace app;

class Putloghostid2 extends Common
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

		$pcloghostid2Dao = new \model\Pcloghostid2();
		$id = $pcloghostid2Dao->insert($data);
	}
}
?>