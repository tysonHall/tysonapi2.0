<?php
namespace model;

class Systaskqueue extends Common
{

	function __construct()
	{
		$this->table = 'dx_systaskqueue';
		// $this->redis_init();
	}

	function get_all()
	{
		// $data = $this->redis->get('ryapi_taskqueue');

		// if(!$data)
		// {
			$this->db_init();
			$data = $this->db->select($this->table, "systype<>'' AND state=0");
		// 	$this->redis->set('ryapi_taskqueue', $data);
		// }

		return $data;
	}
}
?>