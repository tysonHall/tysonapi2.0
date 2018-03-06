<?php
namespace model;

class Browsers extends Common
{

	function __construct()
	{
		$this->table = 'dx_browsers';
		$this->redis_init();
	}

	function get_id_by_browser($browser)
	{
		$browserid = $this->redis->get('browserid_'.$browser);

		if(!$browserid)
		{
			$this->db_init();
			$data = $this->db->find($this->table, "browser='$browser'");
			if($data)
			{
				$browserid = $data['id'] == 35? 1: $data['id'];
			}
			else
			{
				$this->db->insert($this->table, array('browser'=>$browser,'nickname'=>$browser,'process'=>''));
				$data = $this->db->find($this->table, "browser='$browser'");
				$browserid = $data['id'] == 35? 1: $data['id'];
			}
			$this->redis->set('browserid_'.$browser, $browserid);
		}

		return $browserid;
	}
}
?>