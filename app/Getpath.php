<?php
namespace app;

class Getpath extends Common
{
	function __construct()
	{
		if(!$this->uidpathDao)
		{
			$this->uidpathDao = new \model\Uidpath();
		}
	}

	function index()
	{
		$uid = isset($_GET['uid'])?$_GET['uid']:'';
		if($uid != '')
		{
			$data = $this->uidpathDao->find("uid='$uid' AND state=0");
			if($data)
			{
				echo $data['path'];
				$this->uidpathDao->update(array('state'=>1),"uid='$uid' AND state=0");
				exit;
			}
		}
		// $filename = 'getpathlog.txt';
		// $h = fopen($filename, 'a');
		// fwrite($h, $uid." ".date('Y-m-d H:i:s')."\r\n");
		// fclose($h);
	}
}