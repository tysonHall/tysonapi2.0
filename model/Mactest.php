<?php
namespace model;

class Mactest extends Common
{

	function __construct()
	{
		$this->table = 'dx_mac_test';
	}

	function find_by_mac($mac)
	{
		$this->db_init();
		$data = $this->db->find($this->table, "mac='$mac'");

		return $data;
	}

	function insert($data)
	{
		$this->db_init();
		$id = $this->db->insert($this->table, $data);

		return $id;
	}

	function update($data, $condition)
	{
		$this->db_init();
		$r = $this->db->update($this->table, $data, $condition);

		return $r;
	}

	function is_new($mac, $date)
	{
		// $this->redis_init();
		// $adddate = $this->redis->get('mac_adddate_'.$mac);
		// if(!$adddate)
		// {
			$this->db_init();
			$mac_data = $this->db->find($this->table, "mac='$mac'");
			if(!$mac_data)
			{
				return 0;
			}
			$adddate = date('Ymd', strtotime($mac_data['addtime']));
		// 	$this->redis->set('mac_adddate_'.$mac, $adddate);
		// }

		if($adddate == $date)
		{
			return 1;
		}
		return 0;
	}
}
?>