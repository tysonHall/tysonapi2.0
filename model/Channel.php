<?php
namespace model;

class Channel extends Common
{

	function __construct()
	{
		$this->table = 'dx_channel';
		// $this->redis_init();
	}

	function find_by_channel($channel)
	{
		// $data = $this->redis->get('ryapi_channel_'.$channel);

		// if(!$data)
		// {
			$this->db_init();
			$data = $this->db->find($this->table, "channel='$channel'");
		// 	$this->redis->set('ryapi_channel_'.$channel, $data);
		// }

		return $data;
	}
}
?>