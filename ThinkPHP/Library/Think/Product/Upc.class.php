<?php

namespace Think\Product;

class  Upc {

    // 拉取upc
    public static function get_upc($where,$page,$pagesize = 25){
        $u = M('product_upc_code');
        $sql = $u->count();
        if($sql == 0){
            $res['error']  = 0;
            $res['status'] = 110;
            $res['msg']    = '暂无UPC数据';
            return $res;
            exit();
        }
        if(!empty($page)){
            $start_id = ( $page - 1 ) * $pagesize;
            if(empty($where)){
                $res['error']  = 1;
                $res['status'] = 102;
                $res['msg']    = '查询信息有误';
            }else{
                $count   = $u->where($where)->count();
                $counts  = ceil($count/$pagesize);
                $result  = $u->where($where)->order('id asc')->limit($start_id,$pagesize)->select();
                $allupc  = $u->count();
                $usedupc = $u->where("enabled=0")->count();
                $upc = $u->where("enabled=1 and locked=0")->count();
                $lockedupc = $u->where("enabled=1 and locked=1")->count();

                if($result){
                    $res['error']     = 0;
                    $res['status']    = 100;
                    $res['value']     = $result;
                    $res['count']     = $counts;
                    $res['allupc']    = $allupc;
                    $res['usedupc']   = $usedupc;
                    $res['upc']       = $upc;
                    $res['pageNow']   = $page;
                    $res['lockedupc'] = $lockedupc;
                }else{
                    $res['error']  = 1;
                    $res['status'] = 101;
                    $res['msg']    = '暂无相关信息';
                }                
            }

        }else{
            $res['error']  = 1;
            $res['status'] = 102;
            $res['msg']    = '页码有误';
        }
        return $res;
    } 
    
    /*
    * 调用upc
    */
    public static function use_upc($num)
    {
        $u = M('product_upc_code');
        $u->startTrans();
        $count = $u->where(array('enabled'=>1,'locked'=>0))->count();
        if($count >= $num){        //  可用upc不够数量
            $result = $u->where(array('enabled'=>1,'locked'=>0))->order('id asc')->limit($num)->select();
            if($result){
                $data['locked'] = 1;
                $lockNum = 0;
                foreach ($result as $key => $value) {
                    $lock = $u->where("id=".$value['id'])->save($data); // 锁定拉取的upc
                    if($lock){
                        $lockNum += 1;
                    } 
                }
                if($lockNum == $num){  // 锁定个数
                    $res['error'] = 0;
                    $res['value'] = $result;
                    $u->commit();
                }else{
                    $res['error']  = 1;
                    $res['status'] = 103; //  锁定upc出错
                    $res['msg']    = '锁定upc出错';
                    $u->rollback();
                }
            }else{
                $res['error']  = 1;
                $res['status'] = 101; //  调用失败 异常
                $res['msg']    = '调用失败';
            }
        }else{
            $res['error']  = 1;
            $res['status'] = 104;  //  可用upc不够数量
            $res['msg']    = 'upc数量不够';
        }
        return $res;
    }

    //匹配UPC
    static function marry_upcs($form_id){
        set_time_limit(0);

        $has_form = M('product_batch_form')->where(array('id'=>$form_id))->find();
        if(!$has_form){
            return ['status' => 105 , 'error' => 1 , 'msg' => '表格有误'];
        }

        $upcDatabase = M('product_upc_code');
        $data_info   = M('product_batch_information');
        $data_info_p = M('product_batch_form_information');

        $is_marry = $upcDatabase->where(array('form_id' => $form_id))->select();
        if($is_marry){
            return ['status' => 103 , 'error' => 1 , 'msg' => '已经匹配过upc'];
        }
        $need_upc = 0;
        $pid  = $data_info_p->field('product_id')->where(array('form_id'=>$form_id))->select();
        $res = [];

        $f = "id,product_id,parent_id,title,interger_value,char_value";
        foreach($pid as $pk => $pv){
            $s[] = $pv['product_id'];
            $res[]   = $data_info->where(array('product_id'=>$pv['product_id'],'title'=>'external_product_id'))->field($f)->find();
        }

        $parent_id = [];
        foreach($res as $k => $v){
            $parent_id[] = $v['parent_id'];
        }

        foreach($pid as $key => $val){
            if(!in_array($val['product_id'] , $parent_id)){
                $need_upc ++;
            }
        }

        if($need_upc == 0){
            return ['status' => 101 , 'error' => 1 , 'msg' => '没有产品资料匹配'];
        }

        $upc_arr = $upcDatabase->where(array('locked' => 0 , 'enabled' => 1))->order('id asc')->limit($need_upc)->select();
        if(count($upc_arr) < $need_upc){
            return ['status' => 101 , 'error' => 1 , 'msg' => 'upc不够数量，尽快上传'];
        }

        $lock_num = 0;
        foreach($upc_arr as $upc_key => $upc_val){
            $upc_val['locked']  = 1;
            $upc_val['form_id'] = $form_id;
            $lock_upc = $upcDatabase->save($upc_val);
            if($lock_upc){
                $lock_num ++;
            }
        }
        if($lock_num != $need_upc){
            $reback['locked']  = 0;
            $reback['form_id'] = 0;
            $upcDatabase->where(array('form_id'=>$form_id))->save($reback);
            return ['status' => 101 , 'error' => 1 , 'msg' => 'upc锁定出错'];
        }
        $upcs = [];
        foreach($upc_arr as $upc_k => $upc_v){
            $upcs[] = $upc_v['upc_code'];
        }

        $data_info->startTrans();
        foreach($res as $key => $val){
            if(!in_array($val['product_id'] , $parent_id)){
                //$data_c['interger_value'] = $upcs[count($upcs) - 1];
                $data_c['char_value'] = $upcs[count($upcs) - 1];
                $data_c['data_type_code'] = 'upc_code';
                $sa = $data_info->where(array('id'=>$val['id']))->save($data_c);
                if(!$sa){
                    $data_info->rollback();
                    return ['status' => 104 , 'error' => 1 , 'msg' => '写入失败'];
                }
                unset($upcs[count($upcs) - 1]);
            }
        }
        $data_info->commit();

        return ['status' => 100 , 'error' => 0];
    }
}
