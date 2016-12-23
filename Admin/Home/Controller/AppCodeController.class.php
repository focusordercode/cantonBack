<?php
namespace Home\Controller;
use Think\Controller;
/**
* 类目管理控制器
*/
class AppCodeController extends BaseController{

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
