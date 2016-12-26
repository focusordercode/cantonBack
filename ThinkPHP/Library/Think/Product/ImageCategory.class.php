<?php
namespace Think\Product;
/**
 * 类目管理类
 */

class ImageCategory {

    /**
     * 添加子类目
     * $id 父级id
     * $cn_name 中文名称
     * $en_name 英文名称
     * $remark 类目说明
     */
	static function  AddSub($id, $cn_name, $en_name, $remark, $category_id,$creator_id){
        $db = M();
        $db->startTrans();
        $category = $db->table('tbl_product_category')->where("id=%d",array($category_id))->find();
        if(!is_dir('./Pictures/'.str_replace(' ','_',trim($category['en_name'])))){
            mkdir('./Pictures/'.str_replace(' ','_',trim($category['en_name'])));
        }
        $paths = './Pictures/'.str_replace(' ','_',trim($category['en_name'])).'/'.str_replace(" ", "_", $en_name);
        $m = $db->table('tbl_product_gallery')->where(array('dir'=>$paths))->find();
        if($m){
            return 2;
        }
        
        // 添加子类目
		$sql = $db->query("call AddSon($id,'".$cn_name."','".$en_name."','".$remark."',$category_id)");
        $das['dir'] = $paths;
        $adds = $db->table('tbl_product_gallery')->data($das)->where("id=%d",array($sql[0]['id']))->save();
        $data['app_code1'] = 'YB';
        $data['data1_id'] = $category_id;
        $data['app_code2'] = 'DB';
        $data['data2_id'] = $sql[0]['id'];
        $data['creator_id'] = $creator_id;
        $data['created_time'] = date('Y-m-d H:i:s',time());
        $query =$db->table('tbl_data_constraint')->data($data)->add();
		if($sql && $query && $adds !== 'flase'){
            //$name = str_replace(" ", "_", $en_name);
            @mkdir($paths);
            $db->commit();
			return 1;
		}else{
            $db->rollback();
			return -1;
		}
	}

	/**
     * 修改类目名称
     */
	static function UpdaName($id,$cn_name,$en_name){
		$tree_catetory = M('product_gallery');
		$data['cn_name'] = $cn_name;
		$data['en_name'] = $en_name;
        $sql = $tree_catetory->where("id = '%d'",array($id))->data($data)->save();
        if($sql !== 'flase'){
            
        	return  1;
        }else{
        	return  -1;
        }
	}

	/**
     * 查询顶级类目
     */
    static function GetAncestors(){
        $sql = M()->query("select id,cn_name,en_name from imageview where Layer= 1");
        return($sql);      
    }

    /**
     * 查询子类目
     */
    static function GetSub($id){
    	$sql = M()->query("call getImageSons($id)");
    	return($sql);
    }

    /**
     * 模块内移动类目
     * $moveid  要移动的id
     * $id     要移动到的id
     */
    static function move($moveid,$id){
        $sql = M()->query("call imageMove($moveid,$id)");
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
        $sql = M()->query("select * from tbl_product_gallery where cn_name like '%".$data."%' or en_name like '%".$data."%' ");
        return($sql);
    }

    // 拉取类目索引
    static function get_parent_path($id){
        if(empty($id)){
            $data['error'] = 1;
            $data['status'] = 102;
            $data['msg']    = '没有选择类目';
        }else{
            $result = M()->query("call getImageParentPath($id)");
            if($result){
                $data['error'] = 0;
                $data['value'] = $result;
            }else{
                $data['error'] = 1;
                $data['status'] = 101;
                $data['msg']    = '没有查询到相关信息';
            }
        }
        return $data;
    }

    // 通过id拉取类目
    static function get_gallery_by_id($id){
        if(empty($id)){
            $data['error'] = 1;
            $data['status'] = 102;
            $data['msg']    = '没有选择类目';
        }else{
            $result = M('product_gallery')->where(array('id'=>$id))->select();
            if($result){
                $data['error'] = 0;
                $data['value'] = $result;
            }else{
                $data['error'] = 1;
                $data['status'] = 101;
                $data['msg']    = '没有相关信息';
            }
        }
        return $data;
    }

    // 删除类目
    static function Delete_album($id){
        $m = M("product_gallery");
        $query = $m->field("left_id")->where("id=%d",array($id))->find();
        if($query['left_id'] == 1){ //判断是否是顶级类目，是就退出
            return 2;
        }else{
            $f = $m->where('id='.$id)->find();
            if($f['right_id'] - $f['left_id'] != 1){ // 判断是否有子类目
                return 3;
            }else{
                $p = M("product_picture");
                $p->startTrans();
                $pic = $p->where(array('gallery_id'=>$id))->select(); // 查询当前类目的图片
                if($pic){
                    return 4;
                }
                $data_constraint = M('data_constraint');
                $where['app_code2'] = 'DB';
                $where['data2_id'] = $id;
                $query = $data_constraint->where($where)->delete();
                if($query === 'flase'){
                    $p->rollback();
                    return -1;
                }
                $sql = M()->query("call DelImageNode($id)"); // 完成全部操作删除类目
                if($sql){
                    $dir1 = $f['dir'];
                    deldir($dir1); // 删除文件夹
                    $p->commit();
                    return 1;
                }else{
                    $p->rollback();
                    return -1;
                }
            }

        }
    }

}