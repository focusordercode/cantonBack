<?php
namespace Home\Controller;
use Think\Controller;
/**
 * 类目管理控制器
 * @author cxl,lrf
 * @modify 2016/12/21
 */
class CategoryController extends BaseController
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
        $id      = (int)I('post.id');
        $cn_name = I('post.cn_name');
        $en_name = I('post.en_name');
        $remark  = I('post.remark');
        $creator_id = (int)I('post.creator_id');
        if(empty($creator_id)){
            $arr['status'] = 1012;
            $this->response($arr,'json');
            exit();
        }
        if($id == 0) $this->response(['status' => 102, 'msg' => '类目未选取'],'json');
        if($cn_name != null && $en_name != null){
            $en_name = addslashes($en_name);
            $res = \Think\Product\Category::AddSub($id,$cn_name,$en_name,$remark,$creator_id);
            if($res == 1){
                $this->updateCategoryCache();
                $data['status'] = 100;
            }else{
                $data['status'] = 101;
                $data['msg']    = '添加失败';
            }
        }else{
            $data['status'] = 104;
            $data['msg']    = '中英文名必填';
        }
        $this->response($data,'json');
    }

    /**
     * 删除类目
     * @param  id 类目id
     */
    public function  Delete(){
        $id   = (int)I('post.id');

        if($id == 0) $this->response(['status' => 101 ,'msg' => '请求失败']);

        if(!limitOperation('product_category' ,$id ,120 ,$this->loginid ,'R')){
            $this->response(['status' => 101 ,'msg' => '有同事在操作该数据']);
        }
        $res  = \Think\Product\Category::Delete($id);
        $data = array();
        if($res == 1){
            // 更新类目缓存
            $this->updateCategoryCache();
            $data['status'] = 100;
        }elseif($res == 2){
            $data['status'] = 112;
            $data['msg']    = '顶级类目不可操作';
        }elseif($res == 3){
            $data['status'] = 103;
            $data['msg']    = '类目下有关联数据不能删除';
        }elseif($res == 4){
            $data['status'] = 103;
            $data['msg']    = '类目下存在子类不可直接删除';
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
        $id      = (int)I('post.id');
        $cn_name = I('post.cn_name');
        $en_name = I('post.en_name');
        $data    = array();
        if($id == 0) $this->response(['status' => 102, 'msg' => '类目未选取'],'json');
        if($cn_name != null && $en_name != null)
        {
            // 多人操作限制
            if(!limitOperation('product_category' ,$id ,120 ,$this->loginid)){
                $this->response(['status' => 101 ,'msg' => '有同事在操作该数据']);
            }
            $res = \Think\Product\Category::UpdaName($id,$cn_name,$en_name);
            if($res == 1){
                $this->updateCategoryCache();
                $data['status'] = 100;
            }else{
                $data['status'] = 101;
                $data['msg']    = '修改失败';
            }
        }else{
            $data['status'] = 104;
            $data['msg']    = '中英文名必填';
        }
        EndEditTime('product_category' ,$id);
        $this->response($data,'json');
    }

    /**
     * 查询顶级类目
     */
    public function getAncestors(){

        $data = array();
        $res  = \Think\Product\Category::GetAncestors();
        if($res){
            $data['status'] = 100;
            $data['value']  = $res;
        }else{
            $data['value'] = 101;
            $data['msg']   = '没有相关信息';
        }
        $this->response($data,'json');
    }

    /**
     * 查询某类目的下一级类目
     * @param  id 类目id
     */
    public function getChildren(){
        $data = array();
        $id   = I('post.id');
        $res  = \Think\Product\Category::GetSub($id);
        if($res){
            $data['status'] = 100;
            $data['value']  = $res;
        }else{
            $data['status'] = 101;
            $data['msg']    = '没有相关信息';
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
        $id     = I('post.id');
        // 多人操作限制
        if(!limitOperation('product_category' ,$moveid ,120 ,$this->loginid)){
            $this->response(['status' => 101 ,'msg' => '有同事在操作该数据']);
        }
        $data   = \Think\Product\Category::GetAncestors($moveid,$id);
        if($data == 1){
            $this->updateCategoryCache();
            $data['status'] = 100;
        }else{
            $data['status'] = 101;
            $data['msg']    = '移动失败';
        }
        EndEditTime('product_category' ,$moveid);
        $this->response($data,'json');
    }

    /*
     * 模糊搜索类目
     * @param  text  搜索关键词
     */
    public function selVague(){
        $text = I('post.text');
        if(empty($text)){
            $data['status'] = 100;
            $this->response($data,'json');
            exit();
        }
        $text = __sqlSafe__($text);
        $arr = \Think\Product\Category::GetVague($text);
        if($arr){
            $data['status'] = 100;
            $data['value']  = $arr;
        }else{
            $data['status'] = 101;
            $data['msg']    = '没有相关信息';
        }
        $this->response($data,'json');
    }

    /*
     * 产品类目树结构
     * @param  ckey  读取类目 请固定传category
     * */
    public function treeCategory()
    {
        $key = I("post.ckey");
        if($key == 'category'){
            $category = S('category');
            if($category){
                $parents = $category;
            }else{
                $parents = M('product_category')->find(1);
                $result  = M()->query("select id,cn_name,en_name,layer,left_id,right_id from productcategoryview where left_id>=1 and right_id<=".$parents['right_id']);
                $results = treeCa($result,1,$parents['right_id'],1);
                $parents['children'] = $results;
                S('category' ,$parents ,3153600);
            }
        }else{
            $parents['status'] = 101;
            $parents['msg']    = '无权限访问';
        }
        $this->response($parents,'json');
    }

    /*
     * 更新类目数据缓存
     * $this->updateCategoryCache();
     * */
    public function updateCategoryCache(){
        $parents = M('product_category')->find(1);
        $result  = M()->query("select id,cn_name,en_name,layer,left_id,right_id from productcategoryview");
        $results = treeCa($result,1,$parents['right_id'],1);
        $parents['children'] = $results;
        S('category' ,$parents ,3153600);
    }
}