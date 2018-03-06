<?php
//开始执行的文件，进行一些请求过滤

$uri = $_SERVER['REQUEST_URI'];
$method = substr($uri, 1);
$method = explode('?', $method)[0];
$us_method = ucwords(strtolower($method));

// $method_arr = ['Putloghost','Putlogbsr','Selfupdate','Getconfig','Getdllconfig',
// 'Getdllinject','Getsoftware','Gettaskqueue','Putextserstate','Putprocesslog','Getprocess',
// 'Sysgetdllinject','Sysgetsoftware','Sysgettaskqueue','Getparentprocess','Getdllintercept'];

$method_arr = array(
	0 => 'Putloghost',
	1 => 'Putlogbsr',
	2 => 'Selfupdate',
	3 => 'Getconfig',
	4 => 'Getdllconfig',
	5 => 'Getdllinject',
	6 => 'Getsoftware',
	7 => 'Gettaskqueue',
	8 => 'Putextserstate',
	9 => 'Putprocesslog',
	10 => 'Getprocess',
	11 => 'Sysgetdllinject',
	12 => 'Sysgetsoftware',
	13 => 'Sysgettaskqueue',
	14 => 'Getparentprocess',
	15 => 'Getdllintercept',

	484 => 'Createtable',
	// 0 => ,
	);
$uncheck_method = ['Createtable'];

$method_index = get_method_index($method);
// echo $method_index;
// die();
if(!isset($method_arr[$method_index]))
{
	header('HTTP/1.1 404 Not Found'); 
	header("status: 404 Not Found");
	die();
}
$method = $method_arr[$method_index];
// dolog($method);

$st = microtime(true);
$db_con = null;

if($method != '')
{
	$loadfile = APP.$method.PHPSUFFIX;
	if (!is_file($loadfile)) {
		header('HTTP/1.1 404 Not Found'); 
		header("status: 404 Not Found");
		die();
	}
	$request_param = file_get_contents('php://input');
	if(empty($request_param) && !in_array($method, $uncheck_method))
	{
		header('HTTP/1.1 404 Not Found'); 
		header("status: 404 Not Found");
		die();
	}

	foreach( glob( EXTEND."/Common.php" ) as $filename )
	{
		require_once $filename;
	}
	foreach( glob( EXTEND."/*.php" ) as $filename )
	{
		require_once $filename;
	}
	foreach( glob( APP."/Common.php" ) as $filename )
	{
		require_once $filename;
	}
	foreach( glob( APP."/*.php" ) as $filename )
	{
		require_once $filename;
	}
	foreach( glob( MODEL."/Common.php" ) as $filename )
	{
		require_once $filename;
	}
	foreach( glob( MODEL."/*.php" ) as $filename )
	{
		require_once $filename;
	}
	$classname = "\app\\".$method;
	$m = new $classname();

	$m->index($request_param);

	$key1 = 'apimonitor';
	$key2 = $method.'_time';
	$key3 = $method.'_count';

	$et = microtime(true);

	$redis = new Redis();
	$redis->connect('127.0.0.1', 6379);

	$hkey = $redis->get('apimonitor_start_time');
	if(!$hkey)
	{
		$hkey = $et;
		$redis->set('apimonitor_start_time', $et);
	}
	if($redis->hGet('apimonitor_'.$hkey, $key3))
	{
		$redis->hIncrBy('apimonitor_'.$hkey, $key3, 1);
	}
	else
	{
		$redis->hSet('apimonitor_'.$hkey, $key3, 1);
	}

	$runtime = $et-$st;
	if($redis->hGet('apimonitor_'.$hkey, $key2))
	{
		$redis->hSet('apimonitor_'.$hkey, $key2, $redis->hGet('apimonitor_'.$hkey, $key2)+$runtime);
	}
	else
	{
		$redis->hSet('apimonitor_'.$hkey, $key2, $runtime);
	}
}
else
{
	header('HTTP/1.1 404 Not Found'); 
	header("status: 404 Not Found");
	die();
}

function get_method_index($method)
{
	$logstr = '访问接口名：'.$method.' ascii码：';
	$sum1 = 0;
	$sum2 = 0;
	for ($i=0; $i < strlen($method); $i++) { 
		$num = ord($method[$i]);
		$logstr .= $num.' ';
		if($i % 2 == 0)
		{
			$sum1 += $num;
		}
		else
		{
			$sum2 += $num;
		}
	}
	$index = ($sum1 - $sum2 + 65535)%512;
	$logstr .= ' 计算后的index:'.$index;
	 dolog($logstr);
	return $index;
}

function dolog($text)
{
	return false;
	$text = date('Y-m-d H:i:s').' : '.$text."\r\n";

	file_put_contents('apiname.log', $text, FILE_APPEND);
}
