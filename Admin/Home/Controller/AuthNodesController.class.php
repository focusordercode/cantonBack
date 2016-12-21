<?php
namespace Home\Controller;
use Think\Controller;
/*
 * 权限节点分组
 */
class AuthNodesController extends BaseController
{

    /*
     * 权限节点展示
     * @param
     * */
    public function getRulesTree(){
        $m = M('auth_rule');
        // 查询出所有的机构
        $enabled = (int)I('enabled');
        $rules = $m->where("enabled=$enabled")->field('id,auth_address,p_id,name')->select();
        foreach($rules as $key => $val){
            if($val['p_id'] == 0){
                $rules[$key]['open_son']  = (boolean)'';
                $rules[$key]['open_edit'] = (boolean)'';
            }
        }
        $this->response(['status' => 100,'value' => pre($rules)],'json');
    }

    /*
     * 权限节点添加
     * @param auth_address 节点url
     * @param name         节点名称
     * @param p_id         上级节点
     * */
    public function insertRule(){
        $m = M('auth_rule');
        $father_id = (int)I('p_id');
        $url       = I('auth_address');
        $name      = I('name');

        $isset = $m->where(['auth_address' => $url])->find();
        if($isset) $this->response(['status' => 102, 'msg' => '该节点已存在，请确认是否添加有误'], 'json');

        if($father_id != 0) {
            if (!preg_match("/^[A-z]+\/[A-z]+$/", $url)) {
                $this->response(['status' => 102, 'msg' => '有上级示例：“Hello/Word”'], 'json');
            }
        }
        if($father_id == 0){
            if(!preg_match("/^[A-z]+$/" , $url)){
                $this->response(['status' => 102,'msg' => '无上级示例：“helloWord”'],'json');
            }
        }
        $insert_data = [
            'auth_address' => $url,
            'p_id'         => $father_id,
            'name'         => $name,
            'enabled'      => 1
        ];
        // 添加
        $add = $m->add($insert_data);
        if($add){
            $this->response(['status' => 100],'json');
        }else{
            $this->response(['status' => 101,'msg' => '添加出错'],'json');
        }
    }

    /*
     * 权限节点编辑
     * @param auth_address 节点url
     * @param name         节点名称
     * @param p_id         上级节点
     * @param enabled      是否启用
     * */
    public function editRule(){
        $m = M('auth_rule');
        $rule_id   = (int)I('rule_id');
        $father_id = (int)I('p_id');
        $url       = I('auth_address');
        $name      = I('name');
        $enabled   = (int)I('enabled');

        if(!preg_match("/^[A-z]+\/[A-z]+$/" , $url) && !preg_match("/^[A-z]+$/" , $url)){
            $this->response(['status' => 102,'msg' => '地址有误'],'json');
        }
        if(!empty($father_id)){
            $edit_data['p_id'] = $father_id;
        }
        if(!empty($enabled)){
            $edit_data['enabled'] = $enabled;
        }
        $edit_data = [
            'auth_address' => $url,
            'name'         => $name
        ];
        // 编辑
        $edit = $m->where(['id' => $rule_id])->save($edit_data);
        if($edit){
            $this->response(['status' => 100],'json');
        }else{
            $this->response(['status' => 101,'msg' => '编辑出错'],'json');
        }
    }

    /*
     * 权限节点删除
     * @param rule_id  节点id
     * */
    public function deleteRule(){
        $m = M('auth_rule');
        $rule_id = (int)I('rule_id');

        $hasSon = $m->where(['p_id' => $rule_id])->find();
        if($hasSon) $this->response(['status' => 101,'msg' => '存在子节点'],'json');

        $role = M('auth_role')->where("permissions LIKE '%$rule_id%'")->select();
        if($role) $this->response(['status' => 101,'msg' => '该节点（'.$hasSon['name'].'）已被角色组使用'],'json');
        // 删除
        $del = $m->where(['id' => $rule_id])->delete();
        if($del){
            $this->response(['status' => 100],'json');
        }else{
            $this->response(['status' => 101,'msg' => '编辑出错'],'json');
        }
    }

    /*
     * 根据id读取权限节点
     * @param rule_id
     * */
    public function getRulesById(){
        $m = M('auth_rule');
        // 查询出所有的机构
        $rule_id = (int)I('rule_id');
        $rule = $m->where("id=$rule_id")->find();
        if(!$rule) $this->response(['status' => 101,'msg' => '请求失败'],'json');
        $this->response(['status' => 100,'value' => $rule],'json');
    }
}