<?php
namespace Think\Product;
/**
* 产品中心类
*/
class ProductCenter {
	//添加修改产品中心产品信息
	public function SetProductCenter($state,$data,$id=''){
		$product = M('good_info');
		if($state == 'single'){
			$sql=$product->add($data);
			if($sql){
				return 1;
			}else{
				return -1;
			}
		}elseif($state == 'many'){
			$success = 0;
			$fail = 0;
			$product->startTrans();
			foreach ($data as $key => $value) {
				if(empty($value['cn_name'])){
					$fail++;
				}elseif(empty($value['en_name'])){
					$fail++;
				}elseif (!preg_match("/^[a-z_A-Z()\s]+[0-9]{0,10}$/",$value['en_name'])) {
					$fail++;
				}else{
					$sql=$product->add($value);
					if($sql){
						$success++;
					}else{
						$fail++;
					}
				}
			}
			$product->commit();
			$arr['success'] = $success;
			$arr['fail'] = $fail;
			$arr['count'] = count($data);
			return($arr);
		}elseif($state == 'update'){
			$sql=$product->data($data)->where("id=%d",array($id))->save();
			if($sql !== 'flase'){
				return 1;
			}else{
				return -1;
			}
		}
	}

	//获取产品中心产品信息
	public function GetAllProductCenter($category_id,$enabled,$vague,$pages,$num,$orderBy,$sort){
		$ids = ($pages - 1) * $num;
		if(!empty($category_id)){
			$where['p.category_id'] = $category_id;
		}
		if($enabled){
			$where['p.enabled'] = $enabled;
		}
		if (!empty($vague)) {
			$where['_string']='(p.cn_name like "%'.$vague.'%")  OR (p.en_name like "%'.$vague.'%")';
		}elseif($vague == '0'){
			$where['_string']='(p.cn_name like "%'.$vague.'%")  OR (p.en_name like "%'.$vague.'%")';
		}

        switch ($orderBy){
            case 'A': $order = 'p.cn_name '.$sort;       break;
            case 'B': $order = 'p.en_name '.$sort;       break;
            case 'C': $order = 'p.category_id '.$sort;   break;
            case 'D': $order = 'p.modified_time '.$sort; break;
            case 'E': $order = 'p.enabled '.$sort;       break;
            default: $order = 'p.id '.$sort;
        }

		$product = M('good_info p');
		$count = $product->where($where)->count();
		$field = "p.id,p.cn_name,p.en_name,p.enabled,p.remark,p.category_id,p.creator_id,p.created_time,p.modified_time,c.cn_name as category_name";
		$sql = $product
            ->field($field)
            ->join("left join tbl_product_category c on p.category_id = c.id")
            ->where($where)
            ->order($order)
            ->limit($ids,$num)
            ->select();

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

	//获取产品中心产品信息
	public function GetProductCenter($id){
		$product = M('good_info p');
		$where['p.id'] = $id;
		$field = "p.id,p.cn_name,p.en_name,p.enabled,p.remark,p.category_id,c.cn_name as category_name";
		$sql = $product->field($field)->join("left join tbl_product_category c on p.category_id = c.id")->where($where)->select();
		if($sql){
			return($sql);
		}else{
			return -1;
		}
	}

	//删除产品中心产品信息
	public function DelProductCenter($id){
		$product = M('good_info');
		$center2product = M('tbl_content_item_value2product');
		$sql = $product->where("id=%d",array($id))->delete();
		$query = $center2product->where("good_id=%d",array($id))->delete();
		if($sql!=='flase'){
			return 1;
		}else{
			return -1;
		}
	}

	//获取已经关联了词库的产品
	public function GetProduct2Value($category_id,$vague){
		$product = M('good_info g');
		$value2product = M('content_item_value2product');
		$where['category_id'] = $category_id;
		$where['_string']='(cn_name like "%'.$vague.'%")  OR (en_name like "%'.$vague.'%")';
		$where['enabled'] = 1;
		$field = "g.id,g.cn_name,g.en_name";
		$sql = $product->field($field)->where($where)->select();
		if($sql){
			$value2product->startTrans();
			foreach ($sql as $key => $value) {
				$query = $value2product->field('id')->where("good_id=%d",array($value['id']))->find();
				if(!empty($query['id'])){
					$array[] = $value;
				}
			}
			$value2product->commit();
			$arr['status'] = 100;
			$arr['value'] = $array;
		}else{
			$arr['status'] = 101;
			$arr['msg'] = "没有数据";
		}
		return($arr);
	}
}