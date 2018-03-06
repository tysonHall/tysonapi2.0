<?php
namespace model;

class Area extends Common
{

	function __construct()
	{
		$this->table = 'dx_area';
	}

	function select($condition)
	{
		// $this->redis_init();
		// $r = $this->redis->get('ryapi_country');
		// if(!$r)
		// {
			$this->db_init();
			$r = $this->db->select($this->table, $condition);
		// 	$this->redis->set('ryapi_country', $r);
		// }
		return $r;
	}
}
?>