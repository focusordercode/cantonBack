<?php
namespace Think;
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
	static function  AddSub($id, $cn_name, $en_name, $remark, $category_id){
        $db = M();
        $db->startTrans();
        $category = $db->table('tbl_product_category')->where("id=%d",array($category_id))->find();
        if(!is_dir('./pic/'.str_replace(' ','_',trim($category['en_name'])))){
            mkdir('./pic/'.str_replace(' ','_',trim($category['en_name'])));
        }
        $paths = './pic/'.str_replace(' ','_',trim($category['en_name'])).'/'.str_replace(" ", "_", $en_name);
        $m = $db->table('tbl_product_gallery')->where(array('dir'=>$paths))->find();
        if($m){
            return 2;
        }
        
        // 添加子类目
		$sql = $db->query("call AddSon($id,'".$cn_name."','".$en_name."','".$remark."',$category_id)");
        $das['dir'] = $paths;
        $adds = $db->table('tbl_product_picture')->data($das)->save();
        $data['app_code1'] = 'YB';
        $data['data1_id'] = $category_id;
        $data['app_code2'] = 'DB';
        $data['data2_id'] = $sql[0]['id'];
        $data['creator_id'] = isset($_COOKIE["user_id"]) ? cookie("user_id") : 0;
        $data['created_time'] = date('Y-m-d H:i:s',time());
        $query =$db->table('tbl_data_constraint')->data($data)->add();
		if($sql && $query && $adds){
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
     * 删除类目 （暂时没用上）
     */
	static function Delete($id){
        $m = M("product_gallery");
        $query = $m->field("left_id")->where("id=%d",array($id))->find();
        if($query['left_id']==1){
            //判断是否是顶级类目，是就退出
            return 2;
        }else{
            $f = $m->where('id='.$id)->find();
            $sonNode = $m->where("left_id >= ".$f['left_id']." AND right_id <= ".$f['right_id'])->select();
            if(!empty($sonNode)){

                foreach($sonNode as $key => $val){
                    M("product_picture")->where(array('gallery_id'=>$val['id']))->delete();
                    $dir1 = './Pictures/'.$val['en_name'];
                    deldir($dir1);
                }
            }
            $sql = M()->query("call DelImageNode($id)");
            if($sql){
                return 1;
            }else{
                return -1;
            }            
        }
	}

	/**
     * 修改类目名称
     */
	static function UpdaName($id,$cn_name,$en_name){
		$tree_catetory = M('product_gallery');
		$data['cn_name'] = $cn_name;
		$data['en_name'] = $en_name;
        $m = $tree_catetory->where('id='.$id)->find();
        $sql = $tree_catetory->where("id = '%d'",array($id))->data($data)->save();
//         $check = M()->query("SELECT gp.*,f.form_id,f.created_time,f.used_time FROM
//     (SELECT g.*,p.id AS picid,p.file_name,p.path FROM tbl_product_gallery AS g LEFT JOIN tbl_product_picture AS p ON g.id=p.gallery_id AND g.id=139) AS gp,tbl_product_for_picture AS f 
// WHERE gp.picid=f.picture_id");
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
                    // $count = count($pic);
                    // $im = 0;
                    // $da['path']       = './Pictures/RUBBISH';
                    // $da['rubbish']    = 1;
                    // $da['gallery_id'] = 0;
                    // foreach ($pic as $value) { // 查到了将图片全部移到回收站
                    //     $image = $p->where(array('id'=>$value['id']))->find();
                    //     $result = $p->where(array('id'=>$value['id']))->save($da);
                    //     if($result){
                    //         // 图片文件移动
                    //         if(copy($image['path']."/".$image['file_name'],$da['path']."/".$image['file_name'])){
                    //             @unlink($image['path']."/".$image['file_name']);
                    //             $im++;
                    //         }
                    //     }
                    // }
                    // if($im == $count){
                    //     $p->commit();
                    // }else{
                    //     $p->rollback();
                    //     return 4;
                    // }
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
                    $dir1 = './Pictures/'.$f['en_name'];
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

    //移动图片
    static function MovePicture($gallery_id,$pic_ids){
        $success = 0;
        $error = 0;
        $pic = M('product_picture');
        $product4pic = M('product_for_picture');
        $gallery = M('product_gallery');
        $info = M('product_information');
        $form = M('product_form_information');
        $pic->startTrans();
        $folder = $gallery->field("dir")->where("id=%d",array($gallery_id))->find();
        foreach ($pic_ids as $key => $value) {
            $sql = $pic->field("file_name,path")->where("id=%d",array($value))->find();
            if(copy($sql['path'].'/'.$sql['file_name'],$folder['dir'].'/'.$sql['file_name'])){
                $array[] = $sql['path'].'/'.$sql['file_name'];
                $data['path'] = $folder['dir'];
                $data['gallery_id'] = $gallery_id;
                $upda = $pic->data($data)->where("id=%d",array($value))->save();
                if($upda !== 'flase'){
                    $checkpic = $product4pic->field("form_id")->where("picture_id=%d",array($value))->find();
                    if($checkpic){
                        $product_ids = $form->field("product_id")->where("form_id=%d",array($form_id))->select();
                        foreach ($product_id as $keys => $values) {
                            $data['char_value'] = $folder['dir'].'/'.$sql['file_name'];
                            $where['char_value'] = $sql['path'].'/'.$sql['file_name'];
                            $updapic = $info->data($data)->where($where)->save();
                            if($updapic !== 'flase'){
                                $success++;
                            }else{
                                $pic->rollback();
                                $error++; 
                            }
                        }
                    }
                }else{
                    $pic->rollback();
                    $error++;
                }
            }else{
                $error++;
            }
        }
    }
}