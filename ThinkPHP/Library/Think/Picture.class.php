<?php
namespace Think;

class Picture {

	static function delete_pic($id_arr){
        $m = M("product_picture");
        $m->startTrans();
        $count = count($id_arr);
        $dirarr = array();
        $im = 0;
        foreach ($id_arr as $value) {
            $image = $m->where(array('id'=>$value))->find();     // 查找需要删除的图片
	    	$result = $m->where(array('id'=>$value))->delete();  // 按照批量删除方式删除图片
	    	if($result){
                $dirarr[] = $image['path']."/".$image['file_name'];  // 成功删除图片之后记录下图片文件
	    		$im++;
	    	}else{
	    		$data['error']  = 1;
	    		$data['status'] = 101;
                $data['msg']    = '删除失败';
	    		$m->rollback();                                  // 删除失败回滚
	    		return $data;
	    	}
        }
        if($im == $count){
            foreach($dirarr as $v){       // 成功之后删除图片文件
                @unlink($v);
            }
        	$m->commit();
        	$data['error'] = 0;
	    	$data['value'] = $im;
        }
    	return $data;
	}

    // 拉取图片
    static function get_pic($where,$page,$pagesize){
    	$m = M("product_picture");
        // 分页操作
        $start_id = ( $page - 1 ) * $pagesize;
        $count  = $m->where($where)->count();
        $counts = ceil($count/$pagesize);
        $result = $m->where($where)->order('modified_time desc')->limit($start_id,$pagesize)->select();

        foreach ($result as $key => $value) {
            foreach ($value as $k => $v) {
                if($k == "tags"){
                    $result[$key][$k] = array_filter(explode("||",$v)); // 默认标签分隔符“||”分割子数组返回
                }
            }
        }
        $data['countPage']  = $counts;
        $data['countImage'] = $count;

    	if($result){
    		$data['error']  = 0;
    		$data['value']  = $result;
    	}else{
    		$data['error']  = 1;
    		$data['status'] = 101;
            $data['msg']    = '暂无数据';
    	}
    	return $data;
    }

    // 编辑图片
    static function edit_pic($id,$editData){
        $m = M('product_picture');
        if(empty($editData)){
            $data['error'] = 1;
            $data['status'] = 102;
            $data['msg']    = '没有数据被修改';
        }else{
            $edit = $m->where(array('id'=>$id))->save($editData);
            if($edit){
                $data['error'] = 0;
                $data['value'] = $edit;
            }else{
                $data['error'] = 1;
                $data['status'] = 101;
                $data['msg']    = '数据编辑失败';
            }    
        }
        return $data;
    }

    /*
     * 模糊搜索
     */
    static function GetVague($data){
        $sql=M()->query("select id,cn_name,en_name from tbl_image_category where cn_name like '%".$data."%' or en_name like '%".$data."%' ");
        return($sql);
    }

    // 通过类目id查询相册
    static function  get_gallery_by_category_id($category_id){
        if(empty($category_id) || $category_id == 0){
            $data['error'] = 1;
            $data['status'] = 102;
            $data['msg']    = '图片类目未选择';
            return $data;
        }
        $m = M('product_gallery');
        $result = $m->where(array('category_id'=>$category_id))->select();
        if($result){
            $data['error'] = 0;
            $data['value'] = $result;
        }else{
            $data['error'] = 1;
            $data['status'] = 101;
            $data['msg']    = '数据不存在';
        }
        return $data;
    }

    // 放入回收站 RUBBISH
    static function pic_to_rubbish($id_arr){
        $m = M("product_picture");
        $m->startTrans();
        $count = count($id_arr); // 计算图片总数
        $im = 0;
        $da['path']       = './Pictures/RUBBISH';
        $da['gallery_id'] = 0;
        $da['rubbish']    = 1;
        $da['modified_time'] = date('Y-m-d H:i:s',time());
        foreach ($id_arr as $value) {
            $image = $m->where(array('id'=>$value))->find();
            $result = $m->where(array('id'=>$value))->save($da);
            if($result){
                // 操作成功即移动图片文件到回收站
                if(copy($image['path']."/".$image['file_name'],$da['path']."/".$image['file_name'])){
                    @unlink($image['path']."/".$image['file_name']);
                    // 移动成功则操作数量自增
                    $im++;
                }
            }else{
                $data['error']  = 1;
                $data['status'] = 101;
                $data['msg']    = '数据删除失败';
                $m->rollback();
                return $data;
            }
        }
        if($im == $count){
            $m->commit();
            $data['error'] = 0;
            $data['value'] = $im;
        }else{
            $m->rollback();
            $data['error']  = 1;
            $data['status'] = 103;
            $data['msg']    = '图片移动失败';
        }
        return $data;
    }

    // 从回收站恢复图片
    public function recover_pic($gallery_id,$id_arr){
        $m = M("product_picture");
        $m->startTrans();
        $count = count($id_arr);
        $im = 0;
        $dir = M('product_gallery')->where(array('id'=>$gallery_id))->find();
        if(!$dir){
            $data['error']  = 1;
            $data['status'] = 102;
            $data['msg']    = '选择的类目不存在';
            return $data;
        }
        $name = str_replace(" ", "_", $dir['en_name']);
        $da['path']          = './Pictures/'.$name;
        $da['gallery_id']    = $gallery_id;
        $da['rubbish']       = 0;
        $da['modified_time'] = date('Y-m-d H:i:s',time());
        // 恢复操作
        foreach ($id_arr as $value) {
            $image  = $m->where(array('id'=>$value))->find();
            $result = $m->where(array('id'=>$value))->save($da);
            if($result){
                // 恢复数据成功则开始恢复具体图片文件
                if(copy($image['path']."/".$image['file_name'],$da['path']."/".$image['file_name'])){
                    @unlink($image['path']."/".$image['file_name']);
                    $im++;
                }
            }else{
                $data['error']  = 1;
                $data['status'] = 101;
                $data['msg']    = '图片移动失败';
                $m->rollback();
                return $data;
            }
        }
        // 统计正确提交恢复整体操作
        if($im == $count){
            $m->commit();
            $data['error'] = 0;
            $data['value'] = $im;
        }else{
            $m->rollback();
            $data['error']  = 1;
            $data['status'] = 103;
            $data['msg']    = '图片移动失败';
        }
        return $data;
    }

    // 清空回收站
    static function clear_rubbish(){
        //if(isset($_SESSION['user_id'])){
            $clear = M("product_picture")->where("gallery_id=0")->delete();
            if($clear){
                $data["error"] = 0;
                delfile("./Pictures/RUBBISH");
            }else{
                $data["error"]  = 1;
                $data["status"] = 101;
                $data['msg']    = '暂无数据，不需要清除';
            }
        //}else{
        //    $data["error"]  = 1;
        //    $data["status"] = 102;
        //}

        return $data;
    }
}