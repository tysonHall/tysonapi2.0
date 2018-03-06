<?php
//开始执行的文件，进行一些请求过滤

$uri = $_SERVER['REQUEST_URI'];
$method = substr($uri, 1);
$method = explode('?', $method)[0];
$us_method = ucwords(strtolower($method));

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
