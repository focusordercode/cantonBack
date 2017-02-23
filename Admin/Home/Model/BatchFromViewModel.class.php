<?php
namespace Home\Model;
use Think\Model\ViewModel;

class BatchFromViewModel extends ViewModel{
	public $viewFields=array(
		'product_batch_form' => array('id','category_id','template_id','client_id','title','status_code','creator_id','created_time','modified_time','enabled'),
		'product_batch_template' => array('cn_name' => 'tempname','_on' =>'product_batch_form.template_id = product_batch_template.id'),
		'product_category' => array('cn_name' => 'name','_on' => 'product_batch_form.category_id = product_category.id'),
		'customer' => array('custom_name' => 'client_name','_on' => 'product_batch_form.client_id = customer.id'),
	);
}