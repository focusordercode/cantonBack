<?php
namespace Think\Product;
class Product_Template{

	public static function get($type , $where , $pageSize = 8, $next = 1){  // 模板获取
		if($type == 'info'){
			$tem = M("product_template");
            $for = M('product_form');
		}elseif ($type == 'batch') {
			$tem = M("product_batch_template");
            $for = M('product_batch_form');
		}
        $start = ( $next - 1 ) * $pageSize;
        $count  = $tem->where($where)->count();
		$result = $tem->where($where)->order("id desc")->limit($start , $pageSize)->select();  // 传入条件获取数据
		if($result){
			foreach ($result as $key => $value) {    //  根据分类id替换成中文插入到数据里面返回前台
				if($value['category_id'] == 1){
					$result[$key]['category_name'] = "默认模板";  // 1 为默认模板
					$result[$key]['type_code']     = $type;
				}else{
					$res = M("product_category")->where("id=".$value['category_id'])->field("cn_name")->find(); 
					$result[$key]['category_name'] = $res['cn_name'];
					$result[$key]['type_code'] = $type;
				}
                $hasform = $for->where('template_id=%d AND enabled=1',[$value['id']])->find();
                if(!$hasform){
                    $result[$key]['edit'] = 1;
                }else{
                	$result[$key]['edit'] = 0;
                }
			}
			$res['error']     = 0;
			$res['value']     = $result;
			$res['count']     = $count;
			$res['countPage'] = ceil($count / $pageSize);
			$res['pageNow']   = $next;
			$res['num'] = $pageSize;
		}else{
			$res['error'] = 1;
			$res['status'] = 101;
            $res['msg']    = '暂无相关信息';
		}
		return $res;
	}

	public static function GetLinkage($type,$category_id){
		if($type=='info'){
			$tem = M("product_template");
		}elseif($type=='batch') {
			$tem = M("product_batch_template");
		}
		$field = "id,cn_name";
		$sql   = $tem->field($field)->where("category_id=%d",array($category_id))->select();
		if($sql){
			return $sql;
		}else{
			$id = 1;
			$query=$tem->field($field)->where("category_id=%d",array($id))->select();
			return $query;
		}
	}

    // 添加模板
	public static function add($type,$data){
		if($type=='info'){
			$tem = M("product_template");
			$code = 'GT';
		}elseif ($type=='batch') {
			$tem = M("product_batch_template");
			$code = 'U6';
		}
		$tem->startTrans();
		if(!empty($data)){
			$result = $tem->add($data);
			if($result){
				$data_constraint = M('data_constraint');
				$das['app_code1'] = 'YB';
				$das['data1_id'] = $data['category_id'];
				$das['app_code2'] = $code;
				$das['data2_id'] = $result;
				$das['creator_id'] = $data['creator_id'];
				$das['created_time'] = date('Y-m-d H:i:s',time());
				$query = $data_constraint->data($das)->add();
				if(empty($query)){
					$tem->rollback();
					$res['error'] = 1;
					$res['status'] = 101;
        		    $res['msg']    = '添加失败';
        		    return $res;
				}
				$tem->commit();
				$res['error'] = 0;
				$res['value'] = $result;
			}else{
				$tem->rollback();
				$res['error'] = 1;
				$res['status'] = 101;
                $res['msg']    = '添加失败';
			}
		}
		return $res;
	}
    // 模板删除
	public static function del($type,$id){
		if($type=='info'){
			$tem = M("product_template");
			$code = 'GT';
		}elseif ($type=='batch') {
			$tem = M("product_batch_template");
			$code = 'U6';
		}
		$check = checkDataLimit($code,$id);
		if($check != 1){
			$res['error'] = 1;
			$res['status'] = 103;
            $res['msg']    = '该模板下有关联数据';
            return $res;
		}
		$data_constraint = M('data_constraint');
		$tem->startTrans();
		if(!empty($id)){
			$status = $tem->where("id=$id")->field("status_code")->find();  // 启用状态下的模板不可删除
			if($status['status_code'] == 'enabled'){
				$res['error'] = 1;
				$res['status'] = 104;
                $res['msg']    = '该状态下不可操作';
			}else{
				$data["enabled"] = 0;
				$result = $tem->where(array('id'=>$id))->save($data);
				if($result){
					$where['app_code2'] = $code;
					$where['data2_id'] = $id;
					$sql =  $data_constraint->where($where)->delete();
					if($sql === 'flase'){
						$tem->rollback();
						$res['error'] = 1;
						$res['status'] = 101;
                    	$res['msg']    = '保存失败';
					}else{
						$res['error'] = 0;
						$res['value'] = $result;
						$tem->commit();
					}	
				}else{
					$res['error'] = 1;
					$res['status'] = 101;
                    $res['msg']    = '保存失败';
				}				
			}

		}
		return $res;
	}

    // 模板信息修改
	public static function edit($type,$id,$data){
        if(empty($data)){
            $res['error'] = 1;
            $res['status'] = 102;
            $res['msg']    = '编辑数据有误';
            return $res;
        }
		if($type=='info'){
			$tem = M("product_template");
		}elseif ($type=='batch') {
			$tem = M("product_batch_template");
		}
        $status = $tem->where(array("id"=>$id))->field("status_code")->find();  // 启用状态下的模板不可修改
        if($status['status_code'] == 'enabled'){
            $res['error'] = 1;
            $res['status'] = 104;
            $res['msg']    = '该状态不可操作';
        }else{
            $result = $tem->where(array("id"=>$id))->save($data);
            if($result){
                $res['error'] = 0;
                $res['value'] = $result;
            }else{
                $res['error'] = 1;
                $res['status'] = 101;
                $res['msg']    = '编辑失败';
            }
        }
		return $res;
	}

    // 启用模板
	public static function use_tem($type,$id){
		if($type == 'info'){
			$tem = M("product_template");
		}elseif ($type == 'batch') {
			$tem = M("product_batch_template");
		}
		if(!empty($id)){
			$data["status_code"] = "enabled";
			$results = $tem->where("id=$id")->find();  // 查询模板是否已经是启用状态
			if($results["status_code"] == "enabled"){
				$res['status'] = 104;
                $res['msg']    = '该状态下不可操作';
			}else{
				$result = $tem->where("id=$id")->save($data);  // 启用
				if($result){
					$res['status'] = 100;
				}else{
					$res['status'] = 101;
                    $res['msg']    = '启用失败';
				}				
			}
		}else{
			$res['status'] = 102;
            $res['msg']    = '未选择模板';
		}
		return $res;
	}

    // 停用模板
	public static function stop_tem($type,$id){

		if($type=='info'){
			$tem = M("product_template");
		}elseif ($type == 'batch') {
			$tem = M("product_batch_template");
		}
		if(!empty($id)){
			$data["status_code"] = "disabled";
			$result = $tem->where("id=$id")->save($data);   // 停用模板
			if($result){
				$res['status'] = 100;
				$res['msg']    = '停用成功';
			}else{
				$res['status'] = 101;
                $res['msg']    = '停用失败';
			}
		}else{
			$res['status'] = 102;
            $res['msg']    = '未选择模板';
		}
		return $res;
	}

    // 模板模糊搜索
	static function VagueTemName($type,$data,$status_code,$is_paging,$pageSize = 8,$next = 1){
        $where = "enabled = 1";
        if(!empty($status_code)){  // 流程状态码
            $where .= " and status_code='$status_code'";
        }
        if(!empty($data)){  // 搜索关键字
            $where .= " and (cn_name like '%".$data."%' or en_name like '%".$data."%')";
        }elseif($data == '0'){
			$where .= " and (cn_name like '%".$data."%' or en_name like '%".$data."%')";
        }
        $start = ( $next - 1 ) * $pageSize;
		if($type == 'info'){
            $m = M('product_template');
            $for = M('product_form');
		}elseif ($type == 'batch') {
            $m = M('product_batch_template');
            $for = M('product_batch_form');
		}
		if($is_paging == 'yes'){
			$sql = $m->where($where)->order('id desc')->select();
		}else{
			$sql = $m->where($where)->order('id desc')->limit($start,$pageSize)->select();
		}
        
//        echo $m->_sql();
        $count = $m->where($where)->count();
        if($sql){
            foreach($sql as $key => $value){
                $sql[$key]['type_code'] = $type;
                $hasform = $for->where('template_id=%d AND enabled=1',[$value['id']])->find();
                if(!$hasform){
                    $sql[$key]['edit'] = 1;
                }else{
                	$sql[$key]['edit'] = 0;
                }
            }
            $res['error']     = 0;
            $res['value']     = $sql;
            $res['count']     = $count;
            $res['countPage'] = ceil($count / $pageSize);
            $res['pageNow']   = $next;
        }else{
            $res['error']  = 1;
            $res['status'] = 101;
            $res['msg']    = '暂无相关信息';
        }

        return($res);
	}

	static function GetItemValue($type_code,$enabled,$num=8,$next=1,$vague,$category_id){
		if($type_code == 'info'){
			$tem = M("product_template");
            $for = M("product_form");
		}elseif ($type_code == 'batch') {
			$tem = M("product_batch_template");
			$for = M("product_batch_form");
		}

		$where["enabled"] = 1;
		if(!empty($enabled)){
			$where['status_code'] = $enabled;
		}
		if(!empty($category_id)){
			$where['category_id'] =  $category_id;
		}
		if(!empty($vague)){ 
			$where['_string'] = '(cn_name like "%'.$vague.'%")  OR (en_name like "%'.$vague.'%") or (remark like "%'.$vague.'%")';
		}elseif($vague == '0'){
			$where['_string'] = '(cn_name like "%'.$vague.'%")  OR (en_name like "%'.$vague.'%") or (remark like "%'.$vague.'%")';
		}

		$start = ( $next - 1 ) * $num;

		$count  = $tem->where($where)->count();
		$sql = $tem->where($where)->order("id desc")->limit($start , $num)->select();
		//echo $tem->getLastSQL();exit();
		if($sql){
			foreach ($sql as $key => $value) {    //  根据分类id替换成中文插入到数据里面返回前台
				if($value['category_id'] == 1){
					$sql[$key]['category_name'] = "默认模板";  // 1 为默认模板
					$sql[$key]['type_code']     = $type_code;
				}else{
					$res = M("product_category")->where("id=".$value['category_id'])->field("cn_name")->find(); 
					$sql[$key]['category_name'] = $res['cn_name'];
					$sql[$key]['type_code'] = $type_code;
				}
				$hasform = $for->where('template_id=%d AND enabled=1',[$value['id']])->find();
                if(!$hasform){
                    $sql[$key]['edit'] = 1;
                }else{
                	$sql[$key]['edit'] = 0;
                }
			}
			$res['status']     = 100;
			$res['value']     = $sql;
			$res['count']     = $count;
			$res['countPage'] = ceil($count / $num);
			$res['pageNow']   = $next;
			$res['num'] = $num;
		}else{
			$res['status'] = 101;
            $res['msg']    = '暂无相关信息';
		}
		return $res;
	}
}