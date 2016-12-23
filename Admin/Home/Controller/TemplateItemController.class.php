<?php
namespace Home\Controller;
use Think\Controller;
/**
 * 产品资料模板表头/项目模板/excel表头
 * @author cxl,lrf
 * @modify 2016/12/21
 */
class TemplateItemController extends BaseController
{
    protected $rule_enname      = "/^[a-z_A-Z()\s]+[0-9]{0,10}$/";
    protected $rule_num         = "/^[0-9]+$/";
    protected $dt               = "/^([1][7-9]{1}[0-9]{1}[0-9]{1}|[2][0-9]{1}[0-9]{1}[0-9]{1})(-)([0][1-9]{1}|[1][0-2]{1})(-)([0-2]{1}[1-9]{1}|[3]{1}[0-1]{1})*$/";
    protected $dt1              = "/^([1][7-9][0-9][0-9]|[2][0][0-9][0-9])(\.)([0][1-9]|[1][0-2])(\.)([0-2][1-9]|[3][0-1])*$/";
    protected $dt2              = "/^([1][7-9][0-9][0-9]|[2][0][0-9][0-9])([0][1-9]|[1][0-2])([0-2][1-9]|[3][0-1])*$/";
    protected $dt3              = "/^([1][7-9][0-9][0-9]|[2][0][0-9][0-9])(\/)([0][1-9]|[1][0-2])(\/)([0-2][1-9]|[3][0-1])*$/";

    /*
     * 模板表头列表
     * @param template_id 模板id
     * @param field  字段 不填为返回全部
     * @param type_code info / batch
     * */
    public function get_item_template()
    {
        $type_code=strip_tags(trim(I('type_code')));
        if(empty($type_code)){
            $data['status'] = 119;
            $data['msg']    = '系统错误';
            $this->response($data,'json');
            exit();
        }
        // 模板id验证  必要参数
        $t = strip_tags(trim(I("template_id")));
        if(!empty($t) && preg_match($this->rule_num,$t) && $t != 0){
            $template_id = $t;
        }else{
            $data['status'] = 102;
            $data['msg']    = '未选择模板';
        }
        // 返回字段定义，可选参数
        $f = strip_tags(trim(I("field")));
        if(!empty($f)){
            $fields = array_filter(explode(",",$f));
            $field = implode(",",$fields);
        }else{
            $field = "*";
        }
        $G = \Think\Product\Product_Item_Template::get($type_code,$template_id,$field);  // 获取数据
        if($G['error'] == 0){
            $data['status'] = 100;
            $data['value']  = $G['value'];
        }else{
            $data['status'] = $G['status'];
            $data['msg']    = $G['msg'];
        }
        $this->response($data,'json');
    }


    /*
     * 获取剔除模板默认关联后的模板表头数据
     * @param template_id 模板id
     * @param field  字段 不填为返回全部
     * @param type_code info / batch
     * @param new    为新增关系
     */
    public function get_item_eliminate(){
        $type_code=strip_tags(trim(I('type_code')));
        $new = I('post.new');
        if(empty($type_code)){
            $data['status'] = 119;
            $data['msg']    = '系统错误';
            $this->response($data,'json');
            exit();
        }
        // 模板id验证  必要参数
        $t = strip_tags(trim(I("template_id")));
        if(!empty($t) && preg_match($this->rule_num,$t) && $t != 0){
            $template_id = $t;
        }else{
            $data['status'] = 102;
            $data['msg']    = '未选择模板';
        }
        // 返回字段定义，可选参数
        $f = strip_tags(trim(I("field")));
        if(!empty($f)){
            $fields = array_filter(explode(",",$f));
            $field = implode(",",$fields);
        }else{
            $field = "*";
        }
        $G = \Think\Product\Product_Item_Template::get($type_code,$template_id,$field);  // 获取数据
        $count = count($G['value']);
        // 判断是否为新增的关联关系
        if($new == 'yes'){
            foreach (C('TEMPLATE_DEFAULT') as $key => $value) {
                foreach ($G['value'] as $keys => $values) {
                    if(in_array($key,$values)){
                        array_splice($G['value'],$keys,1);      
                    }
                }
            }
        }else{
            // 默认关系，直接读取配置文件添加
            $batch2product = D('TemplateContactView');
            $sql = $batch2product->where("template2_id=%d",array($t))->select();
            if($sql){
                foreach ($sql as $key => $value) {
                    foreach ($G['value'] as $keys => $values) {
                        if(in_array($value['batch'],$values)){
                            array_splice($G['value'],$keys,1);      
                        }
                    }
                }
            }else{
                foreach (C('TEMPLATE_DEFAULT') as $key => $value) {
                    foreach ($G['value'] as $keys => $values) {
                        if(in_array($key,$values)){
                            array_splice($G['value'],$keys,1);      
                        }
                    }
                }
            }
        }
        foreach (C('TEMPLATE_DEFAULT') as $key => $value) {
            foreach ($G['value'] as $keys => $values) {
                if(in_array($key,$values)){
                    array_splice($G['value'],$keys,1);      
                }
            }
        }
        array_values($G['value']);
        if($G['error'] == 0){
            $data['status'] = 100;
            $data['value']  = $G['value'];
        }else{
            $data['status'] = $G['status'];
            $data['msg']    = $G['msg'];
        }
        $this->response($data,'json');
    }


    /*
     * 获取默认关联的数据
     * @param template_id     资料表表头id
     * @param batch_template_id  批量表表头id
     */
    public function get_item_relation(){
        $template_id = I('post.template_id');
        $batch_template_id = I('post.batch_template_id');
        $product_item = M('product_item_template');
        $batch_item = M('product_batch_item_template');
        $sql = $product_item->field("id,en_name")->where("template_id=%d",array($template_id))->select();
        $query = $batch_item->field("id,en_name")->where("template_id=%d",array($batch_template_id))->select();
        $i = 0;
        foreach (C('TEMPLATE_DEFAULT') as $key => $value) {
            foreach ($sql as $ke => $values) {
                if(in_array($value,$values)){
                    $arr[$i]['infoId'] = $values['id'];
                    $arr[$i]['info'] = $values['en_name'];
                    foreach ($query as $keys => $val) {
                        if(in_array($key,$val)){
                            $arr[$i]['batchId'] = $val['id'];
                            $arr[$i]['batch'] = $val['en_name'];      
                        }
                    }
                    $i++;      
                }
            } 
        }
        $array['status'] = 100;
        $array['value'] = $arr;
        $this->response($array,'json');
    }

    /*
     * 添加模板表头数据
     * @param template_id 模板id
     * @param tempData 添加数据包
     * @param type_code info / batch
     * */
    public function add_item_template(){

        $type_code = strip_tags(trim(I('type_code')));
        $form_data = I('post.tempData');

        if($type_code != 'info' && $type_code != 'batch'){
            $data['status'] = 119;
            $data['msg']    = '系统错误';
            $this->response($data,'json');
            exit();
        }
        $template_id = strip_tags(trim(I('template_id')));
        if(empty($template_id) || !preg_match($this->rule_num,$template_id)){  // 表头id非空、正则验证
            $data['status'] = 102;
            $data['msg']    = '未选择模板';
        }else{
            // 处理接收到的数据包
            foreach($form_data as $key => $value){
                if(empty($value['cn_name']) || empty($value['en_name']) || empty($value['data_type_code']) || !preg_match($this->rule_enname,$value['en_name'])) {  //  数据非空、正则验证
                    $data['status'] = 102;
                    $data['msg']    = '参数有误';
                    $this->response($data,'json');
                    exit();
                }
            }
            $add_tem = \Think\Product\Product_Item_Template::add($type_code,$form_data,$template_id); // 添加操作
            if($add_tem['error'] == 0){
                $data['status'] = 100; // 添加成功
            }else{
                $data['status'] = $add_tem['status'];
                $data['msg']    = $add_tem['msg'];
            }
        }
        $this->response($data,'json');
    }


    // 编辑表头信息
    // @param type_code info/batch
    // @param item_num 表头数量
    // @param template_id 模板id
    public function edit_item_template()
    {
        $type_code   = I('type_code');
        $ma          = strip_tags(trim(I('item_num')));   // 总表头数
        $template_id = I('template_id');

        if(!preg_match("/^[0-9]+$/",$template_id)){
            $data['status'] = 102;
            $data['msg']    = "未选择模板";
            $this->response($data,'json');
            exit();
        }
        if($type_code != 'info' && $type_code != 'batch'){
            $data['status'] = 119;
            $data['msg']    = "系统错误，请即使联系管理员。";
            $this->response($data,'json');
            exit();
        }
        // 数据包处理
        if($type_code == 'info'){
            $edit_data = $_POST['tempData'];
            if(empty($edit_data)){
                $data['status'] = 102;
                $data['msg']    = "修改数据为空";
                $this->response($data,'json');
                exit();
            }
        }else{
            $text        = file_get_contents("php://input");
            $textdata    = urldecode($text);
            $num         = ceil( $ma / 30 );
            $j = 0;
            // 数据量过大被php 限制post接收时，分段拿取
            for($z = 0; $z < $num; $z ++) {                     // 分包获取传的产品数量
                $b = stripos($textdata, 'tempData[' . $j . ']');
                $j = $j + 30;
                $c = stripos($textdata, 'tempData[' . $j . ']');
                if (empty($c)) {
                    $g = substr($textdata, $b);
                } else {
                    $g = substr($textdata, $b, $c - $b - 1);
                }
                parse_str($g);
                $pro_data[] = $tempData;
                $tempData = array();
            }
            // 转化成数据方便处理
            $edit_data = array();
            foreach($pro_data as $val){  // 将接收到的多个数据包组合成一个
                foreach($val as $vs){
                    $edit_data[] = $vs;
                }
            }
        }
        // 遍历处理完的数组赋值修改
        foreach($edit_data as $value){
            if(!empty($value['default_value']) && $type_code == 'info'){
                $p = $value['default_value'];
                switch($value['data_type_code']){
                    case 'int':
                        if(!preg_match("/^[0-9]*$/",$p)){
                            $data['status'] = 105;
                            $data['msg']    = "整数类型填写错误";
                            $this->response($data,'json');
                            exit();
                        }
                    break;
                    case 'dc':
                        if(!preg_match("/^(\d*\.)?\d+$/",$p)){
                            $data['status'] = 106;
                            $data['msg']    = "小数类型填写错误";
                            $this->response($data,'json');
                            exit();
                        }
                    break;
                    case 'dt':
                        if(!preg_match($this->dt,$p) && !preg_match($this->dt1,$p) && !preg_match($this->dt2,$p) && !preg_match($this->dt3,$p)){
                            $data['status'] = 107;
                            $data['msg']    = "日期类型填写错误";
                            $this->response($data,'json');
                            exit();
                        }
                    break;
                }
            }
        }
        $edit_tem = \Think\Product\Product_Item_Template::edit($type_code,$edit_data,$template_id); //编辑操作
        if($edit_tem['error'] == 0){
            $data['status'] = 100;
            $data['value'] = $edit_tem['value'];
        }else{
            $data['status'] = $edit_tem['status'];
            $data['msg']    = $edit_tem['msg'];
        }

        $this->response($data,'json');
    }

    /*
     * 表头数据删除
     * @param template_id 模板id
     * @param id          删除的表头id
     * @param type_code info / batch
     * */
    public function del_item_template(){
        $type_code = strip_tags(trim(I('type_code')));
        if(empty($type_code) || ($type_code != 'info' && $type_code != 'batch')){
            $data['status'] = 119;
            $data['msg']    = '系统错误';
            $this->response($data,'json');
            exit();
        }
        if(isset($_POST['id'])){
            $id = strip_tags(trim(I("id")));
            if(empty($id) || !preg_match($this->rule_num,$id) || $id == 0){  //  id验证
                $data['status'] = 102; // 参数错误 id为空
                $data['msg']    = '未选择模板';
            }else{
                $del_tem = \Think\Product\Product_Item_Template::del_by_id($type_code,$id);   //  删除操作
            }
        }elseif(isset($_POST['template_id'])){
            $id = strip_tags(trim(I("template_id")));
            if(empty($id) || !preg_match($this->rule_num,$id) || $id == 0){  //  模板id验证
                $data['status'] = 102; // 参数错误 id为空
                $data['msg']    = '未选择模板';
            }else{
                $del_tem = \Think\Product\Product_Item_Template::del_by_template_id($type_code,$id);  // 删除操作
            }
        }

        if(isset($del_tem) && $del_tem['error'] == 0){
            $data['status'] = 100;                    // 删除成功
            $data['value']  = $del_tem['value'];
        }elseif(isset($del_tem) && $del_tem['error'] == 1){
            $data['status'] = $del_tem['status'];
            $data['msg']    = $del_tem['msg'];        // 删除失败
        }
        $this->response($data,'json');
    }

    /*
     * 根据模板id获取Bootstrap Table表格头
     * @param template_id 模板id
     * @param type_code info / batch
     */
    public function getBootsttrap(){
        $type_code   = I('post.type_code');
        $template_id = I('post.template_id');
        if(empty($template_id)){
            $arr['status'] = 104;
            $arr['msg']    = '未选择模板';
            $this->response($arr,'json');
            exit();
        }
        if(empty($type_code)){
            $arr['status'] = 119;
            $arr['msg']    = '系统错误';
            $this->response($arr,'json');
            exit();
        }
        $res = \Think\Product\Product_Item_Template::GetBootstrapTable($type_code,$template_id);
        if(!empty($res)){
            $arr['status'] = 100;
            foreach ($res as $key => $value) {
                $arr['value'][] = $value['en_name'];
            }
        }else{
            $arr['status'] = 101;
            $arr['msg']    = '暂无相关信息';
        }
        $this->response($arr,'json');
    }

    /*
     * 添加资料表与批量表的关系
     * @param template_id    资料表表头id
     * @param batch_template_id  批量表表头id
     * @param data           关联管理的数据包
     * */
    public function marry_information_batch_by_form(){
        $m = M('product_item2batch_item');
        $m->startTrans();

        $batch_template_id = I('post.batch_template_id');
        $marry_data        = I('post.data');
        $template_id       = I('post.template_id');

        if(!preg_match("/^[0-9]+$/",$batch_template_id)){
            $data['status'] = 102;
            $data['msg']    = '未选择批量表模板';
            $this->response($data,'json');
            exit();
        }
        if(!preg_match("/^[0-9]+$/",$template_id)){
            $data['status'] = 102;
            $data['msg']    = '未选择资料表模板';
            $this->response($data,'json');
            exit();
        }
        if(empty($marry_data) || !is_array($marry_data)){
            $data['status'] = 102;
            $data['msg']    = '未关联模板或数据有误';
            $this->response($data,'json');
            exit();
        }
        // 创建者id
        $creator_id = I('post.creator_id');
        if(empty($creator_id)){
            $arr['status'] = 1012;
            $this->response($arr,'json');
            exit();
        }

        $da['created_time']   = date("Y-m-d H:i:s", time());
        $da['modified_time']  = date("Y-m-d H:i:s", time());
        $da['creator_id']     = $creator_id;
        $m->where(array('template1_id' => $template_id,'template2_id' => $batch_template_id))->delete();
        // 循环查询是否存在，查询之后写入关系到数据库
        // 键为资料表表头id  值为批量表表头id
        foreach($marry_data as $val)
        {
            if(empty($val['infoId']) || empty($val['batchId'])){  // 核查参数id不能为空
                $m->rollback();
                $data['status'] = 102;
                $data['msg']    = '数据选择有误';
                $this->response($data,'json');
                exit();
            }
            // 查找是否有重复
            $isset2 = $m->where(array('template1_id' => $template_id,'template2_id' => $batch_template_id,'title2_id' => $val['batchId']))->find();
            if($isset2){
                continue;           // 重复，跳过
            }
            $da['template1_id']   = $template_id;        // 资料表id
            $da['template2_id']   = $batch_template_id;  // 批量表id
            $da['title1_id']      = $val['infoId'];
            $da['title2_id']      = $val['batchId'];
            $insert = $m->add($da);   // 写入两个表的关系放到关联表
            if(!$insert){
                $m->rollback();
                $data['status'] = 101;
                $data['msg']    = '关联失败';
                $this->response($data,'json');
                exit();
            }
        }
        $datas['app_code1'] = 'GT';
        $datas['data1_id'] = $template_id;
        $datas['app_code2'] = 'U6';
        $datas['data2_id'] = $batch_template_id;
        $datas['creator_id'] = $creator_id;
        $datas['created_time'] = date('Y-m-d H:i:s',time());
        M('data_constraint')->data($datas)->add();
        // 批量表改状态
        $status_code['status_code'] = 'connecting';
        M('product_batch_template')->where(array('id'=>$batch_template_id))->save($status_code);
        $m->commit();
        $data['status'] = 100;
        $this->response($data,'json');
    }


    /*
     * 根据模板id获取Bootstrap Table表格头 以及常用值
     * @param template_id 模板id
     * @param type_code info / batch
     */
    public function getTitleAndValid()
    {
        $template_id = I('post.template_id');
        $type_code   = I('post.type_code');
        if($type_code != 'info' && $type_code != 'batch'){
            $arr['status'] = 119;
            $arr['msg']    = '系统错误';
            $this->response($arr,'json');
            exit();
        }

        if(empty($template_id)){
            $arr['status'] = 104;
            $arr['msg']    = '未选择模板';
            $this->response($arr,'json');
            exit();
        }
        // 模板id读表头
        $res = \Think\Product\Product_Item_Template::GetBootstrapTable($type_code , $template_id);
        if(!empty($res))
        {
            $arr['status'] = 100;
            // 列出默认数据在返回数组里
            foreach($res as $key => $value)
            {
                $arr['value'][$key]['id']              = $value['id'];
                $arr['value'][$key]['en_name']         = $value['en_name'];
                $arr['value'][$key]['length']          = $value['length'];
                $arr['value'][$key]['precision']       = $value['precision'];
                $arr['value'][$key]['default_value']   = $value['default_value'];
                $arr['value'][$key]['data_type_code']  = $value['data_type_code'];
                $arr['value'][$key]['no']              = $value['no'];
                $arr['value'][$key]['template_id']     = $template_id;
                $arr['value'][$key]['filling_type']     = $value['filling_type'];//是否填写的数据
                $arr['value'][$key]['value_requlation'] = (int)$value['value_requlation'];//填写规则的数据
                $arr['value'][$key]['data_types']      = array('char','int','dc','dt','pic','upc_code');
                $arr['value'][$key]['length_types']    = array(20,50,100,200,500,1000,2000);
                $arr['value'][$key]['precision_dc']    = array(1,2,3);
                $arr['value'][$key]['iswrite_array']   = array(1,2,3);//是否填写的数组
                $arr['value'][$key]['rule_array']      = array(1,2);//填写规则的数组
                if($type_code == 'batch'){
                    $valid = M('product_item_valid_value')->where(array('template_id'=>$template_id,'item_id'=>$value['id']))->find();
                    $arr['value'][$key]['valid_value']     = explode(",",$valid['value']);
                }
                $arr['cs'] = $res;
            }
        }else{
            $arr['status'] = 101;
            $arr['msg']    = '暂无相关信息';
        }
        $this->response($arr,'json');
    }

    /*
     * 批量表表头上传
     * @param template_id 模板id
     * @param type_code info / batch
     * @param pageNumber excel页码
     * */
    public function upload_excel_header_file(){

        $template_id = I('template_id');
        $type_code   = I('type_code');
        $number      = I('post.pageNumber');
        $a = $number - 1;

        if(empty($number) || $a < 0){
            $data['status'] = 102;
            $data['msg']    = '模板页码有误';
            $this->response($data,'json');exit();
        }
        if(!preg_match("/^[0-9]+$/" , $template_id)){
            $data['status'] = 102;
            $data['msg']    = '未选择模板';
            $this->response($data,'json');exit();
        }
        $creator_id = I('post.creator_id');
        if(empty($creator_id)){
            $arr['status'] = 1012;
            $this->response($arr,'json');
            exit();
        }
        $extension = array('xls','xlsx');
        // 上传新模板先删除之前已经存在的
        M('product_item2batch_item')->where("template2_id=%d",array($template_id))->delete();
        M('product_item_valid_value')->where("template_id=%d",array($template_id))->delete();
        M('product_batch_item_template')->where("template_id=%d",array($template_id))->delete();

        if($_FILES){
            $type = strtolower(pathinfo($_FILES['file']['name'] , PATHINFO_EXTENSION));
            if(in_array($type , $extension)){
                if($type == 'xls'){
                    $types = 'Excel5';
                }elseif($type == 'xlsx'){
                    $types = 'Excel2007';
                }
                $size = (($_FILES['file']['size'])/1024)/1024; // mb兆
                if($size < 5){
                    $time = date("Ymd-His", time());
                    // 分出资料表与批量表两种模板区别
                    if($type_code == 'info'){
                        $m = M('product_item_template');
                        $file_name = "item_" . $time . '.' . $type;
                        $height = 6;   // 方便读取文件的行
                        $dir    = './Public/Product/Item/';
                    }else{
                        $m = M('product_batch_item_template');
                        $file_name = "batchItem_" . $time . '.' . $type;
                        $height = 3;
                        $dir    = './Public/Template/Item/';
                    }
                    $page   = (int)$a;
                    // 创文件夹
                    if(!is_dir($dir)){
                        mkdir($dir);
                    }
                    $m->startTrans();
                    $have = $m->where(array('template_id' => $template_id , 'enabled'=>1))->select(); // 已经存在表头数据  删除
                    if($have){
                        $m->where(array('template_id' => $template_id , 'enabled'=>1))->delete();
                    }
                    $item_file = $dir.$file_name;
                    if(move_uploaded_file($_FILES['file']['tmp_name'], $item_file)){
                        // 上传成功读取录入
                        $res = read_excel($item_file , $types , $page , $type_code ,$height);
                        $da['enabled']        = 1;
                        $da['creator_id']     = $creator_id;
                        $da['created_time']   = date('Y-m-d H:i:s' , time());
                        $da['modified_time']  = date('Y-m-d H:i:s' , time());
                        $da['template_id']    = $template_id;
                        // 分析数据包
                        if($type_code == 'info'){
                            $foreach_arr = $res[$height-1];
                        }else{
                            $foreach_arr = $res[$height-1];
                        }
                        foreach($foreach_arr as $ke => $val){
                            $da['data_type_code'] = 'char';
                            $da['length']         = 500;
                            if($type_code == 'batch'){
                                if(!preg_match("/^[a-z_A-Z\s0-9]+$/" , $val)){ // 数字字母下划线 空格
                                    $m->rollback();
                                    $data['status'] = 101;
                                    $data['msg']    = '表格数据有误';
                                    $this->response($data,'json');exit();
                                }
                                // 加载默认固定类型和代码长度限制
                                if(C($val)){
                                    $http_head = C($val);
                                    $da['data_type_code'] = $http_head['d_t_code'];
                                    $da['length']         = $http_head['length'];
                                }
                            }
                            $da['cn_name']   = $val;
                            $da['en_name']   = $val;
                            $da['title']     = $val;
                            $da['no']        = $ke + 1;
                            $da['value_requlation']  = '';
                            $da['filling_type']   = 3;
                            $haveInsert = $m->where(array('en_name'=>$val , 'template_id'=>$template_id))->find();
                            if($haveInsert){
                                continue;
                            }
                            $ad = $m->add($da);
                            if(!$ad){
                                $m->rollback();
                                $data['status'] = 101;
                                $data['msg']    = '写入错误';
                                $this->response($data,'json');exit();
                            }
                        }
                    }else{
                        $data['status'] = 101;
                        $data['msg']    = '文件上传出错';
                        $this->response($data,'json');exit();
                    }
                }else{
                    $data['status'] = 104;
                    $data['msg']    = '文件大小超负荷';
                    $this->response($data,'json');exit();
                }
            }else{
                $data['status'] = 103;
                $data['msg']    = '文件格式不被允许';
                $this->response($data,'json');exit();
            }
        }else{
            $data['status'] = 102;
            $data['msg']    = '没有文件被上传';
            $this->response($data,'json');exit();
        }

        if($type_code == 'info'){
            $template_id = 0;
        }
        // 添加文件与模板的关联方便读取写入数据
        $_file = array(
            'template_id'   => $template_id,
            'file'          => $file_name,
            'file_type'     => $type,
            'path'          => $dir,
            'creator_id'    => $creator_id,
            'created_time'  => date('Y-m-d H:i:s' , time()),
            'modified_time' => date('Y-m-d H:i:s' , time()),
        );
        $ins = M('product_batch_template2file')->add($_file);
        
        if($ins){
            $m->commit();
            if($type_code == 'batch'){
                $datas['number'] = $page;
                M('product_batch_template')->data($datas)->where("id=%d",array($template_id))->save();
                // 添加默认值
                $this->add_valid_value($item_file , $types , $template_id , $creator_id);
            }
            $data['status'] = 100;
            $this->response($data,'json');
        }else{
            $data['status'] = 101;
            $data['msg']    = '上传的文件与模板关联出错';
            $this->response($data,'json');exit();
        }

    }

    // 写完表头继续写常用值
    // @param item_file 上传的excel文件
    // @param types     excel文件类型
    // @param template_id 模板id
    public function add_valid_value($item_file , $types , $template_id , $creator_id)
    {
        $sm = M('product_item_valid_value');
        $sm->startTrans();
        $arr = array();
        // 读文件
        $res = read_excel($item_file , $types , 5);
        foreach($res as $key => $value){
            foreach($value as $k => $v){
                if($v != ''){
                    $arr[$k][] = $v;
                }
            }
        }
        foreach($arr as $kv => $vv){
            $strings[$vv[0]] = $vv;
        }
        foreach($strings as $kk => $vs){
            unset($vs[0]);
            $s[$kk] = implode("," ,$vs);
        }

        $headers = \Think\Product\Product_Item_Template::GetBootstrapTable('batch',$template_id);
        $das['template_id']   = $template_id;
        $das['creator_id']    = $creator_id;
        $das['created_time']  = date("Y-m-d H:i:s",time());
        $das['modified_time'] = date("Y-m-d H:i:s",time());

        $issetvalid = $sm->where(array('template_id'=>$template_id))->find();
        if($issetvalid){    // 已经添加过的删除
            $sm->where(array('template_id'=>$template_id))->delete();
        }

        foreach($s as $rk => $rv){ // 开始写入
            foreach($headers as $hv){
                if($rk == $hv['en_name']){

                    $das['item_id']  = $hv['id'];
                    $das['value']    = $rv;

                    $added = $sm->add($das);
                    if(!$added){
                        $sm->rollback();
                        $data['status'] = 101;
                        $data['msg']    = '常用值添加出错';
                        $this->response($data,'json');exit();
                    }
                }
            }
        }
        $sm->commit();
    }

    // 模板撤销
    // @param type_code info/batch
    // @param template_id 模板id
    public function template_back_step(){

        $type_code = I('post.type_code');
        if($type_code != 'info' && $type_code != 'batch'){
            $data['status'] = 119;
            $data['msg']    = '系统错误';
            $this->response($data,'json');exit();
        }

        $template_id = I('post.template_id');
        if(!preg_match("/^[0-9]+$/",$template_id)){
            $data['status'] = 102;
            $data['msg']    = '未选择模板';
            $this->response($data,'json');exit();
        }

        $result = \Think\Product\Product_Item_Template::template_back_step($type_code , $template_id);
        if($result['error'] == 0){
            $data['status'] = 100;
        }elseif($result['error'] == 1){
            $data['status'] = $result['status'];
            $data['msg']    = $result['msg'];
        }
        $this->response($data,'json');exit();
    }

    //获取需要编辑的默认值字段
    // @param type_code info/batch
    // @param form_id 表格id
    public function getEditDefault(){
        $form_id = I('post.form_id');
        $type_code = I('post.type_code');
        if($type_code != 'info' && $type_code != 'batch'){
            $arr['status'] = 119;
            $arr['msg']    = '系统错误';
            $this->response($arr,'json');
            exit();
        }
        if($type_code == 'info'){
            $form = M('product_form');
            $item = M('product_item_template');
            $where['default_value'] = '';
        }else{
            $form = M('product_batch_form');
            $item = M('product_batch_item_template');
        }
        // 表格查模板
        $sql = $form->field("template_id")->where("id=%d",array($form_id))->find();
        $where['filling_type'] = array('exp','IN (1,2)');
        $where['template_id'] = $sql['template_id'];
        // 列出选中的字段
        $query = $item->field("en_name,filling_type")->where($where)->select();
        foreach ($query as $key => $value) {
            if($value['filling_type'] == 1){
                $array['default'][$value['en_name']] = '';
            }else{
                $array['variant'][$value['en_name']] = array('','');
            }
        }
        if(empty($query[0])){
            $arr['status'] = 101;
            $arr['msg']    = '没有数据';
        }else{
            $arr['status'] = 100;
            $arr['value']    = $array;
        }
        $this->response($arr,'json');
    }

    // 获取数据检查的字段与检查规则
    // @param type_code info/batch
    // @param form_id 表格id
    public function getCheckRule(){
        $form_id = I('post.form_id');
        $type_code = I('post.type_code');
        if($type_code != 'info' && $type_code != 'batch'){
            $arr['status'] = 119;
            $arr['msg']    = '系统错误';
            $this->response($arr,'json');
            exit();
        }
        if($type_code == 'info'){
            $form = M('product_form');
            $item = M('product_item_template');
        }else{
            $form = M('product_batch_form');
            $item = M('product_batch_item_template');
        }
        $sql = $form->field("template_id")->where("id=%d",array($form_id))->find();
        $where['value_requlation'] = array('exp','IN (1,2)');
        $where['template_id'] = $sql['template_id'];
        $query = $item->field("en_name,value_requlation")->where($where)->select();
        foreach ($query as $key => $value) {
            $array[$key][] = $value['en_name'];
            $array[$key][] = $value['value_requlation']; 
        }
        if(empty($query)){
            $arr['status'] = 101;
            $arr['msg']    = '没有数据';
        }else{
            $arr['status'] = 100;
            $arr['value']  = $array;
            $arr['title']  = $title;
        }
        $this->response($arr,'json');
    }

    // 获取当前批量表模板与资料表模板的关联关系
    // @param batch_template_id 批量表表头id
    public function getcontact(){
        $batch_template_id = I('post.batch_template_id');
        if(empty($batch_template_id)){
            $arr['status'] = 102;
            $arr['msg']    = '模板id为空';
            $this->response($arr,'json');
            exit();
        }
        // 加载视图
        $item = D('TemplateContactView');
        $sql =$item->where("template2_id=%d",array($batch_template_id))->select();
        $query = M('product_template')->where("id=%d",array($sql[0]['template_id']))->find();
        foreach ($sql as $key => $value) {
            $data[$key]['template_id'] = $value['template_id'];
            $data[$key]['batch_template_id'] = $value['batch_template_id'];
            $data[$key]['batch'] = $value['batch'];
            $data[$key]['batchId'] = $value['batchid'];
            $data[$key]['info'] = $value['info'];
            $data[$key]['infoId'] = $value['infoid'];
        }
        if($sql){
            $arr['status'] = 100;
            $arr['template'] = $query;
            $arr['value'] = $data;
        }else{
            $arr['status'] = 101;
            $arr['msg']    = '没有数据';
        }
        $this->response($arr,'json');
    }
}