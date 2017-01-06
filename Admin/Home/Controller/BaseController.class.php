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

    public $loginid;

    private $modelArr = [
        'CUSTOMER'    => 120,
        'CATEGORY'    => 120,
        'GALLERY'     => 120,
        'ORG'         => 120,
        'ROLES'       => 180,
        'USER'        => 120,
    ];

    /*
     * 初始化
     * @param user_id 用户验证id
     * @param key     验证加密密钥
     * */
    public function _initialize()
    {
        // 没登录
        $auth = new \Think\Product\PAuth();
        $key = I('key');
        $uid = I('user_id');
        $uids = $auth->checkKey($uid, $key);
        if(!$uids){
            $this->response(['status' => 1012,'msg' => '您还没登陆或登陆信息已过期']);
        }
        // 读取访问的地址
        $url = CONTROLLER_NAME . '/' . ACTION_NAME;
        if(!$auth->check($url , $uids)){
            $this->response(['status' => 1011,'msg' => '抱歉，权限不足']);
        }
        $this->loginid = $uids;

        // 添加排除在外的行为地址
        $notInTrack = [
            'Logging/userTrack',
        ];
        if(!in_array($url ,$notInTrack)){
            $this->behaviorTracking($uids ,$url);
        }
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

    /*
     * 多人用户操作限制
     * @param model 模块
     * @param operationId 操作数据的id
     * */
    public function limitUserOperation()
    {
        $operationId = (int)I('operationId');
        $model       = I('model');
        $mArr        = $this->modelArr;

        if(!array_key_exists($model ,$mArr)){
            $this->response(['status' => 100]);
        }

        $overTime    = $mArr[$model];
        $result = limitOperation($model ,$operationId ,$overTime ,$this->loginid);
        if($result){
            $this->response(['status' => 100]);
        } else {
            $this->response(['status' => 101 ,'msg' => '有同事正在操作该数据']);
        }
    }

    /*
     * 解除多人用户操作限制
     * @param model 模块
     * @param operationId 操作数据的id
     * */
    public function clearOperationLimit()
    {
        $operationId = (int)I('operationId');
        $model       = I('model');
        $mArr        = $this->modelArr;

        if(!array_key_exists($model ,$mArr)){
            $this->response(['status' => 100]);
        }
        EndEditTime($model ,$operationId);
        $this->response(['status' => 100]);
    }
}