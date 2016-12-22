<?php
namespace Home\Controller;
use Think\Controller;

/**
* 业务编码控制器
*/
class BusinessCodeController extends BaseController{
	//生成业务编码接口
	public function setBusinessCode(){
		$code = I('post.code');
		$data_code = M('data_code');
		$where['code'] = $code;
		$sql = $data_code->where($where)->find();
		if(empty($sql['id'])){
			$arr['status'] = 101;
			$arr['msg'] = "模块不存在，生成业务编码失败";
		}else{
			$databasecode = '01';
			$businesscode = $databasecode.$sql['code'].$sql['number'];
			$data['number'] = str_pad($sql['number']+1,8,"0",STR_PAD_LEFT);
			$data['update_time'] =date('Y-m-d H:i:s',time());
			$data_code->where($where)->save($data);
			$arr['status'] = 100;
			$arr['code'] = $businesscode; 
		}
		$this->response($arr,'json');
	}
	
	//新添加模块生成相应模块编码接口
	public function setModelCode(){
		$code = generate_code();
		$arr['status'] = 100;
		$arr['value'] = $code;
		$this->response($arr,'json');
	}
}