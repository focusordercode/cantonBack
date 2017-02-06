<?php
namespace Think\Product;
/**
 * 产品资料表类
 */
class ProductInfo{
    private $arr=array();
	/**
     * 查询某一个表单的产品详细信息
     */
    static function GetOneFormInfo($type_code,$form_id,$status=''){
        if($type_code == 'info'){
            $forminfo    = M("product_form_information");
            $info        = M("product_information");
        }elseif ($type_code == 'batch') {
            $forminfo    = M("product_batch_form_information");
            $info        = M("product_batch_information");
        }

        $product_ids = $forminfo->field("product_id")->where("form_id=%d",array($form_id))->order("product_id asc")->select();//获取表单的相关产品id
        $arr         = [];
        $field       = 'id,category_id,template_id,product_id,parent_id,no,title,data_type_code,length,interger_value,char_value,decimal_value,date_value,boolean_value';

        $where['enabled'] = 1;
        foreach ($product_ids as $key => $value) {

            $sql = $info->field($field)->where(array('product_id'=>$value['product_id']))->order("no asc")->select();
            foreach ($sql as $key => $value) {
                $arr['product_id']    = $value['product_id'];
                $arr['parent_id']     = $value['parent_id'];
                // if($value['title'] == 'Image URL（主图）' || $value['title'] == ''){
                //     if(!empty($value['char_value'])){
                //         $arr['photo']          = 'http://'.$_SERVER['HTTP_HOST'].__ROOT__.substr($value['char_value'], 1);
                //     }                    
                // }
                switch ($value['data_type_code']) {
                    case 'upc_code': $arr[$value['title']]  = $value['char_value']; break;
                    case 'int':      $arr[$value['title']]  = $value['interger_value']; break;
                    case 'char':     $arr[$value['title']]  = $value['char_value'];     break;
                    case 'dc':       $arr[$value['title']]  = $value['decimal_value'];  break;
                    case 'dt':       $arr[$value['title']]  = date('Y-m-d',strtotime($value['date_value'])); break;
                    case 'bl':       $arr[$value['title']]  = $value['boolean_value'];  break;
                    case 'pic':      
                        $arr[$value['title']]  = $value['char_value'];
                        if(!empty($value['char_value'])){
                            $arr['photo'] = 'http://'.$_SERVER['HTTP_HOST'].__ROOT__.substr($value['char_value'], 1);
                        }     
                      break;
                }
                if($status != 'preview'){
                    $arr[$value['title'].'_t']   = $value['data_type_code'];
                    $arr[$value['title'].'_length'] = $value['length'];
                    $arr[$value['title'].'_id']     = $value['id'];
                    $arr[$value['title'].'_no']     = $value['no'];
                }
                
            }

            if(!empty($arr)){
                $data[] = $arr;
            }
        }

        $father = [];
        $childs = [];
        foreach($data as $rk => $rv){
            if($rv['parent_id'] == 0){
                $father[] = $rv;
                unset($data[$rk]);
            }else{
                $childs[] = $rv;
                unset($data[$rk]);
            }
        }

        foreach($father as $fk => $fv){
            $return[] = $fv;
            foreach($childs as $ck => $cv){
                if($cv['parent_id'] == $fv['product_id']){
                    $return[] = $cv;
                }
            }
        }

        return($return);
    }
    
    /**
     * 删除产品资料
     */
    static function DelProductInfo($type_code,$form_id,$id){
        if($type_code == 'info'){
            $info = M("product_information");
        }elseif ($type_code == 'batch') {
            $info = M("product_batch_information");
        }
        $info->startTrans();
        $data['enabled']=0;
    	$sql  = $info->data($data)->where("product_id=%d",array($id))->save();
        $test = $info->field("product_id")->where("parent_id=%d",array($id))->find();
        if($test){
            $query=$info->data($data)->where("parent_id=%d",array($id))->save();
            if($sql!=='flase' && $query!=='flase'){
               $info->commit(); 
               return 1;        
            }else{
               $info->rollback();
               return -1;
            }            
        }else{
            if($sql!=='flase'){
               $info->commit(); 
               return 1;        
            }else{
               $info->rollback();
               return -1;
            }
        }
    }

    /**
     * 添加产品资料
     */
    static function AddProductInfo($type_code,$data,$form_id,$pid,$type){
        $arr   = array();
        $array = array();
        if($type_code == 'info'){
            $fi    = M("product_form_information");
            $info  = M("product_information");
            $form      = M('product_form');
        }elseif ($type_code == 'batch') {
            $fi    = M("product_batch_form_information");
            $info  = M("product_batch_information");
            $form      = M('product_batch_form');
        }
        $info->startTrans();
        $n = count($data);
        for($i = 0;$i < $n;$i ++){
            $sql = $info->data($data[$i])->add();
            $array[] = $sql;
        }

        $max = count($pid);
        for($j = 0;$j < $max;$j ++){
            $fiarr['form_id']      = $form_id;
            $fiarr['product_id']   = $pid[$j];
            $fiarr['created_time'] = date('Y-m-d H:i:s',time());
            $fi->add($fiarr);
        }
        $field = 'title';
        for($i = 0;$i < count($arr);$i ++){
            $query = $info->field($field)->where("id=%d",array($array[$i]))->find();
            if(empty($query['title'])){
                $info->rollback();
                return -1;
            }
        }
        if($type == 'submit'){
            $status_code['status_code'] = 'editing4info';
            $form->where(array('id'=>$form_id))->save($status_code);
        }

        $info->commit();
        return 1;
    }

    /*
     * 额外添加变体
     */
    static function AddVariantInfo($num,$product_id,$data){
        $info=M("product_information");
        $info->startTrans();
        $app_code="product_information";
        $sequence=GetSysId($app_code,$num);
        $max=count($data);
        for ($j=0; $j < $num ; $j++) { 
            for ($i=0; $i < $max; $i++) { 
                $data[$i]['parent_id']=$product_id;
                $data[$i]['product_id']=$sequence[$j];
                $sql=$info->data($data[$i])->add();
                $arr[]=$sql;
            }
            $field="title";
            $nums=count($arr);
            for($z=0;$z<$nums;$z++){
                $query=$info->field($field)->where("id=%d",array($arr[$z]))->find();
                if(empty($query['title'])){
                    $info->rollback();
                    return -1;
                }  
            }
        }
        $info->commit();
        return 1;
    }


    /**
     * 查询相同类目的主体信息
     */
    static function GetCategoryInfo($type_code,$category_id,$template_id){
        $arr   = array();
        $where = array();
        $array = array();
        if($type_code == 'info'){
            $info = M("product_information");
        }elseif ($type_code == 'batch') {
            $info = M("product_batch_information");
        }

        $array['category_id'] = $category_id;
        $array['template_id'] = $template_id;
        $array['parent_id']   = 0;
        $array['enabled']     = 1;

        $sql = $info->field("product_id")->where($array)->group('product_id')->select();
        $num = count($sql);
        for($i = 0;$i < $num;$i ++){
            $where['product_id'] = $sql[$i]['product_id'];
            $where['enabled']    = 1;
            $where['parent_id']  = 0;
            $arr[] = $info->where($where)->order("no asc")->select();
        }
        $check = $info->field("product_id")->where($where)->find();
        if(empty($check['product_id'])){
            return 1;
        }else{
            return($arr);
        }
    }

    /*
     * 暂存提交接口类
     */
    static function infoControl($type_code,$addData,$data,$form_id,$pid,$type){
        $k = 0;
        if($type_code == 'info'){
            $info  = M("product_information");
            $fi    = M("product_form_information");
            $types = M('product_form');
        }else{
            $fi    = M("product_batch_form_information");
            $info  = M("product_batch_information");
            $types = M('product_batch_form');
        }

        $info->startTrans();
        $fi->startTrans();
        if(!empty($addData)){
            foreach($addData as $key => $value){
                $sql = $info->add($value);
                if($sql){
                    $k ++;
                }
            }
        }

        if(!empty($data)){
            foreach($data as $k => $v){
                $id = $v['id'];
                $v['modified_time'] = date("Y-m-d H:i:s",time());
                unset($v['id']);
                $sa = $info->where(array('id'=>$id))->save($v);
                if($sa){
                    $k ++;
                }
            }
        }

        if(!empty($pid)){
            $fiarr['form_id']      = $form_id;
            $fiarr['created_time'] = date('Y-m-d H:i:s',time());
            foreach($pid as $val){
                $fiarr['product_id'] = $val;
                $fi->add($fiarr);
            }
        }
        // 强行修改upc码为type_code
        $type_code_['data_type_code'] = 'upc_code';
        $fi->where(array('en_name'=>'external_product_id'))->save($type_code_);

        if($type == 'submit'){
            $status_code['status_code'] = 'editing';
            $types->where(array('id'=>$form_id))->save($status_code);
        }
        $info->commit();
        $fi->commit();
        return $k;
    }

    //根据资料表id获取批量表模板
    static function GetBatchTemplate($form_id){
        $batch = M('product_form');
        $query = $batch->field("template_id")->where("id=%d",array($form_id))->find();

        $product2batch = M('product_item2batch_item');
        $tid  = [];
        $sqls = $product2batch->field('template2_id')->where(array('template1_id'=>$query['template_id']))->group('template2_id')->select();
        if($sqls){
            foreach($sqls as $v){
                $tid[] = $v['template2_id'];
            }

            $wheres = implode(",",$tid);
            $sql = M('product_batch_template')->where('id in ('.$wheres.') and status_code="enabled"')->select();
        }else{
            $sql = "";
        }
        return $sql;
    }

    /*
     * 批量表工作流返回上一步
     * */
    static function back_step($form_id){
        set_time_limit(0);
        $m = M('product_batch_form');
        $n = M('product_batch_form_information');
        $k = M('product_batch_information');
        $data_constraint = M('data_constraint');
        $k->startTrans();
        $res = $m->where(['id'=>$form_id,'enabled'=>1])->find();
        switch($res['status_code']){
            case 'editing':
                $data['status_code'] = 'creating';
                $b = $m->where(array('id' => $form_id))->save($data);
                if($b){
                    $result = ['error' => 0 , 'status' => 100];
                }else{
                    $result = ['error' => 1 , 'status' => 101 , 'msg' => '返回失败'];
                }
                break;
            case 'creating':
                $p_id = $n->where(['form_id' => $form_id])->select();
                foreach($p_id as $v){
                    $k->where("product_id = %d" ,array($v['product_id'],$v['product_id']))->delete();
                }
                $n->where(['form_id' => $form_id])->delete();
                $m->where(['id' => $form_id])->delete();
                $where['app_code2'] = '';
                $where['data2_id'] = $form_id;
                $data_constraint->where($where)->delete();
                $result = ['error' => 0 , 'status' => 100];
                break;
            default: $result = ['error' => 1 , 'status' => 101 , 'msg' => '该步骤不允许返回'];
        }
        $k->commit();
        return $result;
    }

    static function del_product($type_code , $product_id){
        if($type_code == 'info'){
            $m = M('product_form_information');
            $n = M('product_information');
        }elseif($type_code == 'batch'){
            $m = M('product_batch_form_information');
            $n = M('product_batch_information');
        }

        $n->where(array('product_id' => $product_id))->delete();
        $bt = $n->where(array('parent_id' => $product_id,'enabled' => 1))->group('product_id')->select();
        if($bt){
            foreach($bt as $val){
                $m->where(array('product_id' => $val['product_id']))->delete();
            }
            $n->where(array('parent_id'  => $product_id))->delete();
        }
        $m->where(array('product_id' => $product_id))->delete();

        $res['error'] = 0;
        return $res;
    }


    /*
     * 数据检查获取接口
     */
    static function check_data_edit($type_code,$product_id){
        if($type_code == 'info'){
            $info = M("product_information");
        }elseif ($type_code == 'batch') {
            $info = M("product_batch_information");
        }

        $arr   = [];
        $where['enabled'] = 1;
        foreach ($product_id as $key => $value) {
            $sql = $info->where("product_id=%d or parent_id = %d",array($value,$value))->order("product_id asc")->select();
            foreach ($sql as $keys => $values) {
                $ar[] = $values['product_id'];
            }
        }
        $product = array_unique($ar);
        foreach ($product as $ke => $val) {

            $sql = $info->where("product_id=%d",array($val))->order("product_id asc")->select();
            foreach ($sql as $k => $value) {
                $arr['product_id']    = $value['product_id'];
                $arr['parent_id']     = $value['parent_id'];
                switch ($value['data_type_code']) {
                    case 'upc_code': $arr[$value['title']]  = $value['interger_value']; break;
                    case 'int':      $arr[$value['title']]  = $value['interger_value']; break;
                    case 'char':     $arr[$value['title']]  = $value['char_value'];     break;
                    case 'dc':       $arr[$value['title']]  = $value['decimal_value'];  break;
                    case 'dt':       $arr[$value['title']]  = date('Y-m-d',strtotime($value['date_value'])); break;
                    case 'bl':       $arr[$value['title']]  = $value['boolean_value'];  break;
                    case 'pic':      $arr[$value['title']]  = $value['char_value'];     break;
                }
                $arr[$value['title'].'_t']   = $value['data_type_code'];
                $arr[$value['title'].'_length'] = $value['length'];
                $arr[$value['title'].'_id']     = $value['id'];
                $arr[$value['title'].'_no']     = $value['no'];
            }
            if(!empty($arr)){
                $data[] = $arr;
            }
        }

        $father = [];
        $childs = [];
        foreach($data as $rk => $rv){
            if($rv['parent_id'] == 0){
                $father[] = $rv;
                unset($data[$rk]);
            }else{
                $childs[] = $rv;
                unset($data[$rk]);
            }
        }

        foreach($father as $fk => $fv){
            $return[] = $fv;
            foreach($childs as $ck => $cv){
                if($cv['parent_id'] == $fv['product_id']){
                    $return[] = $cv;
                }
            }
        }
        return($return);
    }

     /*
     * 修改查出有误数据接口
     */
    static function update_info($type_code,$data){
        $k = 0;
        if($type_code == 'info'){
            $info  = M("product_information");
        }else{
            $info  = M("product_batch_information");
        }

        $info->startTrans();

        foreach($data as $k => $v){
            $id = $v['id'];
            $v['modified_time'] = date("Y-m-d H:i:s",time());
            unset($v['id']);
            $sa = $info->where(array('id'=>$id))->save($v);
            if($sa){
                $k ++;
            }
        }

        $info->commit();
        return $k;
    }

    //资料表撤销后退
    static function RollbackProduct($form_id){
        $info = M('product_information');
        $form = M('product_form');
        $form2product =M('product_form_information');
        $pic = M('product_for_picture');
        $data_constraint = M('data_constraint');
        $sql = $form->field("status_code,form_no")->where("id=%d",array($form_id))->find();
        switch ($sql['status_code']) {
            case 'halt':
            case 'finished':
            case 'enabled':
                $arr['status'] = 105;
                $arr['msg'] = "表格当前状态无法撤销返回";
              break;
            case 'editing':
                $data['status_code'] = 'selecting';
                $query = $form->data($data)->where("id=%d",array($form_id))->save();
                if($query !== 'flase'){
                    $arr['status'] = 100;
                }else{
                    $arr['status'] = 101;
                    $arr['msg'] = "撤销失败";
                }
              break;
            case 'selecting':
                $data['status_code'] = 'creating';
                $query1 = $form->data($data)->where("id=%d",array($form_id))->save();
                $query = $form2product->field("product_id")->where("form_id=%d",array($form_id))->select();
                $info->startTrans();
                foreach ($query as $key => $value) {
                    $where['product_id'] = $value['product_id'];
                    $delete = $info->where($where)->delete();
                }
                $del = $form2product->where("form_id=%d",array($form_id))->delete();
                $deletes = $pic->where("form_id=%d",array($form_id))->delete();
                $wh['app_code2'] = '1D';
                $wh['data2_id'] = $form_id;
                $dels = $data_constraint->where($wh)->delete();
                $info->commit();
                S($sql['form_no'].'_data',null);
                $arr['status'] = 100;
              break;
            case 'creating':
                $dele = $form->where("id=%d",array($form_id))->delete();
                if($query !== 'flase'){
                    $arr['status'] = 100;
                }else{
                    $arr['status'] = 101;
                    $arr['msg'] = "撤销失败";
                }
              break;
        }
        return($arr);
    }
}