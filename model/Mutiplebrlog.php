<?php
namespace model;

class Mutiplebrlog extends Common
{
	function insert($data)
	{
		$this->db_init();
		$this->table = 'dx_mutiplebrlog_'.substr($data['curdate'], 0, 6);
		$id = $this->db->insert($this->table, $data);
		return $id;
	}
	function find($condition, $curdate = '')
	{
		$curdate = $curdate==''?date('Ymd'):$curdate;
		$this->db_init();
		$this->table = 'dx_mutiplebrlog_'.substr($curdate, 0, 6);
		$r = $this->db->find($this->table, $condition);
		return $r;
	}

	function update($data, $condition, $curdate = '')
	{
		$curdate = $curdate==''?date('Ymd'):$curdate;
		$this->db_init();
		$this->table = 'dx_mutiplebrlog_'.substr($curdate, 0, 6);
		$r = $this->db->update($this->table, $data, $condition);
		return $r;
	}
	function redis_insert($data)
	{
        $check_size = 100;
        $check_timelen = 10;
		$redis = new \Redis();

		$redis->connect("127.0.0.1", 6379);

		$str = json_encode($data);
		$redis->rPush('mutiplebrlog_redis_data', $str);

		$last_time = $redis->get('mutiplebrlog_redis_data_time');
		$last_time = $last_time?$last_time:0;
		$total_size = $redis->lSize('mutiplebrlog_redis_data');
		$time_now = time();
		$time_length = $time_now-$last_time;
		if($total_size > $check_size || $time_length > $check_timelen)
		{
			$redis->set('mutiplebrlog_redis_data_time', $time_now);
			$this->db_init();
			$this->table = 'dx_mutiplebrlog_'.substr($data['curdate'], 0, 6);
			$data_arr = array();
			$column_arr = array();
			for ($i=0; $i < $total_size; $i++)
			{
				$item_data = $redis->lPop('mutiplebrlog_redis_data');
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