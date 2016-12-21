<?php
namespace Home\Controller;
use Think\Controller\RestController;
header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Methods:POST,GET');
header('Access-Control-Allow-Credentials:true'); 
header("Content-Type: application/json;charset=utf-8");
/**
* 类目管理控制器
*/
class CategoryController extends RestController
{
    protected $allowMethod    = array('get','post','put','delete');
    protected $defaultType    = 'json';
    public function _initialize()
    {
        // 没登录
        $auth = new \Think\Product\PAuth();
        $key = I('key');
        $uid = I('user_id');
        $uids = $auth->checkKey($uid, $key);
        if(!$uids){
            $this->response(['status' => 1012,'msg' => '您还没登陆或登陆信息已过期'],'json');
        }
        // 读取访问的地址
        $url = CONTROLLER_NAME . '/' . ACTION_NAME;
        if(!$auth->check($url , $uids)){
            $this->response(['status' => 1011,'msg' => '抱歉，权限不足'],'json');
        }
    }

    /**
     * 添加子类目
     */
    public function addSub(){
        $id      = I('post.id');
        $cn_name = I('post.cn_name');
        $en_name = I('post.en_name');
        $remark  = I('post.remark');
        $creator_id = I('post.creator_id');
        if(empty($creator_id)){
            $arr['status'] = 1012;
            $this->response($arr,'json');
            exit();
        }

        $data = array();
        if($id != null){
            if($cn_name != null){
                if($en_name != null){
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
                    $data['status'] = 105;
                    $data['msg']    = '英文名必填';
                }
            }else{
                $data['status'] = 104;
                $data['msg']    = '中文名必填';
            }   
        }else{
            $data['status'] = 102;
            $data['msg']    = '类目未选取';
        }
        $this->response($data,'json');
    }

    /**
     * 删除类目
     */
    public function  Delete(){
        $id   = I('post.id');
        $res  = \Think\Product\Category::Delete($id);
        $data = array();
        if($res == 1){
            $this->updateCategoryCache();
            $data['status'] = 100;
        }elseif($res == 2){
            $data['status'] = 112;
            $data['msg']    = '顶级类目不可操作';
        }elseif($res == 3){
            $data['status'] = 103;  // 类目下存在图片类目不能直接删除
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
     */
    public function updaName(){
        $id      = I('post.id');
        $cn_name = I('post.cn_name');
        $en_name = I('post.en_name');
        $data    = array();
        if($id != null){
            if($cn_name != null){
                if($en_name != null){
                    $res = \Think\Product\Category::UpdaName($id,$cn_name,$en_name);
                    if($res == 1){
                        $this->updateCategoryCache();
                        $data['status'] = 100;
                    }else{
                        $data['status'] = 101;
                        $data['msg']    = '修改失败';
                    }
                }else{
                    $data['status'] = 105;
                    $data['msg']    = '英文名必填';
                }
            }else{
                $data['status'] = 104;
                $data['msg']    = '中文名必填';
            }  
        }else{
            $data['status'] = 102;
            $data['msg']    = '类目未选择';
        }
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
     */
    public function move(){
        $moveid = I('post.moveid');
        $id     = I('post.id');
        $data   = \Think\Product\Category::GetAncestors($moveid,$id);
        if($data == 1){
            $this->updateCategoryCache();
            $data['status'] = 100;
        }else{
            $data['status'] = 101;
            $data['msg']    = '移动失败';
        }
        $this->response($data,'json');
    }

    /*
     * 模糊搜索类目
     */
    public function selVague(){
        $text = I('post.text');
        if(empty($text)){
            $data['status'] = 100;
            $this->response($data,'json');
            exit();
        }
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


    // 产品类目树结构
    public function treeCategory()
    {
        $key = I("post.ckey");
        if(!empty($key) && $key == 'category'){
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