<?php
namespace model;

class Softconfig extends Common
{
	function __construct()
	{
		$this->table = 'dx_softconfig';
		$this->redis_init();
	}

	function select_by_channel($channel)
	{
		// $data = $this->redis->get('ryapi_softconfig_'.$channel);

		// if(!$data)
		// {
			$this->db_init();
			$data = $this->db->select($this->table, "channel='$channel' AND state=0");
		// 	$this->redis->set('ryapi_softconfig_'.$channel, $data);
		// }

		return $data;
	}
}
?>