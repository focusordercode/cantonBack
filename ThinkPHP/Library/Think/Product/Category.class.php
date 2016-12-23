<?php
namespace Think\Product;
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
     * 添加子类目
     * $id 父级id
     * $cn_name 中文名称
     * $en_name 英文名称
     * $remark 类目说明
     */
	static function  AddSub($id,$cn_name,$en_name,$remark,$creator_id){
        $db = M();
        $db->startTrans();
		$sql = $db->query("call AddSubNode($id,'".$cn_name."','".$en_name."','".$remark."')");
        $data['app_code1'] = 'YB';
        $data['data1_id'] = $id;
        $data['app_code2'] = 'YB';
        $data['data2_id'] = $sql[0]['id'];
        $data['creator_id'] = $creator_id;
        $data['created_time'] = date('Y-m-d H:i:s',time());
        $query = $db->table("tbl_data_constraint")->data($data)->add();
		if($sql && $query){
            $db->commit();
			return 1;
		}else{
            $db->rollback();
			return -1;
		}
	}

	/**
     * 删除类目
     */
	static function Delete($id)
    {
        $res = checkDataLimit('YB',$id);
        if($res != 1){
            return 3;
        }
        $query = M("product_category")->field("left_id,right_id")->where("id=%d",array($id))->find();

        if($query['left_id'] == 1){
            //判断是否是顶级类目，是就退出
            return 2;
        }else{
            $db = M();
            $db->startTrans();
            $sql = M()->query("call DelNode($id)");
            $where['app_code2'] = 'YB';
            $where['data2_id'] = $id;
            $db->table('tbl_data_constraint')->where($where)->delete();
            if(empty($sql)){
                $db->rollback();
                return -1;
            }else{
                $db->commit();
                return 1;
            }            
        }        

	}

	/**
     * 修改类目名称
     */
	static function UpdaName($id,$cn_name,$en_name){
		$tree_catetory=M('product_category');
		$data['cn_name'] = $cn_name;
		$data['en_name'] = $en_name;
        $sql=$tree_catetory->where("id = '%d'",array($id))->data($data)->save();
        if($sql!=='flase'){
        	return  1;
        }else{
        	return  -1;
        }
	}

	/**
     * 查询顶级类目
     */
    static function GetAncestors(){
           $sql = M()->query("select id,cn_name,en_name from productcategoryview where Layer= 1");
           return($sql);      
    }

    /**
     * 查询子类目
     */
    static function GetSub($id){
    	   $sql = M()->query("call GetChildrenNodeList($id)");
    	   return($sql);
    }

    /**
     * 模块内移动类目
     * $moveid  要移动的id
     * $id     要移动到的id
     */
    static function move($moveid,$id){
           $sql = M()->query("call move($moveid,$id)");
           if($sql){
        	   return 1;
            }else{
        	  return -1;
            }
    }

    /*
     * 模糊搜索
     */
    static function GetVague($data){
        $sql = M()->query("select id,cn_name,en_name from tbl_product_category where cn_name like '%".$data."%' or en_name like '%".$data."%' ");
        return($sql);
    }
}