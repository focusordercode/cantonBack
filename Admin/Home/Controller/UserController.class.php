<?php
namespace Home\Controller;
use Think\Controller;
/*
 * 用户中心
 */
class UserController extends BaseController
{
    public $m_rule = '/^1[34578]{1}\d{9}$/';
    public $e_rule = '/^([0-9A-Za-z\\-_\\.]+)@([0-9a-z]+\\.[a-z]{2,3}(\\.[a-z]{2})?)$/i';

    /*
     * 用户访问 / 用户列表
     * */
    public function getUserList()
    {
        $role     = (int)I('roleid');
        $enabled  = isset($_POST['enabled']) ? (int)I('enabled') : 1;
        $search   = I('search');
        $page     = isset($_POST['page']) ? (int)I('page') : 1;
        $pagesize = isset($_POST['pagesize']) ? (int)I('pagesize') : 25;
        $wheres   = "enabled = $enabled";
        if(!empty($search)){
            $wheres .= " AND (username like '%$search%' OR real_name like '%$search%' OR mobile like '%$search%')";
        }

        $result = \Think\Product\User::getuser($wheres,$role,$page,$pagesize);
        if($result['error'] == 0){
            $this->response([
                'status' => 100,
                'value'     => $result['value'],
                'countUser' => $result['countUser'],
                'pageNow'   => $result['pageNow'],
                'countPage' => $result['countPage']
            ],'json');
        }else{
            $this->response(['status' => $result['status'],'msg' => $result['msg']],'json');
        }
    }


    /*
     * 注册 / 添加用户
     * */
    public function doregister()
    {
        if (IS_POST) {
            $username  = trim(I('username'));
            $password  = trim(I('password'));
            $mobile    = trim(I('mobile'));
            $email     = trim(I('email'));
            $real_name = trim(I('real_name'));
            $is_staff  = I('is_staff');
            $is_head   = I('is_head');
            $remark    = trim(I('remark'));
            $roleid    = (array)I('roleid');
            $salt = substr(strtoupper(md5(time())), 0, 6);
            $pwd  = EncodePwd($password, $salt);

            $creator_id = I('post.creator_id');
            if(empty($creator_id)){
                $arr['status'] = 1012;
                $this->response($arr,'json');
                exit();
            }

            if(empty($username) || empty($password) || empty($real_name)){
                $this->response(['status' => 102, 'msg' => '数据不完善'],'json');
            }
            if(strlen($password) < 6 || strlen($password) < 5){
                $this->response(['status' => 102, 'msg' => '账号或密码长度不符合'],'json');
            }
            if (!empty($mobile)) {
                if (!preg_match($this->m_rule, $mobile)) {
                    $this->response(['status' => 102, 'msg' => '手机格式不正确'],'json');
                }
            }
            if(empty($mobile)){
                $mobile = null;
            }
            if (!empty($email)) {
                if (!preg_match($this->e_rule, $email)) {
                    $this->response(['status' => 102, 'msg' => '邮箱格式不正确'],'json');
                }
            }
            $has = M('auth_user')->where(['username'=>$username])->find();
            if($has){
                $this->response(['status' => 102, 'msg' => '用户名已存在'],'json');
            }
            $register_data = [
                'username'  => $username,
                'password'  => $pwd,
                'pwdsuffix' => $salt,
                'real_name' => $real_name,
                'email'     => $email,
                'mobile'    => $mobile,
                'is_staff'  => $is_staff,
                'is_head'   => $is_head,
                'remark'    => $remark,
                'enabled'   => 1,
                'created_time'  => date('Y-m-d H:i:s', time()),
                'creator_id' => $creator_id
            ];
            $insert = M('auth_user')->add($register_data);
            if ($insert) {
                foreach($roleid as $v){
                    M('auth_role_user')->add([
                        'role_id'    => $v,
                        'user_id'    => $insert,
                        'createtime' => date('Y-m-d H:i:s', time()),
                        'creator_id' => $creator_id
                    ]);
                }

                $this->response(['status' => 100],'json');
            } else {
                $this->response(['status' => 101, 'msg' => '注册有误'],'json');
            }
        } else {
            $this->response(['status' => 103, 'msg' => '请求失败'],'json');
        }
    }

    /*
     * 用户编辑
     * */
    public function doedit(){
        if (IS_POST) {
            $uid       = (int)I('uid');
            $password  = I('password');
            $mobile    = I('mobile');
            $email     = I('email');
            $real_name = I('real_name');
            $is_staff  = (int)I('is_staff');
            $is_head   = (int)I('is_head');
            $remark    = I('remark');
//            $sex       = (int)I('sex');
            $enabled   = (int)I('enabled');

            $user = M('auth_user')->where('id = %d',[$uid])->find();
            if(!empty($password)){
                if(strlen($password) < 6){
                    $this->response(['status' => 102, 'msg' => '密码长度不符合'],'json');
                }
                $pwd = EncodePwd($password, $user['pwdsuffix']);
            }else{
                $pwd = $user['password'];
            }
            if(empty($real_name)){
                $this->response(['status' => 102, 'msg' => '真实姓名必填'],'json');
            }
            if (!empty($mobile)) {
                if (!preg_match($this->m_rule, $mobile)) {
                    $this->response(['status' => 102, 'msg' => '手机格式不正确'],'json');
                }
            }
            if(empty($mobile)){
                $mobile = null;
            }
            if (!empty($email)) {
                if (!preg_match($this->e_rule, $email)) {
                    $this->response(['status' => 102, 'msg' => '邮箱格式不正确'],'json');
                }
            }
            $edit_data = [
                'password'  => $pwd,
                'real_name' => $real_name,
                'email'     => $email,
                'mobile'    => $mobile,
                'is_staff'  => $is_staff,
                'is_head'   => $is_head,
                'remark'    => $remark,
                'enabled'   => $enabled,
                'modified_time' => date('Y-m-d H:i:s', time()),
            ];
            $edit = M('auth_user')->where('id = %d',[$uid])->save($edit_data);
            if ($edit) {
                $this->response(['status' => 100],'json');
            } else {
                $this->response(['status' => 101, 'msg' => '编辑异常'],'json');
            }
        } else {
            $this->response(['status' => 103, 'msg' => '请求失败'],'json');
        }
    }

    /*
     * 用户删除
     * */
    public function dodelete(){
        if(IS_POST){
            $m = M("auth_user");
            $user_role = M('auth_role_user');
            $uid = I('uid');
            $arr = array();
            if(!is_array($uid)){    // 按照数组批量删除的方式删除数据
                $arr[] = $uid;
            }elseif(is_array($uid)){
                $arr = $uid;
            }
            foreach($arr as $value){  // 循环删除
                // admin管理员不可删除
                if($value == 1){
                    continue;
                }
                $m->where(array('id' => $value))->delete();
                $user_role->where(array('user_id' => $value))->delete();
            }
            $this->response(['status' => 100],'json');
        }else{
            $this->response(['status' => 103, 'msg' => '请求失败'],'json');
        }
    }
}