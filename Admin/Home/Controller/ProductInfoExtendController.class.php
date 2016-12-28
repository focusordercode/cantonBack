<?php
namespace Home\Controller;
use Think\Controller;
/**
* 产品资料扩展控制器
* @author lrf
* @modify 2016/12/22
*/
class ProductInfoExtendController extends BaseController
{
	protected $dt   = "/^([1][7-9]{1}[0-9]{1}[0-9]{1}|[2][0-9]{1}[0-9]{1}[0-9]{1})(-)([0][1-9]{1}|[1][0-2]{1})(-)([0-2]{1}[1-9]{1}|[3]{1}[0-1]{1})*$/";
    protected $dt1  = "/^([1][7-9][0-9][0-9]|[2][0][0-9][0-9])(\.)([0][1-9]|[1][0-2])(\.)([0-2][1-9]|[3][0-1])*$/";
    protected $dt2  = "/^([1][7-9][0-9][0-9]|[2][0][0-9][0-9])([0][1-9]|[1][0-2])([0-2][1-9]|[3][0-1])*$/";
    protected $dt3  = "/^([1][7-9][0-9][0-9]|[2][0][0-9][0-9])(\/)([0][1-9]|[1][0-2])(\/)([0-2][1-9]|[3][0-1])*$/";

    /*
	 * 获取模板的数据格式
     * @param template_id 模板id
     * @param type_code  资料表或者批量表 
	 */
	public function getTemFormat(){
		$template_id = I('post.template_id');
		$type_code = I('post.type_code');
		if($type_code != 'info' && $type_code !='batch'){
			$arr['status'] = 119;
			$arr['msg'] = "系统错误";
			$this->response($arr);
			exit();
		}
		if(empty($template_id)){
			$arr['status'] = 102;
			$arr['msg'] = "模板信息错误";
			$this->response($arr);
			exit();
		}
		$res = \Think\Product\ProductInfoExtend::GetTemplateFormat($template_id,$type_code);
		$this->response($res);
	}

	/*
	 * 改版的数据提交与暂存
     * @param form_id 表格id
     * @param template_id 模板id
     * @param category_id 类目id
     * @param type_code 判断是资料表（info） 或者批量表（batch）
     * @param type 判断是暂存或者提交
     * @param max 所有产品的数量
     * @param gridColumns   表头数据
     * @param text post的所有数据
	 */
	public function dataCommit(){
        set_time_limit(0);
		$form_id = I('post.form_id');
		$template_id = I('post.template_id');
		$category_id = I('post.category_id');
		$type_code = I('post.type_code');
		$type  = I('post.type');     // 暂存或者提交
		$max = I('post.max');
        $gridColumns = I('post.gridColumns');
		$text = file_get_contents("php://input");
        $textdata    = urldecode($text);

		if($type_code != 'info' && $type_code !='batch'){
			$arr['status'] = 119;
			$arr['msg'] = "系统错误";
			$this->response($arr);
		}
		if(empty($template_id)){
			$arr['status'] = 102;
			$arr['msg'] = "模板信息错误";
			$this->response($arr);
		}
		if(empty($form_id)){
			$arr['status'] = 102;
			$arr['msg'] = "表格信息错误";
			$this->response($arr);
		}
		if(empty($category_id)){
			$arr['status'] = 102;
			$arr['msg'] = "类目信息错误";
			$this->response($arr);
		}

		if($type_code == 'info'){
			$item = M('product_item_template');
			$info = M('product_information');
			$form = M('product_form_information');
			$types = M('product_form');
            $code = 'product_information_record';//应用代码，将用于获取全局产品记录id
            $n = 10;
        }else {
        	$item = M('product_batch_item_template');
        	$info = M('product_batch_information');
        	$form = M('product_batch_form_information');
        	$types = M('product_batch_form');
            $code = 'product_batch_information_record';
            $n = 1;
        }
        $num  = ceil( $max / $n );

        $j = 0;
        for($z = 0; $z < $num; $z ++) {                     // 分包获取传的产品数量
            $b = stripos($textdata, 'gridData[' . $j . ']');
            $j = $j + $n;
            $c = stripos($textdata, 'gridData[' . $j . ']');
            if (empty($c)) {
                $g = substr($textdata, $b);
            } else {
                $g = substr($textdata, $b, $c - $b - 1);
            }
            parse_str($g);
            $pro_data[] = $gridData;
            $gridData = array();
        }

        $info->startTrans();
       	$sql = $item->field("en_name,no,data_type_code,length,precision")->where("template_id=%d",array($template_id))->select();
       	foreach ($sql as $key => $value) {
       		$data_style[$value['en_name']]['no'] = $value['no'];
       		$data_style[$value['en_name']]['data_type_code'] = $value['data_type_code'];
       		$data_style[$value['en_name']]['length'] = $value['length'];
       		$data_style[$value['en_name']]['precision'] = $value['precision'];
       	}

       	$m = 0;
        //找出多少是新添加的
       	foreach ($pro_data as $k => $va) {
            foreach ($va as $vkey => $v_data) {
                if($v_data[array_search('types',$gridColumns)] == 'yes'){
                    $m++;
                }
            }

       	}
       	$newdata = $m*count($data_style);
       	if($newdata > 0){
       		$id = GetSysId($code,$newdata);
       	}

       	$i = 0;

        //数据写入数据库
        foreach ($pro_data as $keys => $values) {
        	foreach ($values as $k => $valu) {
                $product_id = $valu[array_search('product_id',$gridColumns)];
                $parent_id = $valu[array_search('parent_id',$gridColumns)];
                $ty = $valu[array_search('types',$gridColumns)];
                foreach ($valu as $ke => $val) {
                    $value_key = $gridColumns[$ke];
                    if(!array_key_exists($value_key, $data_style)){
                        continue;
                    }
                    switch ($data_style[$value_key]['data_type_code']) {
                        case 'int':
                            $data_type = 'interger_value';
                            if(!empty($val)){
                                if(!preg_match("/^[0-9]*$/", $val)){
                                    $info->rollback();
                                    $array['status'] = 103;
                                    $array['msg']    = '整数数据类型填写错误';
                                    $this->response($array);
                                    exit();
                                }
                            }

                          break;
                        case 'char':
                            $data_type = 'char_value';
                            if(!empty($val)){
                                $nums = strlen(trim($val));
                                if ($nums > $data_style[$value_key]['length']) {
                                    $info->rollback();
                                    $array['status'] = 106;
                                    $array['msg']    = '字符数据类型填写错误';
                                    $this->response($array);
                                    exit();
                                }
                            }
                          break;
                        case 'dc':
                            $data_type = 'decimal_value';
                            if(!empty($val)){
                                if (!preg_match("/^(\d*\.)?\d+$/", $val)) {
                                    $info->rollback();
                                    $array['status'] = 104;
                                    $array['msg'] = '小数数据类型填写错误';
                                    $this->response($array);
                                    exit();
                                }
                            }
                          break;
                        case 'dt':
                            $data_type = 'date_value';
                            if(!empty($val)){
                                if (preg_match($this->dt, $val) || preg_match($this->dt1, $val) ||
                                    preg_match($this->dt2, $val) || preg_match($this->dt3, $val)) {
                                    $info->rollback();
                                    $array['status'] = 105;
                                    $array['msg']    = '日期数据类型填写错误';
                                    $this->response($array);
                                    exit();
                                }
                            }
                          break;
                        case 'bl':
                            $data_type = 'boolean_value';
                          break;
                        case 'upc_code':
                            $data_type = 'char_value';
                          break;
                        case 'pic':
                            $data_type = 'char_value';
                          break;
                    }
                    if(empty($val)){
                        $valss = null;
                    }else{
                        $valss = $val;
                    }
                    $data[$data_type] = $valss;
                    $data['modified_time'] = date('Y-m-d H:i:s',time());
                    if(empty($ty) || $ty != 'yes'){
                        $where['product_id'] = $product_id;
                        $where['title'] = $value_key;
                        $query = $info->data($data)->where($where)->save();
                        if($query === 'flase'){
                            $info->rollback();
                            $arr['status'] = 101;
                            $arr['msg'] = "提交或者暂存失败";
                            $this->response($arr);
                            exit();
                        }
                        $data = array();
                    }else{
                        $data['id'] = $id[$i];
                        $data['category_id']    = $category_id;
                        $data['template_id']    = $template_id;
                        $data['product_id']     = $product_id;
                        $data['parent_id']      = $parent_id;
                        $data['no'] = $data_style[$value_key]['no'];
                        $data['title'] = $value_key;
                        $data['data_type_code'] = $data_style[$value_key]['data_type_code'];
                        $data['length'] = $data_style[$value_key]['length'];
                        $data['precision'] = $data_style[$value_key]['precision'];
                        $data['created_time'] = date('Y-m-d H:i:s',time());
                        $query = $info->data($data)->add();
                        if($query === 'flase'){
                            $info->rollback();
                            $arr['status'] = 101;
                            $arr['msg'] = "提交或者暂存失败";
                            $this->response($arr);
                            exit();
                        }
                        $i++;
                        $data = array();
                    }

                }
                if($pro_data[$keys][$k][array_search('types',$gridColumns)] == 'yes'){
                    $datas['form_id'] = $form_id;
                    $datas['product_id'] = $product_id;
                    $datas['created_time'] = date('Y-m-d H:i:s',time());
                    $oper = $form->data($datas)->add();
                    if(!$oper){
                        $info->rollback();
                        $arr['status'] = 101;
                        $arr['msg'] = "提交或者暂存失败";
                        $this->response($arr);
                        exit();
                    }
                }
            }
        }
        //提交就修改表格状态
        if($type == 'submit'){
            $status_code['status_code'] = 'editing';
            $types->where('id=%d',array($form_id))->data($status_code)->save();
        }
        $info->commit();
        $arr['status'] = 100;
       	$this->response($arr);
	}

    /*
    * 资料表自动填表
    * @param table_info 资料表表格信息
    */
    public function  testAdd(){
        set_time_limit(0);

        $table_info = I('post.table_info');
        $template_id = $table_info['template_id'];//模板id
        $category_id = $table_info['category_id'];//类目id
        $form_no = $table_info['form_no'];//表单编码
        $form_id = $table_info['id'];//表单ID
        $variant_count =$table_info['product_count']; //变体总数
        $num =$table_info['productCount'];  //产品总数
        $variant_num = $table_info['variant_num'];//变体数量

        $hc_data = S($form_no.'_data');// 图片与词库匹配数据
        $creator_id = I('post.creator_id');

        if(empty($creator_id)){
            $arr['status'] = 1012;
            $this->response($arr,'json');
        }

//        if(empty($variant_num)){
//            $num = $product_count;
//        }else{
//            $num = $product_count + ceil($product_count / $variant_num);//产品数量+主体数量
//        }

        $getdata  = I('post.getdata');//填写的默认值的数据
        $reludata = I('post.reludata');
        $SKUprefix = $reludata['sku_front'];//SKU前缀

        //SKU编码
        $sku_num1 = (int)$reludata['sku_num1'];
        $sku_num2 = (int)$reludata['sku_num2'];

        //Quantity on hand
        $quantity1 = (int)$reludata['quantity1'];
        $quantity2 = (int)$reludata['quantity2'];

        //Price (USD)
        $priceUsd1 = (float)$reludata['priceUsd1'];
        $priceUsd2 = (float)$reludata['priceUsd2'];

        //Weight  (ounce)
        $weight1  = (int)$reludata['weight1'];
        $weight2  = (int)$reludata['weight2'];

        //Size
        $size1 = (int)$reludata['size1'];
        $size2 = (int)$reludata['size2'];

        //获取表格表头数据
        $tem_data = \Think\Product\Product_Item_Template::get('info',$template_id,"no,en_name,data_type_code,length,default_value");
        $z = 0;
        $j = 0;

        $code = $sku_num1;
        $size_start = $size1;
        $form_info = M('product_form_information');
        $info = M('product_information');
        $info->startTrans();
        $is_count = $form_info->field("product_id")->where("form_id=%d",array($form_id))->select();
        if(!empty($is_count[0]['product_id'])){//判断是否已经有表格数据
            foreach ($getdata['default'] as $gkey => $gvalue) {//修改常规默认值
                $whe['title'] = $gkey;
                $whe['product_id'] = $is_count[0]['product_id'];
                $sel = $info->field("data_type_code")->where($whe)->find();//查出类型
                switch ($sel['data_type_code']) {
                    case 'int':  $data_type = 'interger_value'; break;
                    case 'char': $data_type = 'char_value'; break;
                    case 'dc':   $data_type = 'decimal_value'; break;
                    case 'dt':   $data_type = 'date_value'; break;
                    case 'bl':   $data_type = 'boolean_value'; break;
                    case 'upc_code': $data_type = 'char_value';break;
                    case 'pic': $data_type = 'char_value';break;
                }
                $dt[$data_type] = $gvalue;
                $wh['title'] = $gkey;
                foreach ($is_count as $iskey => $isvalue) {
                    $wh['product_id'] = $isvalue['product_id'];
                    $update = $info->data($dt)->where($wh)->save();
                }
            }
            if(empty($variant_num)){//检查是否有变体，没有就执行
                foreach ($is_count as $qkey => $qvalue) {
                    $price = rand($priceUsd1,$priceUsd2);
                    $decimal  = rand(1,99) / 100;
                    if(!empty($SKUprefix) && !empty($sku_num1) && !empty($sku_num2)){//重新修改SKU
                        $SKU_data['char_value'] = $SKUprefix.str_pad($code,4,"0",STR_PAD_LEFT);
                        $SKU_where['product_id'] = $qvalue['product_id'];
                        $SKU_where['title'] = 'SKU';
                        $SKU_upda = $info->data($SKU_data)->where($SKU_where)->save();
                    }
                    if(!empty($quantity1) && !empty($quantity2)){//规则中的Quantity on hand有值
                        $q_where['product_id'] = $qvalue['product_id'];
                        $q_where['title'] = 'Quantity on hand';
                        $q_data['interger_value'] = rand($quantity1,$quantity2);
                        $q_data['char_value'] = rand($quantity1,$quantity2);
                        $q_upda = $info->data($q_data)->where($q_where)->save();
                    }
                    if(!empty($weight1) && !empty($weight2)){//规则中的Weight  (ounce)有值
                        $w_where['product_id'] = $qvalue['product_id'];
                        $w_where['title'] = 'Weight  (ounce)';
                        $w_data['interger_value'] = rand($weight1,$weight2);
                        $w_data['char_value'] = rand($weight1,$weight2);
                        $w_upda = $info->data($w_data)->where($w_where)->save();
                    }
                    if(!empty($priceUsd1) && !empty($priceUsd2)){//规则中的Price (USD)有值
                        $usd_where['product_id'] = $qvalue['product_id'];
                        $usd_where['title'] = 'Price (USD)';
                        $usd_data['decimal_value'] = $price+$decimal;
                        $usd_data['char_value'] = $price+$decimal;
                        $usd_upda = $info->data($usd_data)->where($usd_where)->save();
                    }
                    if(!empty($priceUsd1) && !empty($priceUsd2)){//规则中的Price (GBP)有值
                        $gbp_where['product_id'] = $qvalue['product_id'];
                        $gbp_where['title'] = 'Price (GBP)';
                        $gbp_data['decimal_value'] = $price-1+$decimal;
                        $gbp_data['char_value'] = $price-1+$decimal;
                        $gbp_upda = $info->data($gbp_data)->where($gbp_where)->save();
                    }
                    if(!empty($size1) && !empty($size2)){
                        $size_data['char_value'] = $size1;
                        $size_data['interger_value'] = $size1;
                        $size_where['product_id'] = $qvalue['product_id'];
                        $size_where['title'] = 'Size';
                        $size_upda = $info->data($size_data)->where($size_where)->save();
                    }
                    $code++;
                }
            }else{//检查是否有变体，有就执行
                foreach ($is_count as $qkey => $qvalue) {
                    $price = rand($priceUsd1,$priceUsd2);
                    $decimal  = rand(1,99) / 100;
                    if(!empty($quantity1) && !empty($quantity2)){//规则中的Quantity on hand有值
                        $q_where['product_id'] = $qvalue['product_id'];
                        $q_where['parent_id'] = array('NEQ','0');
                        $q_where['title'] = "Quantity on hand";
                        $q_data['interger_value'] = rand($quantity1,$quantity2);
                        $q_data['char_value'] = rand($quantity1,$quantity2);
                        $q_upda = $info->data($q_data)->where($q_where)->save();
                    }
                    if(!empty($weight1) && !empty($weight2)){//规则中的Weight  (ounce)有值
                        $w_where['product_id'] = $qvalue['product_id'];
                        $w_where['parent_id'] = array('NEQ','0');
                        $w_where['title'] = "Weight  (ounce)";
                        $w_data['interger_value'] = rand($weight1,$weight2);
                        $w_data['char_value'] = rand($weight1,$weight2);
                        $w_upda = $info->data($w_data)->where($w_where)->save();
                    }
                    if(!empty($priceUsd1) && !empty($priceUsd2)){//规则中的Price (USD)有值
                        $usd_where['parent_id'] = $qvalue['product_id'];
                        $usd_where['title'] = 'Price (USD)';
                        $usd_data['decimal_value'] = $price+$decimal;
                        $usd_data['char_value'] = $price+$decimal;
                        $usd_upda = $info->data($usd_data)->where($usd_where)->save();
                    }
                    if(!empty($priceUsd1) && !empty($priceUsd2)){//规则中的Price (GBP)有值
                        $gbp_where['parent_id'] =  $qvalue['product_id'];
                        $gbp_where['title'] = "Price (GBP)";
                        $gbp_data['decimal_value'] = $price-1+$decimal;
                        $gbp_data['char_value'] = $price-1+$decimal;
                        $gbp_upda = $info->data($gbp_data)->where($gbp_where)->save();
                    }

                }
                //填写SKU
                if (!empty($SKUprefix) && !empty($sku_num1) && !empty($sku_num2)) {
                    $pid_where['tbl_product_information.parent_id'] = 0;
                    $pid_where['pf.form_id'] = $form_id;
                    $pid_where['title'] = 'SKU';
                    $sel_product_id = $info->field("tbl_product_information.product_id as id")->join("left join tbl_product_form_information pf on pf.product_id = tbl_product_information.product_id")->where($pid_where)->select();
                    //SKU与size有变体的算法
                    foreach ($sel_product_id as $sel_key => $sel_value) {
                        $sel_where['_string'] = "product_id = ".$sel_value['id']." or parent_id = ".$sel_value['id'];
                        $sel_where['title'] = 'SKU';
                        $sel_id = $info->field("product_id,parent_id")->where($sel_where)->select();
                        foreach ($sel_id as $skey => $svalue) {
                            if($svalue['parent_id'] == 0){
                                //重新修改SKU
                                $SKUs_data['char_value'] = $SKUprefix.str_pad($code,4,"0",STR_PAD_LEFT);
                                $SKUs_where['product_id'] = $svalue['product_id'];
                                $SKUs_where['title'] = 'SKU';
                                $SKUs_upda = $info->data($SKUs_data)->where($SKUs_where)->save();

                            }else{
                                //重新修改SKU
                                $c_where['product_id'] = $svalue['product_id'];
                                $c_where['title'] ='Color';
                                $colors = $info->field("char_value")->where($c_where)->find();
                                $SKUs_data['char_value'] = $SKUprefix.str_pad($code,4,"0",STR_PAD_LEFT).'-'.$size_start.'-'.    substr($colors['char_value'],0,2);
                                $SKUs_where['product_id'] = $svalue['product_id'];
                                $SKUs_where['title'] = 'SKU';
                                $SKUs_upda = $info->data($SKUs_data)->where($SKUs_where)->save();
                                if(!empty($size1) && !empty($size2)){
                                    $sizes_data['char_value'] = $size_start;
                                    $sizes_data['interger_value'] = $size_start;
                                    $SKUs_where['product_id'] = $svalue['product_id'];
                                    $SKUs_where['title'] = 'Size';
                                    $SKUs_upda = $info->data($sizes_data)->where($SKUs_where)->save();
                                }
                            }
                            $size_start++;
                        }
                        $code++;
                        $size_start = $size1;
                    }
                }
            }

            $info->commit();
            $arr['status'] = 100;
            $this->response($arr,'json');
            exit();
        }
        $f = 1;
        $s = 0;
        //获取全局id （产品id，产品记录id）
        $id = GetSysId('product_information',$num);
        $ids = GetSysId('product_information_record',count($tem_data['value'])*$num);

        if(empty($variant_num)){//没有变体的自动填表

            for($i = 0 ;$i < $product_count; $i++){
                $price = rand($priceUsd1,$priceUsd2);
                $decimal  = rand(1,99) / 100;
                if(empty($hc_data[$s])){
                    $s = 0;
                }
                //填写数据
                foreach ($tem_data['value'] as $keys => $values ) {
                    $data['id'] = $ids[$z];
                    $data['category_id'] = $category_id;
                    $data['template_id'] = $template_id;
                    $data['product_id'] = $id[$i];
                    $data['parent_id'] =  0;
                    $data['no'] = $values['no'];
                    $data['title'] = $values['en_name'];
                    $data['data_type_code'] = $values['data_type_code'];
                    $data['length'] = $values['length'];
                    $data['creator_id'] = $creator_id;
                    $data['created_time'] = date('Y-m-d H:i:s',time());
                    $data['modified_time'] = date('Y-m-d H:i:s',time());
                    switch ($data['data_type_code']) {
                        case 'int':
                            if(empty($values['default_value'])){
                                if(array_key_exists($values['en_name'], $getdata['default'])){//判断是否是常规数据
                                    $data['interger_value'] = $getdata['default'][$values['en_name']];
                                }elseif(array_key_exists($values['en_name'], $getdata['variant'])){//判断是否是变化值数据
                                    $data['interger_value'] = $getdata['variant'][$values['en_name']][0];
                                }elseif($values['en_name'] == 'Quantity on hand'){
                                    if(!empty($quantity1) && !empty($quantity2)){
                                        $data['interger_value'] = rand($quantity1,$quantity2);
                                    }
                                }elseif($values['en_name'] == 'Weight (ounce)'){
                                    if(!empty($weight1) && !empty($weight2)){
                                        $data['interger_value'] = rand($weight1,$weight2);
                                    }
                                }elseif($values['en_name'] == 'Weight  (ounce)'){
                                    if(!empty($weight1) && !empty($weight2)){
                                        $data['interger_value'] = rand($weight1,$weight2);
                                    }
                                }elseif($values['en_name'] == 'Size'){
                                    if(!empty($size1) && !empty($size2)){
                                        $data['interger_value'] = $size_start;
                                    }
                                }
                            }else{
                                if(array_key_exists($values['en_name'], $getdata['default'])){//判断是否是常规数据
                                    $data['interger_value'] = $getdata['default'][$values['en_name']];
                                }elseif(array_key_exists($values['en_name'], $getdata['variant'])){//判断是否是变化值数据
                                    $data['interger_value'] = $getdata['variant'][$values['en_name']][0];
                                }else{
                                    $data['interger_value'] = $values['default_value'];
                                }

                            }
                            break;
                        case 'char':
                            if($values['en_name'] == 'SKU'){//判断是否是SKU
                                if(!empty($SKUprefix) && !empty($sku_num1) && !empty($sku_num2)){
                                    $data['char_value'] = $SKUprefix.str_pad($code,4,"0",STR_PAD_LEFT);
                                }
                            }else{
                                if(empty($values['default_value'])){
                                    if(array_key_exists($values['en_name'], $getdata['default'])){
                                        $data['char_value'] = $getdata['default'][$values['en_name']];
                                    }elseif(array_key_exists($values['en_name'], $getdata['variant'])){
                                        $data['char_value'] = $getdata['variant'][$values['en_name']][0];
                                    }elseif(array_key_exists($values['en_name'], $hc_data[$s])){
                                        $data['char_value'] = __str_replace($hc_data[$s][$values['en_name']]);
                                    }elseif($values['en_name'] == 'Quantity on hand'){
                                        if(!empty($quantity1) && !empty($quantity2)){
                                            $data['char_value'] = rand($quantity1,$quantity2);
                                        }
                                    }elseif($values['en_name'] == 'Weight (ounce)'){
                                        if(!empty($weight1) && !empty($weight2)){
                                            $data['char_value'] = rand($weight1,$weight2);
                                        }
                                    }elseif($values['en_name'] == 'Weight  (ounce)'){
                                        if(!empty($weight1) && !empty($weight2)){
                                            $data['char_value'] = rand($weight1,$weight2);
                                        }
                                    }elseif($values['en_name'] == 'Size'){
                                        $data['char_value'] = $size_start;
                                    }elseif($values['en_name'] == 'Price (USD)'){
                                        if(!empty($priceUsd1) && !empty($priceUsd2)){
                                            $data['char_value'] = $price+$decimal;
                                        }
                                    }elseif($values['en_name'] == 'Price (GBP)'){
                                        if(!empty($priceGbp1) && !empty($priceGbp2)){
                                            $data['char_value'] = $price-1+$decimal;
                                        }
                                    }
                                }else{
                                    if(array_key_exists($values['en_name'],$getdata['default'])){
                                        $data['char_value'] = $getdata['default'][$values['en_name']];
                                    }elseif(array_key_exists($values['en_name'], $getdata['variant'])){
                                        $data['char_value'] = $getdata['variant'][$values['en_name']][0];
                                    }elseif(array_key_exists($values['en_name'], $hc_data[$s])){
                                        $data['char_value'] = __str_replace($hc_data[$s][$values['en_name']]);
                                    }elseif($values['en_name'] == 'Quantity on hand'){
                                        if(!empty($quantity1) && !empty($quantity2)){
                                            $data['char_value'] = rand($quantity1,$quantity2);
                                        }
                                    }elseif($values['en_name'] == 'Weight (ounce)'){
                                        if(!empty($weight1) && !empty($weight2)){
                                            $data['char_value'] = rand($weight1,$weight2);
                                        }
                                    }elseif($values['en_name'] == 'Weight  (ounce)'){
                                        if(!empty($weight1) && !empty($weight2)){
                                            $data['char_value'] = rand($weight1,$weight2);
                                        }
                                    }elseif($values['en_name'] == 'Size'){
                                        $data['char_value'] = $size_start;
                                    }elseif($values['en_name'] == 'Price (USD)'){
                                        if(!empty($priceUsd1) && !empty($priceUsd2)){
                                            $data['char_value'] = $price+$decimal;
                                        }
                                    }elseif($values['en_name'] == 'Price (GBP)'){
                                        if(!empty($priceUsd1) && !empty($priceUsd2)){
                                            $data['char_value'] = $price-1+$decimal;
                                        }
                                    }else{
                                        $data['char_value'] = $values['default_value'];
                                    }
                                }
                            }
                            break;
                        case 'dc':
                            if(empty($values['default_value'])){
                                if(array_key_exists($values['en_name'], $getdata['default'])){
                                    $data['decimal_value'] = $getdata['default'][$values['en_name']];
                                }elseif(array_key_exists($values['en_name'], $getdata['variant'])){
                                    $data['decimal_value'] = $getdata['variant'][$values['en_name']][0];
                                }elseif($values['en_name'] == 'Price (USD)'){
                                    if(!empty($priceUsd1) && !empty($priceUsd2)){
                                        $data['decimal_value'] = $price+$decimal;
                                    }
                                }elseif($values['en_name'] == 'Price (GBP)'){
                                    if(!empty($priceUsd1) && !empty($priceUsd2)){
                                        $data['decimal_value'] = $price-1+$decimal;
                                    }
                                }
                            }else{
                                $data['decimal_value'] = $values['default_value'];
                            }
                            break;
                        case 'dt':
                            if(empty($values['default_value'])){
                                if(array_key_exists($values['en_name'], $getdata['default'])){
                                    $data['date_value'] = $getdata['default'][$values['en_name']];
                                }elseif(array_key_exists($values['en_name'], $getdata['variant'])){
                                    $data['date_value'] = $getdata['variant'][$values['en_name']][0];
                                }
                            }else{
                                $data['date_value'] = $values['default_value'];
                            }
                            break;
                        case 'bl':
                            if(empty($values['default_value'])){
                                if(array_key_exists($values['en_name'], $getdata['default'])){
                                    $data['boolean_value'] = $getdata['default'][$values['en_name']];
                                }elseif(array_key_exists($values['en_name'], $getdata['variant'])){
                                    $data['boolean_value'] = $getdata['variant'][$values['en_name']][0];
                                }
                            }else{
                                $data['boolean_value'] = $values['default_value'];
                            }
                            break;
                        case 'upc_code':
                            if(empty($values['default_value'])){
                                if(array_key_exists($values['en_name'], $getdata['default'])){
                                    $data['char_value'] = $getdata['default'][$values['en_name']];
                                }elseif(array_key_exists($values['en_name'], $getdata['variant'])){
                                    $data['char_value'] = $getdata['variant'][$values['en_name']][0];
                                }
                            }else{
                                $data['char_value'] = $values['default_value'];
                            }
                            break;
                        case 'pic':
                            if($f == 1){
                                $data['char_value'] = $hc_data[$s]['photo'];
                                $f = 2;
                            }
                            break;
                    }
                    $z++;
                    $sql = $info->add($data);
                    $data = array();
                }
                $f = 1;
                $code++;
                $s++;
            }
        }else{
            for ($i=1; $i < $product_count+1; $i++) { //product_count 变体产品数量

                if($i % $variant_num== 1){//主体
                    $price = rand($priceUsd1,$priceUsd2);
                    $decimal  = rand(1,99) / 100;
                    if($i != 1){
                        $s++;
                        if(empty($hc_data[$s])){
                            $s = 0;
                        }
                        $code++;
                    }
                    foreach ($tem_data['value'] as $key => $value) {
                        $data['id'] = $ids[$z];
                        $data['category_id'] = $category_id;
                        $data['template_id'] = $template_id;
                        $data['product_id'] = $id[$j];
                        $data['parent_id'] = 0;
                        $data['no'] = $value['no'];
                        $data['title'] = $value['en_name'];
                        $data['data_type_code'] = $value['data_type_code'];
                        $data['length'] = $value['length'];
                        $data['creator_id'] = $creator_id;
                        $data['created_time'] = date('Y-m-d H:i:s',time());
                        $data['modified_time'] = date('Y-m-d H:i:s',time());
                        switch ($data['data_type_code']) {
                            case 'int':
                                if(empty($value['default_value'])){//判断是否有默认值
                                    if(array_key_exists($value['en_name'], $getdata['default'])){// 判断是否在编辑的默认值中
                                        $data['interger_value'] = $getdata['default'][$value['en_name']];
                                    }elseif(array_key_exists($value['en_name'], $getdata['variant'])){
                                        $data['interger_value'] = $getdata['variant'][$value['en_name']][0];
                                    }
                                }else{
                                    if(array_key_exists($value['en_name'], $getdata['default'])){
                                        $data['interger_value'] = $getdata['default'][$value['en_name']];
                                    }elseif(array_key_exists($value['en_name'], $getdata['variant'])){
                                        $data['interger_value'] = $getdata['variant'][$value['en_name']][0];
                                    }else{
                                        $data['interger_value'] = $value['default_value'];//使用默认值
                                    }

                                }
                                break;
                            case 'char':
                                if($value['en_name'] == 'SKU'){//判断是否为SKU，是就按下面的规则组合SKU
                                    if(!empty($SKUprefix) && !empty($sku_num1) && !empty($sku_num2)){
                                        $data['char_value'] = $SKUprefix.str_pad($code,4,"0",STR_PAD_LEFT);
                                    }
                                }else{
                                    if(empty($value['default_value'])){
                                        if(array_key_exists($value['en_name'], $getdata['default'])){
                                            $data['char_value'] = $getdata['default'][$value['en_name']];
                                        }elseif(array_key_exists($value['en_name'], $getdata['variant'])){
                                            $data['char_value'] = $getdata['variant'][$value['en_name']][0];
                                        }elseif(array_key_exists($value['en_name'], $hc_data[$s])){//   判断是否在图片与词库的数组中
                                            $data['char_value'] = __str_replace($hc_data[$s][$value['en_name']]);
                                        }
                                    }else{
                                        if(array_key_exists($value['en_name'], $getdata['default'])){
                                            $data['char_value'] = $getdata['default'][$value['en_name']];
                                        }elseif(array_key_exists($value['en_name'], $getdata['variant'])){
                                            $data['char_value'] = $getdata['variant'][$value['en_name']][0];
                                        }elseif(array_key_exists($value['en_name'], $hc_data[$s])){
                                            $data['char_value'] = __str_replace($hc_data[$s][$value['en_name']]);
                                        }else{
                                            $data['char_value'] = $value['default_value'];
                                        }
                                    }
                                }
                                break;
                            case 'dc':
                                if(empty($value['default_value'])){
                                    if(array_key_exists($value['en_name'], $getdata['default'])){
                                        $data['decimal_value'] = $getdata['default'][$value['en_name']];
                                    }elseif(array_key_exists($value['en_name'], $getdata['variant'])){
                                        $data['decimal_value'] = $getdata['variant'][$value['en_name']][0];
                                    }
                                }else{
                                    $data['decimal_value'] = $value['default_value'];
                                }
                                break;
                            case 'dt':
                                if(empty($value['default_value'])){
                                    if(array_key_exists($value['en_name'], $getdata['default'])){
                                        $data['date_value'] = $getdata['default'][$value['en_name']];
                                    }elseif(array_key_exists($value['en_name'], $getdata['variant'])){
                                        $data['date_value'] = $getdata['variant'][$value['en_name']][0];
                                    }
                                }else{
                                    $data['date_value'] = $value['default_value'];
                                }
                                break;
                            case 'bl':
                                if(empty($value['default_value'])){
                                    if(array_key_exists($value['en_name'], $getdata['default'])){
                                        $data['boolean_value'] = $getdata['default'][$value['en_name']];
                                    }elseif(array_key_exists($value['en_name'], $getdata['variant'])){
                                        $data['boolean_value'] = $getdata['variant'][$value['en_name']][0];
                                    }
                                }else{
                                    $data['boolean_value'] = $value['default_value'];
                                }
                                break;
                            case 'upc_code':
                                if(empty($value['default_value'])){
                                    if(array_key_exists($value['en_name'], $getdata['default'])){
                                        $data['char_value'] = $getdata['default'][$value['en_name']];
                                    }elseif(array_key_exists($value['en_name'], $getdata['variant'])){
                                        $data['char_value'] = $getdata['variant'][$value['en_name']][0];
                                    }
                                }else{
                                    $data['char_value'] = $value['default_value'];
                                }
                                break;
                            case 'pic':
                                if($f == 1){
                                    if(empty($hc_data[$s]['photo'])){
                                        $s =0;
                                    }
                                    $data['char_value'] = $hc_data[$s]['photo'];
                                    $f = 2;
                                }
                                break;
                        }
                        $z++;
                        $sql = $info->add($data);
                        $data = array();
                    }
                    $a = $j;
                    $j++;
                    $f = 1;
                }
                //添加主体的变体产品
                foreach ($tem_data['value'] as $keys => $values ) {
                    $data['id'] = $ids[$z];
                    $data['category_id'] = $category_id;
                    $data['template_id'] = $template_id;
                    $data['product_id'] = $id[$j];
                    $data['parent_id'] =  $id[$a];
                    $data['no'] = $values['no'];
                    $data['title'] = $values['en_name'];
                    $data['data_type_code'] = $values['data_type_code'];
                    $data['length'] = $values['length'];
                    $data['creator_id'] = $creator_id;
                    $data['created_time'] = date('Y-m-d H:i:s',time());
                    $data['modified_time'] = date('Y-m-d H:i:s',time());
                    switch ($data['data_type_code']) {
                        case 'int':
                            if(empty($values['default_value'])){
                                if(array_key_exists($values['en_name'], $getdata['default'])){
                                    $data['interger_value'] = $getdata['default'][$values['en_name']];
                                }elseif(array_key_exists($values['en_name'], $getdata['variant'])){
                                    $data['interger_value'] = $getdata['variant'][$values['en_name']][1];
                                }elseif($values['en_name'] == 'Quantity on hand'){
                                    if(!empty($quantity1) && !empty($quantity2)){
                                        $data['interger_value'] = rand($quantity1,$quantity2);
                                    }
                                }elseif($values['en_name'] == 'Weight (ounce)'){
                                    if(!empty($weight1) && !empty($weight2)){
                                        $data['interger_value'] = rand($weight1,$weight2);
                                    }
                                }elseif($values['en_name'] == 'Weight  (ounce)'){
                                    if(!empty($weight1) && !empty($weight2)){
                                        $data['interger_value'] = rand($weight1,$weight2);
                                    }
                                }elseif($values['en_name'] == 'Size'){
                                    $data['interger_value'] = $size_start;
                                }
                            }else{
                                if(array_key_exists($values['en_name'], $getdata['default'])){
                                    $data['interger_value'] = $getdata['default'][$values['en_name']];
                                }elseif(array_key_exists($values['en_name'], $getdata['variant'])){
                                    $data['interger_value'] = $getdata['variant'][$values['en_name']][1];
                                }else{
                                    $data['interger_value'] = $values['default_value'];
                                }

                            }
                            break;
                        case 'char':
                            if($values['en_name'] == 'SKU'){
                                if(!empty($SKUprefix) && !empty($sku_num1) && !empty($sku_num2)){
                                    if(array_key_exists('Color ', $getdata['default'])){
                                        $data['char_value'] = $SKUprefix.str_pad($code,4,"0",STR_PAD_LEFT).'-'.$size_start.'-'.substr($getdata['default']['Color '],0,2);
                                    }elseif(array_key_exists('Color', $getdata['default'])){
                                        $data['char_value'] = $SKUprefix.str_pad($code,4,"0",STR_PAD_LEFT).'-'.$size_start.'-'.substr($getdata['default']['Color'],0,2);
                                    }elseif(array_key_exists('Color ', $getdata['variant'])){
                                        $data['char_value'] = $SKUprefix.str_pad($code,4,"0",STR_PAD_LEFT).'-'.$size_start.'-'.substr($getdata['variant']['Color '][1],0,2);
                                    }elseif(array_key_exists('Color', $getdata['variant'])){
                                        $data['char_value'] = $SKUprefix.str_pad($code,4,"0",STR_PAD_LEFT).'-'.$size_start.'-'.substr($getdata['variant']['Color'][1],0,2);
                                    }elseif(array_key_exists('Color',$values)){
                                        $data['char_value'] = $SKUprefix.str_pad($code,4,"0",STR_PAD_LEFT).'-'.$size_start.'-'.substr($values['Color'],0,2);
                                    }elseif(array_key_exists('Color ',$values)){
                                        $data['char_value'] = $SKUprefix.str_pad($code,4,"0",STR_PAD_LEFT).'-'.$size_start.'-'.substr($values['Color '],0,2);
                                    }else{
                                        $data['char_value'] = $SKUprefix.str_pad($code,4,"0",STR_PAD_LEFT).'-'.$size_start;
                                    }
                                }
                            }else{
                                if(empty($values['default_value'])){
                                    if(array_key_exists($values['en_name'], $getdata['default'])){
                                        $data['char_value'] = $getdata['default'][$values['en_name']];
                                    }elseif(array_key_exists($values['en_name'], $getdata['variant'])){
                                        $data['char_value'] = $getdata['variant'][$values['en_name']][1];
                                    }elseif(array_key_exists($values['en_name'], $hc_data[$s])){
                                        $data['char_value'] = __str_replace($hc_data[$s][$values['en_name']]);
                                    }elseif($values['en_name'] == 'Quantity on hand'){
                                        if(!empty($quantity1) && !empty($quantity2)){
                                            $data['char_value'] = rand($quantity1,$quantity2);
                                        }
                                    }elseif($values['en_name'] == 'Weight (ounce)'){
                                        if(!empty($weight1) && !empty($weight2)){
                                            $data['char_value'] = rand($weight1,$weight2);
                                        }
                                    }elseif($values['en_name'] == 'Weight  (ounce)'){
                                        if(!empty($weight1) && !empty($weight2)){
                                            $data['char_value'] = rand($weight1,$weight2);
                                        }
                                    }elseif($values['en_name'] == 'Size'){
                                        $data['char_value'] = $size_start;
                                    }elseif($values['en_name'] == 'Price (USD)'){
                                        if(!empty($priceUsd1) && !empty($priceUsd2)){
                                            $data['char_value'] = $price+$decimal;
                                        }
                                    }elseif($values['en_name'] == 'Price (GBP)'){
                                        if(!empty($priceUsd1) && !empty($priceUsd2)){
                                            $data['char_value'] = $price-1+$decimal;
                                        }
                                    }

                                }else{
                                    if(array_key_exists($values['en_name'],$getdata['default'])){
                                        $data['char_value'] = $getdata['default'][$values['en_name']];
                                    }elseif(array_key_exists($values['en_name'], $getdata['variant'])){
                                        $data['char_value'] = $getdata['variant'][$values['en_name']][1];
                                    }elseif(array_key_exists($values['en_name'], $hc_data[$s])){
                                        $data['char_value'] = __str_replace($hc_data[$s][$values['en_name']]);
                                    }else{
                                        $data['char_value'] = $values['default_value'];
                                    }
                                }
                            }
                            break;
                        case 'dc':
                            if(empty($values['default_value'])){
                                if(array_key_exists($values['en_name'], $getdata['default'])){
                                    $data['decimal_value'] = $getdata['default'][$values['en_name']];
                                }elseif(array_key_exists($values['en_name'], $getdata['variant'])){
                                    $data['decimal_value'] = $getdata['variant'][$values['en_name']][1];
                                }elseif($values['en_name'] == 'Price (USD)'){
                                    if(!empty($priceUsd1) && !empty($priceUsd2)){
                                        $data['decimal_value'] = $price+$decimal;
                                    }
                                }elseif($values['en_name'] == 'Price (GBP)'){
                                    if(!empty($priceUsd1) && !empty($priceUsd2)){
                                        $data['decimal_value'] = $price-1+$decimal;
                                    }
                                }
                            }else{
                                $data['decimal_value'] = $values['default_value'];
                            }
                            break;
                        case 'dt':
                            if(empty($values['default_value'])){
                                if(array_key_exists($values['en_name'], $getdata['default'])){
                                    $data['date_value'] = $getdata['default'][$values['en_name']];
                                }elseif(array_key_exists($values['en_name'], $getdata['variant'])){
                                    $data['date_value'] = $getdata['variant'][$values['en_name']][1];
                                }
                            }else{
                                $data['date_value'] = $values['default_value'];
                            }
                            break;
                        case 'bl':
                            if(empty($values['default_value'])){
                                if(array_key_exists($values['en_name'], $getdata['default'])){
                                    $data['boolean_value'] = $getdata['default'][$values['en_name']];
                                }elseif(array_key_exists($values['en_name'], $getdata['variant'])){
                                    $data['boolean_value'] = $getdata['variant'][$values['en_name']][1];
                                }
                            }else{
                                $data['boolean_value'] = $values['default_value'];
                            }
                            break;
                        case 'upc_code':
                            if(empty($values['default_value'])){
                                if(array_key_exists($value['en_name'], $getdata['default'])){
                                    $data['char_value'] = $getdata['default'][$values['en_name']];
                                }elseif(array_key_exists($value['en_name'], $getdata['variant'])){
                                    $data['char_value'] = $getdata['variant'][$values['en_name']][1];
                                }
                            }else{
                                $data['char_value'] = $values['default_value'];
                            }
                            break;
                        case 'pic':
                            if($f == 1){
                                $data['char_value'] = $hc_data[$s]['photo'];
                                $f = 2;
                            }
                            break;
                    }
                    $z++;
                    $sql = $info->add($data);
                    $data = array();
                }
                if($i % $variant_num == 0){
                    $size_start= $size1;
                }else{
                    $size_start++;
                }
                $f = 1;
                $j++;
            }
        }

        $info->commit();
        $form_info->startTrans();
        for ($q=0; $q < $num; $q++) {
            $da['form_id'] = $form_id;
            $da['product_id'] = $id[$q];
            $da['created_time'] = date('Y-m-d H:i:s',time());
            $query11 = $form_info->add($da);
        }
        $form_info->commit();
        $arr['status'] = 100;
        $this->response($arr,'json');
    }
}