<?php
namespace model;

class Errorlog extends Common
{

	function __construct()
	{
		$this->table = 'dx_error_log';
	}

	function insert($data)
	{
		$this->db_init();
		$id = $this->db->insert($this->table, $data);

		return $id;
	}
}
?>