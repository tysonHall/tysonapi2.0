<?php
namespace model;

class Dllconfig extends Common
{

	function __construct()
	{
		$this->table = 'dx_dllconfig';
	}

	function select_by_channel($channel)
	{
		$this->db_init();
		$data = $this->db->select($this->table, "channel='$channel' AND state=0");
		return $data;
	}
}
?>