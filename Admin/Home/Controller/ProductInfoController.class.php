<?php
namespace Home\Controller;
use Think\Controller;
/**
 * 产品资料控制器
 * @author cxl,lrf
 * @modify 2016/12/21
 */
class ProductInfoController extends BaseController{
    protected $dt   = "/^([1][7-9]{1}[0-9]{1}[0-9]{1}|[2][0-9]{1}[0-9]{1}[0-9]{1})(-)([0][1-9]{1}|[1][0-2]{1})(-)([0-2]{1}[1-9]{1}|[3]{1}[0-1]{1})*$/";
    protected $dt1  = "/^([1][7-9][0-9][0-9]|[2][0][0-9][0-9])(\.)([0][1-9]|[1][0-2])(\.)([0-2][1-9]|[3][0-1])*$/";
    protected $dt2  = "/^([1][7-9][0-9][0-9]|[2][0][0-9][0-9])([0][1-9]|[1][0-2])([0-2][1-9]|[3][0-1])*$/";
    protected $dt3  = "/^([1][7-9][0-9][0-9]|[2][0][0-9][0-9])(\/)([0][1-9]|[1][0-2])(\/)([0-2][1-9]|[3][0-1])*$/";

	/**
     * 删除产品资料
     * @param form_id   表格id
     * @param product_id 产品id
     * @param type_code info/batch
     */
	public function delInfo(){
        $form_id   = I('post.form_id');
		$id        = I('post.product_id');
        $type_code = I('post.type_code');

        if(empty($type_code) || ($type_code != 'info' && $type_code != 'batch')){
            $data['status'] = 119;
            $data['msg']    = '系统错误';
            $this->response($data,'json');
            exit();
        }

        if(empty($id)){
            $data['status'] = 102;
            $data['msg']    = '没有选择要删除的数据';
        }else{
            $res = \Think\Product\ProductInfo::DelProductInfo($type_code,$form_id, $id);
            if($res == 1){
                $data['status'] = 100;
            }else{
                $data['status'] = 101;
                $data['msg']    = '删除失败';
            }  
        }
		$this->response($data,'json');
	}

	/**
     * 查询表单的详细信息和相关的变体
     * @param form_id   表格id
     * @param template_id 模板id
     * @param type_code info/batch
     */
	public function getOneFormInfo(){
        $form_id     = I('form_id');
        $template_id = I('post.template_id');
        $type_code   = I('post.type_code');
        $status      = I('post.status');

        if(empty($type_code) || ($type_code != 'info' && $type_code != 'batch')){
            $data['status'] = 119;
            $data['msg']    = '系统错误';
            $this->response($data,'json');
            exit();
        }
        if(empty($form_id) || empty($template_id) || empty($type_code)){
            $data['status'] = 102;
            $data['msg']    = '资料表、模板为必选项';
            $this->response($data,'json');
            exit();
        }
         $data = array();
         $res  = \Think\Product\ProductInfo::GetOneFormInfo($type_code,$form_id,$status);

         if($res == 1){
             $data['status'] = 111;
         }elseif($res){
             $data['status']    = 100;
             $data['value']     = $res;
         }else{             // 如果没有数据则给默认
            $data['status'] = 100;
            $data['value'] = array();    
         }
        $this->response($data,'json');
	}

    /**
     * 图片填充至产品资料
     * @param form_id   表格id
     * @param picCount  图片总数
     * @param picArr    图片数据包 避免数据包过大分段获取
     */
    public function getFormInfo(){
        
        set_time_limit(0);
        $form_id = I('post.form_id');
        $picount = (int)I('post.picCount');  // 图片总数
        if(!preg_match("/^[0-9]+$/" , $form_id) || !preg_match("/^[0-9]+$/" , $picount)){
            $this->response(['status' => 102 , 'msg' => '表格选择有误'],'json');exit();
        }
        $text     = file_get_contents("php://input");
        $textdata = urldecode($text);
        $num = ceil($picount / 100);
        $j  = 0;
        $picdata = array();
        // 分段获取图片数据
        for($z = 0; $z < $num; $z ++) {
            $b = stripos($textdata, 'picArr[' . $j . ']');
            $j = $j + 100;
            $c = stripos($textdata, 'picArr[' . $j . ']');
            if (empty($c)) {
                $g = substr($textdata, $b);
            } else {
                $g = substr($textdata, $b, $c - $b - 1);
            }
            parse_str($g);
            $picdata[] = $picArr;
            $picArr = array();
        }
        $pic = array();
        // 梳理获取到的图片数据放数组方便处理
        foreach($picdata as $val){  // 将接收到的多个数据包组合成一个
            foreach($val as $vs){
                $pic[] = $vs;
            }
        }

        $m = M('product_batch_information');
        $m->startTrans();
        // 修改已经上传完成的图片地址
        foreach($pic as $v){
            $d['char_value'] = $v['image_url'];
            $s = $m->where("id in(".$v['id'].")" )->save($d);
            if(!$s){
                $this->response(['status' => 101 , 'msg' => '图片信息保存有误，请重新提交'],'json');exit();
            }
        }
        $m->commit();
        // 修改状态码进入下一步流程
        $status_code['status_code'] = 'uploading';
        M('product_batch_form')->where(array('id'=>$form_id))->save($status_code);

        $this->response(['status' => 100],'json');
        
    }

    // 生成批量表
    // @param form_id 表格id
    public function makeExcel(){
        set_time_limit(0);

        $form_id     = (int)I('form_id');
        $template_id = I('template_id');
        $productSelect = I('productSelect');
        $creator_id = I('post.creator_id');  // 创建者
        if(empty($creator_id)){
            $arr['status'] = 1012;
            $this->response($arr,'json');
            exit();
        }
        if($productSelect == 1){
            $sql = M()->query("select id FROM tbl_product_batch_information where  product_id in (SELECT product_id FROM tbl_product_batch_form_information where form_id = ".$form_id." ) and parent_id <> 0");
            if(count($sql) == 0){
                $data['judge'] = 2;//只有父类产品，也是全部导出，不要背景色
            }else{
                $data['judge'] = 1;//全部导出
            }
        }else{
            $data['judge'] = 3;//只导出子产品
        }
        // 查询当前表的模板文件
        $batch = M("product_batch_template2file")
            ->field("file,file_type,path")
            ->where("template_id = %d",array($template_id))
            ->order("created_time desc")->find();
        // 具体文件位置
        $name = $batch['path'] . $batch['file'];
        $ch   = curl_init();
        $data['form_id']     = (int)$form_id;
        $data['template_id'] =  (int)$template_id;
        $data['path']        =  C('SAVE_PATH') . substr ($name, 1 );
        $date = M('product_batch_form')->field("file_name")->where("id=%d",array($form_id))->find();
        $num = M('product_batch_template')->field("number")->where("id=%d",array($template_id))->find();
        if(empty($date['file_name'])){
            $fileName =  date('Y-m-d-H-i-s',time());
        }else{
            $fileName = $date['file_name'];
        }
        if(empty($num['number'])){
            $data['num'] = 3;
        }else{
            $data['num'] = $num['number'];
        }
        // 推动到java端进行表格生成
        $data['savepath'] =  C('SAVE_PATH').substr(C('BATCH_SAVE_PATH'),1).str_replace(" ","_",$fileName).'.'.$batch['file_type'];
        curl_setopt($ch, CURLOPT_URL, "http://localhost/excel4php/javaoptexcel.php");  
        curl_setopt($ch, CURLOPT_HEADER, false);  
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data); 
        $result = curl_exec($ch);
        curl_close($ch); 
        if($result == '1'){
            // 修改状态码
            $en['status_code'] = 'finished';
            M('product_batch_form')->where(array('id'=>$form_id))->save($en);
            $das['form_id']       = $form_id;
            $das['file']          = str_replace(" ","_",$fileName).'.'.$batch['file_type'];
            $das['file_type']     = $batch['file_type'];
            $das['path']          = C('BATCH_SAVE_PATH').$das['file'];
            $das['creator_id']    = $creator_id;
            $das['created_time']  = date('Y-m-d-H-i-s',time());
            $das['modified_time'] = date('Y-m-d-H-i-s',time());
            M('product_batch_form2file')->data($das)->add();
            $arr['status'] = 100;
            $arr['url']    = C('MY_HTTP_PATH').C('BATCH_SAVE_PATH'). $das['file'];
        }else{
            $arr['status'] = 101;
            $arr['msg']    = $result;
        }
        $this->response($arr,'json');
    }
    /*
     * 返回上一步 cxl
     * @param form_id  表格id
     * */
    public function back_step(){
        set_time_limit(0);
        $form_id = I('post.form_id');
        if(!preg_match("/^[0-9]+$/" , $form_id)){
            $data['status'] = 102;
            $data['msg']    = '表单未选择';
            $this->response($data , 'json');exit();
        }
        // 处理返回
        $result = \Think\Product\ProductInfo::back_step($form_id);
        if($result['error'] == 0){
            $array['status'] = 100;
        }else{
            $array['status'] = $result['status'];
            $array['msg']    = $result['msg'];
        }
        $this->response($array,'json');
    }

    //根据资料表id获取批量表模板
    public function GetBatchTel(){
        $form_id = I('form_id');
        $res = \Think\Product\ProductInfo::GetBatchTemplate($form_id);
        if($res){
            $arr['status'] = 100;
            $arr['value']  = $res;
        }else{
            $arr['status'] = 100;
            $arr['value']  = "";
        }
        $this->response($arr,'json');
    }

    /*
     * 产品资料删除
     * @param product_id  产品id
     * @param type_code   info/batch
     * */
    public function del_product()
    {
        $product_id = I('post.product_id');
        $type_code  = I('post.type_code');
        if(!preg_match("/^[0-9]+$/",$product_id)){
            $data['status'] = 102;
            $data['msg']    = '未选择需删除产品';
            $this->response($data , 'json');exit();
        }
        if($type_code != 'info' && $type_code != 'batch'){
            $data['status'] = 119;
            $data['msg']    = '系统错误';
            $this->response($data , 'json');exit();
        }

        $result = \Think\Product\ProductInfo::del_product($type_code , $product_id);
        if($result['error'] == 0){
            $arr['status'] = 100;
        }else{
            $arr['status'] = $result['status'];
            $arr['msg']    = $result['msg'];
        }

        $this->response($arr,'json');
    }


    // 接收选择图片与配备的词库内容
    // @param data        词库数据包
    // @param form_no     表格编号
    // @param category_id 类目id
    // @param picData     图片数据包
    public function receiveValue(){
        $data        = I('post.data');
        $form_no     = I('post.form_no');
        $category_id = I('post.category_id');
        $picDate     = I('post.picData');
        if(empty($category_id) || empty($form_no)){
            $this->response(['status' => 119, 'msg' => "系统错误"],'json');
            exit();
        }
        $creator_id = I('post.creator_id');
        if(empty($creator_id)){
            $this->response(['status' => 1012],'json');
            exit();
        }
        // 数据包写入文件缓存
        S($form_no.'_data' ,$data);
        // 修改状态码
        $id  = (int)substr($form_no, 8);
        M('product_form')->data(['status_code' => "selecting"])->where("id=%d",$id)->save();

        $pic = M('product_for_picture');
        $data_constraint = M('data_constraint');
        $pic->startTrans();
        $datas['form_id'] = $id;
        $datas['category_id'] = $category_id;
        foreach ($picDate as $key => $value)
        {
            $datas['picture_id']   = $value['id'];
            $datas['created_time'] = date('Y-m-d H:i:s',time());
            $datas['used_time']    = date('Y-m-d H:i:s',time()+3600*24*30*3);
            $query = $pic->data($datas)->add();
            if($query === 'flase'){
                $pic->rollback();
                $this->response(['status' => 101, 'msg' => "添加失败"],'json');
                exit();
            }
            $das['app_code1'] = 'DL';
            $das['data1_id'] = $value['id'];
            $das['app_code2'] = '1D';
            $das['data2_id'] =  $id;
            $das['creator_id'] = $creator_id;
            $das['created_time'] = date('Y-m-d H:i:s',time());
            $add = $data_constraint->data($das)->add();
            if(empty($add)){
                $pic->rollback();
                $arr['status'] = 101;
                $arr['msg'] = "添加失败";
                $this->response($arr,'json');
                exit();
            }
        }
        
        $check = S($form_no.'_data');
        if(empty($check[0])){
            $arr['status'] = 101;
            $arr['msg'] = "添加失败";
            $this->response($arr,'json');
            exit();
        }
        $pic->commit();
        $arr['status'] = 100;
        $this->response($arr,'json');
    }

        /*
     * 资料表自动填表
     * @param table_info 资料表表格信息
     */
    public function  product_AutoFill(){
        set_time_limit(0);
        
        $table_info = I('post.table_info');
        $template_id = $table_info['template_id'];//模板id
        $category_id = $table_info['category_id'];//类目id
        $form_no = $table_info['form_no'];//表单编码
        $form_id = $table_info['id'];//表单ID
        $product_count =$table_info['product_count'];//产品总数
        $variant_num = $table_info['variant_num'];//变体数量

        $hc_data=S($form_no.'_data');//图片与词库匹配数据
        $creator_id = I('post.creator_id');
        if(empty($creator_id)){
            $arr['status'] = 1012;
            $this->response($arr,'json');
            exit();
        }
        
        if(empty($variant_num)){
            $num = $product_count;
        }else{
            $num = $product_count + ceil($product_count / $variant_num);//产品数量+主体数量
        }
        
        $getdata = I('post.getdata');//填写的默认值的数据
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

        //Price (GBP)
        $priceGbp1 = (int)$reludata['priceGbp1'];
        $priceGbp2 = (int)$reludata['priceGbp2'];

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

    /*
     * 批量表数据操作
     * @param form_id     表格id
     * @param DefaultData 图片格式数据包
     * @param VariantData 有变体格式数据包
     * */
    public function batch_info(){
        set_time_limit(0);
        
        $form_id     = (int)I('post.form_id');
        $DefaultData = I('post.DefaultData');
        $VariantData = I('post.VariantData');
        if(empty($DefaultData) && empty($VariantData)){
            $this->response(['status' => 102 , 'msg' => '没有填充数据'] , 'json');exit();
        }
        if(!preg_match("/^[0-9]+$/" , $form_id)){
            $this->response(['status' => 102 , 'msg' => '表格选择有误'] , 'json');exit();
        }

        $mo = M('product_batch_information');
        $mo->startTrans();
        $tem_arr = [];
        $tem_id = M('product_batch_form')->where("id=%d",array($form_id))->field("template_id")->find();

        // 查出当前表格模板的默认值
        $tem_data = \Think\Product\Product_Item_Template::get('batch',$tem_id['template_id'],"en_name,data_type_code,filling_type,default_value");

        // 先填写默认值，后面有提供数据的字段将会被覆盖
        foreach($tem_data['value'] as $t_k => $t_v){
            if(!empty($t_v["default_value"]) || $t_v["default_value"] != "" || $t_v["default_value"] != null){
                $tem_arr[] = $t_v;
            }
        }
        // 列出当前表所有产品id 组合成字符串 "1,2,3,4" 的格式
        $product_id = M('product_batch_form_information')->where("form_id=%d",array($form_id))->field("product_id")->select();
        foreach($product_id as $v){
            $product_ids[] = $v['product_id'];
        }
        $p_id = implode("," , $product_ids);
        foreach($tem_arr as $s_k => $s_v){
            switch($s_v['data_type_code']){
                case 'int':  $s_arr['interger_value'] = $s_v['default_value']; $w = " AND interger_value is null"; break;
                case 'char': $s_arr['char_value']     = $s_v['default_value']; $w = " AND char_value is null"; break;
                case 'dc':   $s_arr['decimal_value']  = $s_v['default_value']; $w = " AND decimal_value is null"; break;
                case 'dt':   $s_arr['date_value']     = $s_v['default_value']; $w = " AND date_value is null"; break;
                case 'bl':   $s_arr['boolean_value']  = $s_v['default_value']; $w = " AND boolean_value is null"; break;
            }
            $where = "product_id IN (".$p_id.") AND title='".$s_v['en_name']."'".$w;
            // 填充默认值
            $mo->where($where)->save($s_arr);
        }
        // 两种不同数据包合并
        $request['default'] = $DefaultData;
        $request['variant'] = $VariantData;
        $res = \Think\Product\ProductInfo::GetOneFormInfo('batch' , $form_id);
        foreach($res as $key => $value){
            foreach($value as $k => $v){
                foreach($request as $kt => $vt){
                    foreach($vt as $h => $hv)
                    {
                        // 根据不同的数据包 拿不同的表头
                        if($kt == 'default'){
                            $values = $hv;
                        }elseif($kt == 'variant'){
                            if($value['parent_id'] == 0){
                                $values = $hv[0];
                            }else{
                                $values = $hv[1];
                            }
                        }
                        if($k == $h){
                            // @ 与 @@ 两个不同方式获取父产品的数据
                            if(substr($values , 0 , 1) == "@"){
                                if(substr($values , 1 , 1) == "@"){  // 双@取当前产品的父产品数据
                                    $p_head = trim(substr($values , 2)); // 表头
                                    switch ($value[$k."_t"]) {
                                        case 'pic': case 'upc_code' : break;
                                        case 'int':  $types = 'interger_value'; break;
                                        case 'char': $types = 'char_value'; break;
                                        case 'dc':   $types = 'decimal_value'; break;
                                        case 'dt':   $types = 'date_value'; break;
                                        case 'bl':   $types = 'boolean_value'; break;
                                    }
                                    // 不同数据形式给不同字段赋值
                                    if($res[$key]['parent_id'] != 0){
                                        $wheres = "product_id=".$res[$key]['parent_id']." AND title='".$p_head."' AND enabled=1";
                                        $result = $mo->where($wheres)->find();
                                        $p_val = $result[$types];   
                                    }
                                    $arr[$types] = $p_val;
                                }else{
                                    $head_from = trim(substr($values , 1));
                                    switch ($value[$k."_t"]) {        // 不同数据形式给不同字段赋值
                                        case 'pic': case 'upc_code' : break;
                                        case 'int':  $arr['interger_value'] = $value[$head_from]; break;
                                        case 'char': $arr['char_value']     = $value[$head_from]; break;
                                        case 'dc':   $arr['decimal_value']  = $value[$head_from]; break;
                                        case 'dt':   $arr['date_value']     = $value[$head_from]; break;
                                        case 'bl':   $arr['boolean_value']  = $value[$head_from]; break;
                                    }
                                }
                            }else{
                                switch ($value[$k."_t"]) {        // 不同数据形式给不同字段赋值
                                    case 'int':
                                        if (preg_match("/^[0-9]*$/", $values)) {
                                            $arr['interger_value'] = $values;
                                        } else {
                                            $this->response(['status' => 103 , 'msg' => '整数数据类型填写错误'] , 'json');exit();
                                        }
                                        break;
                                    case 'char': $arr['char_value'] = $values; break;
                                    case 'dc':
                                        if (preg_match("/^(\d*\.)?\d+$/", $values)) {
                                            $arr['decimal_value'] = $values;
                                        } else {
                                            $this->response(['status' => 104 , 'msg' => '小数数据类型填写错误'] , 'json');exit();
                                        }
                                        break;
                                    case 'dt':
                                        if (preg_match($this->dt, $values) || preg_match($this->dt1, $values) || preg_match($this->dt2, $values) || preg_match($this->dt3, $values)) {
                                            $arr['date_value'] = $values; break;
                                        } else {
                                            $this->response(['status' => 105 , 'msg' => '日期数据类型填写错误'] , 'json');exit();
                                        }
                                        break;
                                    case 'bl': $arr['boolean_value'] = $values; break;
                                }
                            }
                            $mo->where('id=%d' , array($value[$k."_id"]))->save($arr);
                        }
                    }
                }
            }
        }
        $mo->commit();
        $this->response(['status' => 100],'json');
    }

    // 资料表撤销后退
    // @param form_id  表格id
    public function rollbackProduct(){
        $form_id = I('post.form_id');
        if(!preg_match("/^[0-9]+$/" , $form_id) || empty($form_id)){
            $data['status'] = 102;
            $data['msg']    = '表单未选择';
            $this->response($data , 'json');exit();
        }
        // 处理返回
        $result = \Think\Product\ProductInfo::RollbackProduct($form_id);
        $this->response($result,'json');
    }
}