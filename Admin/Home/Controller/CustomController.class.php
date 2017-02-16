<?php
namespace Home\Controller;
use Think\Controller;
/**
 * 客户管理控制器
 * @author cxl,lrf
 * @modify 2016/12/22
 */
class CustomController extends BaseController
{
    public $rule_mobile = "/^1[34578]{1}[0-9]{9}$/";
    public $rule_enname = "/^[A-z\s]+$/";
    public $rule_email  = "/^([0-9A-Za-z\\-_\\.]+)@([0-9a-z]+\\.[a-z]{2,3}(\\.[a-z]{2})?)$/i";

    /**
     * 读取客户列表信息
     * @param enabled  状态是否可用
     * @param pageSize 页面大小
     * @param pageNow  当前页
     */
    public function getCustom()
    {
        $m = M("customer");
        $enabled  = isset($_POST['enabled'])  ? (int)I('enabled')  : 1;
        $pageSize = isset($_POST['pageSize']) ? (int)I('pageSize') : 10;
        $pageNow  = isset($_POST['pageNow'])  ? (int)I('pageNow')  : 1;
        $orderBy  = I('post.orderKey');       // 排序字段
        $sort     = isset($_POST['sort']) ? $_POST['sort'] : 'desc'; // 排序方式  倒序/顺序
        $sortS    = strtolower($sort);
        if($sortS != 'desc' && $sortS != 'asc') $this->response(['status'=> 102, 'msg' => '排序方式有误']);
        switch ($orderBy){
            case 'A': $order = 'custom_name '.$sort;  break;
            case 'B': $order = 'en_name '.$sort;      break;
            case 'C': $order = 'company '.$sort;      break;
            case 'D': $order = 'mobile '.$sort;       break;
            case 'E': $order = 'email '.$sort;        break;
            case 'F': $order = 'address '.$sort;      break;
            case 'G': $order = 'custom_number '.$sort;break;
            default: $order = 'id '.$sort;
        }
        $count = $m->where(array('enabled'=>$enabled))->count();
        $start_id = ( $pageNow - 1 ) * $pageSize;
        $counts = ceil($count/$pageSize);
        $list  = $m->where(array('enabled'=>$enabled))->order($order)->limit($start_id,$pageSize)->select();
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
        $this->response($data);
    }

    /**
     * 客户信息修改
     * @param data   需要修改的数据包
     * @param id     客户id
     */
    public function update()
    {
        $m = M('customer');
        $da = $_POST['data'];
        $id = $da['id'];
        unset($da['id']); // id赋值之后删除

        // 多人同时操作限制
        if(!limitOperation('customer' ,$id ,180 ,$this->loginid)) $this->response(['status' => 101 ,'msg' => '有同事在操作该数据']);
        if(isset($da['checked']))
        {
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
            } else {
                $data['status'] = 101;
                $data['msg']    = '修改失败';
            }
        }

        EndEditTime('customer' ,$id);
        $this->response($data);
    }

    /**
     * 客户信息删除
     * @param id 客户id 有数据类型和int类型单条id的两种传值方式
     */
    public function delete(){
        $m = M("customer");
        $m->startTrans();
        $id = I('id');
        $arr = array();
        if(!is_array($id)){    // 按照数组批量删除的方式删除数据
            $arr[] = $id;
        } elseif (is_array($id)){
            $arr = $id;
        }
        $i = 0;
        foreach($arr as $value){  // 循环删除
            $value = __sqlSafe__($value);
            $res = checkDataLimit('YF',$value);
            if($res == 1){

                if(!limitOperation('customer' ,$value ,180 ,$this->loginid ,'R')){
                    continue;
                }

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
        $this->response($data);
    }

    /**
     * 添加客户信息
     * @param custom_name 客户名
     * @param address     公司地址
     * @param enabled     是否启用
     */
    public function add(){

        $m = M('customer');
        $m->startTrans();
        $arr = $_POST['data'];  // 前台给的数据包
        $da['custom_name'] = __sqlSafe__($arr['custom_name']);
        $da['en_name']     = __sqlSafe__($arr['en_name']);
        $da['company']     = __sqlSafe__($arr['company']);
        $da['mobile']      = __sqlSafe__($arr['mobile']);
        $da['email']       = __sqlSafe__($arr['email']);
        $da['address']     = __sqlSafe__($arr['address']);
        $da['enabled']     = isset($arr['enabled']) ? (int)$arr['enabled'] : 1;
         // 验证
        if(!preg_match($this->rule_enname , $da['en_name'])){
            $data['status'] = 102;
            $data['msg']    = '英文名填写错误';
            $this->response($data);
        }
        if(!empty($arr['mobile'])){
            if(!preg_match($this->rule_mobile , $arr['mobile'])){
                $data['status'] = 102;
                $data['msg']    = '手机号填写错误';
                $this->response($data);
            }
            $da['mobile'] = $arr['mobile'];
        }
        if(!empty($arr['email'])){
            if(!preg_match($this->rule_email , $arr['email'])){
                $data['status'] = 102;
                $data['msg']    = '邮箱填写错误';
                $this->response($data);
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
        $this->response($data);
    }

    // 编号获取，自动填充位数为 0
    public function auto_add_zero($code){
        $data_code = M('data_code');
        $where['code'] = $code;
        $sql = $data_code->where($where)->find();
        $databasecode = '01';
        $businesscode = $databasecode . $sql['code'].$sql['number'];
        $data['number'] = str_pad($sql['number']+1,8,"0",STR_PAD_LEFT);
        $data['update_time'] =date('Y-m-d H:i:s',time());
        $data_code->where($where)->save($data);
        return $businesscode;
    }

    /*
     * 模糊搜索客户信息
     * @param keyword 搜索关键词
     */
    public function selVague(){
        $text = I('post.keyword');
        if(empty($text)){
            $data['status'] = 102;
            $data['msg']    = '关键词必填';
            $this->response($data);
        }
        // 如果有分号替换掉
        $text = __sqlSafe__($text);
        // 中英文名称
        $arr = M('customer')
            ->where("en_name LIKE '%".$text."%' OR custom_name LIKE '%".$text."%'")
            ->select();

        if($arr){
            $data['status'] = 100;
            $data['value']  = $arr;
        }else{
            $data['status'] = 101;
            $data['msg']    = '暂无相关信息';
        }
        $this->response($data);
    }

    /*
     * 表格移交专属客户搜索
     * */
    public function TransformSearchCustomer()
    {
        $users = M()
            ->table("tbl_auth_role_user ru")
            ->join("tbl_auth_user u ON u.id=ru.user_id")
            ->where("u.id = ".$this->loginid)
            ->field("ru.role_id")
            ->select();

        $where = "";
        $us = M("auth_user")->find($this->loginid);
        if($us['is_head'] == 0) $where = "u.is_head = 0 AND";

        if(!$users) $this->response(['status' => 101 ,'msg' => '暂无相关信息']);
        foreach($users as $val){
            $roles[] = M()
                ->table("tbl_auth_role_user ru")
                ->join("tbl_auth_user u ON u.id=ru.user_id")
                ->where("$where u.enabled=1 AND ru.role_id = ".$val['role_id'])
                ->field("u.id,u.username,u.real_name")
                ->select();
        }
        foreach($roles as $rval){
            foreach($rval as $v){
                $arr[] = $v;
            }
        }
        $this->response(['status' => 100 ,'value' => $arr]);
    }
}