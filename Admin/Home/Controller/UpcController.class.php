<?php
namespace Home\Controller;
use Think\Controller;
/**
 * upc
 * @author cxl,lrf
 * @modify 2016/12/21
 */
class UpcController extends BaseController
{
    protected $dir = "./Public/upc";
    protected $rule_time = "/^\d{4}-\d{2}-\d{2}(\s)\d{2}:\d{2}:\d{2}$/s";

    /*
     * 读取上传upc文件
     * */
    public function read_upc_file ($file) {  // 读取文件并录入
        set_time_limit(0);
        $u = M('product_upc_code');
        $u->startTrans();
        $error = 0;
        $da = array(
            'enabled'         => 1,
            'locked'          => 0,
            'operation_id'    => isset($_SESSION['user_id']) ? session('user_id') : 0,
            'operation_time'  => date('Y-m-d H:i:s',time()),
        );
        $same_upc = 0;  // 已经存在的upc
        $inserted = 0;  // 录入成功的upc

        $handle = @fopen($file, "r");
        if ($handle) {
            while (!feof($handle)) {
                $buffer = fgets($handle, 15); // 逐行读取upc
                $v = trim($buffer);
                if(preg_match("/^\d{12,13}\b/", trim($v))){
                    $isset = $u->where("upc_code='$v'")->find(); // 查询upc是否已经存在
                    if($isset){
                        $same_upc += 1;
                        continue;
                    }
                    $da['upc_code'] = $v;
                    $insert = $u->add($da);  // 添加
                    if($insert){
                        $inserted += 1;
                    }
                }else{
                    $error = 1;break;
                }
            }
            fclose($handle);
        }

        if($error == 1){
            unlink($file);
            $u->rollback();
            $data['status'] = 105; // upc非法
            $data['msg']    = 'UPC非法';
            $this->response($data);
        }else{
            $u->commit();
            $data['status'] = 100;
            $data['value'] = array(
                'same_upc' => $same_upc,
                'inserted' => $inserted
            );
            $this->response($data);
        }
    }

    /*
     * upc文件上传  txt文本文档类型
     * 每行一条upc  12-13个数字 已正则校验 否则不能匹配
     * */
    public function upload_upc_file()
    {
        set_time_limit(0);
        if(!is_dir($this->dir)){
            mkdir($this->dir);
        }
        $extension = 'txt';
        $tmp_file = $_FILES['file'];
        if($_FILES){
            $type = pathinfo($tmp_file['name'],PATHINFO_EXTENSION); //获取文件类型
            if($type == $extension){
                $size = (($tmp_file['size'])/1024)/1024; // mb兆
                if($size < 5){
                    $upc_file_name = $this->dir."/upc_".date("Ymd-His", time()). '.' .$extension;
                    if(move_uploaded_file($tmp_file['tmp_name'], $upc_file_name)){
                        $this->read_upc_file($upc_file_name);     // 上传成功读取录入upc
                    }
                }else{
                    $data['status'] = 104;
                    $data['msg']    = '文件大小超负荷';
                    $this->response($data);
                }
            }else{
                $data['status'] = 103;
                $data['msg']    = '文件格式不被允许';
                $this->response($data);
            }
        }else{
            $data['status'] = 102;
            $data['msg']    = '没有文件被上传';
            $this->response($data);
        }
    }

    /*
     * 拉取upc列表
     * @param pageNum 页码
     * @param locked  锁定
     * @param enabeld 有无效
     * @param pageSize 页面尺寸
     */
    public function get_upc_list()
    {
        $nowPage = isset($_POST['pageNum']) ? (int)I("post.pageNum") : 1;
        if(preg_match("/^[0-9]+$/", $nowPage)){
            $locked  = (int)I("post.locked");
            $enabled = (int)I("post.enabled");
            if($locked){
                $where = "locked = $locked";
            }else{
                $where = "locked = 0 ";
            }

            if($enabled){
                $where .= " and enabled = $enabled";
            }

            $pageSize = (int)I("pageSize");
            if($pageSize != 0){
                $pa = $pageSize;
            }else{
                $pa = 25;
            }
            if(isset($_POST['start_time']) && isset($_POST['end_time'])){
                $s_time = date("Y-m-d H:i:s",strtotime(I("start_time")));
                $e_time = date("Y-m-d H:i:s",strtotime(I("end_time")));
                $where .= " and operation_time between '$s_time' and '$e_time'";
            }
            $where = __sqlSafe__($where);
            // 配置完成读取条件读取
            $get_upc = \Think\Product\Upc::get_upc($where,$nowPage,$pa);
            if($get_upc['error'] == 0){
                $data['status'] = 100;
                $data['value'] = $get_upc['value'];
                $data['count'] = $get_upc['count'];
                $data['allupc'] = $get_upc['allupc'];
                $data['usedupc'] = $get_upc['usedupc'];
                $data['pageNow'] = $get_upc['pageNow'];
                $data['upc'] = $get_upc['upc'];
                $data['lockedupc'] = $get_upc['lockedupc'];
            }else{
                $data['status'] = $get_upc['status'];
                $data['msg']    = $get_upc['msg'];
            }
        }else{
            $data['status'] = 102;
            $data['msg']    = '页码有误';
        }
        $this->response($get_upc);
    }

    // 匹配UPC
    // @param form_id 表格id 自动匹配
    public function marry_upc(){
        $form_id = I('form_id');

        if(!preg_match("/^[0-9]+$/",$form_id)){
            $returnArr['status'] = 102;
            $returnArr['msg']    = '未选则表格数据';
            $this->response($returnArr);
        }

        $result = \Think\Product\Upc::marry_upcs($form_id);
        if($result['error'] == 0){
            $returnArr['status'] = 100;
        }else{
            $returnArr['status'] = $result['status'];
            $returnArr['msg']    = $result['msg'];
        }
        $this->response($returnArr);
    }


    /*
     * 解绑upc
     * @param form_id 表格id
     * */
    public function unLockUpc()
    {
        $form_id = (int)I('form_id');
        $uid     = (int)I('create_id');

        if($form_id == 0) $this->response(['status' => 102, 'msg' => '表格未选取']);
        if($uid == 0)     $this->response(['status' => 102, 'msg' => '请确认是否已登录']);
        $result = \Think\Product\Upc::unLock($form_id ,$uid);
        if($result['error'] == 0){
            $ret['status'] = 100;
        }else{
            $ret['status'] = $result['status'];
            $ret['msg']    = $result['msg'];
        }
        $this->response($ret);
    }
}
