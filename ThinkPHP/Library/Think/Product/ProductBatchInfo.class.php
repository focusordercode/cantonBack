<?php
namespace Think\Product;
/**
 * 产品批量表类
 */
class ProductBatchInfo{
    private $arr=array();
    /*
     * 删除单条产品批量表数据
     */
    static function DelBatchInfo($product_id){
       $batchinfo=M("product_batch_information");
       $batchinfo->startTrans();
       $data['enabled']=0;
       $sql=$batchinfo->data($data)->where("product_id=%d",array($product_id))->save();
       $test=$batchinfo->field("product_id")->where("parent_id=%d",array($product_id))->find();
       if($test){
          $query=$batchinfo->data($data)->where("parent_id=%d",array($product_id))->save();
          if($sql!=='flase' && $query!=='flase'){
            $batchinfo->commit(); 
           	return 1;        
          }else{
            $batchinfo->rollback();
           	return -1;
          }
       }else{
          if($sql!=='flase'){
            $batchinfo->commit(); 
           	return 1;        
          }else{
            $batchinfo->rollback();
           	return -1;
          }       	
       }

    }

    /*
     * 添加单条产品批量表数据和变体
     */
    static function AddBatchInfo($data,$num){
    	$batchinfo=M("product_batch_information");
    	$batchinfo->startTrans();
    	$app_code="product_batch_information";
        $sequence=GetSysId($app_code,$num+1);
        $n=count($data);
        for($i=0;$i<$n;$i++){
            $data[$i]['product_id']=$sequence[0];
            $sql=$batchinfo->data($data[$i])->add();
            $array[]=$sql;
        }
        $field="title";
        for($i=0;$i<count($arr);$i++){
            $query=$batchinfo->field($field)->where("id=%d",array($array[$i]))->find();
            if(empty($query['title'])){
                    $batchinfo->rollback();
                    return -1;
            }  
        }
        $product_id= intval($sequence[0]);
        //判断是否有变体，有就复制需要的数量
        if(!empty($num)){
            $s=1;
            for($i=0;$i<$num;$i++){
               for($j=0;$j<$n;$j++){
                    $dt[$j]=$data[$j];
                    $dt[$j]['parent_id']=$product_id;
                    $dt[$j]['product_id']=$sequence[$s];
                    $sql=$batchinfo->data($dt[$j])->add();
                    $arr[]=$sql;
                }
                $field="title";
                for($z=0;$z<count($arr);$z++){
                    $query=$batchinfo->field($field)->where("id=%d",array($arr[$z]))->find();
                    if(empty($query['title'])){
                       $batchinfo->rollback();
                       return -1;
                    }
                }
                $arr=array();
                $s++; 
            }
            $num--;
        }
        $batchinfo->commit(); 
        return 1;
    }

    /*
     * 修改产品批量表数据
     */
    static function UpdateBatchInfo($id,$data){
    	$batchinfo=M("product_batch_information");
    	$batchinfo->startTrans();
    	$max=count($id);
    	for ($i=0; $i < $max ; $i++) {
    		$sql=$batchinfo->data($data[$i])->where("id=%d",array($id[$i]['id']))->save();
            if($sql!=='flase'){
               $arr[]=1;
    		}else{
    		   $arr[]=-1;
    		}
    	}
    	for ($j=0; $j < $max; $j++) { 
    		if($arr[$j]==-1){
               $batchinfo->rollback();
               return 0;
    		}
    	}
    	$batchinfo->commit();
    	return 1;
    }

    
    /*
     * 查询单条产品批量表数据和变体
     */
    static function SelBatchInfo($product_id){
    	$where=array();
    	$array=array();
    	$arr=array();
    	$batchinfo=M("product_batch_information");
    	$where['product_id']=$product_id;
    	$where['parent_id']=0;
        $where['enabled']=1;
        $fl="id,category_id,template_id,product_id,parent_id,no,title,data_type_code,interger_value,char_value,decimal_value,date_value,boolean_value";
    	$sql=$batchinfo->field($fl)->where($where)->select();
    	$arr[]=$sql;
    	$productid=$batchinfo->field("product_id")->where("parent_id=%d",array($product_id))->group("product_id")->select();
    	$max=count($productid);
    	for($i=0;$i<$max;$i++){
    		$array['product_id']=$productid[$i]['product_id'];
    		$array['enabled']=1;
            $data=$batchinfo->field($fl)->where($array)->order("no asc")->select();
            $arr[]=$data;
        }
    	return($arr);
    }



// ======================================================
// ======================================================
// =====================程晓良添加代码===================
// ======================================================
// ======================================================

    public static function GetAllBatchInfo($template_id){

    	$product = M('product_batch_information');
    	$where = array(
    		'template_id' => $template_id,
    		'enabled'     => 1,
    		'parent_id'   => 0
    		);
    	$result = $product->where($where)->field("id,product_id")->group("product_id")->select();
    	foreach ($result as $key => $value) {
    		$where['product_id'] = $value['product_id'];
    		$res[] = $product->where($where)->order('no asc')->select();
    	}
    	if(empty($res)){
    		return 101;
    	}else{
    		return $res;
    	}
    }


    /*
     * 额外添加变体
     */
    static function AddVariantInfo($num,$product_id,$data){
        $info=M("product_batch_information");
        $info->startTrans();
        $app_code="product_batch_information";
        $sequence=GetSysId($app_code,$num);
        $max=count($data);
        for ($j=0; $j < $num ; $j++) {
            for ($i=0; $i < $max; $i++) { 
                $data[$i]['parent_id']=$product_id;
                $data[$i]['product_id']=$sequence[$j];
                $sql=$info->data($data[$i])->add();
                $arr[]=$sql;
            }
            $field="title";
            $nums=count($arr);
            for($z=0;$z<$nums;$z++){
                $query=$info->field($field)->where("id=%d",array($arr[$z]))->find();
                if(empty($query['title'])){
                    $info->rollback();
                    return -1;
                }  
            }
        }
        $info->commit();
        return 1;
    }
}
