<?php
namespace Home\Controller;
use Think\Controller;
/**
* 读取日志控制器
*/
class ReadLogController extends BaseController
{
	/*
	 * 读取日志
	 */
	public function ReadLogs(){
		$date=I('post.date');
		$dt=substr(str_replace("-","_",$date),2);
		$fp = fopen('E:\www\canton\Admin\Runtime\Logs\Home\2016\07\16_07_15.log', 'r'); //文件
		$result=array();
		
		while (!feof($fp)) {
			$line = fgets($fp, 1024);  
			if(strpos($line,'SQL',0)>0 || strpos($line,'SELECT',0)>0 ) {   //如果存在你要查找的字符，则保存到数组中 
			    $result[] = $line;
			  } 
			}
			print_r($result);
	    $this->response($result,'json');
	}
}