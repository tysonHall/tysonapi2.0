<?php
namespace model;

class Extserstate extends Common
{

	function insert($data)
	{
		$this->db_init();
		$this->table = 'dx_extserstate_'.date('Ym', $data['curtime']);
		$id = $this->db->insert($this->table, $data);
		return $id;
	}
}
?>