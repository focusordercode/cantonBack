<?php
namespace Home\Model;
use Think\Model\ViewModel;

class Good2CenterValueViewModel extends ViewModel{
	public $viewFields=array(
		'content_item_value2product' => array('item_id','good_id'),
		'content_item_value' => array('value','_on' => 'content_item_value2product.item_id = content_item_value.item_id'),
		'good_info' => array('cn_name','en_name','_on' => 'good_info.id = content_item_value2product.good_id'),
		'content_item' => array('name','_on' => 'content_item.id = content_item_value2product.item_id'),
	);
}