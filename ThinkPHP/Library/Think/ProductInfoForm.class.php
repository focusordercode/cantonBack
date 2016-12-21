<?php
namespace Think;
/**
 * 产品信息表格类
 */
class ProductInfoForm{
	/*
	 * 根据类目id获取相关表格/产品批量表格
	 */
	static function GetInfoForm($type_code , $status_code , $category_id, $pageSize = 15, $next = 1){
		//判断是产品信息表（info）还是产品批量表（batch）
		if($type_code == 'info'){
			$form = M("product_form");
			$tem  = M("product_template");
            $cou  = M('product_form_information');
            $field = "id,form_no,category_id,template_id,client_id,title,status_code,creator_id,created_time,modified_time";
		}elseif ($type_code == 'batch') {
			$form = M("product_batch_form");
			$tem  = M("product_batch_template");
            $cou  = M('product_batch_form_information');
            $field = "id,form_no,category_id,template_id,client_id,title,status_code,creator_id,created_time,modified_time,site_name";
		}

		//判断是否存在类目id（为空是查询所有表单）
		if(!empty($category_id)){
            $where['category_id'] = $category_id;
		}

		//判断要获取什么状态下的表单（为空是查询所有表单）
		if(!empty($status_code)){
            $where['status_code'] = $status_code;
		}

		$where['enabled'] = 1;

        $start = ( $next - 1 ) * $pageSize;
        $count = $form->where($where)->count();
		$sql   = $form->where($where)->field($field)->order('id desc')->limit($start,$pageSize)->select();

		foreach ($sql as $key => $value) {

			$name    = M("product_category")->field("cn_name")->where("id=%d",array($value['category_id']))->find();
			$cn_name = $tem->field("cn_name")->where("id=%d",array($value['template_id']))->find();
			$sql[$key]['name']      = $name['cn_name'];
			$sql[$key]['tempname']  = $cn_name['cn_name'];
			$sql[$key]['type_code'] = $type_code;

            $client = M('customer')->where(array('id' => $value['client_id']))->field('custom_name,company')->find();
            $sql[$key]['client_name'] = $client['custom_name'];
            $sql[$key]['company_name'] = $client['company'];
            $p_count = $cou->where(array('form_id' => $value['id']))->count();
            $sql[$key]['productCount'] = $p_count;
		}
        $data['value']     = $sql;
        $data['count']     = $count;
        $data['countPage'] = ceil($count / $pageSize);
        $data['pageNow']   = $next;

		return($data);
	}

	/*
	 * 根据模板id获取相关表格/产品批量表格
	 */
	static function GetTempInfoForm($type_code,$status_code,$template_id){
		if($type_code=='info'){
			$form=M("product_form");
			$tem=M("product_template");
            $field = "id,category_id,template_id,client_id,title,status_code,creator_id,created_time,modified_time";
		}elseif ($type_code=='batch') {
			$form=M("product_batch_form");
			$tem=M("product_batch_template");
            $field = "id,category_id,template_id,client_id,title,status_code,creator_id,created_time,modified_time,site_name";
		}
		if(!empty($template_id)){
            $where['template_id'] = $template_id;
		}
		if(!empty($status_code)){
            $where['status_code'] = $status_code;
		}
		$where['enabled'] = 1;
		$sql   = $form->field($field)->where($where)->select();
		foreach ($sql as $key => $value) {
			$name    = M("product_category")->field("cn_name")->where("id=%d",array($value['category_id']))->find();
			$cn_name = $tem->field("cn_name")->where("id=%d",array($value['template_id']))->find();
			$sql[$key]['name']        = $name['cn_name'];
			$sql[$key]['tempname']    = $cn_name['cn_name'];
			$sql[$key]['type_code']   = $type_code;

            $client = M('customer')->where(array('id' => $value['client_id']))->field('custom_name')->find();
			$sql[$key]['client_name'] = $client['custom_name'];
		}
		return($sql);
	}

	/*
	 * 根据id获取相关表格/产品批量表格
	 */
	static function GetOneForm($type_code,$id){
		if($type_code == 'info'){
			$form = M("product_form");
			$tem  = M("product_template");
            $cou  = M('product_form_information');
		}elseif ($type_code == 'batch') {
			$form = M("product_batch_form");
			$tem  = M("product_batch_template");
            $cou  = M('product_batch_form_information');
            $path = M('product_batch_form2file')->field("path")->where("form_id=%d",array($id))->find();
		}
		$where['id']      = $id;
		$where['enabled'] = 1;
		$sql = $form->where($where)->select();
		foreach ($sql as $key => $value) {
			$name    = M("product_category")->field("cn_name")->where("id=%d",array($value['category_id']))->find();
			$cn_name = $tem->field("cn_name")->where("id=%d",array($value['template_id']))->find();
			$sql[$key]['name']         = $name['cn_name'];
			$sql[$key]['tempname']     = $cn_name['cn_name'];
			$sql[$key]['type_code']    = $type_code;
            $client_name = M("customer")->where(array('id'=>$value['client_id']))->find();
			$sql[$key]['client_name']  = $client_name['custom_name'];
            if($type_code=='batch'){
                $sql[$key]['url'] = C('MY_HTTP_PATH').substr($path['path'],1);
            }
            $p_count = $cou->where(array('form_id' => $value['id']))->count();
            $sql[$key]['productCount'] = $p_count;
            $s = S($sql[$key]['form_no']);
            $sql[$key]['product_count'] = $s['product_count'];
            $sql[$key]['variant_num'] = $s['variant_num'];
		}
		return($sql);
	}

	/*
	 * 根据类目id和模板id获取相关表格/产品批量表格
	 */
	static function GetCTForm($type_code,$category_id,$template_id){
		if($type_code=='info'){
			$form=M("product_form");
			$tem=M("product_template");
            $field="id,category_id,template_id,client_id,title,status_code,creator_id,created_time,modified_time";
		}elseif ($type_code=='batch') {
			$form=M("product_batch_form");
			$tem=M("product_batch_template");
            $field="id,category_id,template_id,client_id,title,status_code,creator_id,created_time,modified_time,site_name";
		}
		$where['category_id']=$category_id;
		$where['template_id']=$template_id;
		$where['enabled']=1;

		$sql=$form->field($field)->where($where)->select();
		foreach ($sql as $key => $value) {
			$name=M("product_category")->field("cn_name")->where("id=%d",array($value['category_id']))->find();
			$cn_name=$tem->field("cn_name")->where("id=%d",array($value['template_id']))->find();
			$sql[$key]['name']=$name['cn_name'];
			$sql[$key]['tempname']=$cn_name['cn_name'];
			$sql[$key]['type_code']=$type_code;
            $client = M('customer')->where(array('id' => $value['client_id']))->field('custom_name')->find();
            $sql[$key]['client_name'] = $client['custom_name'];
		}
		return($sql);
	}

	/*
	 * 模糊搜索表格/产品批量表格
	 */
	static function VagueTitle($type_code,$title){
		if($type_code=='info'){
			$tem=M("product_template");
			$sql=M()->query("SELECT id,category_id,template_id,client_id,title,status_code,creator_id FROM tbl_product_form WHERE title LIKE '%".$title."%' ");
		    foreach ($sql as $key => $value) {
				$name=M("product_category")->field("cn_name")->where("id=%d",array($value['category_id']))->find();
				$cn_name=$tem->field("cn_name")->where("id=%d",array($value['template_id']))->find();
				$sql[$key]['name']=$name['cn_name'];
				$sql[$key]['tempname']=$cn_name['cn_name'];
				$sql[$key]['type_code']=$type_code;
                $client = M('customer')->where(array('id' => $value['client_id']))->field('custom_name')->find();
                $sql[$key]['client_name'] = $client['custom_name'];
			}
		}elseif ($type_code=='batch') {
			$tem=M("product_batch_template");
			$sql=M()->query("SELECT id,category_id,template_id,client_id,title,status_code,creator_id,site_name FROM tbl_product_batch_form WHERE title LIKE '%".$title."%' ");
			foreach ($sql as $key => $value) {
				$name=M("product_category")->field("cn_name")->where("id=%d",array($value['category_id']))->find();
				$cn_name=$tem->field("cn_name")->where("id=%d",array($value['template_id']))->find();
				$sql[$key]['name']      = $name['cn_name'];
				$sql[$key]['tempname']  = $cn_name['cn_name'];
				$sql[$key]['type_code'] = $type_code;
                $client = M('customer')->where(array('id' => $value['client_id']))->field('custom_name')->find();
                $sql[$key]['client_name'] = $client['custom_name'];
			}
		}
		return($sql);
	}

	/*
	 * 添加产品资料表格名/产品批量表格
 	 */
 	static function AddInfoForm($type_code,$data){
 		if($type_code=='info'){
			$form = M("product_form");
			$app_code = "product_form";
		}elseif ($type_code=='batch') {
			$form = M("product_batch_form");
			$app_code="product_batch_form";
		}
		$data_constraint = M('data_constraint');
		
		$form->startTrans();//启动事务
 		

		$sql = $form->data($data)->add();
		$creator_id = isset($_COOKIE["user_id"]) ? cookie("user_id") : 0;
 		if($sql){
 			if($type_code=='info'){
				$datas['app_code1'] = 'GT';
				$datas['data1_id'] = $data['template_id'];
				$datas['app_code2'] = '1D';
				$datas['data2_id'] = $data['id'];
				$datas['creator_id'] = $creator_id;
				$datas['created_time'] = date('Y-m-d H:i:s',time());
				$query = $data_constraint->data($datas)->add();
				if(empty($query)){
					$form->rollback();
					return -1;
				}
				$data1['app_code1'] = 'YF';
				$data1['data1_id'] = $data['client_id'];
				$data1['app_code2'] = '1D';
				$data1['data2_id'] = $data['id'];
				$data1['creator_id'] = $creator_id;
				$data1['created_time'] = date('Y-m-d H:i:s',time());
				$query1 = $data_constraint->data($data1)->add();
				if(empty($query1)){
					$form->rollback();
					return -1;
				}
				$data2['app_code1'] = 'YB';
				$data2['data1_id'] = $data['category_id'];
				$data2['app_code2'] = '1D';
				$data2['data2_id'] = $data['id'];
				$data2['creator_id'] = $creator_id;
				$data2['created_time'] = date('Y-m-d H:i:s',time());
				$query2 = $data_constraint->data($data2)->add();
				if(empty($query2)){
					$form->rollback();
					return -1;
				}
			}elseif ($type_code=='batch') {
				$datas['app_code1'] = 'U6';
				$datas['data1_id'] = $data['template_id'];
				$datas['app_code2'] = 'TG';
				$datas['data2_id'] = $data['id'];
				$datas['creator_id'] = $creator_id;
				$datas['created_time'] = date('Y-m-d H:i:s',time());
				$query = $data_constraint->data($datas)->add();
				if(empty($query)){
					$form->rollback();
					return -1;
				}
				$data1['app_code1'] = 'YF';
				$data1['data1_id'] = $data['client_id'];
				$data1['app_code2'] = 'TG';
				$data1['data2_id'] = $data['id'];
				$data1['creator_id'] = $creator_id;
				$data1['created_time'] = date('Y-m-d H:i:s',time());
				$query1 = $data_constraint->data($data1)->add();
				if(empty($query1)){
					$form->rollback();
					return -1;
				}
				$data2['app_code1'] = 'YB';
				$data2['data1_id'] = $data['category_id'];
				$data2['app_code2'] = 'TG';
				$data2['data2_id'] = $data['id'];
				$data2['creator_id'] = $creator_id;
				$data2['created_time'] = date('Y-m-d H:i:s',time());
				$query2 = $data_constraint->data($data2)->add();
				if(empty($query2)){
					$form->rollback();
					return -1;
				}
				$das['app_code1'] = '1D';
				$das['data1_id'] = $data['product_form_id'];
				$das['app_code2'] = 'TG';
				$das['data2_id'] = $data['id'];
				$das['creator_id'] = $creator_id;
				$das['created_time'] = date('Y-m-d H:i:s',time());
				$query3 = $data_constraint->data($das)->add();
				if(empty($query3)){
					$form->rollback();
					return -1;
				}
			}
 			$form->commit();//成功，事务提交
 			return $sql;
 		}else{
 			$form->rollback();//成功，事务回滚
 			return -1;
 		}
 	}

 	/*
 	 * 删除产品资料表格/产品批量表格
 	 */
 	static function DelInfoForm($type_code,$id){
 		if($type_code == 'info'){
			$form = M("product_form");
			$code = '1D';
			$res = checkDataLimit($code,$id);
			if($res != 1){
				return 3;
			}
		}elseif ($type_code == 'batch') {
			$form=M("product_batch_form");
			$code = 'TG';
		}
		$data_constraint = M('data_constraint');
		$form->startTrans();
 		$sql=$form->field("status_code")->where("id=%d",array($id))->find();
 		if($sql['status_code']=="enabled" || $sql['status_code']=="finished"){
 			//判断状态是否是有效状态或者完成状态，是就退出
 			return 2;
 		}else{
 			$where['app_code2'] = $code;
 			$where['data2_id'] = $id;
 			$del = $data_constraint->where($where)->delete();
 			$dt['enabled']=0;
 			$query=$form->data($dt)->where("id=%d",array($id))->save();
 			if($query!=='false'){
 				$form->commit(); 
 				return 1;
 			}else{
 				$form->rollback();
 				return -1;
 			}
 			
 		}
 	}

 	/*
 	 * 停用产品资料表格/产品批量表格
 	 */
 	static function StopInfoForm($type_code,$id){
 		if($type_code=='info'){
			$form=M("product_form");
		}elseif ($type_code=='batch') {
			$form=M("product_batch_form");
		}
		$form->startTrans();
		$query=$form->field("status_code")->where("id=%d",array($id))->find();
		if($query['status_code']=="finished"){
			return 2;
		}else{
			$data['status_code']='finished';
			$sql=$form->data($data)->where("id=%d",array($id))->save();
			if($query!=='false'){
				$form->commit(); 
	 			return 1;
	 		}else{
	 			$form->rollback();
	 			return -1;
	 		}			
		}
 	}

 	/*
 	 * 启用产品资料表格/产品批量表格
 	 */
 	static function UseInfoForm($type_code,$id){
 		if($type_code=='info'){
			$form=M("product_form");
		}elseif ($type_code=='batch') {
			$form=M("product_batch_form");
		}
		$form->startTrans();
		$query=$form->field("status_code")->where("id=%d",array($id))->find();
		if($query['status_code']=="enabled"){
			return 2;
		}else{
			$data['status_code']='enabled';
			$sql=$form->data($data)->where("id=%d",array($id))->save();
			if($query!=='false'){
				$form->commit();
	 			return 1;
	 		}else{
	 			$form->rollback();
	 			return -1;
	 		}			
		}

 			
 	}

 	/*
 	 * 修改产品资料表格
 	 */
 	static function UpdateInfoForm($type_code,$id,$data){
 		if($type_code == 'info'){
			$form = M("product_form");
		}elseif ($type_code == 'batch') {
			$form = M("product_batch_form");
		}

		$form->startTrans();
		$query = $form->field("status_code")->where("id=%d",array($id))->find();
		if($query['status_code'] == "enabled" || $query['status_code'] == "finished"){
			return 2;
		}
 		$sql = $form->data($data)->where("id=%d" , array($id))->save();
 		if($sql !== 'false'){
 			$form->commit();
 			return 1;
 		}else{
 			$form->rollback();
 			return -1;
 		}
 	}
}