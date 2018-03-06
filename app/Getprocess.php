<?php
namespace app;

class Getprocess extends Common
{
    function __construct()
    {
        if(!$this->processconfigDao)
        {
            $this->processconfigDao = new \model\Processconfig();
            $this->dldasDao = new \model\Dldas();
        }
    }

    function index()
    {
    	// if($prostate['state'] == 0)
    	// {
    		$process = $this->processconfigDao->select("state=0");
    		$result = array(
    			'IsRun' => "1");
            $special = $this->dldasDao->find(" 1=1 order by id desc");

            $result['special'] = array(
                'md5'=>$special['fmd5'],
                'plist' => json_decode($special['productjson'], true),
                );
    		foreach ($process as $key => $o) {
    			$file = json_decode($o['file'], true);
    			$new_file = array();
    			foreach ($file['drv'] as $key1 => $p) {
    				$new_file['drv'][$key1+1] = $p;
    			}
    			foreach ($file['lnk'] as $key1 => $p) {
    				$new_file['lnk'][$key1+1] = $p;
    			}
    			$reg = json_decode($o['reg'], true);
    			$new_reg = array();
    			foreach ($reg['drv'] as $key1 => $p) {
    				$new_reg['drv'][$key1+1] = $p;
    			}
    			foreach ($reg['soft'] as $key1 => $p) {
    				$new_reg['soft'][$key1+1] = $p;
    			}
    			$o_arr = array(
    				'pname' => $o['pname'],
					'kname' => $o['kname'],
                    'install' => $o['install'],
					'file' => array(
						array('drv' => $new_file['drv']),
						array('lnk' => $new_file['lnk'])),
					'reg' => array(
						array('drv' => $new_reg['drv']),
						array('soft' => $new_reg['soft']))
					);
    			$result['result'][] = $o_arr;
    		}
    	// }
    	// else
    	// {
	    // 	$result = array(
	    // 		'IsRun' => "0");
    	// }
    	$this->return_json($result);
    }
}
