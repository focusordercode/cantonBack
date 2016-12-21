<?php
namespace Home\Controller;
use Think\Controller\RestController;
header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Methods:POST,GET');
header('Access-Control-Allow-Credentials:true'); 
header("Content-Type: application/json;charset=utf-8");
/**
* 产品信息管理控制器
*/
class ProductController extends RestController{
	protected $allowMethod    = array('get','post','put','delete');
    protected $defaultType    = 'json';

    /**
     * 查询所有产品标题
    */
	public function getTitle(){
		$res = \Think\Product\Product::GetTitle();
		if($res){
            $data['status'] = 100;
            $data['value']  = $res;
		}else{
			$data['status'] = 101;
		}
		$this->response($data,'json');
	}

	/**
     * 查询相同类目下的产品标题
    */
	public function getCategoTitle(){
		$category = I('post.catego');
		$res = \Think\Product\Product::GetTitle($category);
		if($res){
            $data['status'] = 100;
            $data['value']  = $res;
		}else{
			$data['status'] = 101;
		}
		$this->response($data,'json');
	}

	/**
     * 查询产品详细信息
    */
	public function getInfo(){
		$id = I('post.id');
		$category = I('post.catego');
		$res = \Think\Product\Product::GetInfo(1,'car');
		if($res){
            $data['status'] = 100;
            $data['value']  = $res;
		}else{
			$data['status'] = 101;
		}
		$this->response($data,'json');
	}

	/**
     * 删除产品信息
    */
	public function delInfo(){
		$id = I('post.id');
		$category = I('post.catego');
		$res = \Think\Product\Product::DelInfo($id,$category);
		if($res == 1){
			$data['status'] = 100;
		}else{
			$data['status'] = 101;
		}
		$this->response($data,'json');
	}

	/**
     * 修改产品标题
    */
	public function updateTitle(){
        $id    = I('post.id');
		$title = I('post.title');
		$res = \Think\Product\Product::UpdaTitle($id,$title);
		if($res == 1){
			$data['status'] = 100;
		}else{
			$data['status'] = 101;
		}
		$this->response($data,'json');
	}

	/**
     * 修改产品详细信息
    */
	public function updateInfo(){
		$id    = I('post.id');
		$data  = array();
	    $arr   = array();
	    $category               = I('post.catego');
	    $data['SKU']            = I('post.SKU');
	    $data['UPC_EAN']        = I('post.UPC');
	    $data['manufacturer']   = I('post.manufacturer');
	    $data['brand']          = I('post.brand');
	    $data['product_title']  = I('post.title');
	    $data['description']    = I('post.description');
	    $data['quantity']       = I('post.quantity');
	    $data['price_USD']      = I('post.USD');
	    $data['price_GBP']      = I('post.GBP');
	    $data['bulletpoint_1']  = I('post.bulletpoint1');
	    $data['bulletpoint_2']  = I('post.bulletpoint2');
	    $data['bulletpoint_3']  = I('post.bulletpoint3');
	    $data['bulletpoint_4']  = I('post.bulletpoint4');
	    $data['bulletpoint_5']  = I('post.bulletpoint5');
	    $data['searchterm_1']   = I('post.searchterm1');
	    $data['searchterm_2']   = I('post.searchterm2');
	    $data['searchterm_3']   = I('post.searchterm3');
	    $data['searchterm_4']   = I('post.searchterm4');
	    $data['searchterm_5']   = I('post.searchterm5');
	    $data['imageURL']       = I('post.image');
	    $data['cloor']          = I('post.color');
	    $data['size']           = I('post.size');
	    $data['weight']         = I('post.weight');
	    $data['category']       = $category;
	    switch ($category) {
    		case 'car':
    		    $arr['product_type']     = I('post.type');
    		    $arr['partslink_number'] = I('number');
    			break;
    		case 'clothing':
    		    $arr['gender']           = I('post.gender');
    		    $arr['type']             = I('post.cltype');
    		    $arr['size']             = I('post.clsize');
    		    $arr['material_fabric']  = I('post.fabric');
    			break;
            case 'sunglass':
                $arr['department']       = I('post.department');
                $arr['frame_color']      = I('post.frame');
                $arr['frame_material']   = I('post.material');
                $arr['lens_color']       = I('post.lenscolor');
                $arr['lens_material']    = I('post.lensmaterial');
                $arr['lens_width']       = I('post.lenswidth');
                $arr['item_shape']       = I('post.itemshape');
                $arr['lens_type']        = I('lenstype');
    			break;
    		case 'toys':
    		    $arr['minimum_age']         = I('post.mininum_age');
    		    $arr['minimum_age_measure'] = I('post.measure');
    		    $arr['choking_hazard']      = I('post.hazard');
    		    $arr['age']                 = I('age');
    		    $arr['age_measure']         = I('age_measure');
    			break;
    		case 'band':
    		    $arr['targetaudiencr']   = I('post.targetaudiencr');
    		    $arr['band_color']       = I('post.bandcolor');
    		    $arr['band_width']       = I('post.bandwidth');
    		    $arr['band_material']    = I('post.bandmaterial');
    		    $arr['dial_color']       = I('post.dialcolor');
    		    $arr['item_shape']       = I('post.itemshape');
    		    $arr['movement_type']    = I('post.movement');
    		    $arr['crystal_type']     = I('post.crystal');
    		    $arr['water_resitant']   = I('post.water');
    			break;
    		case 'jewellery':
    		    $arr['type']             = I('post.jtype');
    		    $arr['metal_type']       = I('post.metaltype');
    		    $arr['metal_stamp']      = I('post.metalstamp');
    		    $arr['ring_size']        = I('post.ringsize');
    			break;
    	}
        $res = \Think\Product\Product::updaInfo($id,$data,$arr,$category);
		if($res == 1){
			$data['status'] = 100;
		}else{
			$data['status'] = 101;
		}
		$this->response($data,'json');

	}

	/**
     * 添加产品详细信息
     */
	public function AddInfo(){
		$data = array();
	    $arr  = array();
	    $category                 = I('post.catego');
	    $data['SKU']              = I('post.SKU');
	    $data['UPC_EAN']          = I('post.UPC');
	    $data['manufacturer']     = I('post.manufacturer');
	    $data['brand']            = I('post.brand');
	    $data['product_title']    = I('post.title');
	    $data['description']      = I('post.description');
	    $data['quantity']         = I('post.quantity');
	    $data['price_USD']        = I('post.USD');
	    $data['price_GBP']        = I('post.GBP');
	    $data['bulletpoint_1']    = I('post.bulletpoint1');
	    $data['bulletpoint_2']    = I('post.bulletpoint2');
	    $data['bulletpoint_3']    = I('post.bulletpoint3');
	    $data['bulletpoint_4']    = I('post.bulletpoint4');
	    $data['bulletpoint_5']    = I('post.bulletpoint5');
	    $data['searchterm_1']     = I('post.searchterm1');
	    $data['searchterm_2']     = I('post.searchterm2');
	    $data['searchterm_3']     = I('post.searchterm3');
	    $data['searchterm_4']     = I('post.searchterm4');
	    $data['searchterm_5']     = I('post.searchterm5');
	    $data['imageURL']         = I('post.image');
	    $data['cloor']            = I('post.color');
	    $data['size']             = I('post.size');
	    $data['weight']           = I('post.weight');
	    $data['category']         = $category;
	    switch ($category) {
    		case 'car':
    		    $arr['product_type']         = I('post.type');
    		    $arr['partslink_number']     = I('number');
    			break;
    		case 'clothing':
    		    $arr['gender']               = I('post.gender');
    		    $arr['type']                 = I('post.cltype');
    		    $arr['size']                 = I('post.clsize');
    		    $arr['material_fabric']      = I('post.fabric');
    			break;
            case 'sunglass':
                $arr['department']           = I('post.department');
                $arr['frame_color']          = I('post.frame');
                $arr['frame_material']       = I('post.material');
                $arr['lens_color']           = I('post.lenscolor');
                $arr['lens_material']        = I('post.lensmaterial');
                $arr['lens_width']           = I('post.lenswidth');
                $arr['item_shape']           = I('post.itemshape');
                $arr['lens_type']            = I('lenstype');
    			break;
    		case 'toys':
    		    $arr['minimum_age']          = I('post.mininum_age');
    		    $arr['minimum_age_measure']  = I('post.measure');
    		    $arr['choking_hazard']       = I('post.hazard');
    		    $arr['age']                  = I('age');
    		    $arr['age_measure']          = I('age_measure');
    			break;
    		case 'band':
    		    $arr['targetaudiencr']       = I('post.targetaudiencr');
    		    $arr['band_color']           = I('post.bandcolor');
    		    $arr['band_width']           = I('post.bandwidth');
    		    $arr['band_material']        = I('post.bandmaterial');
    		    $arr['dial_color']           = I('post.dialcolor');
    		    $arr['item_shape']           = I('post.itemshape');
    		    $arr['movement_type']        = I('post.movement');
    		    $arr['crystal_type']         = I('post.crystal');
    		    $arr['water_resitant']       = I('post.water');
    			break;
    		case 'jewellery':
    		    $arr['type']                 = I('post.jtype');
    		    $arr['metal_type']           = I('post.metaltype');
    		    $arr['metal_stamp']          = I('post.metalstamp');
    		    $arr['ring_size']            = I('post.ringsize');
    			break;
    		default:
    			
    			break;
    	}
        $res = \Think\Product\Product::AddInfo($data,$arr,$category);
		if($res == 1){
			$data['status'] = 100;
		}else{
			$data['status'] = 101;
		}
		$this->response($data,'json');
	}

}