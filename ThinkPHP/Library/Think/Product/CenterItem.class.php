<?php
namespace Think\Product;
/**
* 词库项目类
*/
class CenterItem{
	//添加词库项目
	static function AddCenterItem($state,$data){
		$centeritem = M('content_item');
		if($state == 'single'){
			$sql = $centeritem->data($data)->add();
			if($sql){
				return 1;
			}else{
				return -1;
			}
		}elseif ($state == 'many') {
			$success = 0;
			$fail = 0;
			$centeritem->startTrans();
			foreach ($data as $key => $value) {
				if(empty($value['name'])){
					$fail++;
				}else{
					$sql=$centeritem->add($value);
					if($sql){
						$success++;
					}else{
						$fail++;
					}
				}
			}
			$centeritem->commit();
			$arr['success'] = $success;
			$arr['fail'] = $fail;
			$arr['count'] = count($data);
			return($arr);
		}
	}

	//修改词库项目
	static function UpdaCenterItem($id,$data){
		$centeritem = M('content_item');
		$sql = $centeritem->data($data)->where("id=%d",array($id))->save();
		if($sql !== 'flase'){
			return 1;
		}else{
			return -1;
		}
	}

	//获取词库项目列表
	static function GetAllCenterItem($enabled,$vague,$pages,$num,$orderBy,$sort){
		$centeritem = M('content_item ci');
		$ids = ($pages - 1) * $num;
		if($enabled){
			$where['ci.enabled'] = $enabled;
		}
		if (!empty($vague)) {
			$where['_string']='(ci.name like "%'.$vague.'%")  OR (ci.remark like "%'.$vague.'%")';
		}elseif($vague == '0'){
			$where['_string']='(ci.name like "%'.$vague.'%")  OR (ci.remark like "%'.$vague.'%")';
		}

        switch ($orderBy){
            case 'A': $order = 'name '.$sort;          break;
            case 'B': $order = 'remark '.$sort;        break;
            case 'C': $order = 'created_time '.$sort;  break;
            case 'D': $order = 'modified_time '.$sort; break;
            case 'E': $order = 'enabled '.$sort;       break;
            default: $order = 'remark '.$sort;
        }

		$sql = $centeritem->where($where)->limit($ids,$num)->order($order)->select();
		$count = $centeritem->where($where)->count();
		if($sql){
			$arr['count'] = $count;
			$arr['pages'] = ceil($count/$num);
			$arr['nowpages'] = $pages;
			$arr['value'] = $sql;
			return($arr);
		}else{
			return -1;
		}
	}

	//获取词库项目信息
	static function GetCenterItemInfo($id){
		$centeritem = M('content_item');
		$sql = $centeritem->where("id=%d",array($id))->find();
		if($sql){
			return($sql);
		}else{
			return -1;
		}
	}

	//删除词库项目
	static function DelCenterItem($id){
		$centeritem = M('content_item');
		$center_value = M('content_item_value');
		$item2good = M('content_item_value2product');
		$sql = $centeritem->where("id=%d",array($id))->delete();
		$query = $center_value->where("item_id=%d",array($id))->delete();
		$imp = $item2good->where("item_id=%d",array($id))->delete();
		if($sql !== 'flase' && $query !== 'flase' && $imp !== 'flase'){
			return 1;
		}else{
			return -1;
		}
	}

	//添加词库内容
	static function AddCenterItemValue($state,$data){
		$center_value = M('content_item_value');
		if($state == 'single'){
			$sql = $center_value->data($data)->add();
			if($sql){
				return 1;
			}else{
				return -1;
			}
		}elseif ($state == 'many') {
			$success = 0;
			$fail = 0;
			$center_value->startTrans();
			foreach ($data as $key => $value) {
				if(empty($value['value'])){
					$fail++;
				}else{
					$sql=$center_value->add($value);
					if($sql){
						$success++;
					}else{
						$fail++;
					}
				}
			}
			$center_value->commit();
			$arr['success'] = $success;
			$arr['fail'] = $fail;
			$arr['count'] = count($data);
			return($arr);
		}
	}

	//修改词库内容
	static function UpdaCenterItemValue($id,$data){
		$center_value = M('content_item_value');
		$sql = $center_value->data($data)->where("id=%d",array($id))->save();
		if($sql !== 'flase'){
			return 1;
		}else{
			return -1;
		}
	}

	//获取词库内容信息
	static function GetCenterItemValue($id){
		$center_value = M('content_item_value cv');
		$field="cv.*,ci.name";
		$sql = $center_value->field($field)->join("left join tbl_content_item ci on ci.id=cv.item_id")->where("cv.item_id=%d",array($id))->select();
		if($sql){
			return($sql);
		}else{
			return -1;
		}
	}

	//删除词库内容信息
	static function DelCenterItemValue($id){
		$center_value = M('content_item_value');
		if(is_array($id)){
			$center_value->startTrans();
			$success = 0;
			$fail = 0;
			foreach ($id as $key => $value) {
				$sql = $center_value->where("id=%d",array($value))->delete();
				if($sql !== 'flase'){
					$success++;
				}else{
					$fail++;
				}
			}
			$center_value->commit();
			$array['count'] = count($id);
			$array['success'] = $success;
			$array['fail'] = $fail;
			return($array);
		}else{
			$sql = $center_value->where("id=%d",array($id))->delete();
			if($sql !== 'flase'){
				return 1;
			}else{
				return -1;
			}
		}
	}

	//添加词库与产品的关系
	static function AddCenter2Good($data){
		$item2good = M('content_item_value2product');
		$where['item_id'] = $data['item_id'];
		$where['good_id'] = $data['good_id'];
		$query = $item2good->where($where)->find();
		if(empty($query['id'])){
			$sql = $item2good->data($data)->add();
			if($sql){
				return 1;
			}else{
				return -1;
			}
		}else{
			return -2;
		}
		
	}

	//删除词库与产品的关系
	static function DelCenter2Good($id){
		$item2good = M('content_item_value2product');
		if(is_array($id)){
			$item2good->startTrans();
			$success = 0;
			$fail = 0;
			foreach ($id as $key => $value) {
				$sql = $item2good->where("id=%d",array($value))->delete();
				if($sql !== 'flase'){
					$success++;
				}else{
					$fail++;
				}
			}
			$item2good->commit();
			$array['count'] = count($id);
			$array['success'] = $success;
			$array['fail'] = $fail;
			return($array);
		}else{
			$sql = $item2good->where("id=%d",array($id))->delete();
			if($sql !== 'flase'){
				return 1;
			}else{
				return -1;
			}
		}
	}

	//获取词库与产品的关系
	static function GetCenter2Good($id){
		$center2good = D('Center2GoodView');
		$sql = $center2good->where("item_id=%d",array($id))->select();
		return($sql);
	}

	//获取产品所关联的词库内容
	static function GetGood2CenterValue($good_id){
		$content2product = M('content_item_value2product');
		$good2centervalue = D('Good2CenterValueView');	
		$sql = $content2product->field("item_id")->where("good_id=%d",array($good_id))->select();
		foreach ($sql as $key => $value) {
			$query = $good2centervalue->where("content_item_value2product.item_id=%d and good_id=%d",array($value['item_id'],$good_id))->select();
			if(!empty($query[0]['name'])){
				$arr[$query[0]['name']] = $query;
			}
		}
		return($arr);
	}
}