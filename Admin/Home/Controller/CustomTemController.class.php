<?php
namespace Home\Controller;
use Think\Controller;
/**
* 定制模板控制器
*/
class CustomTemController extends BaseController
{
	/**
     * 创建模板
    */
	public function establishTem(){
		$category=I('post.catego');
		$cnname=I('post.cnname');
		$enname=I('post.enname');
		$order=I('post.order');
		$data=array();
		$arr=array();
		$num=count($cnname);
		for($i=0;$i<$num;$i++){
			$data[$i]['cn_name']=$cnname[$i];
			$data[$i]['en_name']=$enname[$i];
			$data[$i]['orders']=$order[$i];
			$data[$i]['category']=$category;
		}
		$res=\Think\Product\CustomTem::EstablishTem($data);
		if($res==1){
            $arr['status']=100;
        }else{
            $arr['status']=101;
        }
        $this->response($arr,'json');
	}

	/**
     * 形成模板
     */
	public function formTem(){
		$category=I('get.catego');
		session_start();
		$_SESSION['TemCategory']=$category;
		$arr=array();
		$res=\Think\Product\CustomTem::GetTem($category);
		if($res){
            $arr['status']=100;
            $arr['value']=$res;
        }else{
            $arr['status']=101;
        }
        $this->response($arr,'json');
	}

	/**
     * 删除模板
     */
	public function delTemplate(){
		$data=array();
		$category=I('post.catego');
		$res=\Think\Product\CustomTem::delTem($category);
		if($res==1){
			$data['status']=100;
		}else{
			$data['status']=101;
		}
		$this->response($data,'json');
	}
    
    /**
     * 形成excle表格并下载
     */
    public function domExcle(){
       $res=\Think\Product\CustomTem::GetTem("Apparel");
       for($i=0;$i<count($res);$i++){
          $headArr[]=$res[$i]['en_name'];
        }
        $filename="amazon";
        $data=\Think\Product\Product::GetInfo(1,"car");
        getExcel($filename,$headArr,$data);
    }
} 