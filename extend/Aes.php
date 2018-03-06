<?php
namespace extend;

class Aes {
	public static function encrypt($input, $key1, $key2) {
		if(is_array($key1))
			$key1 = Aes::getStrFromHexarr($key1);
		if(is_array($key2))
			$key2 = Aes::getStrFromHexarr($key2);
		$size = 16;
		$totallen = strlen($input);
		$totalcount = ceil($totallen/$size);
		$key11 = substr($key1, 0, 16);
		$key12 = substr($key1, 16, 16);
		$key21 = substr($key2, 0, 16);
		$key22 = substr($key2, 16, 16);
		$inputarr = Aes::get_ord($input);
		$inputarrarr = array();
		$lastchar = $size-$totallen%$size;
		for ($i=0; $i < $totalcount; $i++) { 
			for ($j=0; $j < 16; $j++) { 
				if($totallen > $i*16+$j)
					$inputarrarr[$i][] = $inputarr[$i*16+$j];
				else
					$inputarrarr[$i][] = dechex($lastchar);
			}
		}

		$encryptarr = array();
		$encode = '';
		if($totalcount>1)
		{
			for ($i=$totalcount-1; $i > 0; $i--) {
				if(($i+$totalcount)%2 == 1) //先用key1后用key2
					$str = Aes::encryptb($inputarrarr[$i], $inputarrarr[$i-1], $key11, $key12, $key21, $key22, $size);
				else  //先用key2后用key1
					$str = Aes::encryptb($inputarrarr[$i], $inputarrarr[$i-1], $key21, $key22, $key11, $key12, $size);
				
				$encode = $str.$encode;
			}
		}
		if($totalcount%2 == 0)//最后一次先用key2后用key1
			$encode = Aes::encryptf($inputarrarr[0], $key11, $key12, $key21, $key22, $size).$encode;
		else //最后一次先用key1后用key2
			$encode = Aes::encryptf($inputarrarr[0], $key21, $key22, $key11, $key12, $size).$encode;

		return base64_encode($encode);
	}

	private static function encryptf($d1, $key11, $key12, $key21, $key22, $size)
	{
		$d1str = Aes::getStrFromBytesarr($d1);
		$a = $d1str^$key21;
		$keyarr = Aes::get_ord($key21.$key22);
		$b = Aes::rc4($keyarr,$a);
		$c = $b^$key11;
		$d = Aes::aesencode($c, $key11.$key12, $size);
		return $d;
	}

	private static function encryptb($d2, $d1, $key11, $key12, $key21, $key22, $size)
	{
		$d1str = Aes::getStrFromBytesarr($d1);
		$d2str = Aes::getStrFromBytesarr($d2);
		$a = $d2str^$key11;
		$key13 = $key11.($key12^$d1str);
		$keyarr = Aes::get_ord($key13);
		$b = Aes::rc4($keyarr,$a);
		$c = $b^$key21;
		$key23 = $key21.($key22^$d1str);
		$d = Aes::aesencode($c, $key23, $size);
		return $d;
	}

    private static function getStrFromHexarr($arr)
    {
    	$count = count($arr);
    	$str = '';
    	for ($i=0; $i < $count; $i++) {
    		$b = $arr[$i];
    		$str .= chr($b);
    	}
    	return $str;
    }

    private static function getStrFromBytesarr($arr)
    {
    	$count = count($arr);
    	$str = '';
    	for ($i=0; $i < $count; $i++) { 
    		$b = $arr[$i];
    		$str .= chr(hexdec($b));
    	}
    	return $str;
    }

	private static function aesencode($input, $key, $size)
	{
		$td = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_ECB, '');
		$iv = mcrypt_create_iv (mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
		mcrypt_generic_init($td, $key, $iv);
		$data = mcrypt_generic($td, $input);
		mcrypt_generic_deinit($td);
		mcrypt_module_close($td);
		return $data;
	}
 
	public static function decrypt($str, $key1, $key2) {
		if(is_array($key1))
			$key1 = Aes::getStrFromHexarr($key1);
		if(is_array($key2))
			$key2 = Aes::getStrFromHexarr($key2);
		$size = 16;
		$key11 = substr($key1, 0, 16);
		$key12 = substr($key1, 16, 16);
		$key21 = substr($key2, 0, 16);
		$key22 = substr($key2, 16, 16);
		$str = base64_decode($str);
		$strarr = Aes::get_ord($str);
		$totallen = count($strarr);
		$totalcount = $totallen/16;
		$strarrarr = array();
		for ($i=0; $i < $totalcount; $i++) { 
			for ($j=0; $j < 16; $j++) { 
				$strarrarr[$i][] = $strarr[$i*16+$j];
			}
		}
		$destr = '';
		if($totalcount%2 == 1)//第一个先用key2后用key1
			$destr .= Aes::decryptf($strarrarr[0], $key11, $key12, $key21, $key22, $size);
		else //第一个先用key1后用key2
			$destr .= Aes::decryptf($strarrarr[0], $key21, $key22, $key11, $key12, $size);

		if($totalcount>1)
		{
			$d1 = $destr;
			for ($i=1; $i < $totalcount; $i++) {
				if(($i+$totalcount)%2 == 1)//先用key1后用key2
					$d2 = Aes::decryptb($strarrarr[$i], $d1, $key11, $key12, $key21, $key22, $size);
				else //先用key2后用key1
					$d2 = Aes::decryptb($strarrarr[$i], $d1, $key21, $key22, $key11, $key12, $size);

				if($i == $totalcount-1)
				{
					$lastchar = $d2[15];
					$count_char = ord($lastchar);
					if($count_char < 16)
					{
						$removeable = true;
						for ($j=(16-$count_char); $j < 16; $j++) {
							$char = $d2[$j];
							if($char != $lastchar)
							{
								$removeable = false;
							}
						}
						if($removeable)
							$d2 = substr($d2, 0, (16-$count_char));
					}
				}
				$destr .= $d2;
				$d1 = $d2;
			}
		}
		return $destr;
	}

	private static function decryptf($strarrarr, $key11, $key12, $key21, $key22, $size)
	{
		$str = Aes::getStrFromBytesarr($strarrarr);
		$a = mcrypt_decrypt(MCRYPT_RIJNDAEL_128,$key21.$key22,$str,MCRYPT_MODE_ECB);
		$b = $a^$key21;
		$keyarr = Aes::get_ord($key11.$key12);
		$c = Aes::rc4($keyarr,$b);
		$d = $c^$key11;
		return $d;
	}

	private static function decryptb($strarrarr, $d1, $key11, $key12, $key21, $key22, $size)
	{
		$str = Aes::getStrFromBytesarr($strarrarr);
		$key23 = $key21.($key22^$d1);
		$a = mcrypt_decrypt(MCRYPT_RIJNDAEL_128,$key23,$str,MCRYPT_MODE_ECB);
		$b = $a^$key21;
		$key13 = $key11.($key12^$d1);
		$keyarr = Aes::get_ord($key13);
		$c = Aes::rc4($keyarr,$b);
		$d = $c^$key11;
		return $d;
	}

	private static function get_ord($str)
	{
		$len = strlen($str);
		$arr = array();
		for ($i=0; $i < $len; $i++) { 
			$o = ord($str[$i]);
			$v = dechex($o);

			$arr[] = $v;
		}
		return $arr;
	}

	/**
	 * 
	 * rc4加密解密
	 * @param array $key
	 * @param string $data_str
	 */
	private static function rc4($key, $data_str) 
	{
		foreach ($key as $i => $k) {
			$key[$i] = hexdec($k);
		}
	      for ( $i = 0; $i < strlen($data_str); $i++ ) 
	      {
	         $data[] = ord($data_str{$i});
	      }
	     // prepare key
	      $state = array( 0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,
	                      16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,
	                      32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,
	                      48,49,50,51,52,53,54,55,56,57,58,59,60,61,62,63,
	                      64,65,66,67,68,69,70,71,72,73,74,75,76,77,78,79,
	                      80,81,82,83,84,85,86,87,88,89,90,91,92,93,94,95,
	                      96,97,98,99,100,101,102,103,104,105,106,107,108,109,110,111,
	                      112,113,114,115,116,117,118,119,120,121,122,123,124,125,126,127,
	                      128,129,130,131,132,133,134,135,136,137,138,139,140,141,142,143,
	                      144,145,146,147,148,149,150,151,152,153,154,155,156,157,158,159,
	                      160,161,162,163,164,165,166,167,168,169,170,171,172,173,174,175,
	                      176,177,178,179,180,181,182,183,184,185,186,187,188,189,190,191,
	                      192,193,194,195,196,197,198,199,200,201,202,203,204,205,206,207,
	                      208,209,210,211,212,213,214,215,216,217,218,219,220,221,222,223,
	                      224,225,226,227,228,229,230,231,232,233,234,235,236,237,238,239,
	                      240,241,242,243,244,245,246,247,248,249,250,251,252,253,254,255 );
	      $len = count($key);
	      $index1 = $index2 = 0;
	      for( $counter = 0; $counter < 256; $counter++ ){
	         $index2   = ( $key[$index1] + $state[$counter] + $index2 ) % 256;
	         $tmp = $state[$counter];
	         $state[$counter] = $state[$index2];
	         $state[$index2] = $tmp;
	         $index1 = ($index1 + 1) % $len;
	      }
	      // rc4
	      $len = count($data);
	      $x = $y = 0;
	      for ($counter = 0; $counter < $len; $counter++) {
	         $x = ($x + 1) % 256;
	         $y = ($state[$x] + $y) % 256;
	         $tmp = $state[$x];
	         $state[$x] = $state[$y];
	         $state[$y] = $tmp;
	         $data[$counter] ^= $state[($state[$x] + $state[$y]) % 256];
	      }
	      // convert output back to a string
	      $data_str = "";
	      for ( $i = 0; $i < $len; $i++ ) {
	         $data_str .= chr($data[$i]);
	      }
	      return $data_str;
	 }
}
?>