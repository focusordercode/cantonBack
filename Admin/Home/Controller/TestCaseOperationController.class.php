<?php
namespace Home\Controller;
use Think\Controller\RestController;
header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Methods:POST,GET');
header('Access-Control-Allow-Credentials:true'); 
header("Content-Type: application/json;charset=utf-8");

/**
*  测试用例的操作
*/
class TestCaseOperationController extends RestController{
	protected $arr = array('batch','info','template','batch_tem','all');

	/*
	 * 复制数据
	 */
	public function copyDatas(){
		set_time_limit(0);
		$model = I('model');
		$id = (int)I('id');
		if(!in_array($model, $this->arr)){
			$array['status'] = 102;
			$array['msg'] = "要复制的模块不存在";
			$this->response($array,'json');
			exit();
		}
		if(empty($id)){
			$array['status'] = 102;
			$array['msg'] = "请输入id";
			$this->response($array,'json');
			exit();
		}
		switch ($model) {
			case 'batch':
				$outcome = $this->operBatch($id,'copy');
				break;
			case 'info':
				$outcome = $this->operInfo($id,'copy');
				break;
			case 'template':
				$outcome = $this->operTemplate($id,'copy');
				break;
			default:
				$outcome = $this->operBatchTemplate($id,'copy');
				break;
		}
		if($outcome == 1){
			$array['status'] = 100;
		}else{
			$array['status'] = 101;
			$array['msg'] = "操作失败";
		}
		$this->response($array,'json');
	}

	/*
	 * 删除测试数据
	 */
	public function delDatas(){
		set_time_limit(0);
		$model = I('model');
		$id = (int)I('id');
		$start_time = I('start_time');
		$end_time = I('end_time');
		if(!in_array($model, $this->arr)){
			$array['status'] = 102;
			$array['msg'] = "要复制的模块不存在";
			$this->response($array,'json');
			exit();
		}
		if(empty($id)){
			$array['status'] = 102;
			$array['msg'] = "请输入id";
			$this->response($array,'json');
			exit();
		}
		switch ($model) {
			case 'batch':
				$outcome = $this->operBatch($id,'delete');
				break;
			case 'info':
				$outcome = $this->operInfo($id,'delete');
				break;
			case 'template':
				$outcome = $this->operTemplate($id,'delete');
				break;
			case 'batch_tem':
				$outcome = $this->operBatchTemplate($id,'delete');
				break;
			default:
				$outcome = $this->operALL($id,$start_time,$end_time);
				break;
		}
		if($outcome == 1){
			$array['status'] = 100;
		}else{
			$array['status'] = 101;
			$array['msg'] = "操作失败";
		}
		$this->response($array,'json');
	}

	/*
	 * 操作批量表
	 */
	protected function operBatch($id,$manipulate){
		set_time_limit(0);
		$data_code = M('data_code');
		$form = M('product_batch_form');
		$batch_form2info = M('product_batch_form_information');
		$info = M('product_batch_information');
		$data_constraint = M('data_constraint');
		$info->startTrans();
		if($manipulate == 'copy'){//复制操作
			//获取表格编码
			$where['code'] = 'TG';
			$sql = $data_code->where($where)->find();
			$databasecode = '01';
			$businesscode = $databasecode.$sql['code'].$sql['number'];
			$data['number'] = str_pad($sql['number']+1,8,"0",STR_PAD_LEFT);
			$data['update_time'] =date('Y-m-d H:i:s',time());
			$query = $data_code->where($where)->save($data);
			$data = array();
			//复制表格
			$sql_form = $form->where("id=%d",array($id))->find();
			$da = $sql_form;
			$da['id'] = (int)substr($businesscode,7);
			$da['form_no'] = $businesscode;
			$da['title'] = 'test_'.$sql_form['title'];
			$da['creator_id'] = 10000;
			$da['created_time'] = date('Y-m-d H-i-s',time());
			$da['modified_time'] = date('Y-m-d H-i-s',time());
			$set_form = $form->data($da)->add();
			$da =array();
			if($set_form === 'flase'){
				$info->rollback();
				return -1;
			}

			$count = $batch_form2info->where("form_id=%d",array($id))->count();
			$get_batch4info = $batch_form2info->where("form_id=%d",array($id))->select();
			$countTem = M('product_batch_item_template')->where("template_id=%d",array($sql_form['template_id']))->count();
			$product_ids = GetSysId('product_batch_information',$count);
			$num = $count * $countTem;
			$ids = GetSysId('product_batch_information_record',$num);

			//复制表格与表格数据之间的中间表
			$data['form_id'] = (int)substr($businesscode,7);
			foreach ($product_ids as $key => $value) {
				$data['product_id'] = $value;
				$data['created_time'] = date('Y-m-d H-i-s',time());
				$add = $batch_form2info->data($data)->add();
				if(empty($add)){
					$info->rollback();
					return -2;
				}
			}
			$data = array();

			$i = 0;
			$j = 0;
			//复制表格
			$parent_ids = M()->query("SELECT product_id FROM tbl_product_batch_information where product_id in (SELECT product_id FROM tbl_product_batch_form_information where form_id = ".$id.") and parent_id = 0 GROUP BY (product_id)");
			foreach ($parent_ids as $keys => $values) {
				$get_info = $info->where("product_id=%d",array($values['product_id']))->select();
				foreach ($get_info as $k => $val) {
					$datas = $val;
					$datas['id'] = $ids[$j];
					$datas['product_id'] = $product_ids[$i];
					$datas['parent_id'] = 0;
					$datas['creator_id'] = 10000;
					$datas['created_time'] = date('Y-m-d H-i-s',time());
					$datas['modified_time'] = date('Y-m-d H-i-s',time());
					$set_info = $info->data($datas)->add();
					if($set_info === 'flase'){
						$info->rollback();
						return -3;
					}
					$j++;
				}
				$product_id = $product_ids[$i];
				$i++;
				$son_ids = M()->query("SELECT product_id FROM tbl_product_batch_information where parent_id = ".$values['product_id']." GROUP BY (product_id)");
				foreach ($son_ids as $son_key => $son_value) {
					$son_info = $info->where("product_id=%d",array($son_value['product_id']))->select();
					foreach ($son_info as $son_k => $son_val) {
						$datas = $son_val;
						$datas['id'] = $ids[$j];
						$datas['product_id'] = $product_ids[$i];
						$datas['parent_id'] = $product_id;
						$datas['creator_id'] = 10000;
						$datas['created_time'] = date('Y-m-d H-i-s',time());
						$datas['modified_time'] = date('Y-m-d H-i-s',time());
						$set_info = $info->data($datas)->add();
						if($set_info === 'flase'){
							$info->rollback();
							return -3;
						}
						$j++;
					}
					$i++;
				}		
			}
			$info->commit();
			return 1;
		}else{
			//删除表格内容
			$get = $batch_form2info->field("product_id")->where("form_id=%d",array($id))->select();
			foreach ($get as $key => $value) {
				$del = $info->where("product_id=%d",array($value['product_id']))->delete();
				if($del === 'flase'){
					$info->rollback();
					return -2;
				}
			}
			//删除表格与表格内容的中间表
			$query = $batch_form2info->where("form_id=%d",array($id))->delete();
			if($query === 'flase'){
				$info->rollback();
				return -3;
			}
			$where['app_code2'] = 'TG';
			$where['data2_id'] = $id;
			$del = $data_constraint->where($where)->delete();
			$upc_data['form_id'] = 0;
			$upc_data['locked'] = 0;
			$upc_sql = M('product_upc_code')->data($upc_data)->where("form_id=%d",array($id))->save();
			//删除表格
			$sql = $form->where("id=%d",array($id))->delete();
			if($sql === 'flase'){
				$info->rollback();
				return -1;
			}
			$info->commit();
			return 1;
		}
		
	}

	/*
	 * 操作资料表
	 */
	protected function operInfo($id,$manipulate){
		set_time_limit(0);
		$data_code = M('data_code');
		$form = M('product_form');
		$form_info = M('product_form_information');
		$info = M('product_information');
		$pic = M('product_for_picture');
		$data_constraint = M('data_constraint');
		$info->startTrans();
		if($manipulate == 'copy'){
			//获取表格编码
			$where['code'] = '1D';
			$sql = $data_code->where($where)->find();
			$databasecode = '01';
			$businesscode = $databasecode.$sql['code'].$sql['number'];
			$data['number'] = str_pad($sql['number']+1,8,"0",STR_PAD_LEFT);
			$data['update_time'] =date('Y-m-d H:i:s',time());
			$query = $data_code->where($where)->save($data);
			$data = array();
			//复制表格
			$sql_form = $form->where("id=%d",array($id))->find();
			$da = $sql_form;
			$da['id'] = (int)substr($businesscode,7);
			$da['form_no'] = $businesscode;
			$da['title'] = 'test_'.$sql_form['title'];
			$da['creator_id'] = 10000;
			$da['created_time'] = date('Y-m-d H-i-s',time());
			$da['modified_time'] = date('Y-m-d H-i-s',time());
			$set_form = $form->data($da)->add();
			$da =array();
			if($set_form === 'flase'){
				$info->rollback();
				return -1;
			}

			$count = $form_info->where("form_id=%d",array($id))->count();
			$get_batch4info = $form_info->where("form_id=%d",array($id))->select();
			$countTem = M('product_item_template')->where("template_id=%d",array($sql_form['template_id']))->count();
			$product_ids = GetSysId('product_information',$count);
			$num = $count * $countTem;
			$ids = GetSysId('product_information_record',$num);	

			//复制表格与表格数据之间的中间表
			$data['form_id'] = (int)substr($businesscode,7);
			foreach ($product_ids as $key => $value) {
				$data['product_id'] = $value;
				$data['created_time'] = date('Y-m-d H-i-s',time());
				$add = $form_info->data($data)->add();
				if(empty($add)){
					$info->rollback();
					return -2;
				}
			}
			$data = array();
	
			$i = 0;
			$j = 0;

			//复制表格
			$parent_ids = M()->query("SELECT product_id FROM tbl_product_information where product_id in (SELECT product_id FROM tbl_product_form_information where form_id = ".$id.") and parent_id = 0 GROUP BY (product_id)");
			foreach ($parent_ids as $keys => $values) {
				$get_info = $info->where("product_id=%d",array($values['product_id']))->select();
				foreach ($get_info as $k => $val) {
					$datas = $val;
					$datas['id'] = $ids[$j];
					$datas['product_id'] = $product_ids[$i];
					$datas['parent_id'] = 0;
					$datas['creator_id'] = 10000;
					$datas['created_time'] = date('Y-m-d H-i-s',time());
					$datas['modified_time'] = date('Y-m-d H-i-s',time());
					$set_info = $info->data($datas)->add();
					if($set_info === 'flase'){
						$info->rollback();
						return -3;
					}
					$j++;
				}
				$product_id = $product_ids[$i];
				$i++;
				$son_ids = M()->query("SELECT product_id FROM tbl_product_information where parent_id = ".$values['product_id']."  GROUP BY (product_id)");
				foreach ($son_ids as $son_key => $son_value) {
					$son_info = $info->where("product_id=%d",array($son_value['product_id']))->select();
					foreach ($son_info as $son_k => $son_val) {
						$datas = $son_val;
						$datas['id'] = $ids[$j];
						$datas['product_id'] = $product_ids[$i];
						$datas['parent_id'] = $product_id;
						$datas['creator_id'] = 10000;
						$datas['created_time'] = date('Y-m-d H-i-s',time());
						$datas['modified_time'] = date('Y-m-d H-i-s',time());
						$set_info = $info->data($datas)->add();
						if($set_info === 'flase'){
							$info->rollback();
							return -3;
						}
						$j++;
					}
					$i++;
				}		
			}
			$info->commit();
			return 1;
		}else{
			//删除表格内容
			$get = $form_info->field("product_id")->where("form_id=%d",array($id))->select();
			foreach ($get as $key => $value) {
				$del = $info->where("product_id=%d",array($value['product_id']))->delete();
				if($del === 'flase'){
					$info->rollback();
					return -2;
				}
			}
			//删除表格与表格内容的中间表
			$query = $form_info->where("form_id=%d",array($id))->delete();
			if($query === 'flase'){
				$info->rollback();
				return -3;
			}

			$where['app_code2'] = '1D';
			$where['data2_id'] = $id;
			$del = $data_constraint->where($where)->delete();
			if($del === 'flase'){
				$info->rollback();
				return -1;
			}

			$delete = $pic->where("form_id=%d",array($id))->delete();
			if($delete === 'flase'){
				$info->rollback();
				return -1;
			}
			
			//删除表格
			$sql = $form->where("id=%d",array($id))->delete();
			if($sql === 'flase'){
				$info->rollback();
				return -1;
			}
			$info->commit();
			return 1;
		}
	}

	/*
	 * 操作批量表模板
	 */
	protected function operBatchTemplate($id,$manipulate){
		set_time_limit(0);
		$tem = M('product_batch_template');
		$file = M('product_batch_template2file');
		$item = M('product_batch_item_template');
		$item_val = M('product_item_valid_value');
		$item2batch_item = M('product_item2batch_item');
		$data_constraint = M('data_constraint');
		$tem->startTrans();
		if($manipulate == 'copy'){
			//复制模板
			$sql = $tem->where("id=%d",array($id))->find();
			unset($sql['id']);
			$sql['cn_name'] = "测试_".$sql['cn_name'];
			$sql['en_name'] = "Test_".$sql['en_name'];
			$sql['creator_id'] = 10000;
			$sql['created_time'] = date('Y-m-d H:i:s',time());
			$sql['modified_time'] = date('Y-m-d H:i:s',time());
			$query = $tem->data($sql)->add();
			if(empty($query)){
				$tem->rollback();
				return -1;
			}

			//复制模板文件
			$sql_file = $file->where("template_id=%d",array($id))->find();
			unset($sql_file['id']);
			$sql_file['template_id'] = $query;
			$sql_file['creator_id'] = 10000;
			$sql_file['created_time'] = date('Y-m-d H:i:s',time());
			$sql_file['modified_time'] = date('Y-m-d H:i:s',time());
			$query_file = $file->data($sql_file)->add();
			if(empty($query_file)){
				$tem->rollback();
				return -2;
			}
			//复制模板内容
			$sql_item = $item->where("template_id=%d",array($id))->select();
			foreach ($sql_item as $key => $value) {
				$data = $value;
				unset($data['id']);
				$data['template_id'] = $query;
				$data['creator_id'] = 10000;
				$data['created_time'] = date('Y-m-d H:i:s',time());
				$data['modified_time'] = date('Y-m-d H:i:s',time());
				$query_item = $item->data($data)->add();
				if(empty($query_item)){
					$tem->rollback();
					return -3;
				}
				//复制模板有效值
				$sql_value = $item_val->where("template_id=%d AND item_id=%d",array($id,$value['id']))->find();
				if(!empty($sql_value['id'])){
					unset($sql_value['id']);
					$sql_value['template_id'] = $query;
					$sql_value['item_id'] = $query_item;
					$sql_value['creator_id'] = 10000;
					$sql_value['created_time'] = date('Y-m-d H:i:s',time());
					$sql_value['modified_time'] = date('Y-m-d H:i:s',time());
					$query_value =  $item_val->data($sql_value)->add();
					if(empty($query_value)){
						$tem->rollback();
						return -4;
					}
				}
				//复制模板关联关系
				$sql_info2batch = $item2batch_item->where("template2_id=%d AND title2_id=%d",array($id,$value['id']))->find();
				if(!empty($sql_info2batch['id'])){
					unset($sql_info2batch['id']);
					$sql_info2batch['template2_id'] = $query;
					$sql_info2batch['title2_id'] = $query_item;
					$sql_info2batch['creator_id'] = 10000;
					$sql_info2batch['created_time'] = date('Y-m-d H:i:s',time());
					$sql_info2batch['modified_time'] = date('Y-m-d H:i:s',time());
					$query_value =  $item2batch_item->data($sql_info2batch)->add();
					if(empty($query_value)){
						$tem->rollback();
						return -5;
					}
				}
			}
			
			$tem->commit();
			return 1;
		}else{
			
			//删除模板内容
			$query = $item->where("template_id=%d",array($id))->delete();
			if($query === 'flase'){
				$tem->rollback();
				return -2;
			}
			//删除模板有效值
			$del_value = $item_val->where("template_id=%d",array($id))->delete();
			if($del_value === 'flase'){
				$tem->rollback();
				return -3;
			}
			//删除模板文件
			$del_file = $file->where("template_id=%d",array($id))->delete();
			if($del_file === 'flase'){
				$tem->rollback();
				return -4;
			}
			//删除批量表模板与资料表模板的关联关系
			$del_info2batch = $item2batch_item->where("template2_id=%d",array($id))->delete();
			if($del_file === 'flase'){
				$tem->rollback();
				return -5;
			}
			$where['app_code2'] = 'U6';
			$where['data2_id'] = $id;
			$del = $data_constraint->where($where)->delete();
			//删除模板
			$sql = $tem->where("id=%d",array($id))->delete();
			if($sql === 'flase'){
				$tem->rollback();
				return -1;
			}
			$tem->commit();
			return 1;
		}
			
	}

	/*
	 * 操作资料表模板
	 */
	protected function operTemplate($id,$manipulate){
		set_time_limit(0);
		$tem = M('product_template');
		$item = M('product_item_template');
		$data_constraint = M('data_constraint');
		$tem->startTrans();
		if($manipulate == 'copy'){
			//复制模板
			$sql = $tem->where("id=%d",array($id))->find();
			unset($sql['id']);
			$sql['cn_name'] = "测试_".$sql['cn_name'];
			$sql['en_name'] = "Test_".$sql['en_name'];
			$sql['creator_id'] = 10000;
			$sql['created_time'] = date('Y-m-d H:i:s',time());
			$sql['modified_time'] = date('Y-m-d H:i:s',time());
			$query = $tem->data($sql)->add();
			if(empty($query)){
				$tem->rollback();
				return -1;
			}

			//复制模板内容
			$sql_item = $item->where("template_id=%d",array($id))->select();
			foreach ($sql_item as $key => $value) {
				$data = $value;
				unset($data['id']);
				$data['template_id'] = $query;
				$data['creator_id'] = 10000;
				$data['created_time'] = date('Y-m-d H:i:s',time());
				$data['modified_time'] = date('Y-m-d H:i:s',time());
				$query_item = $item->data($data)->add();
				if(empty($query_item)){
					$tem->rollback();
					return -3;
				}
			}
			$tem->commit();
			return 1;
		}else{
			
			//删除模板内容
			$query = $item->where("template_id=%d",array($id))->delete();
			if($query === 'flase'){
				$tem->rollback();
				return -2;
			}
			$where['app_code2'] = 'GT';
			$where['data2_id'] = $id;
			$del = $data_constraint->where($where)->delete();
			//删除模板
			$sql = $tem->where("id=%d",array($id))->delete();
			if($sql === 'flase'){
				$tem->rollback();
				return -1;
			}
			$tem->commit();
			return 1;
		}
	}

	/*
	 * 通过创建者id删除测试数据
	 */
	protected function operALL($id,$start_time='',$end_time=''){
		set_time_limit(0);
		if(!empty($start_time)){
			$where['created_time'] = array('EGT',$start_time);
		}
		if(!empty($end_time)){
			$where['created_time'] = array('ELT',$end_time);
		}
		$where['creator_id'] = $id;
		//删除批量表
		$query_batch = M('product_batch_form')->field("id")->where($where)->select();
		foreach ($query_batch as $ks => $vals) {
			$checkbatch = $this->operBatch($vals['id'],'delete');
			if($checkbatch != 1){
				return -4;
			}
		}
		//删除批量表模板
		$sql_batch = M('product_batch_template')->field("id")->where($where)->select();
		foreach ($sql_batch as $k => $val) {
			$checkbatch_tem = $this->operBatchTemplate($val['id'],'delete');
			if($checkbatch_tem != 1){
				return -3;
			}
		}
		//删除资料表
		$query = M('product_form')->field("id")->where($where)->select();
		foreach ($query as $keys => $values) {
			$checkinfo = $this->operInfo($values['id'],'delete');
			if($checkinfo != 1){
				return -2;
			}
		}
		//删除资料表模板
		$sql = M('product_template')->field("id")->where($where)->select();
		foreach ($sql as $key => $value) {
			$checktem = $this->operTemplate($value['id'],'delete');
			if($checktem != 1){
				return -1;
			}
		}
		return 1;
	}
}