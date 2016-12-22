<?php
namespace Home\Controller;
use Think\Controller;

/**
* 文件管理器
* @author lrf
* @modify 2016/12/22
*/
class FileManagerController extends BaseController
{
	/*
	 * 获取相关模块的目录或者文件
	 * @param type  模块类型
	 * @param number  当前页数
	 * @param num 	每页显示数量
	 */
	Public function GetFolder(){
		$type = I('post.type');
		switch ($type) {
			case 'log':
				$url='./Public/data/';
			  break;
			case 'product':
				$url='./Public/Product/';
			  break;
			case 'batch':
				$url='./Public/Template/';
			  break;
			case 'upc':
				$url='./Public/upc/';
			  break;
			case 'pictures':
				$url='./Pictures/';
			  break;
		}
		$number=I('post.number');
		$num=I('post.num');
		$num = (empty($num)) ? 15 : $num ;
		$numbers = (empty($number)) ? 0 : ($number-1)*$num ;
		$res=$this->readfile($url);
   		if($res){
   			$arr['status']=100;
   			$arr['countPage']=ceil(count($res)/$num);
   			$arr['pageNow']=(empty($number)) ? 1 : $number;
   			$arr['count']=count($res);
    		$arr['value']=array_slice($res,$numbers,$num);
   		}else{
   			$arr['status']=101;
   			$arr['msg']="没有数据！";
   		}
   		$this->response($arr,'json');	
	}

	/*
	 * 获取目录下的所有文件
	 * @param type  模块类型
	 * @param number  当前页数
	 * @param num 	每页显示数量
	 */
	Public function GetFlie(){
		$url=I('post.url');
		$number=I('post.number');
		$num=I('post.num');
		$num = (empty($num)) ? 15 : $num ;
		$numbers = (empty($number)) ? 0 : ($number-1)*$num ;
		$res=$this->readfile($url);
		if($res){
   			$arr['status']=100;
   			$arr['countPage']=ceil(count($res)/$num);
   			$arr['pageNow']=(empty($number)) ? 1 : $number;
   			$arr['count']=count($res);
    		$arr['value']=array_slice($res,$numbers,$num);
   		}else{
   			$arr['status']=101;
   			$arr['msg']="没有数据！";
   		}
   		$this->response($arr,'json');
	}

	//获取文件方法
	protected function readfile($url){
		$dh = opendir($url);
   		$i=0;
   		while ($file = readdir($dh)) {
   		    if($file != "." && $file != "..") {
   		        $fullpath = $url.$file;
   		        if(!is_dir($fullpath)) {
   		        	$arr[$i]['type']='file';
   		        	$arr[$i]['name']=$file;
   		            $arr[$i]['url']=$url.$file;
   		            $i++;
   		        }else{
   		        	$arr[$i]['type']='catalog';
   		        	$arr[$i]['name']=$file;
   		        	$arr[$i]['url']=$fullpath;
   		        	$i++;
   		        }
   		    }
   		}
   		closedir($dh);
   		return($arr);
	}

	/*
	 * 删除文件
	 * @param url 文件地址
	 */
	Public function DeleteFile(){
		$url=I('post.url');
		if(is_array($url)){//传回的url参数是否为数组
			foreach ($url as $key => $value) {
				if(is_dir($value)){
					$arr['status']=101;
					$arr['msg']="目录不能删除！";
					$this->response($arr,'json');
				}else{
					if(file_exists()){
						unlink($value);
					}else{
						$arr['status']=102;
						$arr['msg']="文件不存在！";
						$this->response($arr,'json');
					}
					
				}
			}
		}else{
			if(is_dir($url)){
				$arr['status']=101;
				$arr['msg']="目录不能删除！";
				$this->response($arr,'json');
			}else{
				if(file_exists()){
					unlink($value);
				}else{
					$arr['status']=102;
					$arr['msg']="文件不存在！";
					$this->response($arr,'json');
				}
			}
		}
		$arr['status']=100;
		$arr['success']=$s;
		$arr['fail']=$f;
		$this->response($arr,'json');
	}
}