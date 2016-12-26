<?php
namespace Think\Product;
class Product_Item_Template{

	public static function get($type,$template_id){
		if($type == 'info'){
			$tem = M("product_item_template");
		}elseif ($type == 'batch') {
			$tem = M("product_batch_item_template");
		}
		if(empty($template_id)) return 102;
		$result = $tem->where(array("enabled"=>1,"template_id"=>$template_id))->order("no asc")->select();
		if($result){
			foreach ($result as $key => $value) {
				$result[$key]['save_text'] = '';   // 添加一个前端存放数据的键
			}
			$res['error'] = 0;
			$res['value'] = $result;
		}else{
			$res['error'] = 1;
			$res['status'] = 101;
            $res['msg']    = '暂无相关信息';
		}
		return $res;
	}

	//依id删除
	public static function del_by_id($type,$id){
		if($type == 'info'){
			$tem = M("product_item_template");
			$mm  = M("product_template");
		}elseif ($type == 'batch') {
			$tem = M("product_batch_item_template");
			$mm  = M("product_batch_template");
		}
		if(!empty($id)){
			$template_id = $tem->where("id=$id")->field("template_id")->find();
			$status = $mm->where("id=".$template_id['template_id'])->field("status_code")->find();
			if($status['status_code'] == 'enabled'){   // 判断模板是否是启用状态
				$res['error']  = 1;
				$res['status'] = 104;
                $res['msg']    = '该状态下不可操作';
			}else{
				$data["enabled"] = 0;
				$result = $tem->where("id=$id")->delete();
				if($result){
					$res['error'] = 0;
					$res['value'] = $result;
				}else{
					$res['error'] = 1;
					$res['status'] = 101;
                    $res['msg']    = '删除失败';
				}				
			}
		}
		return $res;
	}

	//依模板id删除
	public static function del_by_template_id($type,$id){
		if($type=='info'){
			$tem = M("product_item_template");
		}elseif ($type=='batch') {
			$tem = M("product_batch_item_template");
		}
		if(!empty($id)){
			$status = M("product_template")->where("id=$id")->field("status_code")->find();
			if($status['status_code'] == 'enabled'){   // 判断模板是否是启用状态
				$res['error']  = 1;
				$res['status'] = 104;
                $res['msg']    = '该状态下不可操作';
			}else{
				$result = $tem->where("template_id=$id")->delete();
				if($result){
					$res['error'] = 0;
					$res['value'] = $result;
				}else{
					$res['error'] = 1;
					$res['status'] = 101;
                    $res['msg']    = '删除失败';
				}				
			}
		}
		return $res;
	}

	// 模板表头数据编辑
	public static function edit($type,$data,$template_id)
	{

		if($type=='info'){
			$tem = M("product_item_template");
            $tt  = M("product_template");
            $for = M("product_form");
		}elseif ($type=='batch') {
			$tem = M("product_batch_item_template");
            $tt  = M("product_batch_template");
            $for = M("product_batch_form");
            if(!preg_match("/^[0-9]+$/",$template_id)){
                $res['error'] = 1;
                $res['status'] = 102;
                $res['msg']    = "模板没有选择";
                return $res;
            }
		}
		$tem->startTrans();
        $status = $tt->where("id=".$template_id)->field("status_code")->find();
        $hasform = $for->where(array("template_id" => $template_id,'enabled'=>1))->find();
        if($status['status_code'] == 'enabled' && $hasform){     // 判断模板是否是启用状态
            $res['error'] = 1;
            $res['status'] = 104;
            $res['msg']    = "启用状态不能修改";
            return $res;
        }

		if(!empty($data)){
			$num = 0;
			foreach ($data as $key => $value) {

				$value['title'] = $value['en_name'];
                $value['modified_time']  = date('Y-m-d H:i:s' , time());

                switch($value['data_type_code']){
                    case 'int' :      $value['length'] = '18';   break;
                    case 'dc'  :      $value['length'] = '10';   break;
                    case 'dt'  :      $value['length'] = '25';   break;
                    case 'pic' :      $value['length'] = '200';  break;
                    case 'upc_code' : $value['length'] = '13';   break;
                }
                unset($value['valid_value']);
                unset($value['length_types']);
                unset($value['precision_dc']);
                unset($value['data_types']);

				$result = $tem->where(array("id"=>$value['id']))->save($value);
				if($result){
					$num ++;
				}else{
					$tem->rollback();//不成功，则回滚
				}
			}

			if($num != 0){
                $ds['status_code'] = 'editing';
                if($type == 'batch'){
                    $ds['status_code'] = 'defining';
                }
                $tt->where(array('id'=>$template_id))->save($ds);
                $tem->commit();//成功则提交
				$res['error'] = 0;
				$res['value'] = $num;
				return $res;
			}else{
				$res['error']  = 1;
				$res['status'] = 101;
                $res['msg']    = "修改失败";
				return $res;
			}
		}else{
			$res['error'] = 1;
			$res['status'] = 102;
            $res['msg']    = "修改数据不能为空";
			return $res;
		}
	}

	/*
	 * 根据模板id获取Bootstrap Table表格头
	 */
	static function GetBootstrapTable($type,$template_id){
		if($type == 'info'){
			$tem = M("product_item_template");
		}elseif ($type=='batch') {
			$tem = M("product_batch_item_template");
		}
		$field = "id,en_name,precision,data_type_code,length,no,default_value,filling_type,value_requlation";
		$sql   = $tem->field($field)->where("template_id=%d",array($template_id))->order('no asc')->select();
		return($sql);
	}

    /*
     * 模板表头添加操作
     * */
    public static function add($type,$data,$template_id)
    {
        if($type == 'info'){
            $tem = M("product_item_template");
            $sta = M("product_template");
        }elseif ($type == 'batch') {
            $tem = M("product_batch_item_template");
            $sta = M("product_batch_template");
        }
        $tem->startTrans();
        if(!empty($data)){
            $num = 0;
            $k   = 1;
            foreach ($data as $key => $value) {
                $status = $sta->where(array("id"=>$template_id))->field("status_code")->find();
                if($status['status_code'] == 'enabled'){   // 判断该模板是否是启用状态
                    $res['error'] = 1;
                    $res['status'] = 104;
                    $res['msg']    = '该状态下不可操作';
                    return $res;
                }

                $is = $tem->where(array("template_id"=>$template_id,"en_name"=>$value['en_name']))->find();
                if($is){
                    $k ++;
                    continue; // 已经存在，跳过该次循环
                }
                if(isset($value['id'])){   // 存在id则为数据库已经存在的数据，跳过
                    $k ++;
                    continue;
                }
                $value['template_id']    = $template_id;
                $value['no']             = $k;
                $value['title']          = trim($value['en_name']);
                $value['enabled']        = 1;
                $value['created_time']   = date("Y-m-d H:i:s",time());
                $value['modified_time']  = date("Y-m-d H:i:s",time());
                $value['creator_id']     = isset($_SESSION['user_id']) ? session('user_id') : 0;

                $result = $tem->add($value);
                if($result){
                    $num ++;
                    $k   ++;
                }else{
                    $tem->rollback();//不成功，则回滚
                    $k = 1;
                    $res['error']  = 1;
                    $res['status'] = 101;
                    $res['msg']    = '操作失败';
                    return $res;
                }
            }

            if($num != 0){
                $tem->commit();//成功则提交
                $res['error'] = 0;
                $res['status'] = 100;
                if($type == "batch"){ // 批量表改状态
                    $status_code['status_code'] = 'defining';
                    $sta->where(array('id'=>$template_id))->save($status_code);
                }
                return $res;
            }
        }else{
            $res['error']  = 1;
            $res['status'] = 102;
            $res['msg']    = '添加数据有误';
            return $res;
        }
    }

    /*
     * 获取模板的有效值
     */
    static function GetVirtual($template_id){
        $virtual=M('product_item_valid_value v');
        $sql=$virtual->field("item_id")->where("template_id=%d",array($template_id))->group("item_id")->select();
        $field="b.title,v.value";
        foreach($sql as $key => $value){
            $query=$virtual->field($field)->join("tbl_product_batch_item_template b on b.id=v.item_id")->where("v.item_id=%d",array($value['item_id']))->find();
            $array[$query['title']]=$query['value'];
        }
        return ($array);
    }

    //模板撤销
    static function template_back_step($type_code , $template_id){
        if($type_code == 'info'){
            $m = M('product_template');
            $n = M('product_item_template');
            $tem = $m->find($template_id);
            if(!$tem){
                $res['error']  = 1;
                $res['status'] = 101;
                $res['msg']    = '模板不存在';
            }
        }elseif($type_code == 'batch'){
            $m = M('product_batch_template');
            $n = M('product_batch_item_template');
            $tem = $m->find($template_id);
            if(!$tem){
                $res['error']  = 1;
                $res['status'] = 101;
                $res['msg']    = '模板不存在';
                return $res;
            }
        }
        switch($tem['status_code']){
            case 'creating'  :
                $m->delete($template_id);
                $n->where(array('template_id'=>$template_id))->delete();
                $res['error']  = 0;
                break;
            case 'defining'  :
                M('product_item2batch_item')->where(array('template2_id'=>$template_id))->delete();
                $res['error']  = 0;
                $sd['status_code'] = 'creating';
                $m->where(array('id'=>$template_id))->save($sd);
                break;
            case 'connecting':
                $res['error']  = 0;
                $sd['status_code'] = 'defining';
                $m->where(array('id'=>$template_id))->save($sd);
                $where['app_code2'] = 'U6';
                $where['data2_id'] = $template_id;
                M('data_constraint')->where($where)->delete();
                break;
            default :
                $res['error']  = 1;
                $res['status'] = 101;
                $res['msg']    = '模板不存在';
        }
        return $res;
    }
}