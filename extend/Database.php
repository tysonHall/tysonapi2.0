<?php
namespace extend;

class Database
{
	public $db_type = '';
	public $db_hostname = '';
	public $db_database = '';
	public $db_username = '';
	public $db_password = '';
	public $db_hostport = '';

	function __construct()
	{
		//数据库类初始化
		$dataconf = require 'conf/db.php';
		$this->db_type = $dataconf['type'];
		$this->db_hostname = $dataconf['hostname'];
		$this->db_database = $dataconf['database'];
		$this->db_username = $dataconf['username'];
		$this->db_password = $dataconf['password'];
		$this->db_hostport = $dataconf['hostport'];
	}

	function connect()
	{
		global $db_con;
		if($db_con)
		{
			return $db_con;
		}
		$dsn = $this->db_type.":host=".$this->db_hostname.";dbname=".$this->db_database;
		try{
			$db_con = new \PDO($dsn, $this->db_username, $this->db_password);
			// $con = new \PDO($dsn, $this->db_username, $this->db_password, array(\PDO::ATTR_PERSISTENT => true));
		}catch(\PDOException $e)
		{
			header('HTTP/1.1 404 Not Found'); 
			header("status: 404 Not Found");
			echo "Error: " . $e->getMessage() . "<br/>";
			die();
		}
		
		if(!$db_con)
		{
			die('Could not connect');
		}
		return $db_con;
	}

	function run_sql($sql)
	{
		$con = $this->connect();
		// mysql_select_db($this->db_database);
		// $query = mysql_query($sql,$con);
		$query = $con->query($sql);

		if(!$query)
		{
			die('Error : '.$sql);
		}

		return $query;
	}

	function query($sql)
	{
		$query = $this->run_sql($sql);
		if($query === TRUE)
		{
			return TRUE;
		}
		$result_arr = $query->fetchAll();
		// while ($o = mysql_fetch_assoc($query)) {
		// 	$result_arr[] = $o;
		// }

		return $result_arr;
	}

	function insert($table = '', $data)
	{
		if($table == '' || !is_array($data))
		{
			return FALSE;
		}

		$columns_arr = array();
		$values_arr = array();
		foreach ($data as $key => $o) {
			$columns_arr[] = "$key";
			$values_arr[] = "'$o'";
		}
		$columns = implode(',', $columns_arr);
		$values = implode(',', $values_arr);

		$sql = "INSERT INTO `$table` ($columns) VALUES ($values)";

		$query = $this->run_sql($sql);
		if($query)
		{
			// return mysql_insert_id();
			return $query;
		}
	}

	function update($table = '', $update_data, $condition)
	{
		if($table == '' || !is_array($update_data) || !is_string($condition))
		{
			return FALSE;
		}

		$columns_arr = array();
		foreach ($update_data as $key => $o) {
			$columns_arr[] = "$key='$o'";
		}

		$columns = implode(',', $columns_arr);

		$where = '';
		if($condition != '')
		{
			$where = " WHERE $condition";
		}
		$sql = "UPDATE `$table` SET $columns $where";

		$query = $this->run_sql($sql);
		if($query)
		{
			return TRUE;
		}
	}

	function find($table = '', $condition)
	{
		if($table == '' || !is_string($condition))
		{
			return FALSE;
		}

		$sql = "SELECT * FROM `$table` WHERE $condition LIMIT 1";
		$query = $this->run_sql($sql);
		// return mysql_fetch_assoc($query);
		return $query->fetch();
	}

	function select($table = '', $condition)
	{
		if($table == '' || !is_string($condition))
		{
			return FALSE;
		}

		$sql = "SELECT * FROM `$table` WHERE $condition";
		$query = $this->run_sql($sql);
		$result_arr = $query->fetchAll();
		// while ($o = mysql_fetch_assoc($query)) {
		// 	$result_arr[] = $o;
		// }
		foreach ($query as $key => $o) {
			$result_arr[] = $o;
		}

		return $result_arr;
	}
}

?>