<?php
namespace Home\Model;
use Think\Model\ViewModel;

class Batch2ProductViewModel extends ViewModel{
	public $viewFields=array(
		'product_batch_form_information' => array('form_id'),
		'product_batch_information' => array('id','product_id','parent_id','no','title','data_type_code','interger_value','char_value','decimal_value','date_value','boolean_value','enabled','_on' => 'product_batch_information.product_id = product_batch_form_information.product_id'),
	);
}