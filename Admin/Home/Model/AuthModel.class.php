<?php
namespace Home\Model;
use Think\Model;

class AuthModel extends Model
{

    protected $tableName = 'auth_user';
    /*
     * 获取能操作的用户
     * @param id   登陆的用户id
     * */
    public function GetOwnUser($id)
    {
        $role = M('auth_role_user')->field('role_id')->where("user_id = $id")->select();
        foreach($role as $rv){
            if($rv['role_id'] == 2 || $rv['role_id'] == 5) return 'ALL';
        }

        $is_head = M('auth_user')->field('is_head')->find($id);
        // 不是领导直接返回自身 id
        if($is_head['is_head'] == 0) return $id;

        $ownUsers = $this->GetOrgsByUid($id);
        return $ownUsers;
    }

    /*
     * 通过用户id获取该用户机构的所有用户
     * @param id  用户id
     * */
    public function GetOrgsByUid($id)
    {
        $user_orgs = M()
            ->table('tbl_auth_role_user u')
            ->join('tbl_auth_role_org o ON u.role_id = o.role_id')
            ->where("u.`user_id`=$id")
            ->field("o.`org_id`")->select();
        // 未查询到机构
        if(!$user_orgs) return false;

        $o = M('auth_org');
        foreach($user_orgs as $key => $value){
            $orgId = $value['org_id'];
            $orgs[] = $o->where("FIND_IN_SET($orgId , `relationship`)")->select();
        }

        foreach($orgs as $ke => $va){
            foreach($va as $k => $v){
                $str[] = $this->GetUsersByOid($v['id']);
            }
        }
        return implode(',' ,array_filter($str));
    }

    /*
     * 查询某个机构里所有用户
     * @param org_id  机构id
     * reutn string
     * */
    public function GetUsersByOid($org_id)
    {
        $users = M()
            ->table('tbl_auth_role_org o')
            ->join('tbl_auth_role_user u ON u.role_id = o.role_id')
            ->where("o.`org_id`=$org_id")
            ->field("u.`user_id`")->select();
        // 未查询到
        if(!$users) return false;

        foreach($users as $k => $v){
            $uids[] = $v['user_id'];
        }
        return implode(',' ,$uids);
    }
}