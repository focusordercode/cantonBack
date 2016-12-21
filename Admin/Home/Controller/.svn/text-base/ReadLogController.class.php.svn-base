<?php
namespace Home\Controller;
use Think\Controller\RestController;
header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Methods:POST,GET');
header('Access-Control-Allow-Credentials:true'); 
header("Content-Type: application/json;charset=utf-8");
/**
* 读取日志控制器
*/
class ReadLogController extends RestController{
	/*
	 * 读取日志
	 */
	public function ReadLogs(){
		$date=I('post.date');
		$dt=substr(str_replace("-","_",$date),2);
		$fp = fopen('E:\www\canton\Admin\Runtime\Logs\Home\2016\07\16_07_15.log', 'r'); //文件
		$result=array();
		
		while (!feof($fp)) {
			 //for($j=1;$j<=1000;$j++) {         //读取下面的1000行并存储到数组中
			  //$logarray[] = stream_get_line($fp, 200, "＼n");
			        // break;
			  // }
			$line = fgets($fp, 1024);  
			if(strpos($line,'SQL',0)>0 || strpos($line,'SELECT',0)>0 ) {   //如果存在你要查找的字符，则保存到数组中 
			    $result[] = $line;
			  } 
			}
			print_r($result);
	    $this->response($result,'json');
	}
}