<?php
namespace app;

class Mactoredis extends Common
{
	function __construct()
	{
		if(!$this->macDao)
		{
			$this->macDao = new \model\Mac();
		}
	}
	function index($param = '')
	{
		$addtime = isset($_GET['addtime'])?$_GET['addtime']:0;
		if($addtime == 0 || $addtime == '')
		{
			echo 'error';
			die();
		}
		$count = isset($_GET['count'])?$_GET['count']:1000;

		$list = $this->macDao->select("addtime<'$addtime' order by addtime desc limit $count");

		if(!$this->redis)
		{
			$this->redis = new \extend\Redis();
		}
		$total_count = 0;
		$lastaddtime = '';
		foreach ($list as $key => $o) {
			$data = $this->redis->get('mac_adddate_'.$o['mac']);
			if($data)
			{
				$lastaddtime = $o['addtime'];
				continue;
			}
			$adddate = date('Ymd', strtotime($o['addtime']));
			$this->redis->set('mac_adddate_'.$o['mac'], $adddate);
			$total_count++;
		}
		echo $total_count;

		echo "<script>window.location.href='http://ss1.dh012.com/mactoredis?addtime=$lastaddtime&count=$count'</script>";
	}
}
?>