<?php
namespace model;

class Maccurtime extends Common
{

	function __construct()
	{
		$this->table = 'dx_maccurtime_'.date('Ymd');
		$this->db_init();
		$this->db->query("CREATE TABLE if not exists `".$this->table."`  (
			`id` INT(11) NOT NULL AUTO_INCREMENT,
			`text` CHAR(22) NULL DEFAULT NULL,
			`count` INT(11) NULL DEFAULT '1',
			PRIMARY KEY (`id`),
			INDEX `text` (`text`)
		)
		COLLATE='utf8_general_ci'
		ENGINE=InnoDB
		;");
	}

	function update_count($where)
	{
		$this->db->query("update ".$this->table." set count=count+1 where $where");
	}
}
?>