<?php
namespace Home\Model;
use Think\Model\ViewModel;

class Center2GoodViewModel extends ViewModel{
	public $viewFields=array(
		'content_item_value2product' => array('id','item_id','good_id'),
		'content_item' => array('name','_on' => 'content_item_value2product.item_id = content_item.id'),
		'good_info' => array('cn_name','en_name','_on' => 'good_info.id = content_item_value2product.good_id'),
		'product_category' => array('cn_name' => 'category_cn','en_name' => 'category_en','_on' => 'product_category.id = good_info.category_id'),
	);
}