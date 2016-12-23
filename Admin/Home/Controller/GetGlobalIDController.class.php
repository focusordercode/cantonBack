<?php
namespace Home\Controller;
use Think\Controller;
/*
 * 全局id
 * @author lrf
 * @modify 2016/12/22
 */
class GetGlobalIDController extends BaseController{

    /*
     * 获取全局id
     * @param app_code  模块代码
     * @param num 数量
     */
	public function getSysId(){

	   $app_code = I('post.app_code');
	   $num      = I('post.num');
	   $arrid    = GetSysId($app_code,$num);
	   if(empty($arrid)){
	   	  	$data['status'] = 101;
	   }else{
	   	  	$data['status'] = 100;
          	$data['value']  = $arrid;
	   }
	   $this->response($data,'json');
	}

}