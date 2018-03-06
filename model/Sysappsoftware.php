<?php
namespace model;

class Sysappsoftware extends Common
{

	function __construct()
	{
		$this->table = 'dx_sysappsoftware';
	}

	function select_except_ids($ids = array(), $condition = '')
	{
		$this->db_init();
		$where = "state=0";
		if(!empty($ids))
		{
			$ids_str = implode(',', $ids);
			$where .= " AND id NOT IN ($ids_str)";
		}
		if($condition != '')
		{
			$condition = trim($condition);
			$condition = ltrim($condition, 'AND');
			$condition = ltrim($condition, 'and');
			$where .= " AND (".$condition.")";
		}
		$data = $this->db->select($this->table, $where);
		return $data;
	}
}
?>