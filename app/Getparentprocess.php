<?php
namespace app;

class Getparentprocess extends Common
{
    function __construct()
    {
        if(!$this->parentprocessDao)
        {
            $this->parentprocessDao = new \model\Parentprocess();
        }
    }

    function index($param = '')
    {
        $param_arr = $this->get_param_arr($param);
        if(isset($param_arr['GET']))
        {
            //如果上报内容不符合要求，就返回404
            // $this->send404();
            $param_arr = $param_arr['GET'];
        }
        if(!isset($param_arr['BSRSTARTPROCESS']))
        {
            $this->send404();
        }
		$data = $this->parentprocessDao->select("1=1");
		$result = array(
            'Process' => []);

        if($data)
        {
            $process = json_decode($data[0]['process'],true);
            foreach ($process as $key => $o) {
                $result['Process'][] = $o;
            }
        }
    	$this->return_json($result);
    }
}
