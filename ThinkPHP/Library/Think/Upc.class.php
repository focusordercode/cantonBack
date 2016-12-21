<?php

namespace Think;

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
}
