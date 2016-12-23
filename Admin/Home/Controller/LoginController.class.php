<?php
namespace Home\Controller;
use Think\Controller;
header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Methods:POST,GET');
header('Access-Control-Allow-Credentials:true');
header("Content-Type: application/json;charset=utf-8");
/**
 * Login登陆操作
 * @author cxl,lrf
 * @modify 2016/12/22
 */
class LoginController extends Controller
{
    public $m_rule = '/^1[34578]{1}\d{9}$/';
    public $e_rule = '/^([0-9A-Za-z\\-_\\.]+)@([0-9a-z]+\\.[a-z]{2,3}(\\.[a-z]{2})?)$/i';

    /*
     * 登陆
     * @param username 用户名
     * @param password 密码
     * */
    public function dologin()
    {
        if(IS_POST)
        {
            // 登陆查询ip是否被限制
            if(!$this->loginLimit()){
                echo json_encode(['status' => 104 ,'msg' => 'ip已被限制15分钟，请休息一会。']);exit();
            }
            $username  = I('username');
            $password  = I('password');
            if(empty($username) || empty($password)){
                echo json_encode(['status' => 101 ,'msg' => '登陆信息不完善']);exit();
            }
            $m = M('auth_user');
            $user = $m->where(['username' => $username,'enabled' => 1])->find();
            if($user){
                // 获取密码方式
                $pwd = EncodePwd($password ,$user['pwdsuffix']);
                // 核对登陆信息
                if($pwd == $user['password']){
                    $token = substr(md5(get_client_ip()),0,6);
                    $ids = base64_encode(authcode($user['id'],'ENCODE',md5(C('ENCODE_USERID'))));
                    $keyCode = C('ENCODE_KEY_CODE').'_'.(time() + 36000) . '_' . $user['id'] . '_' . $token;
                    $key = base64_encode(authcode($keyCode,'ENCODE',md5(C('ENCODE_KEY'))));
                    $users = [
                        'user' => [
                            'uid'       => $user['id'],
                            'username'  => $user['username'],
                            'real_name' => $user['real_name'],
                            'email'     => $user['email'],
                            'mobile'    => $user['mobile'],
                            'sex'       => $user['sex'],
                        ],
                        'user_id' => $ids,
                        'key'     => $key
                    ];
                    // 缓存用户的权限
                    $auth = new \Think\Product\PAuth();
                    $auth->cacheUserAuth($user['id']);
                    $this->loginSuccess($user['id'],$token);
                    echo json_encode(['status' => 100 , 'value' => $users]);
                }else{
                    $this->loginFailed();
                    echo json_encode(['status' => 101 ,'msg' => '密码有误']);exit();
                }
            }else{
                $this->loginFailed();
                echo json_encode(['status' => 102 ,'msg' => '用户不存在']);exit();
            }
        }else{
            $this->loginFailed();
            echo json_encode(['status' => 103 ,'msg' => '请求失败']);exit();
        }
    }

    /*
     * 登陆限制
     * @ 一个ip超过5次登陆失败限制15分钟
     * */
    public function loginLimit(){
        $m = M('auth_login_log');
        $ip = get_client_ip();
        $logip = $m->where(['log_ip' => $ip])->find();
        if(!$logip) return true;
        if($logip['is_limit'] == 1 && $logip['clear_time'] > time()){return false;}
        return true;
    }

    /*
     * 登陆成功
     * @ 修改登陆信息
     * */
    public function loginSuccess($uid ,$key){
        $m = M('auth_user');
        $ip = get_client_ip();
        $login_log = [
            'last_login_ip'   => $ip,
            'last_login_time' => date('Y-m-d H:i:s',time()),
            'login_key'       => $key,
        ];
        $m->where('id=%d',[$uid])->save($login_log);
        M('auth_login_log')->where(['log_ip' => $ip])->save([
            'log_ip'     => $ip,
            'is_limit'   => 0,
            'lock_time'  => 0,
            'clear_time' => 0,
            'error_num'  => 0,
        ]);
        return true;
    }

    /*
     * 登陆成功
     * @ 修改登陆信息
     * */
    public function loginFailed(){
        $m = M('auth_login_log');
        $ip = get_client_ip();
        $ips = $m->where(['log_ip' => $ip])->find();
        if(!$ips){
            $m->add([
                'log_ip'     => $ip,
                'is_limit'   => 0,
                'lock_time'  => 0,
                'error_num'  => 1,
            ]);
        }else{
            if($ips['error_num'] >= 4){
                $m->where('id = %d',[$ips['id']])->save([
                    'is_limit'   => 1,
                    'lock_time'  => time(),
                    'clear_time' => time() + 15 * 60,
                    'error_num'  => 5,
                ]);
            }else{
                $m->where('id = %d',[$ips['id']])->save([
                    'error_num'  => $ips['error_num'] + 1,
                ]);
            }
        }
        return true;
    }

    /*
     * 登出
     * @ param
     * */
    public function logout()
    {
        $cache = \Think\Cache::getInstance('Memcache');
        $ids = I('user_id');
        if(!$ids){
            return true;
        }
        $uid = authcode($ids,'DECODE',md5(C('ENCODE_USERID')));
        $cache->set('user_'.$uid, null);
    }

}