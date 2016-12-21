<?php
namespace Home\Controller;
use Think\Controller\RestController;
header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Methods:POST,GET');
header('Access-Control-Allow-Credentials:true'); 
header("Content-Type: application/json;charset=utf-8");
/**
 * 基类
 * @author cxl,lrf
 * @modify 2016/12/21
 */
class BaseController extends RestController
{
    protected $allowMethod    = array('get','post','put','delete');
    protected $defaultType    = 'json';

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
        $this->behaviorTracking($uids ,$url);
    }


    /*
     * 用户行为跟踪
     * @param uid     用户id
     * @param action  访问的地址
     * */
    protected function behaviorTracking($uid ,$action)
    {
        $auth = new \Think\Product\PAuth();
        $router = $auth->GetUrlOwner($action);

        $result = M('auth_rule')->where(["auth_address" => $router])->find();
        $track = M('user_track')->add([
            'uid'              => $uid,
            'request_address'  => $action,
            'router_address'   => $router,
            'title'            => $result['name'],
            'request_time'     => date('Y-m-d H:i:s',time()),
            'request_ip'       => get_client_ip(),
        ]);
        if($track){
            return true;
        }else{
            return false;
        }
    }
}