<?php
namespace Think\Product;
/**
 * 定制模板类
 */
class CustomTem{
	/**
     * 创建模板
     */
    static function EstablishTem($data){
        $tem=M('template');
        for($i=0;$i<count($data);$i++){
        	$sql=$tem->data($data[$i])->add();
        }
        if($sql){
            return  1;
        }else{
            return  -1;
        }
    }

    /**
     * 查询模板
     */
    static function GetTem($category){
    	$tem=M('template');
    	$field="en_name,orders";
    	$data['category']=$category;
    	$sql=$tem->field($field)->where($data)->order('orders asc')->select();
        return($sql);
    }

    /**
     * 删除模板
     */
    static function delTem($category){
        $tem=M('template');
        $data['category']=$category;
        $sql=$tem->where($data)->delete();
        if($sql){
           	   return 1;
           }else{
           	   return -1;
           }
    }
}