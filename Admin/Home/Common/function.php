<?php

/*
 * 读取excel内容
 * 参数说明 $filename   文件名(包括路径)
 * 参数说明 $type       文件类型 xls、xlsx
 * 参数说明 $model      获取的模块  Template、Valid Value
 * 参数说明 $highestRow 获取行数
 * */
function read_excel($filename,$type,$model,$type_code,$highestRow = ''){

    import("ORG.Util.PHPExcel");
    $objReader = PHPExcel_IOFactory::createReader($type);
    $objReader->setReadDataOnly(true);
    $objPHPExcel = $objReader->load($filename);
    $objWorksheet = $objPHPExcel->getSheet($model); // 最大读6页  第7页会出错
    if(empty($highestRow)){
        $highestRow = $objWorksheet->getHighestRow();
    }
    $highestColumn = $objWorksheet->getHighestColumn();
    $highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
    $excelData = array();
    if($type_code == 'info'){
        $row = 6;
    }elseif($type_code == 'batch'){
        $row = 3;
    }else{
        if($model == '0'){
            $row = 3;
        }else{
            $row = 2;
            $check = 'tem';
        }
    }
    
    for ( $row; $row <= $highestRow; $row++){
        for ($col = 0;$col < $highestColumnIndex; $col ++) {
            $string = trim((string)$objWorksheet->getCellByColumnAndRow($col,$row)->getValue());
            if(empty($check)){
                if($string != ""){
                    $excelData[$row-1][] = $string;
                }
            }else{
                $excelData[$row-1][] = $string;
            }
            
        }
    }
    return $excelData;
}

/*
 * 列出文件名
 * */
function read_file($dir){
    $dh = opendir(C('LOG_PATH').$dir);
    $i=0;
    while ($file = readdir($dh)) {
        if($file != "." && $file != "..") {
            $fullpath = $dir.$file;
            if(!is_dir($fullpath)) {
                $arr[$i]['url']=$dir.$file;
                $arr[$i]['name']=$file;
                $i++;
            }
        }
    }
    closedir($dh);
    return($arr);
}
/*
 * 获取全局id
 * $app_code   功能特性的应用代码 
 * $num        需要获取几个id
 */
function GetSysId($app_code,$num = 1)
{
   $arr = array();
   $sys = M("sys_sequence");
   $sys->startTrans();
   $sql = $sys->lock(true)->where("app_code='".$app_code."'")->find();
   $next_id = (int)$sql['next_id'];
   $step    = $sql['step'];
   $data['next_id'] = $next_id+$num*$step;
   $query = $sys->data($data)->where("app_code='".$app_code."'")->save();
   $sys->commit();
   for($i = 0;$i < $num;$i ++){
       $arr[] = $next_id;
       $next_id += $step;
   }
   return($arr);
}

/*
 * 根据应用代码和id查询是那个表
 */
function GETtable($id,$app_code){
    $fragment_table=M("fragment_table");
    $sql=$fragment_table->field("name,end_id")->where("ap1p_code='".$app_code."'and $id between start_id and  end_id")->find();
    return ($sql); 
}

/*
 * 根据id查询是那个表
 */
function GetIDtable($id){
    $fragment_table=M("fragment_table");
    $sql=$fragment_table->field("name")->where("$id > start_id and $id < end_id")->find();
    return $sql['name'];
}


function xml_decode($xml, $root = 'so') {
    $search = '/<(' . $root . ')>(.*)<\/\s*?\\1\s*?>/s';
    $array = array();
    if(preg_match($search, $xml, $matches)){
        $array = $this->xml_to_array($matches[2]);
    }
    return $array;
}

function xml_to_array($xml) {
    $search = '/<(\w+)\s*?(?:[^\/>]*)\s*(?:\/>|>(.*?)<\/\s*?\\1\s*?>)/s';
    $array = array ();
    if(preg_match_all($search, $xml, $matches)){
        foreach ($matches[1] as $i => $key) {
            $value = $matches[2][$i];
            if(preg_match_all($search, $value, $_matches)){
                $array[$key] = xml_to_array($value);
            }else{
                if('ITEM' == strtoupper($key)){
                    $array[] = html_entity_decode($value);
                }else{
                    $array[$key] = html_entity_decode($value);
                }
            }
        }
    }
    return $array;
}

function deldir($dir) {
    //先删除目录下的文件：
    $dh=opendir($dir);
    while ($file=readdir($dh)) {
        if($file!="." && $file!="..") {
            $fullpath=$dir."/".$file;
            if(!is_dir($fullpath)) {
                unlink($fullpath);
            } else {
                deldir($fullpath);
            }
        }
    }
    closedir($dh);
    //删除当前文件夹：
    if(rmdir($dir)) {
        return true;
    } else {
        return false;
    }
}

function delfile($dir){
    $dh=opendir($dir);
    while ($file=readdir($dh)) {
        if($file!="." && $file!="..") {
            $fullpath = $dir."/".$file;
            if(!is_dir($fullpath)) {
                unlink($fullpath);
            } else {
                delfile($fullpath);
            }
        }
    }
    closedir($dh);
}

// 类目信息树结构展示
function treeCa($arr,$lid,$rid,$l){
    $a = array();
    foreach($arr as $v){
        if($lid < $v['left_id'] && $rid > $v['left_id'] && ($l + 1) == $v['layer']){ // 查到对应子类则归类
            $v['children'] = treeCa($arr ,$v['left_id'],$v['right_id'],$v['layer']);
            $a[] = $v;
        }
    }
    return $a;
}

//打开一个挂起执行分区脚本的通道
function doRequest($url, $param=array())
{

    $urlinfo = parse_url($url);

    $host = $urlinfo['host'];
    $path = $urlinfo['path'];
    $query = isset($param) ? http_build_query($param) : '';

    $port = 80;
    $errno = 0;
    $errstr = '';
    $timeout = 10;

    $fp = fsockopen($host, $port, $errno, $errstr, $timeout);
    if (!$fp) {
        return flase;
    } else {
        $out = "POST " . $path . " HTTP/1.1\r\n";
        $out .= "host:" . $host . "\r\n";
        $out .= "content-length:" . strlen($query) . "\r\n";
        $out .= "content-type:application/x-www-form-urlencoded\r\n";
        $out .= "connection:close\r\n\r\n";
        $out .= $query;

        fputs($fp, $out);
        fclose($fp);
        //echo fread($fp, 1024); //我们不关心服务器返回
        return true;
    }
}

//随机生成模块编码
function generate_code( $length = 2 ) {  
    // 字符集，可任意添加你需要的字符  
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';  
    $code = '';  
    for ( $i = 0; $i < $length; $i++ ){  
        // 取字符数组 $chars 的任意元素  
        $code .= $chars[ mt_rand(0, strlen($chars) - 1) ];  
    }
    $sys_app = M('sys_app');  
    $where['code'] = $code;
    $sql = $sys_app->field("id")->where($where)->find();
    if(empty($sql)){
        return $code; 
    }else{
        $this->generate_code();
    }       
}


function __str_replace($str){

    $str = str_replace("&lt;", "<", $str);
    $str = str_replace("&gt;", ">", $str);
    return $str;
}


/**
 * CURL模拟POST上传图片到API
 * @param $url  图片服务器API地址
 * @param $name 图片文件名
 * @param $path 图片路径
 * @param $type 图片类型
 * @param $form_id 图片所属的表格id
 * @return json 图片上传结果
 */
function imageUpload( $name, $path, $type, $form_id, $id, $num, $pic_id='')
{
    \Think\Log::record("开始时间:".date('Y-m-d H:i:s',time()),'DEBUG',true);
    set_time_limit(0);
    // 图片API服务器
    //$url = 'http://photo.focusorder.com/upload.api.php';
    //$url = 'http://120.25.228.115/InterPhotos/upload.api.php';
    $url = 'http://192.168.1.40/interphoto/upload.api.php';
    $post_data = array(
        'pic' => new CURLFile(realpath($path), $type, $name),
        'categoryid' => 1,// gallery_id
        'form_id' => $form_id,
        'pic_id'  => $pic_id,
        'ids' => json_encode($id)
    );
    \Think\Log::record("jshu时间:".date('Y-m-d H:i:s',time()),'DEBUG',true);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST,true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT , 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
    $res = curl_exec($ch);
    curl_close($ch);
    \Think\Log::record("wc时间:".date('Y-m-d H:i:s',time()),'DEBUG',true);
    return $res;
}

/**
 * CURL模拟POST到图片空间检测图片是否存在
 * @param $url  图片服务器API地址
 * @return pic_id 图片id
 * @param $num  图片需要复制的次数 
 */
function imageCheck($pic_id,$categoryid,$id)
{
    \Think\Log::record("开始时间:".date('Y-m-d H:i:s',time()),'DEBUG',true);
    set_time_limit(0);
    // 图片API服务器
    //$url = 'http://photo.focusorder.com/upload.api.php';
    //$url = 'http://120.25.228.115/InterPhotos/upload.api.php';
    $url = 'http://192.168.1.40/interphoto/imageupload.php';
    $post_data = array(
        'pic_id' => $pic_id,
        'categoryid' => $categoryid,
        'ids' => json_encode($id)
    );
    \Think\Log::record("jshu时间:".date('Y-m-d H:i:s',time()),'DEBUG',true);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST,true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT , 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
    $res = curl_exec($ch);
    curl_close($ch);
    \Think\Log::record("wc时间:".date('Y-m-d H:i:s',time()),'DEBUG',true);
    return $res;
}

// 图片上传抓取处理
function pre_arr($arr , $product_id){
    $r_arr = [];
    $i = 0;
    $len = count($arr);
    foreach($product_id as $k => $pid){
        foreach($arr as $key => $value){
            if($value['product_id'] == $pid || $value['parent_id'] == $pid){
                $r_arr[$i]['ids']      .= $value['id'].",";
                $r_arr[$i]['image_url'] = 'http://'.$_SERVER['HTTP_HOST'].__ROOT__.substr($value['image_url'], 1);
                $r_arr[$i]['photo'] = 'http://'.$_SERVER['HTTP_HOST'].__ROOT__.substr($value['image_url'], 1);
                unset($arr[$key]);
            }
        }
        $i ++;
    }
    foreach($r_arr as $k => $v){
        $r_arr[$k]['ids'] = substr($v['ids'] , 0 , strlen($v['ids'])-1);
    }
    return array_merge($r_arr);
}

//可扩展的数据约束方法
function checkDataLimit($model,$id){
    $data_constraint = M('data_constraint');
    $where['app_code1'] = $model;
    $where['data1_id'] = $id;
    $sql = $data_constraint->field("id")->where($where)->find();
    if(empty($sql['id'])){
        return 1;
    }else{
        return -1;
    }
}

/**
 * DISCUZ 加密函数
 * $string 明文或密文
 * $operation 加密ENCODE或解密DECODE
 * $key 密钥
 * $expiry 密钥有效期
 */
function authcode($string, $operation = 'DECODE', $key = '', $expiry = 0)
{
    $ckey_length = 4;
    // 密匙
    // $GLOBALS['discuz_auth_key'] 这里可以根据自己的需要修改
    $key = md5($key ? $key : C('auth_key'));

    // 密匙a会参与加解密
    $keya = md5(substr($key, 0, 16));
    // 密匙b会用来做数据完整性验证
    $keyb = md5(substr($key, 16, 16));
    // 密匙c用于变化生成的密文
    $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';
    // 参与运算的密匙
    $cryptkey = $keya.md5($keya.$keyc);
    $key_length = strlen($cryptkey);
    // 明文，前10位用来保存时间戳，解密时验证数据有效性，10到26位用来保存$keyb(密匙b)，解密时会通过这个密匙验证数据完整性
    // 如果是解码的话，会从第$ckey_length位开始，因为密文前$ckey_length位保存 动态密匙，以保证解密正确
    $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
    $string_length = strlen($string);
    $result = '';
    $box = range(0, 255);
    $rndkey = array();
    // 产生密匙簿
    for($i = 0; $i <= 255; $i++) {
        $rndkey[$i] = ord($cryptkey[$i % $key_length]);
    }
    // 用固定的算法，打乱密匙簿，增加随机性，好像很复杂，实际上并不会增加密文的强度
    for($j = $i = 0; $i < 256; $i++) {
        $j = ($j + $box[$i] + $rndkey[$i]) % 256;
        $tmp = $box[$i];
        $box[$i] = $box[$j];
        $box[$j] = $tmp;
    }
    // 核心加解密部分
    for($a = $j = $i = 0; $i < $string_length; $i++) {
        $a = ($a + 1) % 256;
        $j = ($j + $box[$a]) % 256;
        $tmp = $box[$a];
        $box[$a] = $box[$j];
        $box[$j] = $tmp;
        // 从密匙簿得出密匙进行异或，再转成字符
        $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
    }
    if($operation == 'DECODE') {
        // substr($result, 0, 10) == 0 验证数据有效性
        // substr($result, 0, 10) - time() > 0 验证数据有效性
        // substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16) 验证数据完整性
        // 验证数据有效性，请看未加密明文的格式
        if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
            return substr($result, 26);
        } else {
            return '';
        }
    } else {
        // 把动态密匙保存在密文里，这也是为什么同样的明文，生产不同密文后能解密的原因
        // 因为加密后的密文可能是一些特殊字符，复制过程可能会丢失，所以用base64编码
        return $keyc.str_replace('=', '', base64_encode($result));
    }
}

/*
 * 密码加密解密函数
 * */
function EncodePwd($pwdstr , $salt){
    $pwd = md5(md5($pwdstr).$salt);
    return $pwd;
}

// 权限系统通用树结构
function pre($arr,$pid = 0){
    $a = array();
    foreach($arr as $v){
        if($v['p_id'] == $pid){ // 查到对应子类则归类
            $v['son'] = pre($arr ,$v['id']);
            $a[] = $v;
        }
    }
    return $a;
}

// 更新图片类目缓存
function updateGalleryCache(){
    $parents = M('product_gallery')->find(1);
    $result  = M()->query("SELECT g.id,g.cn_name,g.en_name,g.category_id,g.layer,g.left_id,g.right_id,p.picture_count FROM `imageview` AS g LEFT JOIN (SELECT gallery_id,COUNT(*) AS picture_count FROM `tbl_product_picture` GROUP BY gallery_id) AS p ON g.id = p.gallery_id GROUP BY g.id");
    foreach($result as $k => $val){
        if($val['picture_count'] == null){
            $result[$k]['picture_count'] = 0;
        }
    }
    $results = treeCa($result,1,$parents['right_id'],1);
    $parents['children'] = $results;
    S('gallery' ,$parents);
}

// sql防注入处理
function __sqlSafe__($sql){
    $entities_match = array(';','$','!','@','#','^','&','{','}','"','?','[',']','\\','/','+','~','`');
    return str_replace($entities_match, '', trim($sql));
}

/*
 * 操作限制
 * @param $model        操作的模块
 * @param $operationId  操作的数据id
 * @param $overTime     默认超时时间（必需以秒为单位  例：1分钟 填写 60）
 * @param $uid          登陆用户的id
 * @param $type         访问类型 只是读取 R  需要编辑操作 W
 * */
function limitOperation($model ,$operationId ,$overTime ,$uid , $type = 'W')
{
    $t = M('user_restrict');
    $result = $t->where("operation_id = $operationId AND model_name = '$model'")->find();
    if(!$result)
    {
        if($type == 'R') return true;
        $reSave1 = $t->add([
            'model_name'    => $model,
            'operation_id'  => $operationId,
            'uid'           => $uid,
            'modified_time' => time(),
        ]);
        if(!$reSave1) return false;
        return true;
    }

    // 还是刚刚用户操作
    if($uid == $result['uid']) {
        $t->where("operation_id = $operationId AND model_name = '$model'")->save([
            'modified_time' => time()
        ]);
        return true;
    }

    // 查询到了最后操作时间 + 默认超时时间 = 过期时间
    $expired = $result['modified_time'] + $overTime;
    if($expired >= time()) return false;

    // 否则可以进去编辑，需要修改时间
    if($type == 'R') return true;

    $reSave = $t->where("operation_id = $operationId AND model_name = '$model'")->save([
        'uid'           => $uid,
        'modified_time' => time(),
    ]);
    if($reSave) return true;
    return false;
}

/*
 * 手动结束编辑操作
 * @param $table        操作的模块
 * @param $operationId  操作的数据id
 * */
function EndEditTime($model ,$operationId)
{
    $t = M('user_restrict');
    $t->where("operation_id = $operationId AND model_name = '$model'")->delete();
    return true;
}
