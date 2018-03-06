<?php
namespace model;

class Pclog extends Common
{

	function insert($data)
	{
		$this->db_init();
		$this->table = 'dx_pclog_'.date('Ym', $data['curtime']);
		$id = $this->db->insert($this->table, $data);
		return $id;
	}

	function get_total_count_by_time($st, $et)
	{
		$this->db_init();
		//根据查询日期锁定表
		$this->table = 'dx_pclog_'.date('Ym', $st);
		$sdate = date('Ymd', $st);
		$ehour = intval(date('Hi', $et))==0?2400:intval(date('Hi', $et));
		$result = $this->db->query("SELECT COUNT(distinct(hardwareid)) AS tp_count FROM `".$this->table."` a WHERE a.curdate=$sdate AND a.curhour <= $ehour");
		// echo "SELECT COUNT(distinct(hardwareid)) AS tp_count FROM `dx_".$this->table_name."` a WHERE a.curdate=$sdate AND a.curhour <= $ehour <br>";
		return $result[0]['tp_count'];
	}

	function get_new_count_by_time($st, $et)
	{
		$this->db_init();
		//根据查询日期锁定表
		$this->table = 'dx_pclog_'.date('Ym', $st);
		$sdate = date('Ymd', $st);
		$ehour = intval(date('Hi', $et))==0?2400:intval(date('Hi', $et));
		$result = $this->db->query("SELECT COUNT(distinct(hardwareid)) AS tp_count FROM `".$this->table."` a WHERE a.curdate=$sdate AND a.curhour <= $ehour AND a.newflag=1");
		return $result[0]['tp_count'];
	}

	function redis_insert($data)
	{
        $check_size = 500;
        $check_timelen = 5;
		$redis = new \Redis();

		$redis->connect("127.0.0.1", 6379);

		$str = json_encode($data);
		$redis->rPush('pclog_redis_data', $str);

		$last_time = $redis->get('pclog_redis_data_time');
		$last_time = $last_time?$last_time:0;
		$total_size = $redis->lSize('pclog_redis_data');
		$time_now = time();
		$time_length = $time_now-$last_time;
		if($total_size > $check_size || $time_length > $check_timelen)
		{
			$redis->set('pclog_redis_data_time', $time_now);
			$this->db_init();
			$this->table = 'dx_pclog_'.date('Ym', $data['curtime']);
			$data_arr = array();
			$column_arr = array();
			for ($i=0; $i < $total_size; $i++)
			{
				$item_data = $redis->lPop('pclog_redis_data');
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
			$this->query($sql);
		}
	}
}
?>