<?php
namespace Think\Product;
/*
 * 删除限制方法
 * 具体的删除操作可以直接调用相对应的操作  如: 删除类目
 * if(dgallery($galleryid)){
 *     可删除
 * }else{
 *     不可删除
 * }
 * */

class  DeleteLimit
{
    /*
     * 图片类目删除 ImagesCategory
     * @ $galleryid 图片类目id
     * */
    public static function dgallery($galleryid)
    {
        // gallery_id 图片类目id
        $m = M('product_picture');
        $n = M('product_gallery');
        $resultA = $m->where('gallery_id = %d' ,[$galleryid])->select();
        $resultB = $n->where('gallery_id = %d' ,[$galleryid])->find();
        // 有子类目
        if($resultB['right_id'] - $resultB['left_id'] != 1){
            return false;
        }
        // 有图片 || 顶级类目
        if($resultA || $galleryid == 1){
            return false;
        }
        return true;
    }

    /*
     * 产品类目删除
     * @ param $categoryid 产品类目id
     * */
    public static function dcategory($categoryid)
    {
        // form_id 批量表id 只有批量表匹配upc
        // 需要检查模板、表格、图库、子类目
        $f = M('product_category');
        $e = M('product_gallery');
        $d = M('product_template');
        $c = M('product_batch_template');
        // 没有模板即不存在表格
        $resultF = $f->where('id = %d' ,[$categoryid])->find();
        $resultE = $e->where('category_id = %d' ,[$categoryid])->select();
        $resultD = $d->where('category_id = %d' ,[$categoryid])->select();
        $resultC = $c->where('category_id = %d' ,[$categoryid])->select();

        // 有子类
        if($resultF['right_id'] - $resultF['left_id'] != 1){
            return false;
        }
        if($resultC || $resultD || $resultE){
            return false;
        }
        return true;
    }

    /*
     * 客户删除
     * @ param $customid 客户id
     * */
    public static function dcustomer($customid)
    {
        // client_id 客户id
        $m = M('product_batch_form');
        $n = M('product_form');
        $resultA = $m->where('id = %d' ,[$customid])->select();
        $resultB = $n->where('id = %d' ,[$customid])->select();
        if($resultA || $resultB){
            return false;
        }
        return true;
    }

    /*
     * UPC删除
     * @ param $formid 批量表id
     * */
    public static function dupc($formid)
    {
        // form_id 批量表id 只有批量表匹配upc
        $m = M('product_upc_code');

        return true;
    }

    /*
     * 图片删除
     * @ param $pictureid 图片id
     * */
    public static function dpicture($pictureid)
    {
        // form_id 批量表id 只有批量表匹配upc
        $m = M('product_for_picture');
        $n = M('product_picture');


        return true;
    }
}
