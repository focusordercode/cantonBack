<?php
namespace Home\Controller;
use Think\Controller;
/**
* 产品中心控制器
* @author lrf
* @modify 2016/12/22
*/
class ProductCenterController extends BaseController
{
	protected $file = "./Public/file/";
	protected $rule_str    = "/^[a-z_A-Z()\s]+[0-9]{0,10}$/";
	/*
	 * 添加修改产品中心产品信息接口
	 * @param state 添加模式
	 * @param creator_id 创建者id
	 * @param category_id 类目id
	 * @param cn_name 中文名称
	 * @param en_name 英文名称
	 * @param remark  说明
	 * @param enabled 状态
	 */
	public function setProductCenter(){
		$state = I('post.state');
		$category_id = I('post.category_id');
		$creator_id = I('post.creator_id');
		if(empty($creator_id)){
			$arr['status'] = 1012;
			$this->response($arr,'json');
			exit();
		}
		if(empty($category_id)){
			$arr['status'] = 102;
			$arr['msg'] = "请选择类目";
			$this->response($arr,'json');
			exit();
		}
		if($state == 'single'){
			$cn_name =  I('post.cn_name');
			$en_name =  I('post.en_name');
			$enabled =  !empty(I('post.enabled')) ? I('post.enabled') : 1;
			$remark  = I('post.remark');
			$data['cn_name'] = $cn_name;
			$data['en_name'] = $en_name;
			$data['enabled'] = $enabled;
			$data['category_id'] = !empty($category_id)?$category_id:1;
			$data['remark']  = $remark;
			$data['creator_id'] = $creator_id;
			$data['created_time'] = date('Y-m-d H:i:s',time());
			$data['modified_time'] = date('Y-m-d H:i:s',time());
			if(empty($cn_name)){
				$arr['status'] = 102;
				$arr['msg'] = "中文名称不能为空";
				$this->response($arr,'json');
				exit();
			}
			if(empty($en_name)){
				$arr['status'] = 102;
				$arr['msg'] = "英文名称不能为空";
				$this->response($arr,'json');
				exit();
			}
			if(!preg_match($this->rule_str,$en_name)){
				$arr['status'] = 102;
				$arr['msg'] = "英文名称只能是字母";
				$this->response($arr,'json');
				exit();
			}
			$res = \Think\Product\ProductCenter::SetProductCenter($state,$data,1);
			if($res == 1){
				$arr['status'] = 100;
			}else{
				$arr['status'] = 101;
				$arr['msg'] = "添加失败";
			} 
		}elseif ($state == 'many') {
			//上传文件添加
			$xlsx = 'xlsx';
			$xls = 'xls';
    		if($_FILES){
    			$type = strtolower(substr(strrchr($_FILES['file']['name'], '.'), 1)); //获取文件类型
    			if($type == $xlsx || $type == $xls){
					$size = (($_FILES['file']['size'])/1024)/1024; // mb兆
					if($size < 3){
						if($type == 'xls'){
               			    $types = 'Excel5';
               			}elseif($type == 'xlsx'){
               			    $types = 'Excel2007';
               			}
						$file_name = $this->file."product_".date("Ymd-His", time()). '.' .$type;
						move_uploaded_file($_FILES['file']['tmp_name'], $file_name);
						$array=read_excel($file_name,$types,0);
						if(empty($array)){
							$arr['status'] = 101;
							$arr['msg'] = "没有数据";
							$this->response($arr,'json'); 
							exit();
						}else{
							foreach ($array as $key => $value) {
								list($cn_name,$en_name,$enabled,$remark)=$value;
								$data[$key]['cn_name'] = $cn_name;
								$data[$key]['en_name'] = $en_name;
								$data[$key]['enabled'] = !empty($enabled)?$enabled:1;
								$data[$key]['remark'] = $remark;
								$data[$key]['category_id'] = !empty($category_id)?$category_id:1;
			 					$data[$key]['creator_id'] = $creator_id;
			 					$data[$key]['created_time'] = date('Y-m-d H:i:s',time());
			 					$data[$key]['modified_time'] = date('Y-m-d H:i:s',time());
							}
						}
						$res = \Think\Product\ProductCenter::SetProductCenter($state,$data,1);
						unlink($file_name);
						$arr['status'] = 100;
						$arr['value'] = $res;
					}else{
						$data['status'] = 104;
        	            $data['msg']    = '文件大小超负荷';
						$this->response($data,'json');exit();
					}
    			}else{
    				$data['status'] = 103;
        	        $data['msg']    = '文件格式不被允许';
    				$this->response($data,'json');exit();
    			}
    		}else{
    			$data['status'] = 105;
        	    $data['msg']    = '没有文件被上传';
    			$this->response($data,'json');exit();
    		}
		}elseif ($state == 'update') {
			$id = I('post.id');
			$cn_name =  I('post.cn_name');
			$en_name =  I('post.en_name');
			$enabled =  I('post.enabled');
			$remark = I('post.remark');
			$data['cn_name'] =  $cn_name;
			$data['en_name'] =  $en_name;
			$data['enabled'] =  $enabled;
			$data['category_id'] = $category_id;
			$data['remark']  = $remark;
			$data['modified_time'] = date('Y-m-d H:i:s',time());
			if(empty($cn_name)){
				$arr['status'] = 102;
				$arr['msg'] = "中文名称不能为空";
				$this->response($arr,'json');
				exit();
			}
			if(empty($en_name)){
				$arr['status'] = 102;
				$arr['msg'] = "英文名称不能为空";
				$this->response($arr,'json');
				exit();
			}
			if(!preg_match($this->rule_str,$en_name)){
				$arr['status'] = 102;
				$arr['msg'] = "英文名称只能是字母";
				$this->response($arr,'json');
				exit();
			}
			if(empty($status)){
				$data['status'] = 1;
			}
			if(empty($category_id)){
				$arr['status'] = 102;
				$arr['msg'] = "请选择类目";
				$this->response($arr,'json');
				exit();
			}
			$res = \Think\Product\ProductCenter::SetProductCenter($state,$data,$id);
			if($res == 1){
				$arr['status'] = 100;
			}else{
				$arr['status'] = 101;
				$arr['msg'] = "修改失败";
			}
		}else{
			$arr['status'] = 102;
			$arr['msg'] = "未定义的操作";
		}
		$this->response($arr,'json');
	}

	/*
	 * 获取产品中心产品列表接口
	 * @param category_id 类目id
	 * @param enabled 状态
	 * @param vague 模糊搜索条件
	 * @param pages 页数
	 * @param num 每页数量
	 */
	public function getallProductCenter(){
		$category_id = I('post.category_id');
		$enabled     = I('post.enabled');
		$vague       = I('post.vague');
		$pages       = I('post.pages');
		$num         = I('post.num');
        $orderBy     = I('post.orderKey');       // 排序字段
        $sort        = isset($_POST['sort']) ? $_POST['sort'] : 'desc'; // 排序方式  倒序/顺序
        $sortS       = strtolower($sort);
        if($sortS != 'desc' && $sortS != 'asc') $this->response(['status'=> 102, 'msg' => '排序方式有误']);

		if(empty($pages)){
			$pages = 1;
		}
		if(empty($num)){
			$num = 25;
		}
		$res = \Think\Product\ProductCenter::GetAllProductCenter($category_id,$enabled,$vague,$pages,$num,$orderBy,$sort);
		if($res == -1){
			$arr['status'] = 101;
			$arr['msg'] = "没有数据";
		}else{
			foreach ($res['value'] as $key => $value) {
				$res['value'][$key]['enabled'] = (int)$res['value'][$key]['enabled'];
			}
			$arr['status'] = 100;
			$arr['count'] = $res['count'];
			$arr['pages'] = $res['pages'];
			$arr['nowpages'] = $res['nowpages'];
			$arr['value'] = $res['value'];
		}
		$this->response($arr,'json'); 
	}

	/*
	 * 获取产品中心产品信息接口
	 * @param id 产品id
	 */
	public function getProductCenterInfo(){
		$id = I('post.id');
		$res = \Think\Product\ProductCenter::GetProductCenter($id);
		if($res == -1){
			$arr['status'] = 101;
			$arr['msg'] = "没有数据";
		}else{
			$arr['status'] = 100;
			$arr['value'] = $res;
		}
		$this->response($arr,'json'); 
	}

	/*
	 * 删除产品中心产品信息接口
	 * @param id 产品id
	 */
	public function delProductCenter(){
		$id = I('post.id');
		if(empty($id)){
			$arr['status'] = 102;
			$arr['msg'] = "请选择要删除的产品";
			$this->response($arr,'json');
			exit();
		}
		$res = \Think\Product\ProductCenter::DelProductCenter($id);
		if($res == 1){
			$arr['status'] = 100;
		}else{
			$arr['status'] = 101;
			$arr['msg'] = "删除失败";
		}
		$this->response($arr,'json'); 
	}

	/*
	 * 上传文件读取数据
	 */
	public function uploadProductCenter(){
		$xlsx = 'xlsx';
		$xls = 'xls';
    	if($_FILES){
    		$type = strtolower(substr(strrchr($_FILES['file']['name'], '.'), 1)); //获取文件类型
    		if($type == $xlsx || $type == $xls){
				$size = (($_FILES['file']['size'])/1024)/1024; // mb兆
				if($size < 3){
					if($type == 'xls'){
               		    $types = 'Excel5';
               		}elseif($type == 'xlsx'){
               		    $types = 'Excel2007';
               		}
					$file_name = $this->file."product_".date("Ymd-His", time()). '.' .$type;
					$array=read_excel($file_name,$types,0);
					if(empty($array)){
						$arr['status'] = 101;
						$arr['msg'] = "没有数据";
						$this->response($arr,'json'); 
						exit();
					}else{
						foreach ($array as $key => $value) {
							list($cn_name,$en_name,$enabled,$remark)=$value;
							$data[$key]['cn_name'] = $cn_name;
							$data[$key]['en_name'] = $en_name;
							$data[$key]['enabled'] = $enabled;
							$data[$key]['remark'] = $remark;
						}
					}
					unlink($file_name);
				}else{
					$arr['status'] = 104;
                    $arr['msg']    = '文件大小超负荷';
					$this->response($arr,'json');exit();
				}
    		}else{
    			$arr['status'] = 103;
                $arr['msg']    = '文件格式不被允许';
    			$this->response($arr,'json');exit();
    		}
    	}else{
    		$arr['status'] = 105;
            $arr['msg']    = '没有文件被上传';
    		$this->response($arr,'json');exit();
    	}
		$arr['status'] = 100;
		$arr['value'] = $data;
		$this->response($arr,'json'); 
	}

	/*
	 * 获取已经关联了词库的产品
	 * @param category_id 类目id
	 */
	public function getProduct2Value(){
		$category_id = I('post.category_id');
		$vague = __sqlSafe__(I('post.vague'));
		if(empty($category_id)){
			$data['status'] = 102;
            $data['msg']    = '请选择类目';
    		$this->response($data,'json');exit();
		}
		$res = \Think\Product\ProductCenter::GetProduct2Value($category_id,$vague);
		$this->response($res,'json'); 
	}
}