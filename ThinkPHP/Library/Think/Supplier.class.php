<?php
namespace Think;
/**
 * 供应商类
 */
class Supplier{
    private $arr=array();
    private $array=array();

	/**
     * 查询所有供应商
     */
    static function GetAll(){
    	$supplier=M("");
    	$array['']=true;
    	$field="";
    	$sql=$supplier->field($field)->where($array)->select();
    	return($sql);
    }

    /**
     * 查询所有标记为flase的供应商
     */
    static function GetAll(){
    	$supplier=M("");
    	$array['']=flase;
    	$sql=$supplier->field($field)->where($array)->select();
    	return($sql);
    }

    /**
     * 查询供应商的详细信息
     */
    static function GetSupplier($id){
    	$supplier=M("");
    	$sql=$supplier->where("id=%d",array($id))->select();
    	return($sql);
    }

    /**
     * 模糊搜索供应商
     */
    static function FuzzySearch($name){
        $supplier=M("");
        $arr['a|b|c|d'] = array('like',"%{$name}%");
        $arr['']=ture;
        $sql=$supplier->where($arr)->select();  
        return($sql);
    }

    /**
     * 删除供应商
     */
    static function DelSupplier($id){
    	$supplier=M("");
    	$data['']=flase;
    	$sql=$supplier->where("id=%d",array($id))->data($data)->save();
    	if($sql){
           	   return 1;
           }else{
           	   return -1;
           }
    }
    
    /**
     * 修改供应商
     */
    static function UpdaSupplier($id,$data){
    	$supplier=M("");
    	$sql=$supplier->where("id=%d",array($id))->data($data)->save();
        if($sql!=='flase'){
        	return  1;
        }else{
        	return  -1;
        }
    }

    /**
     * 添加供应商
     */
    static function AddSupplier($data){
        $supplier=M("");
        $data['']=true;
        $sql=$supplier->data($data)->add();
        if($sql){
        	return  1;
        }else{
        	return  -1;
        }
    }

}