<?php

namespace Think;

class  User {

    // 拉取用户列表
    public static function getuser($wheres,$roleid = 0,$page = 1,$pagesize = 25)
    {
        $role = M('auth_role_user');
        $user = M('auth_user');
        $roles1 = '';
        $orgs1  = '';

        if($roleid != 0){
            $user_ids = [];
            $userids = $role->where('role_id = %d' [$roleid])->field('user_id')->select();
            foreach($userids as $val){
                $user_ids[] = $val['org_id'];
            }
            $wheres .= "AND id IN (".implode(",",$user_ids).")";
        }
        // 自定义返回字段
        $fields = 'id,username,real_name,email,mobile,is_staff,is_head,sex,enabled,creator_id,created_time,modified_time,remark';
        // 简单分页管理
        $start = ( $page - 1 ) * $pagesize;
        $count  = $user->where($wheres)->count();
        $result = $user->where($wheres)->order('id asc')->field($fields)->limit($start,$pagesize)->select();
        foreach($result as $key => $val){
            // 查询所属角色
            $roles = M()
                ->table("tbl_auth_role_user u,tbl_auth_role r")
                ->where("u.`role_id`=r.`id` AND u.`user_id`=".$val['id']." AND r.enabled=1")
                ->field("r.id role_id,r.`name`")->select();
            if($roles){
                foreach($roles as $va){
                    $roles1 .= $va['name'].",";
                    // 查询每个角色有的组织机构
                    $orgs = M()
                        ->table("tbl_auth_role_org r,tbl_auth_org o")
                        ->where("r.`role_id`=".$va['role_id']." AND o.`id`=r.`org_id` AND o.enabled=1")
                        ->field("o.`name` as oname")->select();
                    if($orgs){
                        foreach($orgs as $v){
                            $orgs1 .= $v['oname'].",";
                        }
                    }
                }
            }
            $result[$key]['roles'] = trim($roles1,",");
            $result[$key]['orgs']  = trim($orgs1,",");
            // 用过之后重新赋空
            $roles1 = '';
            $orgs1 = '';
        }
        if($result){
            return [
                'error'     => 0,
                'value'     => $result,
                'countUser' => $count,
                'pageNow'   => $page,
                'countPage' => ceil($count/$pagesize)
            ];
        }else{
            return ['error' => 1 , 'msg' => '无相关用户信息' , 'status' => 101];
        }
    }

    /*
     * @param uid 用户id
     * */
    public static function getuserByid($uid){
        $user = M('auth_user');
        $result = $user->field('id,username,real_name,email,mobile,is_staff,is_head,sex,enabled,remark')->find($uid);
        if($result){
            return ['error' => 0,'value' => $result,];
        }else{
            return ['error' => 1 , 'msg' => '用户不存在' , 'status' => 101];
        }
    }
}
