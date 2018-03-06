<?php
namespace model;

class Brlog extends Common
{
	function insert($data)
	{
		$this->db_init();
		$this->table = 'dx_brlog_'.date('Ym', $data['curtime']);
		$id = $this->db->insert($this->table, $data);
		return $id;
	}

	function redis_insert($data)
	{
        $check_size = 500;
        $check_timelen = 20;
		$redis = new \Redis();

		$redis->connect("127.0.0.1", 6379);

		$str = json_encode($data);
		$redis->rPush('brlog_redis_data', $str);

		$last_time = $redis->get('brlog_redis_data_time');
		$last_time = $last_time?$last_time:0;
		$total_size = $redis->lSize('brlog_redis_data');
		$time_now = time();
		$time_length = $time_now-$last_time;
		if($total_size > $check_size || $time_length > $check_timelen)
		{
			$redis->set('brlog_redis_data_time', $time_now);
			$this->db_init();
			$this->table = 'dx_brlog_'.date('Ym', $data['curtime']);
			$data_arr = array();
			$column_arr = array();
			for ($i=0; $i < $total_size; $i++)
			{
				$item_data = $redis->lPop('brlog_redis_data');
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
			$sql = "INSERT INTO ".$this->table." ($column_str) VALUES $data_str";
			// echo $sql;
			$this->query($sql);
		}
	}
}
?>