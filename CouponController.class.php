<?php
namespace Home\Controller;
use Think\Controller;
/**
 * 优惠券控制器
 * @author cxl,lrf
 * @modify 2016/12/22
 */
class CouponController extends BaseController
{
    //电话，邮箱判断，
    public $m_rule = '/^1[34578]{1}\d{9}$/';
    public $e_rule = '/^([0-9A-Za-z\\-_\\.]+)@([0-9a-z]+\\.[a-z]{2,3}(\\.[a-z]{2})?)$/i';
    public $d_rule = '/^[1-9][0-9]{3}-(0[1-9]|1[0-2])-([0-2][1-9]|3[0-1]) ([01]\d|2[0-3]):([0-5][0-9]):([0-5][0-9])$/';
    public $t_rule = '/^\d{12,20}$/';
    
    
    //批次优惠码生成
    public function couponsCreate(){
        
//        $_POST['name'] = '测试用码';
//        $_POST['type'] = '1';
//        $_POST['min_amount'] = 0;
//        $_POST['denomination'] = '100';
//        $_POST['price'] = 50;
//        $_POST['issuant_amount'] = 20;
//        $_POST['restriction_amount'] = 0;
//        $_POST['validity_begin'] = '2017-04-11';
//        $_POST['validity_end'] = '2017-04-15';
//        $_POST['remark'] = '测试优惠券';
//        $_POST['issuant_status'] = 1;
  
      
        if($_POST){
        $num =  $_POST['issuant_amount'];
        $coupon_data = array();
        $coupon_data['name'] = $_POST['name'] ;//名称
        $coupon_data['type'] = $_POST['type'];//类型
        $coupon_data['min_amount'] = $_POST['min_amount'];//最低消费额度
        $coupon_data['denomination'] = $_POST['denomination'];//现金券面值
        $coupon_data['price'] = $_POST['price'];//现金券价格
        $coupon_data['issuant_amount'] = $num;//发行数目
        $coupon_data['restriction_amount'] = $_POST['restriction_amount'];//使用限制
        $coupon_data['validity_begin'] = $_POST['validity_begin'];//有效期开始
        $coupon_data['validity_end'] = $_POST['validity_end'];//有效期结束
        $coupon_data['remark'] = $_POST['remark'];//备注
        $coupon_data['issuant_status'] = $_POST['issuant_status'];//发行状态
        
        $date_time = date('Y-m-d H:i:s',time());
        $coupon_data['creator_id'] = $this->loginid;//创建者
        $coupon_data['created_time'] = $date_time;//
        
        
        //将优惠码写入表里面，检测数据是否有误
        //必须检测的数据：发行数量，面值，价格，发行状态
        if(empty($num) || !isset($coupon_data['denomination']) || !isset($coupon_data['price']) || empty($coupon_data['issuant_status'])){
                $this->response(['status' => 102, 'msg' => '数据不完善','data'=>$_POST],'json');
        }
        //主表写入
        $main_id = M('coupon_main')->add($coupon_data);
            //发行状态的优惠码，执行次表写入，提取数据,获取优惠码
            if($coupon_data['issuant_status'] == 2 && !empty($main_id)){
            $code_arr = $this->getCouponCode($num);   
            $where_detail['main_id'] = $main_id;
            $where_detail['creator_id'] = $this->loginid;
            $where_detail['created_time'] = $date_time;
            $where_detail['code_all'] = $code_arr;
            $main_id = $this->createCouponList($where_detail);
            }
            if($main_id){
              $this->response(['status' => 100],'json');  
            }else{
               $this->response(['status' => 101, 'msg' => '发行有误'],'json'); 
            }
        }else{
             $this->response(['status' => 103, 'msg' => '请求失败'],'json');
        }
        
        
    }
    
    //发布优惠码
    public function releaseCoupon(){
       //  $_POST['id'] = 8; 
        if($_POST){
            $id = $_POST['id']; 
            //主表写入
            $main_data = M('coupon_main')->where(array('id'=>$id))->find();
            $date_time = date('Y-m-d H:i:s',time());
            //发行状态的优惠码，执行次表写入，提取数据,获取优惠码
            if($main_data['issuant_status'] == 1){
            $code_arr = $this->getCouponCode($main_data['issuant_amount']);   
            $where_detail['main_id'] = $main_data['id'];
            $where_detail['creator_id'] = $this->loginid;
            $where_detail['created_time'] = $date_time;
            $where_detail['code_all'] = $code_arr;
            $main_id = $this->createCouponList($where_detail);
            }
            if($main_id){
              $this->response(['status' => 100],'json');  
            }else{
               $this->response(['status' => 101, 'msg' => '发行有误'],'json'); 
            }
        }else{
           $this->response(['status' => 103, 'msg' => '请求失败'],'json'); 
        }
    }
    
    
    //批次优惠码查询列表
    public function couponsList(){    
     //接收条件 生效和准备和所有，搜索内容，模糊搜索优惠券名称，展示的条数，以及当前页码  
//    $_POST['type'] = 1;     
//    $_POST['search'] = '测试';     
//    $_POST['num'] = 3;     
//    $_POST['page'] = 1;     
    if($_POST){    
    $type = $_POST['type'];    
    $search = $_POST['search'];    
    $num = $_POST['num'];    
    $page = $_POST['page'];    
    //设置条件 
    $where = array();
    if(!empty($type)){
    $where['type'] = $type;
    }
    if(!empty($search)){
    $where['name'] = array('like','%'.$search.'%');
    }
    if(!isset($page)){
      $page = 1;  
    }
    if(!isset($num)){
      $num = 10;  
    }
    $first = $num*($page-1);
    $coupons_data = M('coupon_main')->where($where)->field("id,name,type,denomination,price,issuant_amount,validity_begin,validity_end,issuant_status")->limit($first,$num)->select();   
     //获取当前页和总页数
    $count = M('coupon_main')->where($where)->count();
    $page_data['page'] = $page;
    $page_data['count_page'] = ceil($count/$num);
    
    foreach($coupons_data as $key=>$val){
        
        if($val['type'] == 1){
         $coupons_data[$key]['type_msg'] = '现金优惠券';   
        }else if($val['type'] == 2){
         $coupons_data[$key]['type'] = '折扣优惠券';      
        }
        if($val['issuant_status'] == 1){
        $coupons_data[$key]['issuant_status_msg'] = '生效';       
        }else if($val['issuant_status'] == 2){
        $coupons_data[$key]['issuant_status_msg'] = '准备';       
        }else if($val['issuant_status'] == 5){
        $coupons_data[$key]['issuant_status_msg'] = '作废';       
        }
        $coupons_data[$key]['validity_begin'] = date('Y-m-d',strtotime($val['validity_begin']));
        $coupons_data[$key]['validity_end'] = date('Y-m-d',strtotime($val['validity_end']));
        $coupons_data[$key]['price'] = '￥'.$val['price'];
        $coupons_data[$key]['denomination'] = '￥'.$val['denomination'];
        
    }
    
    $this->response(['status' => 100,'value'=>$coupons_data,'pages'=>$page_data],'json');  
    }else{
       $this->response(['status' => 103, 'msg' => '请求失败'],'json'); 
    }
    }
    
    
   //批次优惠码删除
    public function couponsDelete(){
     //执行把优惠码全部删除
    
     $id = $_POST['id'];
     if($_POST){
        $where['id'] = $id;
        //查询是否是未准备的
        $res = M('coupon_main')->where(array('id'=>$id,'issuant_status'=>2))->find();
        if($res){
        M('coupon_main')->where($where)->delete();  
        }else{
        $this->response(['status' => 102, 'msg' => '该批次优惠码状态不可删除'],'json');       
        }
        $this->response(['status' => 100],'json');   
     }else{
        $this->response(['status' => 103, 'msg' => '请求失败'],'json');  
     }
           
    }
    
    //批次优惠码作废
    public function couponsVoid(){
       
        $id = $_POST['id'];
        if($_POST){
        $where['id'] = $id;
        //查询是否是未准备的
        $res = M('coupon_main')->where(array('id'=>$id,'issuant_status'=>1))->find();
        if($res){
            //作废为0，把子表的状态等于1的设置为6
            $main_save = array();
            $main_save['issuant_status'] = 5;
            $main_save['modified_time'] = date('Y-m-d H:i:s',time());
            $main_save['creator_id'] = $this->loginid;
            M('coupon_main')->where($where)->save($main_save); 
            $where_detail = array();
            $where_detail['main_id'] = $id;
            $where_detail['status'] = 1;
            $detail_save = array();
            $detail_save['status'] = 5;
            $detail_save['modified_time'] = date('Y-m-d H:i:s',time());
            $detail_save['creator_id'] = $this->loginid;
            M('coupon_detail')->where($where_detail)->save($detail_save);  
            $value =  array('issuant_status'=>5,'issuant_status_msg'=>'作废');
            $this->response(['status' => 100, 'value' => $value],'json');  
        }else{
        $this->response(['status' => 102, 'msg' => '该批次优惠码状态不可作废'],'json');       
        }
        
     }else{
        $this->response(['status' => 103, 'msg' => '请求失败'],'json');  
     }
    }
    
    
    
    //优惠码查询,展示一条优惠码信息
    public function couponFind(){
        
   //     $_POST['id'] = 6;
        if($_POST){ 
            $id = $_POST['id'];
           //获取父级和次优惠券的信息
            $detail_data = M('coupon_detail')->where(array('id'=>$id))->find();
            $main_data = M('coupon_main')->where(array('id'=>$detail_data['main_id']))->find();
            
            
           if($detail_data){
            $detail_data = $this->dealDetailData($detail_data);    
            $main_data = $this->dealCouponData($main_data);

            $this->response(['status' => 100, 'detail' => $detail_data,'main'=>$main_data],'json');  
           }else{
              $this->response(['status' => 102, 'msg' => '数据不存在'],'json');      
           }
            
        }else{
         $this->response(['status' => 103, 'msg' => '请求失败'],'json');     
        }
        
        
    }
    
    //处理输出的批次优惠券信息,对输入的一条优惠券信息进行处理，以符合前端调用标准
    private function dealCouponData($coupons_data){
        
       if($coupons_data['type'] == 1){
        $coupons_data['type_msg'] = '现金优惠券';     
       }else if($coupons_data['type'] == 2){
        $coupons_data['type_msg'] = '折扣优惠券';      
       }
       
       switch ($coupons_data['issuant_status']){
            case 1:
                    $coupons_data['issuant_status_msg'] = '准备';     
                break;
            case 2:
                    $coupons_data['issuant_status_msg'] = '发行';     
                break;
            case 3:
                    $coupons_data['issuant_status_msg'] = '使用';     
                break;
            case 4:
                    $coupons_data['issuant_status_msg'] = '完成';     
                break;
            case 5:
                    $coupons_data['issuant_status_msg'] = '作废';     
                break;
            case 6:
                    $coupons_data['issuant_status_msg'] = '过期失效';     
                break;
            default:
                    $coupons_data['issuant_status_msg'] = '未知状态';     
            }
        //判断此优惠券是否已经失效？调用检测的方法，可能调用修改的失效方法
        if($coupons_data['issuant_status'] != 6){    
            $status = $this->couponDateCheck(array('begin'=>$coupons_data['validity_begin'],'end'=>$coupons_data['validity_end']));    
//            if($status != 1){
//                //执行数据库数据失效处理
//                $coupons_data['issuant_status_msg'] = '过期失效'; 
//                $this->mainDataInvalid();
//            }    
        }
        
       if($coupons_data['restriction_amount'] == 0){
        $coupons_data['restriction_amount_msg'] = '不限';     
       }else{
        $coupons_data['restriction_amount_msg'] = $coupons_data['restriction_amount'].'张';      
       }
       $coupons_data['min_amount'] = $coupons_data['min_amount'].'元';
       $coupons_data['issuant_amount'] = $coupons_data['issuant_amount'].'张';
       $coupons_data['denomination'] = $coupons_data['denomination'].'元';
       $coupons_data['price'] = $coupons_data['price'].'元';
       
       $coupons_data['validity_begin'] = date('Y-m-d',strtotime($coupons_data['validity_begin']));
       $coupons_data['validity_end'] = date('Y-m-d',strtotime($coupons_data['validity_end']));
       $coupons_data['created_time'] = date('Y-m-d',strtotime($coupons_data['created_time']));
       
       //数据操作查询处理
       $coupons_data['creator_id_msg'] = M('auth_user')->where(array('id'=>$coupons_data['creator_id']))->getField("real_name"); 
       
       return $coupons_data;
        
    }
    
    //处理详细的数据
    public function dealDetailData($detail_data){
        switch ($detail_data['status']){
            case 1:
                    $detail_data['status_msg'] = '待发放';
                break;
            case 2:
                    $detail_data['status_msg'] = '已发放';
                break;
            case 3:
                    $detail_data['status_msg'] = '待审核';
                break;
            case 4:
                    $detail_data['status_msg'] = '已使用';
                break;
            case 5:
                    $detail_data['status_msg'] = '审核失败';
                break;
            case 6:
                    $detail_data['status_msg'] = '作废';
                break;
            case 7:
                    $detail_data['status_msg'] = '过期失效';
                break;
            }
            if($detail_data['granted_date']){
            $detail_data['granted_date'] = date('Y-m-d',strtotime($detail_data['granted_date']));;
            }
            if($detail_data['granted_date']){
            $detail_data['consume_date'] = date('Y-m-d',strtotime($detail_data['consume_date']));
             }
            $detail_data['created_time'] = date('Y-m-d',strtotime($detail_data['created_time']));   
            return $detail_data;
    }
    
    
    //优惠码列表
    public function couponList(){
        //优惠码列表：接收优惠码批次ID，获取所有优惠码，接收页，接收条数
//        $_POST['main_id'] = 3;
//        $_POST['num'] = 5;
//        $_POST['page'] = 2;
       if($_POST){ 
        //查询发行用户ID获取用户名，查询优惠码表获取发行人
       $id = $_POST['main_id'];
       $num = $_POST['num'];
       $where = array();
       $where['id'] = $id;
       $coupons_data = M('coupon_main')->where($where)->find();
       
       //数据处理
       $coupons_data = $this->dealCouponData($coupons_data);
       
        //获取优惠码列表
        $where_detail = array();
        if(!isset($page)){
          $page = 1;  
        }
        if(!isset($num)){
          $num = 10;  
        }
        $first = $num*($page-1);
        
        $coupon_detail_data = M()
            ->table("tbl_coupon_detail c,tbl_auth_user u")
            ->where("c.`creator_id`=u.`id` AND c.`main_id`=$id")
            ->field("c.id,c.main_id,c.code,c.status,c.created_time,c.granted_date,c.holder,c.holder_telephone,c.consume_date,c.consumer,c.consumer_telephone,u.real_name")
            ->limit($first,$num)->select();
        
        $count = M('coupon_detail')->where(array('main_id'=>$id))->count();
        $page_data['page'] = $page;
        $page_data['count_page'] = ceil($count/$num);
        
        foreach($coupon_detail_data as $key => $val){
            $coupon_detail_data[$key] = $this->dealDetailData($val); 
        }
        $coupon['main'] = $coupons_data;
        $coupon['detail'] = $coupon_detail_data;
        $coupon['pages'] = $page_data;         
        $this->response(['status' => 100, 'value' => $coupon],'json');  
        
      }else{
        $this->response(['status' => 103, 'msg' => '请求失败'],'json');  
     }  
    } 
    
    
   

    //优惠码作废
    public function couponVoid(){
        //获取优惠码ID，对其作废
        $_POST['id'] = 1;
        $id = $_POST['id'];
        if($_POST){
        $where['id'] = $id;
        //查询是否是未准备的
        $res = M('coupon_detail')->where(array('id'=>$id,'status'=>1))->find();
        if($res){
            //作废为5，把状态等于1的设置为5
            $detail_save = array();
            $detail_save['status'] = 6;
            $detail_save['modified_time'] = date('Y-m-d H:i:s',time());
            $detail_save['creator_id'] = $this->loginid;
            $res = M('coupon_detail')->where($where)->save($detail_save); 
            if($res){
            $value =  array('status'=>6,'status_msg'=>'作废');
            $this->response(['status' => 100, 'value' => $value],'json'); 
            }else{
            $this->response(['status' => 101, 'msg' => '操作失败，请重试'],'json');       
            }
        }else{
        $this->response(['status' => 102, 'msg' => '该优惠码状态不可作废'],'json');       
        }
        
     }else{
        $this->response(['status' => 103, 'msg' => '请求失败'],'json');  
     }
        
        
    }
    
    
    //优惠码编辑接口
    public function couponEdit(){
        
        //判断情况，当状态等于1等于2…………，不同状态的可编辑内容是不同的，输入优惠券ID
//        $_POST['id'] = 3;
//        $_POST['order_no'] = '7418529630159753';
//        $_POST['granted_date'] = '2017-09-17 22:23:20';
//        $_POST['holder'] = 'name_chen';
//        $_POST['holder_telephone'] = '13345214568';
//        $_POST['holder_email'] = '3524786@qq.com';

        
        $id = $_POST['id'];
        if($_POST){       
          //查询当前的内容
          $coupon_detail_data = M('coupon_detail')->where(array('id'=>$id))->find();  
          
          //判断是否已经过期，如果已经过期，代表失效,获取批次的时间信息
          $main_id = $coupon_detail_data['main_id'];
          $time_data = M('coupon_main')->where(array('id'=>$main_id))->field("validity_begin begin,validity_end end")->find();

           $remark = trim($_POST['remark']);
           $order_no = trim($_POST['order_no']);
           $granted_date = $_POST['granted_date'];
           $holder = trim($_POST['holder']);
           $holder_telephone = trim($_POST['holder_telephone']);
           $holder_email = trim($_POST['holder_email']);
           $consume_date = $_POST['consume_date'];
           $consumer = trim($_POST['consumer']);
           $consumer_telephone = trim($_POST['consumer_telephone']);
           $consumer_email = trim($_POST['consumer_email']);
           $status = $_POST['status'];
          
           //修改的时间和操作人
           $save_detail = array();
           $time = date('Y-m-d H:i:s',time());
           $save_detail['creator_id'] = $this->loginid;
           $save_detail['modified_time'] = $time;
            
           
           switch ($coupon_detail_data['status']){
            case 1:
                //可以输入淘宝订单号，销售日期，持有人姓名，电话，邮箱。待发放
               if(empty($order_no) || empty($granted_date) || empty($holder) || empty($holder_telephone)|| empty($holder_email)){
                   $this->response(['status' => 102, 'msg' => '数据不完整'],'json');
               }
                //对内容进行验证，如果有问题，就返回，如果失效，就改状态
                if (!empty($granted_date)) {
                if (!preg_match($this->d_rule, $granted_date)) {
                    $this->response(['status' => 102, 'msg' => '时间格式不正确'],'json');
                    }
                }
               if (!empty($holder_telephone)) {
                if (!preg_match($this->m_rule, $holder_telephone)) {
                    $this->response(['status' => 102, 'msg' => '手机格式不正确'],'json');
                    }
                }
                if (!empty($holder_email)) {
                    if (!preg_match($this->e_rule, $holder_email)) {
                        $this->response(['status' => 102, 'msg' => '邮箱格式不正确'],'json');
                    }
                }
                if (!empty($order_no)) {
                if (!preg_match($this->t_rule, $order_no)) {
                    $this->response(['status' => 102, 'msg' => '淘宝订单号格式不正确'],'json');
                    }
                }
                //检测是否失效
               $time_data['modern'] =  $granted_date;
               $time_status = $this->couponDateCheck($time_data);
               $save_detail['order_no'] = $order_no;
               $save_detail['granted_date'] = $granted_date;
               $save_detail['holder'] = $holder;
               $save_detail['holder_telephone'] = $holder_telephone;
               $save_detail['holder_email'] = $holder_email;

               if($time_status != 1){
                   //失效，将此条信息设置为失效，并备注，返回不成功信息
                $save_detail['status'] = 7;   
                $save_detail['remark'] = $coupon_detail_data['remark']."\n此优惠券购买日期已失效。";  
                $res = M('coupon_detail')->where(array('id'=>$id))->save($save_detail);
                if($res){
                 $this->response(['status' => 104, 'msg' => '此优惠券购买日期已失效'],'json');      
                }else{
                 $this->response(['status' => 101, 'msg' => '信息编辑异常'],'json');   
                }
               }else{
                   //将数据保存进来，改变状态，并发送成功后的数据过去
                $save_detail['status'] = 2;   
                $res = M('coupon_detail')->where(array('id'=>$id))->save($save_detail);
                if($res){
                 $this->response(['status' => 100],'json');      
                }else{
                 $this->response(['status' => 101, 'msg' => '信息编辑异常'],'json');   
                }
               }

                break;
            case 2:
                //可以输入备注，发放日期，，使用人姓名，电话，邮箱。已经发放，待录入
                if(empty($consume_date) || empty($consumer) || empty($consumer_telephone) || empty($consumer_email)){
                   $this->response(['status' => 102, 'msg' => '数据不完整'],'json');
                }
                //对内容进行验证，如果有问题，就返回，如果失效，就改状态
                if (!empty($consume_date)) {
                if (!preg_match($this->d_rule, $consume_date)) {
                    $this->response(['status' => 102, 'msg' => '时间格式不正确'],'json');
                    }
                }
               if (!empty($consumer_telephone)) {
                if (!preg_match($this->m_rule, $consumer_telephone)) {
                    $this->response(['status' => 102, 'msg' => '手机格式不正确'],'json');
                    }
                }
                if (!empty($consumer_email)) {
                    if (!preg_match($this->e_rule, $consumer_email)) {
                        $this->response(['status' => 102, 'msg' => '邮箱格式不正确'],'json');
                    }
                }
                 //检测是否失效
               $time_data['modern'] =  $consume_date;
               $time_status = $this->couponDateCheck($time_data);
               $save_detail['consume_date'] = $consume_date;
               $save_detail['consumer'] = $consumer;
               $save_detail['consumer_telephone'] = $consumer_telephone;
               $save_detail['consumer_email'] = $consumer_email;

               if($time_status != 1){
                   //失效，将此条信息设置为失效，并备注，返回不成功信息
                $save_detail['status'] = 7;   
                $save_detail['remark'] = $coupon_detail_data['remark']."\n此优惠券使用日期已失效。";  
                $res = M('coupon_detail')->where(array('id'=>$id))->save($save_detail);
                if($res){
                 $this->response(['status' => 104, 'msg' => '此优惠券使用日期已失效'],'json');      
                }else{
                 $this->response(['status' => 101, 'msg' => '信息编辑异常'],'json');   
                }
               }else{
                   //将数据保存进来，改变状态，并发送成功后的数据过去
                $save_detail['status'] = 3;   
                $res = M('coupon_detail')->where(array('id'=>$id))->save($save_detail);
                if($res){
                    
                 $this->response(['status' => 100],'json');      
                }else{
                 $this->response(['status' => 101, 'msg' => '信息编辑异常'],'json');   
                }
               }
                break;
            case 3:
                //可以输入备注，审核状态，是通过还是拒绝。已经录入，待审核
               if(!empty($remark)){
                 $save_detail['remark'] = $remark;  
               } 
               $save_detail['status'] = $status; 
             
               if(!in_array($status,array(4,5))){
                   
               $this->response(['status' => 102, 'msg' => '审核状态不正常'],'json');    
               }
               $res = M('coupon_detail')->where(array('id'=>$id))->save($save_detail);
               if($res){
                   
                 $this->response(['status' => 100],'json');      
                }else{
                 $this->response(['status' => 101, 'msg' => '信息编辑异常'],'json');   
                }
                break;
            default:
               $this->response(['status' => 105, 'msg' => '此状态不可编辑'],'json');       
            }
            exit;
        }else{
        $this->response(['status' => 103, 'msg' => '请求失败'],'json');      
        }
   
    }
    
    
    
    
    
    
    
    //优惠码下载
    public function couponDownload(){
        
        $_REQUEST['main_id'] = 2;
        if($_REQUEST['main_id']){        
        $main_id = $_REQUEST['main_id'];
        //文件生成
        $file_path_arr = $this->createCouponFile($main_id);
        //文件下载
        $filename = $file_path_arr['file_name'];
        header("Content-type: text/html; charset=utf-8"); //指定文件类型
        header('Content-Disposition: attachment; filename="'.$filename.'"'); //指定下载文件的描述
        header('Content-Length:'.filesize($filename)); //指定下载文件的大小
        //将文件内容读取出来并直接输出，以便下载
        readfile($file_path_arr['file_path']);
        }else{
        $this->response(['status' => 103, 'msg' => '请求失败'],'json');  
     }
        
    }
    
    //批量生成优惠码,已知优惠编码，批次ID，用户ID，时间，生成优惠码
    private function createCouponList($where_detail){

        foreach($where_detail['code_all'] as $val){
            $detail_arr = array();
            $detail_arr['main_id'] = $where_detail['main_id'];
            $detail_arr['code'] = $val;
            $detail_arr['status'] = 1;
            $detail_arr['creator_id'] = $where_detail['creator_id'];
            $detail_arr['created_time'] = $where_detail['created_time'];
            $res = M('coupon_detail')->add($detail_arr);
            if(!$res){
               return $res; 
            }  
        }
        return $res;  
    }
    

        //获取生成的优惠码列表
    private function getCouponCode($num){
        
        $coupon_all_arr = $auth = M('coupon_detail')->where(["auth_address" => $authUrl])->getField('code',true);

        $coupon_arr =  $this->createBatchCode($num,$coupon_all_arr,8);

        return $coupon_arr;   
    }

    //获取批次优惠码，带入需求个数，已存在的优惠码列表，优惠码长度
    private function createBatchCode($no_of_codes,$exclude_codes_array='',$code_length =6)  
    {  
            $characters = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";  
            $promotion_codes = array();//这个数组用来接收生成的优惠码  
            for($j = 0 ; $j < $no_of_codes; $j++)  
                {  
            $code = "";  
            for ($i = 0; $i < $code_length; $i++)  
                {  
            $code .= $characters[mt_rand(0, strlen($characters)-1)];  
                }  
            //如果生成的6位随机数不再我们定义的$promotion_codes函数里面  
            if(!in_array($code,$promotion_codes))  
            {  
                if(is_array($exclude_codes_array))//  
                {  
                    if(!in_array($code,$exclude_codes_array))//排除已经使用的优惠码  
                        {  
                    $promotion_codes[$j] = $code;//将生成的新优惠码赋值给promotion_codes数组  
                    }  
                    else  
                        {  
                    $j--;  
                      }  
                }  else  {  
                $promotion_codes[$j] = $code;//将优惠码赋值给数组  
                   }  
            }  
            else  
                 {  
            $j--;  
               }  
            }  
            return $promotion_codes;  
    }  
    
    
    //优惠码下载处理,生成文件
    private function createCouponFile($main_id){
        
        //生成一个时间戳+用户ID的文件，删除暂时不考虑
        $time = time().$this->loginid;
        $file_path = "./public/data/".$time.".txt";
       
      
        $path = "./public/data/"; // 接收文件目录
            if (! file_exists ( $path )) {
                mkdir ( "$path", 0777, true );
        }
        
        
        $myfile = fopen($file_path, "w") or die("Unable to open file!");
        $where['main_id'] = $main_id;
        $coupon_data = M('coupon_detail')->where($where)->field("id,code,status")->select();

        $txt = "ID \n 优惠码编号 \n 状态码 \r\n";
        fwrite($myfile, $txt);
        foreach($coupon_data as $val){
        $txt = $val['id'] . "   \n ".$val['code'] . "    \n " . $val['status']." \r\n";
        fwrite($myfile, $txt);
        }
        fclose($myfile);  
        
        $file_arr['file_name'] = $time.".txt";
        $file_arr['file_path'] = $file_path;
        return $file_arr;
    }
    
    //输入的日期和有效期做判断，查看是否已经失效
    private function couponDateCheck($date_arr){
        if(empty($date_arr['modern'])){
            $date_arr['modern'] = date('Y-m-d H:i:s',time());
        }
        //时间转时间戳
        $begin = strtotime($date_arr['begin']);
        $end = strtotime($date_arr['end']);
        $modern = strtotime($date_arr['modern']);
        
        //当时间还未到，当时间相等或在之间，当时间已经过去，当开始时间大于结束时间，5个状态
        if($modern < $begin){
            $status = 2;
        }else if($begin <= $modern && $modern <= $end){
            $status = 1;
        }else if($modern > $end){
             $status = 3;
        }else if($begin >= $end){
            $status = 4;
        }else{
            $status = 5;
        }
        return $status;
    }
    //将批次优惠券数据失效处理
    private function mainDataInvalid($id){
        M('coupon_main')->where(array('id'=>$id))->setField('issuant_status',6); 
    }
    
    
}