<?php
namespace Home\Controller;
use Think\Controller;

class GetGlobalIDController extends BaseController{

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

    // 表格编号获取
    public function get_form_number(){
        $type_code = I('post.type_code');

        if($type_code == 'info'){  // 假如是资料表，条件
            $app_code = 'product_form';
            $t_code   = 'PRODUCT';
        }else{                     // 否则
            $app_code = 'product_batch_form';
            $t_code   = 'BATCH';
        }
        $arrid     = GetSysId($app_code,1);
        if(empty($arrid)){
            $data['status'] = 101;
        }else{
            $data['status']      = 100;
            $data['value']       = $this->auto_add_zero($arrid[0],$t_code);
        }
        $this->response($data,'json');
    }

    // 编号获取，自动填充位数为 0
    public function auto_add_zero($id , $type_code){
        $zero = 10 - strlen($id);
        $auto_adds = max($zero , 0);
        $str = '';
        for($i = 0;$i < $auto_adds;$i ++){ // 位数 零
            $str .= '0';
        }
        $custom_number = $type_code . $str . $id;
        return $custom_number;
    }
}