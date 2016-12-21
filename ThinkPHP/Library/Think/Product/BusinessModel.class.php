<?php
namespace Think\Product;
/**
* 业务模块类
*/
class BusinessModel{
	//添加业务模块
	public function AddBusinessModel($data){
		$app = M('sys_app');
		$data_code = M('data_code');
		$sql = $app->data($data)->add();
		$da['code'] = $data['code'];
		$da['number'] = str_pad(1,8,"0",STR_PAD_LEFT);
		$da['update_time'] = date('Y-m-d H:i:s',time());
		$query = $data_code->data($da)->add();
		if($sql){
			$arr['status'] = 100;
		}else{
			$arr['status'] = 101;
			$arr['msg'] = "添加失败";
		}
		return($arr);
	}

	//获取业务模块列表
	public function GetBusinessModel($vague){
		$ids = ($pages - 1) * $num;
		if(!empty($vague)){
			$where['_string'] = '(cn_name like "%'.$vague.'%")  OR (en_name like "%'.$vague.'%") OR (remark like "%'.$vague.'")';
		}elseif($vague == '0'){
			$where['_string'] = '(cn_name like "%'.$vague.'%")  OR (en_name like "%'.$vague.'%") OR (remark like "%'.$vague.'")';
		}
		$where['enabled'] = 1;
		$app = M('sys_app');
		$sql = $app->where($where)->select();
		//$count = $app->where($where)->count();
		if(empty($sql[0]['id'])){
			$arr['status'] = 101;
			$arr['msg'] ="没有数据";
		}else{
			$arr['status'] = 100;
			// $arr['count'] = $count;
			// $arr['pageNow'] = $pages;
			// $arr['pages'] = ceil($count / $num);
			$arr['value'] = $sql;
		}
		return($arr);
	}

	//删除业务模块
	public function DelBusinessModel($id){
		$app = M('sys_app');
		$data['enabled'] = '0';
		$sql = $app->data($data)->where("id=%d",array($id))->save();
		if($sql !== 'flase'){
			$arr['status'] = 100;
		}else{
			$arr['status'] = 101;
			$arr['msg'] = "删除失败";
		}
		return($arr);
	}

	//修改业务模块
	public function UpdateBusinessModel($id,$data){
		$app = M('sys_app');
		$sql = $app->data($data)->where("id=%d",array($id))->save();
		if($sql !== 'flase'){
			$arr['status'] = 100;
		}else{
			$arr['status'] = 101;
			$arr['msg'] = "修改失败";
		}
		return($arr);
	}
}