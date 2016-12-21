<?php
namespace Home\Controller;
use Think\Controller\RestController;
header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Methods:POST,GET');
header('Access-Control-Allow-Credentials:true'); 
header("Content-Type: application/json;charset=utf-8");
/**
* 类目管理控制器
*/
class AppCodeController extends RestController{
    
    /**
     * 查询所有模块
     */
	public function getAppCode(){
		$res=\Think\Product\AppCode::GetAppCode();
		if($res){
        $data['status']=100;
        $data['value']=$res;
        }else{
        $data['status']=101;
        }
		$this->response($data,'json');
	}
}
