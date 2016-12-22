<?php
namespace Home\Controller;
use Think\Controller;
/**
 * 图片类目管理控制器
 * @author cxl,lrf
 * @modify 2016/12/21
 */
class ImageCategoryController extends BaseController
{

    /**
     * 添加子类目
     * @param  id         上级类目id
     * @param  cn_name    中文名
     * @param  en_name    英文名
     * @param  remark     注解
     * @param  creator_id 创建者
     */
    public function addSub(){
        // 参数整合
        $id           = I('post.id');
        $cn_name      = I('post.cn_name');
        $en_name      = I('post.en_name');
        $category_id  = I('category_id');
        $remark       = I('post.remark');
        $creator_id = I('post.creator_id');
        if(empty($creator_id)){
            $arr['status'] = 1012;
            $this->response($arr,'json');
            exit();
        }
        $data = array();
        if($id == ""){
            $parentss = M()->query("select min(left_id) as minl from imageview where category_id=".$category_id);
            if($parentss[0]['minl'] != "") {
                // 查到id
                $ids = M('product_gallery')->where(array('left_id' => $parentss[0]['minl']))->find();
                $id = $ids['id'];
            }else{
                $id = 1;
            }
        }
        if($cn_name != null){
            if($en_name != null && preg_match("/^[a-zA-Z_0-9\s\'-]+$/",$en_name)){
                $en_name = addslashes($en_name);
                if($id == 1){ // 是顶级必需传产品类目id
                    if(!empty($category_id) && preg_match("/^[0-9]+$/",$category_id)){
                        $category_ids = $category_id;
                    }else{
                        $data['status'] = 102;
                        $data['msg']    = '产品类目选取失败';
                        $this->response($data,'json');
                        exit;
                    }
                }else{ // 不是则继承
                    $re = M('product_gallery')->find($id);
                    $category_ids = $re['category_id'];
                }
                $res = \Think\Product\ImageCategory::AddSub($id,$cn_name,$en_name,$remark,$category_ids,$creator_id);
                if($res == 1){
                    $data['status'] = 100;
                    // 更新缓存
                    updateGalleryCache();
                }elseif($res == 2){
                    $data['status'] = 106;
                    $data['msg']    = '该英文名已经存在';
                }else{
                    $data['status'] = 101;
                    $data['msg']    = '添加失败';
                }
            }else{
                $data['status'] = 105;
                $data['msg']    = '英文名为必填';
            }
        }else{
            $data['status'] = 104;
            $data['msg']    = '中文名为必填';
        }
        $this->response($data,'json');
    }

    /**
     * 删除类目
     * @param  id 类目id
     */
    public function Delete(){
        $id = I('post.id');
        $res = \Think\Product\ImageCategory::Delete_album($id);
        $data = array();
        if($res == 1){
            $data['status'] = 100;
            updateGalleryCache();
        }elseif($res == 2){
            $data['status'] = 112;
            $data['msg']    = '顶级类目不可操作';
        }elseif($res == 3){
            $data['status'] = 113;  // 该相册还有子相册不可删除
            $data['msg']    = '该相册还有子相册不可直接删除';
        }elseif($res == 4){
            $data['status'] = 114;  // 图片移动失败（移到回收站）
            $data['msg']    = '该相册还有图片不可直接删除';
        }else{
            $data['status'] = 101;
            $data['msg']    = '删除失败';
        }
        $this->response($data,'json');
    }

    /**
     * 修改类目名称
     * @param  id      类目id
     * @param  cn_name 中文名
     * @param  en_name 英文名
     */
    public function updaName(){
        $id = I('post.id');
        $cn_name = I('post.cn_name');
        $en_name = I('post.en_name');
        $data = array();
        if($id != null){
            if($cn_name != null){
                if($en_name != null && preg_match("/^[a-zA-Z_0-9\s\'-]+$/", $en_name)){
                    $res = \Think\Product\ImageCategory::UpdaName($id,$cn_name,$en_name);
                    if($res == 1){
                        $data['status'] = 100;
                        updateGalleryCache();
                    }elseif($res == -2){
                        $data['status'] = 101;
                        $data['msg']    = '类目已有图片，防止使用出错，不可修改';
                    }else{
                        $data['status'] = 101;
                        $data['msg']    = '更新失败';
                    }
                }else{
                    $data['status'] = 105;
                    $data['msg']    = '英文名错误';
                }
            }else{
                $data['status'] = 104;
                $data['msg']    = '中文名为必填';
            }  
        }else{
            $data['status'] = 102;
            $data['msg']    = '没有选择图片';
        }
        $this->response($data,'json');
    }
    
    /**
     * 查询顶级类目
     */
    public function getAncestors(){
        $data = array();
        $res = \Think\Product\ImageCategory::GetAncestors();
        if($res){
            $data['status'] = 100;
            $data['value']  = $res;
        }else{
            $data['value']  = 101;
            $data['msg']    = '暂无信息';
        }
        $this->response($data,'json');
    }

    /**
     * 查询某类目的下一级类目
     * @param  id 类目id
     */
    public function getChildren(){
        $data = array();
        $id = I('post.id');
        $res = \Think\Product\ImageCategory::GetSub($id);
        if($res){
            $data['status'] = 100;
            $data['value']  = $res;
        }else{
            $data['status'] = 101;
            $data['msg']    = '暂无数据';
        }
        $this->response($data,'json');
    }

    /**
     * 模块内移动类目
     * @param  id      移动到的类目id
     * @param  moveid  需移动的类目id
     *
     */
    public function move(){
        $moveid = I('post.moveid');
        $id = I('post.id');
        $data = \Think\Product\ImageCategory::GetAncestors($moveid,$id);
        if($data == 1){
            $data['status'] = 100;
            updateGalleryCache();
        }else{
            $data['status'] = 101;
            $data['msg']    = '移动失败';
        }
        $this->response($data,'json');
    }

    /*
     * 模糊搜索类目
     * @param keyword 搜索关键词
     */
    public function selVague(){
        $text = I('post.keyword');
        if(empty($text)){
            $data['status'] = 100;
            $this->response($data,'json');
            exit();
        }
        $arr = \Think\Product\ImageCategory::GetVague($text);
        if($arr){
            $data['status'] = 100;
            $data['value']  = $arr;
        }else{
            $data['status'] = 101;
            $data['msg']    = '暂无此类数据';
        }
        $this->response($data,'json');
    }

    // 拉取父级类目索引
    public function get_parent_path(){
        $id = I("id");
        if(empty($id)){
            $data['status'] = 102;
            $data['msg']    = '没有选择类目';
        }else{
            $result = \Think\Product\ImageCategory::get_parent_path($id);
            if($result){
                $data['status'] = 100;
                $data['value']  = $result['value'];
            }else{
                $data['status'] = $result['status'];
                $data['msg']    = $result['msg'];
            }
        }
        $this->response($data,'json');
    }

    // 通过id拉取类目
    // @param id
    public function get_gallery_by_id(){
        $id = I("id");
        if(empty($id)){
            $data['status'] = 102;
            $data['msg']    = '没有选择类目';
        }else{
            $result = \Think\Product\ImageCategory::get_gallery_by_id($id);
            if($result){
                $data['status'] = 100;
                $data['value']  = $result['value'];

            }else{
                $data['status'] = $result['status'];
                $data['msg']    = $result['msg'];
            }
        }
        $this->response($data,'json');
    }

    // 格式化分类树结构
    // @param category_id 所属产品类目id
    public function treeGallery()
    {
        $id = isset($_POST['category_id']) ? (int)I('category_id') : 0;
        $gallery = S('gallery');
        // 有缓存
        if($gallery){
            if($id == 0){
                $data['status']  = 100;
                $data['value'][] = $gallery;
            }else{
                foreach($gallery as $k => $val){
                    foreach($val as $ks => $v){
                        if($v['category_id'] == $id){
                            $data['status']  = 100;
                            $data['value'][] = $v;
                            break;
                        }
                    }
                }
            }
            if(empty($data['value'])){
                $data['status'] = 101;
                $data['msg']    = '没有相关信息';
                $data['value']  = array(
                    "warning" => "没有图片目录"
                );
            }
        }else{

            // 查询一组相册里面的顶级一个
            $parentss = M()->query("select min(left_id) as minl from imageview where category_id=".$id);
            if($parentss[0]['minl'] != ""){
                // 查到id
                $ids = M('product_gallery')->where(array('left_id'=>$parentss[0]['minl']))->find();
                $parents = M()->query("select id,cn_name,en_name,category_id,layer,left_id,right_id from imageview where id=".$ids['id']);
                // 统计最顶级类目的图片总数
                $pics = M('product_picture')->where(array('gallery_id'=>$parents[0]['id']))->count();
                $parents[0]['picture_count'] = $pics;
                if(!empty($parents)){
                    // 查到数据包
                    $result = M()->query("select id,cn_name,en_name,category_id,layer,left_id,right_id from imageview where left_id>=".$parents[0]['left_id']." and right_id<=".$parents[0]['right_id']);
                    // 统计每个图片类目里面图片总数
                    foreach($result as $key => $value){
                        $pic_count = M('product_picture')->where(array('gallery_id'=>$value['id']))->count();
                        $result[$key]['picture_count'] = $pic_count;
                    }
                    // 格式化归类 treeCa
                    $results = treeCa($result,$parents[0]['left_id'],$parents[0]['right_id'],$parents[0]['layer']);
                    $parents[0]['children'] = $results;
                    $data['status'] = 100;
                    $data['value']  = $parents;
                    updateGalleryCache();
                }else{
                    $data['status'] = 101;
                    $data['msg']    = '没有相关信息';
                    $data['value']  = array(
                        "warning" => "没有图片目录"
                    );
                }
            }else{
                $data['status'] = 101;
                $data['msg']    = '没有相关信息';
                $data['value']  = array( "warning" => "没有图片目录" );
            }
        }

        $this->response($data,'json');
    }

    // 更新图片类目缓存
    public function updateGalleryCache(){
        updateGalleryCache();
    }
}
