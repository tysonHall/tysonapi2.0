<?php
namespace model;

class Process extends Common
{

	function insert($data)
	{
		$this->db_init();
		$this->table = 'dx_process_'.date('Ym', $data['addtime']);
		$id = $this->db->insert($this->table, $data);
		return $id;
	}
}