<?php
namespace model;

class Taskqueueusecount extends Common
{

	function __construct()
	{
		$this->table = 'dx_taskqueue_usecount';
		// $this->redis_init();
	}
}
?>