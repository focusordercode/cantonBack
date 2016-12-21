<?php
namespace Home\Controller;
use Think\Controller\RestController;
header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Methods:POST,GET');
header('Access-Control-Allow-Credentials:true'); 
header("Content-Type: application/json;charset=utf-8");

/**
* 业务模块控制器
*/
class BusinessModelController extends RestController{
	public function _initialize()
    {
        // 没登录
        $auth = new \Think\Product\PAuth();
        $key = I('key');
        $uid = I('user_id');
        $uids = $auth->checkKey($uid, $key);
        if(!$uids){
            $this->response(['status' => 1012,'msg' => '您还没登陆或登陆信息已过期'],'json');
        }
        // 读取访问的地址
        $url = CONTROLLER_NAME . '/' . ACTION_NAME;
        if(!$auth->check($url , $uids)){
            $this->response(['status' => 1011,'msg' => '抱歉，权限不足'],'json');
        }
    }
	//添加模块与对应的编码
	public function addBusinessmodel(){
		$cn_name = I('post.cn_name');
		$en_name = I('post.en_name');
		$remark = I('post.remark');
		if(empty($cn_name)){
			$arr['status'] = 102;
			$arr['msg'] = "中文名称不能为空";
			$this->response($arr,'json');
			exit();
		}
		$data['cn_name'] = $cn_name;
		$data['en_name'] = $en_name;
		$data['remark'] = $remark;
		$data['code'] = generate_code();
		$data['enabled'] = 1;
		$res = \Think\Product\BusinessModel::AddBusinessModel($data);
		$this->response($res,'json');
	}

	//获取模块与对应编码的列表
	public function getBusinessmodel(){
		$vague = I('post.vague');
		// $pages = I('post.pages');
		// $num = I('post.num');
		// if(empty($pages)){
		// 	$pages = 1;
		// }
		// if(empty($num)){
		// 	$num = 25;
		// }
		$res = \Think\Product\BusinessModel::GetBusinessModel($vague);
		$this->response($res,'json');
	}

	//删除模块
	public function delBusinessmodel(){
		$id = I('post.id');
		if(empty($id)){
			$arr['status'] = 102;
			$arr['msg'] = "请选择模块";
			$this->response($arr,'json');
			exit();
		}
		$res = \Think\Product\BusinessModel::DelBusinessModel($id);
		$this->response($res,'json');
	}

	//修改模块
	public function updateBusinessmodel(){
		$id = I('post.id');
		$cn_name = I('post.cn_name');
		$en_name = I('post.en_name');
		$remark = I('post.remark');
		if(empty($cn_name)){
			$arr['status'] = 102;
			$arr['msg'] = "中文名称不能为空";
			$this->response($arr,'json');
			exit();
		}
		$data['cn_name'] = $cn_name;
		$data['en_name'] = $en_name;
		$data['remark'] = $remark;
		$res = \Think\Product\BusinessModel::UpdateBusinessModel($id,$data);
		$this->response($res,'json');
	}
}