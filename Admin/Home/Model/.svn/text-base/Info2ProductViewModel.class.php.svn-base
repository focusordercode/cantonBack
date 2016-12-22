<?php
namespace Home\Model;
use Think\Model\ViewModel;

class Info2ProductViewModel extends ViewModel{
	public $viewFields=array(
		'product_form_information' => array('form_id'),
		'product_information' => array('id','product_id','parent_id','no','title','data_type_code','interger_value','char_value','decimal_value','date_value','boolean_value','enabled','_on' => 'product_information.product_id = product_form_information.product_id'),
	);
}