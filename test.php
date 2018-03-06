<?php
$redis = new Redis();
$redis->connect('127.0.0.1', 6379);
echo 'dbsize:'.$redis->dbsize().'<br>';
if(isset($_GET['key']))
{
	$key = $_GET['key'];
	if(strstr($key, '*'))
	{
		$keys = $redis->keys($key);
		if(empty($keys))
		{
			echo 'no data';
		}
		foreach ($keys as $x => $o) {
			echo '<b>'.$o.'</b>:  ';
			echo $redis->get($o);
			echo '<br/>';
		}
	}
	else
	{
		echo $key.':';
		var_dump($redis->get($key));
	}
}


?>