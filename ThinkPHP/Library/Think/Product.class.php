<?php
namespace Think;
/**
 * 产品信息管理类
 */
class Product{
	// protected $id;
	// protected $SKU;
	// protected $UPC_EAN;
	// protected $manufacturer;
	// protected $brand;
	// protected $title;
	// protected $description;
	// protected $quantity;
	// protected $USD;
	// protected $GBP;
	// protected $bullet1;
	// protected $bullet2;
	// protected $bullet3;
	// protected $bullet4;
	// protected $bullet5;
	// protected $sear1;
	// protected $sear2;
	// protected $sear3;
	// protected $sear4;
	// protected $sear5;
	// protected $imageURL;
	// protected $color;
	// protected $size;
	// protected $weight;
    
    /**
     * 获取产品信息标题
     */
    static function GetTitle(){
    	$tbl_prodcut=M('product');
    	$filed="id,product_title,category";
    	$sql=$tbl_prodcut->field($filed)->select();
    	return($sql);
    }

     /**
     * 获取相同类目的产品信息标题
     */
    static function GetCategoryTitle($category){
    	$tbl_prodcut=M('product');
    	$filed="id,product_title,category";
    	$data=array();
    	$data['categoey']=$categoey;
    	$sql=$tbl_prodcut->field($filed)->where($data)->select();
    	return($sql);
    }

    /**
     * 获取产品详细信息
     */
    static function GetInfo($id,$category){
    	$tbl_prodcut=M('product p');
    	switch ($category) {
    		case 'car':
    		    $filed="p.id,p.SKU,p.UPC_EAN,p.manufacturer,p.brand,p.product_title,p.description,p.quantity,p.price_USD,p.price_GBP,p.bulletpoint_1,p.bulletpoint_2,p.bulletpoint_3,p.bulletpoint_4,p.bulletpoint_5,p.searchterm_1,p.searchterm_2,p.searchterm_3,p.searchterm_4,p.searchterm_5,p.imageURL,p.color,p.size,p.weight,car.product_type,car.partslink_number";
    			$sql=$tbl_prodcut->field($field)->join('left join tbl_product_car car on p.id=car.product_id')->where("id=%d",array($id))->select();
    			break;
    		case 'clothing':
    		    $filed="p.id,p.SKU,p.UPC_EAN,p.manufacturer,p.brand,p.product_title,p.description,p.quantity,p.price_USD,p.price_GBP,p.bulletpoint_1,p.bulletpoint_2,p.bulletpoint_3,p.bulletpoint_4,p.bulletpoint_5,p.searchterm_1,p.searchterm_2,p.searchterm_3,p.searchterm_4,p.searchterm_5,p.imageURL,p.color,p.size,p.weight,c.gender,c.type,c.size,c.material_fabric";
    			$sql=$tbl_prodcut->field($field)->join('left join tbl_product_clothing c on p.id=c.product_id')->where("id=%d",array($id))->select();
    			break;
            case 'sunglass':
    		    $filed="p.id,p.SKU,p.UPC_EAN,p.manufacturer,p.brand,p.product_title,p.description,p.quantity,p.price_USD,p.price_GBP,p.bulletpoint_1,p.bulletpoint_2,p.bulletpoint_3,p.bulletpoint_4,p.bulletpoint_5,p.searchterm_1,p.searchterm_2,p.searchterm_3,p.searchterm_4,p.searchterm_5,p.imageURL,p.color,p.size,p.weight,s.departement,s.frame_color,s.frame_material,s.lens_color,s.lens_material,s.lends_width,s.item_shape,s.lens_type";
    			$sql=$tbl_prodcut->field($field)->join('left join tbl_product_sunglass s on p.id=s.product_id')->where("id=%d",array($id))->select();
    			break;
    		case 'toys':
    		    $filed="p.id,p.SKU,p.UPC_EAN,p.manufacturer,p.brand,p.product_title,p.description,p.quantity,p.price_USD,p.price_GBP,p.bulletpoint_1,p.bulletpoint_2,p.bulletpoint_3,p.bulletpoint_4,p.bulletpoint_5,p.searchterm_1,p.searchterm_2,p.searchterm_3,p.searchterm_4,p.searchterm_5,p.imageURL,p.color,p.size,p.weight,t.minimum_age,t.minimum_age_measure,t.choking_hazard,t.age,t.age_measure";
    			$sql=$tbl_prodcut->field($field)->join('left join tbl_product_toys t on p.id=t.product_id')->where("id=%d",array($id))->select();
    			break;
    		case 'band':
    		    $filed="p.id,p.SKU,p.UPC_EAN,p.manufacturer,p.brand,p.product_title,p.description,p.quantity,p.price_USD,p.price_GBP,p.bulletpoint_1,p.bulletpoint_2,p.bulletpoint_3,p.bulletpoint_4,p.bulletpoint_5,p.searchterm_1,p.searchterm_2,p.searchterm_3,p.searchterm_4,p.searchterm_5,p.imageURL,p.color,p.size,p.weight,b.targetaudiencr,b.band_color,b.band_width,b.material,b.dial_color,b.item_shape,b.movement_type,b.crystal_type,b.water_resitant";
    			$sql=$tbl_prodcut->field($field)->join('left join tbl_product_band b on p.id=b.product_id')->where("id=%d",array($id))->select();
    			break;
    		case 'jewellery':
    		    $filed="p.id,p.SKU,p.UPC_EAN,p.manufacturer,p.brand,p.product_title,p.description,p.quantity,p.price_USD,p.price_GBP,p.bulletpoint_1,p.bulletpoint_2,p.bulletpoint_3,p.bulletpoint_4,p.bulletpoint_5,p.searchterm_1,p.searchterm_2,p.searchterm_3,p.searchterm_4,p.searchterm_5,p.imageURL,p.color,p.size,p.weight,j.type,j.metal_type,j.metal_stamp,j.ring_size";
    			$sql=$tbl_prodcut->field($field)->join('left join tbl_product_jewellery j on p.id=j.product_id')->where("id=%d",array($id))->select();
    			break;
    		default:
    			$sql=$tbl_prodcut->where("id=%d",array($id))->select();
    			break;
    	}
    	return($sql);
    }

    /**
     * 添加产品详细信息
     */
    static function AddInfo($data,$arr,$category){
    	$tbl_prodcut=M('product p');
    	$tbl_prodcut->startTrans();
    	$sql=$tbl_prodcut->data($data)->add();
    	$arr['product_id']=$sql;
    	switch ($category) {
    		case 'car':
    		    $car=M('product_car');
                $query=$car->data($arr)->add();
    			break;
    		case 'clothing':
    		    $clothing=M('product_clothing');
                $query=$clothing->data($arr)->add();
    			break;
            case 'sunglass':
    		    $sunglass=M('product_sunglass');
                $query=$sunglass->data($arr)->add();
    			break;
    		case 'toys':
    		    $toys=M('product_toys');
                $query=$toys->data($arr)->add();
    			break;
    		case 'band':
    		    $band=M('product_band');
                $query=$toys->data($arr)->add();
    			break;
    		case 'jewellery':
    		    $jewellery=M('product_jewellery');
                $query=$jewellery->data($arr)->add();
    			break;
    		default:
    			
    			break;
    	}
    	if($sql and $query){
    		$tbl_prodcut->commit();//成功则提交
    		return 1;
    	}else{
    		$tbl_prodcut->rollback();//不成功，则回滚
    		return-1;
    	}   	
    }

    /**
     * 删除产品详细信息
     */
    static function DelInfo($id,$category){
        $tbl_prodcut=M('product p');
    	$tbl_prodcut->startTrans();
    	$sql=$tbl_prodcut->where("id = '%d'",array($id))->delete();
    	$arr['product_id']=$sql;
    	switch ($category) {
    		case 'car':
    		    $car=M('product_car');
                $query=$car->where("id = '%d'",array($id))->delete();
    			break;
    		case 'clothing':
    		    $clothing=M('product_clothing');
                $query=$clothing->where("id = '%d'",array($id))->delete();
    			break;
            case 'sunglass':
    		    $sunglass=M('product_sunglass');
                $query=$sunglass->where("id = '%d'",array($id))->delete();
    			break;
    		case 'toys':
    		    $toys=M('product_toys');
                $query=$toys->where("id = '%d'",array($id))->delete();
    			break;
    		case 'band':
    		    $band=M('product_band');
                $query=$toys->where("id = '%d'",array($id))->delete();
    			break;
    		case 'jewellery':
    		    $jewellery=M('product_jewellery');
                $query=$jewellery->where("id = '%d'",array($id))->delete();
    			break;
    		default:
    			
    			break;
    	}
    	if($sql and $query){
    		$tbl_prodcut->commit();//成功则提交
    		return 1;
    	}else{
    		$tbl_prodcut->rollback();//不成功，则回滚
    		return-1;
    	}
    }
    
    /**
     * 修改产品标题
     */
    static function UpdaTitle($id,$title){
        $tbl_prodcut=M('product');
        $data['title']=$title;
        $sql=$tbl_prodcut->where("id=%d",array($id))->data($data)->save();
        if($sql!=='flase'){
        	return  1;
        }else{
        	return  -1;
        }
    }

    /**
     * 修改产品详细信息
     */
    static function updaInfo($id,$data,$arr,$category){
        $tbl_prodcut=M('product p');
    	$tbl_prodcut->startTrans();
    	$sql=$tbl_prodcut->where("id = '%d'",array($id))->data($data)->save();
    	$arr['product_id']=$sql;
    	switch ($category) {
    		case 'car':
    		    $car=M('product_car');
                $query=$car->where("id = '%d'",array($id))->data($arr)->save();
    			break;
    		case 'clothing':
    		    $clothing=M('product_clothing');
                $query=$clothing->where("id = '%d'",array($id))->data($arr)->save();
    			break;
            case 'sunglass':
    		    $sunglass=M('product_sunglass');
                $query=$sunglass->where("id = '%d'",array($id))->data($arr)->save();
    			break;
    		case 'toys':
    		    $toys=M('product_toys');
                $query=$toys->where("id = '%d'",array($id))->data($arr)->save();
    			break;
    		case 'band':
    		    $band=M('product_band');
                $query=$toys->where("id = '%d'",array($id))->data($arr)->save();
    			break;
    		case 'jewellery':
    		    $jewellery=M('product_jewellery');
                $query=$jewellery->where("id = '%d'",array($id))->data($arr)->save();
    			break;
    		default:
    			
    			break;
    	}
    	if($sql!=='flase' and $query!=='flase'){
    		$tbl_prodcut->commit();//成功则提交
    		return 1;
    	}else{
    		$tbl_prodcut->rollback();//不成功，则回滚
    		return-1;
    	} 
    }
    
}