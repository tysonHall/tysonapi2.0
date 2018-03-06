<?php
namespace model;

class Common
{
	public $db = null;
	public $redis = null;
	public $table = '';
	function __construct()
	{

	}

	function redis_init()
	{
		if(!$this->redis)
		{
			$this->redis = new \extend\Redis();
		}
	}
	function db_init()
	{
		if(!$this->db)
		{
			$this->db = new \extend\Database();
		}
	}

	function find($condition)
	{
		$this->db_init();
		$r = $this->db->find($this->table, $condition);
		return $r;
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

	function select($condition)
	{
		$this->db_init();
		$r = $this->db->select($this->table, $condition);
		return $r;
	}

	function get_all()
	{
		$this->db_init();
		$r = $this->db->select($this->table, "1=1");
		return $r;
	}

	function query($sql)
	{
		$this->db_init();
		$r = $this->db->query($sql);
		return $r;
	}
}
?>