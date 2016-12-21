<?php
namespace Think\Product;
/**
* 产品资料扩展
*/
class ProductInfoExtend{
	/*
	 * 获取模板的数据格式
	 */
	static function GetTemplateFormat($template_id,$type_code){
		if($type_code == 'info'){
			$tem_item = M('product_item_template');
		}else{
			$tem_item = M('product_batch_item_template');
		}
		$sql = $tem_item->field("en_name,no,data_type_code,length,precision,filling_type,value_requlation")->where("template_id=%d",array($template_id))->select();
		$i = 0;
		if($sql){
			$arr['status'] = 100;
			foreach ($sql as $key => $value) {
				$arr['value'][$i]['data'] = $value['en_name'];
				$arr['value'][$i]['no'] = $value['no'];
				$arr['value'][$i]['data_type_code'] = $value['data_type_code'];
				$arr['value'][$i]['length'] = $value['length'];
				$arr['value'][$i]['preciasion'] = $value['precision'];
				$arr['value'][$i]['filling_type'] = (int)$value['filling_type'];
				$arr['value'][$i]['value_requlation'] = (int)$value['value_requlation'];
				$i++;
			}
		}else{
			$arr['status'] = 101;
			$arr['msg'] = "没有数据";
		}
		return($arr);
	}
}