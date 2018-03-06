<?php
namespace model;

class Mac extends Common
{

	function __construct()
	{
		$this->table = 'dx_mac';
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
		$this->redis_init();
		$adddate = $this->redis->get('mac_adddate_'.$mac);
		if(!$adddate)
		{
			$this->db_init();
			$mac_data = $this->db->find($this->table, "mac='$mac'");
			if(!$mac_data)
			{
				return 0;
			}
			$adddate = date('Ymd', strtotime($mac_data['addtime']));
			$this->redis->set('mac_adddate_'.$mac, $adddate);
		}

		if($adddate == $date)
		{
			return 1;
		}
		return 0;
	}

	function redis_insert($data)
	{
        $check_size = 500;
        $check_timelen = 5;
		$redis = new \Redis();

		$redis->connect("127.0.0.1", 6379);

		$str = json_encode($data);
		$redis->rPush('mac_redis_data', $str);
		$adddate = date('Ymd', strtotime($data['addtime']));
		$redis->set('mac_adddate_'.$data['mac'], $adddate);

		$last_time = $redis->get('mac_redis_data_time');
		$last_time = $last_time?$last_time:0;
		$total_size = $redis->lSize('mac_redis_data');
		$time_now = time();
		$time_length = $time_now-$last_time;
		if($total_size > $check_size || $time_length > $check_timelen)
		{
			$redis->set('mac_redis_data_time', $time_now);
			$this->db_init();
			$this->table = 'dx_mac';
			$data_arr = array();
			$column_arr = array();
			for ($i=0; $i < $total_size; $i++)
			{
				$item_data = $redis->lPop('mac_redis_data');
				$item_arr = array();
				if(!$item_data || empty($item_data))
				{
					continue;
				}
				$item_data_arr = json_decode($item_data, true);
				foreach ($item_data_arr as $key => $o) {
					if($i == 0)
					{
						$column_arr[] = $key;
					}
					$item_arr[] = "'$o'";
				}
				$item_str = implode(',', $item_arr);
				$data_arr[] = "($item_str)";
			}

			$column_str = implode(',', $column_arr);
			$data_str = implode(',', $data_arr);
			if($column_str == '' || $data_str == '')
			{
				return;
			}
			$sql = "INSERT INTO ".$this->table." ($column_str) VALUES $data_str";
			$this->query($sql);
		}
	}
}
?>