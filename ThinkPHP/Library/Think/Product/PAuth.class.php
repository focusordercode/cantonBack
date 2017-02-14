<?php
namespace Think\Product;
/**
 * 权限认证类
 */

class PAuth
{
    /**
     * 检查权限
     * @param url string    需要验证的规则列表
     * @param uid  int      认证用户的id
     * @return boolean      通过验证返回true;失败返回false
     */
    public function check($url, $uid)
    {
        $cache = \Think\Cache::getInstance('Memcache');
        // 查询访问的url
        $authUrl = $this->GetUrlOwner($url);
        if(!$authUrl) return false;

        // 查询是否有权限缓存
        $userAuth = $cache->get('user_'.$uid);
        if($userAuth){
            $authIds = unserialize($userAuth);
        }else{
            // 查询用户所在组别
            $authRole = $this->GetUserRole($uid);
            if(!$authRole) return false;
            foreach($authRole as $val){
                $auths[] = $val['permissions'];
            }
            // 拥有所有权限节点的id
            $authIds = trim(implode(",",$auths), ",");
        }

        // 校验
        $authRoleCheck = $this->checkRoleAuth($authUrl,$authIds);
        if(!$authRoleCheck) return false;
        return true;
    }

    /*
     * @param action 控制器操作
     * 传入控制器里的方法，读取该方法属于什么模块里增删改查的类别
     * */
    public function GetUrlOwner($action)
    {
        $urlAuth = C('URL_OWNER_TYPE');
        foreach($urlAuth as $key => $value){
            foreach($value as $k => $val){
                foreach ($val as $v) {
                    if(strtolower($action) == strtolower($v)){
                        return $k;
                    } 
                }
            }
        }
        return false;
    }

    /*
     * @param action 控制器操作
     * uid 用户id 查询到用户所在角色组
     * */
    public function GetUserRole($uid)
    {
        $user_roles = M()
            ->table("tbl_auth_role_user u,tbl_auth_role r")
            ->where("u.`role_id`=r.`id` AND u.`user_id`=$uid AND r.enabled=1")
            ->field("r.id,r.`name`,r.`permissions`,u.`user_id`")->select();
        return $user_roles;
    }

    /*
     * @param   authUrl 请求的url
     * authIds  拥有所有权限节点的id
     * 匹配
     * */
    protected function checkRoleAuth($authUrl,$authIds)
    {
        // 节点没找到
        $auth = M('auth_rule')->where(["auth_address" => $authUrl])->find();
        if(!$auth) return false;

        $auths = explode("," ,$authIds);
        if(in_array($auth['id'],$auths)){
            return true;
        }

        return false;
    }

    /*
     * 用户登陆成功调用 缓存用户权限信息
     * @param user_id 用户id
     * */
    public function cacheUserAuth($uid){
        // 权限缓存初始化 Memcache
        $cache = \Think\Cache::getInstance('Memcache');
        // 查询用户角色
        $roles = $this->GetUserRole($uid);
        // 节点没找到
        if(!$roles) return false;
        $auth  = [];
        $auths = '';
        foreach($roles as $key => $val){
            if(empty($val['permissions']) || $val['permissions'] == null || $val['permissions'] == "")
                continue;
            $auth[] = $val['permissions'];
        }
        // 把所有权限放进一维数组
        foreach($auth as $v){
            $auths .= $v.',';
        }
        if(!$auths) return false;
        $cacheUserAuth = serialize(trim($auths, ','));
        $cache->set('user_'.$uid, $cacheUserAuth);
        return $cacheUserAuth;
    }

    /*
     * 用户登陆之后验证key 和userid
     * @param strid   用户id
     * @param strkey  加密key
     * */
    public function checkKey($strid, $strkey)
    {
        $ids = base64_decode($strid);
        $key = base64_decode($strkey);

        $uids = authcode($ids,'DECODE',md5(C('ENCODE_USERID')));
        if(!$uids){
            return false;
        }
        $keys = explode("_" ,authcode($key,'DECODE',md5(C('ENCODE_KEY'))));
        $login_key = M('auth_user')->where('id = %d',[$uids])->find();
        $token = $login_key['login_key'];
        // 第一个参数加密code 第二时间戳 第三用户id 三重验证
        if($keys[0] != C('ENCODE_KEY_CODE') || $keys[1] < time() || $keys[2] != $uids || $keys[3] != $token){
            return false;
        }

        return $uids;
    }
}