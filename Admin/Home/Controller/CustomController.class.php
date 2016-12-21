<?php
namespace Home\Controller;
use Think\Controller;

class CustomController extends BaseController
{
    public $rule_mobile = "/^1[34578]{1}[0-9]{9}$/";
    public $rule_enname = "/^[A-z\s]+$/";
    public $rule_email  = "/^([0-9A-Za-z\\-_\\.]+)@([0-9a-z]+\\.[a-z]{2,3}(\\.[a-z]{2})?)$/i";

    // 拉取客户信息
    public function getCustom()
    {
        $m = M("customer");
        $enabled = isset($_POST['enabled']) ? I('enabled') : 1;
        $pageSize = isset($_POST['pageSize']) ? I('pageSize') :10;
        $pageNow = isset($_POST['pageNow']) ? I('pageNow') :1;

        $count = $m->where(array('enabled'=>$enabled))->count();
        $start_id = ( $pageNow - 1 ) * $pageSize;
        $counts = ceil($count/$pageSize);
        $list  = $m->where(array('enabled'=>$enabled))->order('id desc')->limit($start_id,$pageSize)->select();
        foreach ($list as $key => $value) {
            if($value['mobile'] == 0 || $value['mobile'] == '0'){
                $value['mobile'] = ' ';
            }
        }
        if($list){
            $data = array(
                'status'    => 100,
                'cus_count' => $count,    // 客户总数
                'pageNow'   => $pageNow,  // 当前页码
                'countPage' => $counts,   // 总页数
                'value'     => $list,
            );
        }else{
            $data['status'] = 101;
            $data['msg']    = '暂无相关信息';
        }
        $this->response($data,'json');
    }

    // 更新客户信息
    public function update(){
        $m = M('customer');
        $da = $_POST['data'];
        $id = $da['id'];
        unset($da['id']); // id赋值之后删除
        if(isset($da['checked'])){
            unset($da['checked']);  // 删除前台赋值的元素
        }
        if(empty($da['company']) || !preg_match($this->rule_enname , $da['en_name']) || empty($da['custom_name'])){
            $data['status'] = 102;
            $data['msg']    = '客户名或英文名或公司名有填写错误';
        }else{
            $s = $m->where(array('id'=>$id))->save($da);
            if($s){
                $data['status'] = 100;
                $data['value']  = $s;
            }else{
                $data['status'] = 101;
                $data['msg']    = '修改失败';
            }
        }
        $this->response($data,'json');
    }

    // 删除客户信息
    public function delete(){
        $m = M("customer");
        $m->startTrans();
        $id = I('id');
        $arr = array();
        if(!is_array($id)){    // 按照数组批量删除的方式删除数据
            $arr[] = $id;
        }elseif(is_array($id)){
            $arr = $id;
        }
        $i = 0;
        foreach($arr as $value){  // 循环删除
            $res = checkDataLimit('YF',$value);
            if($res == 1){
                $del = $m->where(array('id'=>$value))->delete();
                if($del){
                    $i ++;
                }
            }
        }

        if($i != 0){
            $m->commit();
            $data['status'] = 100;
            $data['value']  = $i;
        }else{
            $m->rollback();
            $data['status'] = 101;
            $data['msg']    = '删除失败';
        }
        $this->response($data,'json');
    }

    // 添加客户信息
    public function add(){

        $m = M('customer');
        $m->startTrans();
        $arr = $_POST['data'];  // 前台给的数据包
        $da['custom_name'] = trim($arr['custom_name']);
        $da['en_name']     = trim($arr['en_name']);
        $da['company']     = trim($arr['company']);
        $da['mobile']      = trim($arr['mobile']);
        $da['email']       = trim($arr['email']);
        $da['address']     = trim($arr['address']);
        $da['enabled']     = isset($arr['enabled']) ? trim($arr['enabled']) : 1;
         // 验证
        if(!preg_match($this->rule_enname , $da['en_name'])){
            $data['status'] = 102;
            $data['msg']    = '英文名填写错误';
            $this->response($data,'json');
            exit();
        }
        if(!empty($arr['mobile'])){
            if(!preg_match($this->rule_mobile , $arr['mobile'])){
                $data['status'] = 102;
                $data['msg']    = '手机号填写错误';
                $this->response($data,'json');
                exit();
            }
            $da['mobile'] = $arr['mobile'];
        }
        if(!empty($arr['email'])){
            if(!preg_match($this->rule_email , $arr['email'])){
                $data['status'] = 102;
                $data['msg']    = '邮箱填写错误';
                $this->response($data,'json');
                exit();
            }
            $da['email'] = $arr['email'];
        }
        $a = $m->add($da);
        if($a){
            $das['custom_number'] = $this->auto_add_zero('YF'); // 自动分配客户编号 按照数据本身的id

            $s = $m->where(array('id'=>$a))->save($das);
            if($s){
                $data['status'] = 100;
                $data['value']  = $a;
                $m->commit();
            }else{
                $data['status'] = 101;
                $data['msg']    = '添加失败';
                $m->rollback();
            }
        }
        $this->response($data,'json');
    }

    // 编号获取，自动填充位数为 0
    public function auto_add_zero($code){
        $data_code = M('data_code');
        $where['code'] = $code;
        $sql = $data_code->where($where)->find();
        $databasecode = '01';
        $businesscode = $databasecode.$sql['code'].$sql['number'];
        $data['number'] = str_pad($sql['number']+1,8,"0",STR_PAD_LEFT);
        $data['update_time'] =date('Y-m-d H:i:s',time());
        $query = $data_code->where($where)->save($data);
        return $businesscode;
    }

    /*
     * 模糊搜索客户信息
     */
    public function selVague(){
        $text = I('post.keyword');
        if(empty($text)){
            $data['status'] = 102;
            $data['msg']    = '关键词必填';
            $this->response($data,'json');
            exit();
        }
        //  中英文名称
        $arr = M()->query("select * from tbl_customer where en_name like '%".$text."%' or custom_name like '%".$text."%' ");

        if($arr){
            $data['status'] = 100;
            $data['value']  = $arr;
        }else{
            $data['status'] = 101;
            $data['msg']    = '暂无相关信息';
        }
        $this->response($data,'json');
    }
}