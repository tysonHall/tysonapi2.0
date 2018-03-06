<?php
namespace app;

class Createtable extends Common
{
    function index()
    {
    	$dir = EXTEND.'/createtable_sql';

    	$createtype = isset($_GET['type'])?$_GET['type']:0;
    	if($createtype == 1)
    	{
	    	$tablename_tail = date("Ym");
			$start_date = date('Ym01');
			$end_date = date('Ym01', strtotime("+1 month"));
		}
		else
		{
	    	$tablename_tail = date("Ym",strtotime("+1 month"));
			$start_date = date('Ym01', strtotime("+1 month"));
			$end_date = date('Ym01', strtotime("+2 month"));
		}

		$filelist = opendir($dir);
		while ($file = readdir($filelist)) {
			if(is_file($dir.'/'.$file) && substr($file, -4)=='.sql')
			{
				$sql = file_get_contents($dir.'/'.$file);

				$sql = str_replace('--tablenamedate--', $tablename_tail, $sql);

				$sql .= " PARTITION BY RANGE  COLUMNS(curdate)";

				$sql .= "(";
				$sql .= implode(',', $this->get_partition_arr($start_date, $end_date));
				$sql .= ");";

				$this->db_init();
				$r = $this->db->query($sql);
				echo $file.' success<br>';
			}
		}
    }

    function get_partition_arr($start_date, $end_date)
    {
		$partition_arr = array();
		for ($i=$start_date; $i < $end_date; $i=date('Ymd', strtotime("$i +1 day"))) {
			//PARTITION p20170601 VALUES LESS THAN (20170602) ENGINE = InnoDB,
			$next_day = date('Ymd', strtotime("$i +1 day"));
			$partition_arr[] = "PARTITION p$i VALUES LESS THAN ($next_day) ENGINE = InnoDB";
		}
		return $partition_arr;
	}
}
?>