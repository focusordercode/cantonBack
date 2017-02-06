<?php
namespace Home\Controller;
use Think\Controller;

/**
* 角色-权限控制器
* @author lrf
* @modify 2016/12/22
*/
class RolesController extends BaseController{

	/*
	 * 获取角色列表
	 * @param vague 模糊搜索条件
	 * @param id 角色id
	 */
	public function getRoles(){
		$vague = I('post.vague');
		$id = I('post.id');
		//根据传回的数据组合搜索条件
        $vague = __sqlSafe__($vague);
		if(!empty($vague)){
			$where['_string'] = '(name like "%'.$vague.'%")';
		}
		if(!empty($id)){
			$where['id'] = $id;
		}

		$role = M('auth_role');
		$role_org = M('auth_role_org');
		$org = M('auth_org');
		$role->startTrans();
		$sql = $role->where($where)->select();
		//根据查出的角色将其对应的组织机构查出来
		foreach ($sql as $key => $value) {
			$query = $role_org->field("org_id")->where("role_id=%d",array($value['id']))->select();
			foreach ($query as $keys => $values) {
				$arr[] = $values['org_id'];
			}
			$ids = implode(',',$arr);
			if(!empty($ids)){
				$get = $org->field("id,name")->where("id in (".$ids.")")->select();
				foreach ($get as $ks => $val) {
					$array[$ks]['id'] = $val['id'];
					$array[$ks]['name'] = $val['name'];
					$names[] = $val['name'];
				}
			}
			$datas[$key] = $value;
			$datas[$key]['org'] = $array;
			$datas[$key]['org_name'] = implode(',',$names);
			$arr = array();
			$array = array();
			$ids = null;
			$names = array();
		}
		$role->commit();
		if($sql){
			$data['status'] = 100;
			$data['value'] = $datas;
		}else{
			$data['status'] = 101;
			$data['msg'] = "没有数据";
		}
		$this->response($data,'json');
	}

	/*
	 * 修改角色信息
	 * @param name 角色名称
	 * @param remark 角色说明
	 * @param enabled 角色状态
	 * @param org_ids 组织机构id
	 * @param role_id 角色id
	 */
	public function updateRoles(){
		$name = I('post.name');
		$remark = I('post.remark');
		$enabled = I('post.enabled');
		$org_ids = I('post.org_ids');
		$id = I('post.role_id');

		if(empty($name)){
			$arr['status'] = 102;
			$arr['msg'] = "角色名称不能为空";
			$this->response($arr,'json');
		}
		if(empty($enabled) && $enabled!=0 && $enabled !='0'){
			$arr['status'] = 102;
			$arr['msg'] = "角色状态不能为空";
			$this->response($arr,'json');
		}
        if($id == 2 || $id == 5) $this->response(['status' => 104,'msg' => '超级管理员与总经理角色不能修改']);
        // 多人同时操作限制
        if(!limitOperation('auth_role' ,$id ,240 ,$this->loginid)) {
            $this->response(['status' => 101 ,'msg' => '有同事在操作该数据']);
        }
		$role = M('auth_role');
		$org = M('auth_role_org');
		$role->startTrans();
		$data['name'] = $name;
		$data['remark'] = $remark;
		$data['enabled'] = $enabled;
		$data['modified_time'] = date('Y-m-d H:i:s',time());
		$sql = $role->data($data)->where("id=%d",array($id))->save();
		$org->where("role_id=%d",array($id))->delete();
		$das['role_id'] = $id;
		foreach ($org_ids as $key => $value) {
			if(!empty($value['id'])){
				$das['org_id'] = $value['id'];
				$das['created_time'] = date('Y-m-d H:i:s',time());
				$query = $org->data($das)->add();
				if(empty($query)){
					$role->rollback();
					$arr['status'] = 101;
					$arr['msg'] = "修改失败";
                    EndEditTime('auth_role' ,$id);
					$this->response($arr,'json');
				}
			}
		}

		if($sql !== 'flase'){
			$role->commit();
			$arr['status'] = 100;
		}else{
			$role->rollback();
			$arr['status'] = 101;
			$arr['msg'] = "修改失败";
		}
        EndEditTime('auth_role' ,$id);
		$this->response($arr,'json');
	}

	/*
	 * 添加角色
	 * @param name 角色名称
	 * @param remark 角色说明
	 * @param enabled 角色状态
	 * @param org_ids 组织机构id
	 * @param creator_id 创建者id
	 */
	public function addRoles(){
		$name = I('post.name');
		$remark = I('post.remark');
		$enabled = I('post.enabled');
		$org_ids = I('post.org_id');
 		if(empty($name)){
			$arr['status'] = 102;
			$arr['msg'] = "角色名称不能为空";
			$this->response($arr,'json');
		}
		if(empty($enabled)){
			$arr['status'] = 102;
			$arr['msg'] = "角色状态不能为空";
			$this->response($arr,'json');
		}
		$creator_id = I('post.creator_id');
		if(empty($creator_id)){
			$arr['status'] = 1012;
			$this->response($arr,'json');
		}
		$role = M('auth_role');
		$org = M('auth_role_org');
		$role->startTrans();
		$data['name'] = $name;
		$data['remark'] = $remark;
		$data['enabled'] = $enabled;
		$data['creator_id'] = $creator_id;
		$data['created_time'] = date('Y-m-d H:i:s',time());
		$data['modified_time'] = date('Y-m-d H:i:s',time());
		$sql = $role->data($data)->add();
		$das['role_id'] = $sql;
		$das['creator_id'] = $creator_id;
		foreach ($org_ids as $key => $value) {
			$das['org_id'] = $value;
			$das['created_time'] = date('Y-m-d H:i:s',time());
			$query = $org->data($das)->add();
			if(empty($query)){
				$role->rollback();
				$arr['status'] = 101;
				$arr['msg'] = "修改失败";
				$this->response($arr,'json');
			}
		}
		if($sql){
			$role->commit();
			$arr['status'] = 100;
		}else{
			$role->rollback();
			$arr['status'] = 101;
			$arr['msg'] = "修改失败";
		}
		$this->response($arr,'json');
	}

	/*
	 * 删除角色
	 * @param role_id 角色id
	 */
	public function delRoles(){
		$id = I('post.role_id');
		if(empty($id)){
			$arr['status'] = 102;
			$arr['msg'] = "请选择角色";
			$this->response($arr,'json');
		}

        // 多人同时操作限制
        if(!limitOperation('auth_role' ,$id ,240 ,$this->loginid ,'R')) {
            $this->response(['status' => 101 ,'msg' => '有同事在操作该数据']);
        }
        if($id == 2 || $id == 5) $this->response(['status' => 104,'msg' => '超级管理员与总经理角色不能删除']);
		$role = M('auth_role');
		$role_user = M('auth_role_user');
		$role_org = M('auth_role_org');
		$query = $role_user->field("id")->where("role_id=%d",array($id))->find();
		if(empty($query['id'])){
			$role->where("id=%d",array($id))->delete();
			$sql = $role_org->where("role_id=%d",array($id))->delete();
			if($sql !== 'flase'){
				$arr['status'] = 100;
			}else{
				$arr['status'] = 101;
				$arr['msg'] = "删除失败";
			}
		}else{
			$arr['status'] = 103;
			$arr['msg'] = "角色下有关联用户";
		}
		$this->response($arr,'json');
	}

	/*
	 * 读取角色的权限
	 * @param role_id 角色id
	 */
	public function getRule2Role(){
		$role_id = I('post.role_id');
		if(empty($role_id)){
			$arr['status'] = 102;
			$arr['msg'] = "请选择角色";
			$this->response($arr,'json');
			exit();
		}
		// 列出所拥有的权限
		$role = M('auth_role');
		$sql = $role->field("permissions")->where("id=%d",array($role_id))->find();
		$arr = explode(",",$sql['permissions']);
		// 列出所有权限 
		$rule = M('auth_rule');
		$query = $rule->field("id,name,p_id")->select();
		foreach ($query as $key => $value) {
			$data[$key] = $value;
			if(in_array($value['id'], $arr)){
				$data[$key]['have'] = (boolean)true;
			}else{
				$data[$key]['have'] = (boolean)'';
			}
		}
		$datas = pre($data,0);
		$array['status'] = 100;
		$array['value'] = $datas;
		$this->response($array,'json');
	}

	/*
	 * 给角色分配权限
	 * @param role_id 角色id
	 * @param rule_ids 权限id数组
	 */
	public function allotRule2Role(){
		$role_id = I('post.role_id');
		$rule_ids = I('post.rule_ids');
		$role = M('auth_role');

        // 多人同时操作限制
        if(!limitOperation('auth_role' ,$role_id ,240 ,$this->loginid)) {
            $this->response(['status' => 101 ,'msg' => '有同事在操作该数据']);
        }

		$data['permissions'] = implode(",",$rule_ids);
		$data['modified_time'] = date('Y-m-d H:i:s',time());
		$sql = $role->data($data)->where("id=%d",array($role_id))->save();
		if($sql !== 'flase'){
			$arr['status'] = 100;
		}else{
			$arr['status'] = 101;
			$arr['msg'] = "分配失败";
		}
        EndEditTime('auth_role' ,$role_id);
		$this->response($arr,'json');
	}
}
