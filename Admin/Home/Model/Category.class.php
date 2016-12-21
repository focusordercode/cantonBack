<?php
namespace Think;
/**
 * 类目管理类
 */
class Category {
	protected $id;
	protected $app_code;
	protected $cn_name;
	protected $en_name;
	protected $left_id;
	protected $right_id;
	protected $remark;
	protected $layer;
	protected $data=array();

    /**
     * 添加子孙节点
     */
	static function  AddSub($id,$app_code,$cn_name,$en_name,$remark){
		$sql=M()->query('call AddSubNode($id,'.$app_code.','.$cn_name.','.$en_name.','.$remark.')');
		if($sql){
			return 1;
		}else{
			return -1;
		}
	}


	/**
     * 添加顶级节点
     */
	static function  Add($app_code,$cn_name,$en_name,$remark){
		$tree_catetory=M('tree_catetory');
		$data['app_code']=$app_code;
		$data['cn_name']=$cn_name;
		$data['en_name']=$en_name;
		$data['left_id']=1;
		$data['right_id']=2;
		$data['remark']=$remark;
		$sql=$tree_catetory->data($data)->add();
		if($sql){
			return 1;
		}else{
			return -1;
		}
	}

	/**
     * 删除节点
     */
	static function Delete($id){
        $sql=M()->query('call DelNode($id)');
        if($sql){
        	return 1;
        }else{
        	return -1;
        }
	}

	/**
     * 修改节点名称
     */
	static function UpdaName($id,$cn_name,$en_name){
		$tree_catetory=M('tree_catetory');
		$data['cn_name']=$cn_name;
		$data['en_name']=$en_name;
        $sql=$tree_catetory->where("id = '%d'",array($id))->data($data)->save();
        if($sql!=='flase'){
        	return  1;
        }else{
        	return  -1;
        }
	}

	/**
     * 移动节点
     */
	static function Move($moveid,$id){
        
	}

	/**
     * 查询节点
     */
    static function GetSub($id=0,$app_code='amazon',$layer=0){
    	// if(id==0){
     //       $sql=M()->query("select id,app_code,cn_name,en_name,Layer from treeview where app_code=".$app_code."and Layer=".$layer+1);
     //       return($sql);
     //    }else{

     //    }
     return "12345678";
    }

}