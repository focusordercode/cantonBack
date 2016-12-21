<?php
namespace Home\Model;
use Think\Model\ViewModel;

class TemplateContactViewModel extends ViewModel{
	public $viewFields=array(
		'product_item2batch_item'  => array('template1_id' => 'template_id','template2_id' => 'batch_template_id'),
		'product_batch_item_template' =>array('en_name' => 'batch','id' => 'batchid','_on' => 'product_item2batch_item.title2_id = product_batch_item_template.id'),
		'product_item_template' =>array('en_name' => 'info','id' => 'infoid','_on' => 'product_item2batch_item.title1_id = product_item_template.id'),
	);
}