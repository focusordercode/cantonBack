<?php
namespace Home\Controller;
use Think\Controller\RestController;
header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Methods:POST,GET');
header('Access-Control-Allow-Credentials:true'); 
header("Content-Type: application/json;charset=utf-8");

/**
* 产品资料表格控制器
*/
class ProductInfoFormController extends RestController{

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


	/*
	 * 获取同一个类目下的产品资料表格
	 */
	public function getInfoForm()
	{
		$category_id = I('post.category_id');
		$status_code = I('post.status_code');
		$type_code   = I('post.type_code');
        $pageSize    = isset($_POST['num']) ? (int)I('post.num') : 15;
        $next        = isset($_POST['next']) ? (int)I('post.next') : 1;
		if(empty($type_code)){
			$data['status'] = 119;
            $data['msg']    = '系统错误';
			$this->response($data,'json');
			exit();
		}
		$res = \Think\Product\ProductInfoForm::GetInfoForm($type_code,$status_code,$category_id,$pageSize,$next);
		if($res){
			$data['status']    = 100;
			$data['value']     = $res['value'];
            $data['count']     = $res['count'];
            $data['countPage'] = $res['countPage'];
            $data['pageNow']   = $res['pageNow'];
		}else{
			$data['status'] = 101;
            $data['msg']    = '暂无相关信息';
		}
		$this->response($data,'json');
	}

	/*
	 * 获取同一个模板下的产品资料表格
	 */
	public function getTemInfoForm()
	{
		$template_id = I('post.template_id');
		$status_code = I('post.status_code');
		$type_code   = I('post.type_code');
		if(empty($type_code)){
			$data['status'] = 119;
            $data['msg']    = '系统错误';
			$this->response($data,'json');
			exit();
		}
		$res = \Think\Product\ProductInfoForm::GetTempInfoForm($type_code,$status_code,$template_id);
		if($res){
			$data['status'] = 100;
			$data['value']  = $res;
		}else{
			$data['status'] = 101;
            $data['msg']    = '暂无相关信息';
		}
		$this->response($data,'json');
	}

	/*
	 * 根据id获取产品资料表格
	 */
	public function getOneForm()
	{
		$id = I('post.id');
		$type_code = I('post.type_code');

		if(empty($type_code)){
			$data['status'] = 119;
            $data['msg']    = '系统错误';
			$this->response($data,'json');
			exit();
		}
		if(empty($id)){
			$data['status'] = 102;
            $data['msg']    = '未选择表格信息';
		}else{
			$res=\Think\Product\ProductInfoForm::GetOneForm($type_code,$id);
			if($res){
				$data['status'] = 100;
				$data['value']  = $res;
			}else{
				$data['status'] = 101;
                $data['msg']    = '暂无相关信息';
			}			
		}

		$this->response($data,'json');
	}

	/*
	 * 获取同一个类目下同一个模板的产品资料表格
	 */
	public function getCTInfoForm()
	{
		$category_id = I('post.category_id');
		$template_id = I('post.template_id');
		$type_code   = I('post.type_code');
		if(empty($type_code)){
			$data['status'] = 119;
            $data['msg']    = '系统错误';
			$this->response($data,'json');
			exit();
		}
		if(empty($category_id)){
			$data['status'] = 103;
            $data['msg']    = '未选择产品类目';
		}elseif(empty($template_id)){
			$res = \Think\Product\ProductInfoForm::GetInfoForm($type_code,$category_id);
			if($res){
				$data['status'] = 100;
				$data['value']  = $res;
			}else{
				$data['status'] = 101;
                $data['msg']    = '暂无相关信息';
			}
		}else{
			$res = \Think\Product\ProductInfoForm::GetCTForm($type_code,$category_id,$template_id);
			if($res){
				$data['status'] = 100;
				$data['value']  = $res;
			}else{
				$data['status'] = 101;
                $data['msg']    = '暂无相关信息';
			}
		}
		$this->response($data,'json');
	}

	/*
	 * 模糊搜索表格
	 */
	public function vagueTitle(){
		$title     = I("post.title");
		$type_code = I('post.type_code');
		if(empty($type_code)){
			$data['status'] = 119;
            $data['msg']    = '系统错误';
			$this->response($data,'json');
			exit();
		}
		if(empty($title)){
			$data['status'] = 105;
            $data['msg']    = '标题为必填';
		}else{
			$res = \Think\Product\ProductInfoForm::VagueTitle($type_code,$title);
			if($res){
				$data['status'] = 100;
				$data['value']  = $res;
			}else{
				$data['status'] = 101;
                $data['msg']    = '暂无相关信息';
			}			
		}

		$this->response($data,'json');
	}


	/*
	 * 创建产品资料表格
	 */
	public function addInfoForm(){
		$array     = array();
		$type_code = I('post.type_code');
		if(empty($type_code)){
			$data['status'] = 119;
            $data['msg']    = '系统错误';
			$this->response($data,'json');
			exit();
		}
        $creator_id = I('post.creator_id');
        if(empty($creator_id)){
            $arr['status'] = 1012;
            $this->response($arr,'json');
            exit();
        }
		$data['category_id']    = I('post.category_id');
		$data['template_id']    = I('post.template_id');
		$data['client_id']      = I('post.client_id');
		$data['title']          = I('post.title');
		$data['form_no']        = I('post.form_no');
		$data['enabled']        = 1;
		$data['status_code']    = "creating";
		$data['creator_id']     = $creator_id;
		$product_count          = (int)I('product_num');
		$data['created_time']   = date('Y-m-d H:i:s',time());
		$data['modified_time']  = date('Y-m-d H:i:s',time());
		if(empty($data['category_id']) || !preg_match("/^[0-9]*$/",intval($data['category_id']))){
			$array['status'] = 103;
            $array['msg']    = '未选择产品类目';
			$this->response($array,'json');
			exit();
		}elseif(empty($data['template_id']) || !preg_match("/^[0-9]*$/",intval($data['template_id']))){
			$array['status_id'] = 104;
            $array['msg']       = '未选择模板';
			$this->response($array,'json');
			exit();
		}elseif(empty($data['title'])){
			$array['status'] = 105;
            $array['msg']    = '标题为必填';
			$this->response($array,'json');
			exit();
		}
		if(empty($data['client_id'])){
			$data['client_id'] = 1;
		}
        if(empty($data['form_no'])){
            $array['status'] = 102; // 编号不能为空
            $array['msg']    = '编号为必填';
            $this->response($array,'json');
            exit();
        }

        $data['id'] = (int)substr($data['form_no'],8);
        if ($type_code=='batch') {
            $data['site_name']      = I('post.site_name');
            $data['product_form_id']= I('post.product_form_id');
            $data['file_name'] = I('post.file_name');
            if(empty($data['site_name'])){
                $array['status'] = 106;
                $array['msg']    = '亚马逊站点为必填';
                $this->response($array,'json');
                exit();
            }
            if(empty($data['product_form_id'])){
                $array['status'] = 107;
                $array['msg']    = '资料表为必选';
                $this->response($array,'json');
                exit();
            }
            $data['id'] = (int)substr($data['form_no'],5);
            // 查资料表编号 -> 通过编号取缓存的表格数据
            $form_no = M('product_form')->where('id = %d',[$data['product_form_id']])->find();
            $Zt = S($form_no['form_no']);
        }else{
            $s['product_count'] = I('post.product_count');
            $s['variant_num'] = I('post.variant_num');
            S($data['form_no'],$s);
        }
		$res = \Think\Product\ProductInfoForm::AddInfoForm($type_code,$data);
		if($res){
			$array['status'] = 100;
			$array['title'] = $data['title'];
			$array['id']    = $data['id'];

             if($type_code == 'batch'){
                 // 所需要的主题数量
                 $this->get_product_msg($data['id'],$data['product_form_id'],$product_count , $Zt['variant_num'],$creator_id);
             }
		}else{
			$array['status'] = 101;
            $array['msg']    = '暂无相关信息';
		}
		$this->response($array,'json');
	}

    /*
     * 获取关联资料表的数据
     */
    public function get_product_msg($form_id,$product_form_id,$product_count = '', $vnum,$creator_id){
        set_time_limit(0);

        $batch_form  = M('product_batch_form');
        $form        = M('product_form');
        $item        = M('product_item_template');
        $batch_item  = M('product_batch_item_template');
        $item2batch  = M('product_item2batch_item');

        //获取资料表与批量表的模板id
        $batch_tel_id = $batch_form->field("template_id,category_id")->where("id=%d",array($form_id))->find();
        $tel_id       = $form->field("template_id")->where("id=%d",array($product_form_id))->find();

        //获取资料表与批量表的关联关系
        $array = $item2batch->field("title1_id,title2_id")->where("template1_id=%d and template2_id=%d",array($tel_id['template_id'],$batch_tel_id['template_id']))->select();
        foreach ($array as $key => $value) {
            $items  = $item->field("en_name")->where("id=%d",array($value['title1_id']))->find();
            $bitems = $batch_item->field("en_name")->where("id=%d",array($value['title2_id']))->find();
            $bitem[$bitems['en_name']] = $items['en_name'];
        }

        $info = \Think\Product\ProductInfo::GetOneFormInfo('info',$product_form_id);
        // 只拿相对应的产品数量
        if(!empty($product_count)){
        	if($vnum == 0 || empty($vnum)){
        		$vnum = 1;
        	}
            $parentnum = ceil($product_count / $vnum);
            $all_product = $product_count;
        }else{
            $all_product = count($info);
            $parentnum = ceil($all_product / ($vnum + 1));
        }

        $p = 0;
        foreach ($info as $k => $va) {
            if($p == $parentnum){
                break;
            }
            if($va['parent_id'] == 0){
                $zhuti[] = $va;
                $p ++;
            }
        }
		if(empty($product_count)){
            $all_product = $all_product - count($zhuti);
        }
        $bproduct_id = GetSysId('product_batch_information',$parentnum);
        $bie  = \Think\Product\Product_Item_Template::get('batch',$batch_tel_id['template_id']);
        $bienum = count($bie['value']);
        $nums = $parentnum * $bienum;
        $brecord_id = GetSysId('product_batch_information_record',$nums);
        $z = 0;
        // $bianti = $all_product - $parentnum;
        $btproduct_id = GetSysId('product_batch_information',$all_product);
        $biantinums   = $all_product * $bienum;
        $btrecord_id  = GetSysId('product_batch_information_record',$biantinums);
        $j = 0;
        $u = 0;
        $now = $bie['value'];
        for ($i = 0; $i < $parentnum; $i ++) {
            foreach ($now as $keys => $values) {
                $data['id']          = $brecord_id[$z];
                $data['product_id']  = $bproduct_id[$i];
                $data['category_id'] = $batch_tel_id['category_id'];
                $data['template_id'] = $batch_tel_id['template_id'];
                $data['parent_id']   = 0;
                $data['no']          = $now[$keys]['no'];
                $data['title']       = $now[$keys]['en_name'];
                $data['length']      = $now[$keys]['length'];
                $data['data_type_code'] = $now[$keys]['data_type_code'];
                if(!empty($bitem[$data['title']])){
                    switch ($data['data_type_code']) {
                        case 'int':  $data['interger_value'] = $zhuti[$i][$bitem[$data['title']]]; break;
                        case 'upc_code':  $data['interger_value'] = $zhuti[$i][$bitem[$data['title']]]; break;
                        case 'char': $data['char_value']     = $zhuti[$i][$bitem[$data['title']]]; break;
                        case 'dc':   $data['decimal_value']  = $zhuti[$i][$bitem[$data['title']]]; break;
                        case 'dt':   $data['date_value']     = $zhuti[$i][$bitem[$data['title']]]; break;
                        case 'bl':   $data['boolean_value']  = $zhuti[$i][$bitem[$data['title']]]; break;
                        case 'pic':  $data['char_value']     = $zhuti[$i][$bitem[$data['title']]]; break;
                    }
                }
                $data['enabled']    = 1;
                $data['creator_id'] = $creator_id;
                $data['created_time']  = date('Y-m_d H:i:s',time());
                $data['modified_time'] = date('Y-m_d H:i:s',time());
                $datas[] = $data;
                $data    = array();
                $z ++;
            }
            $a    = $zhuti[$i]['product_id'];
            $pid[] = $bproduct_id[$i];

            foreach ($info as $ks => $vas) {
                if($vas['parent_id'] == $a){
                    foreach ($now as $kes => $vs) {
                        $data['id']          = $btrecord_id[$u];
                        $data['product_id']  = $btproduct_id[$j];
                        $data['category_id'] = $batch_tel_id['category_id'];
                        $data['template_id'] = $batch_tel_id['template_id'];
                        $data['parent_id']   = $bproduct_id[$i];
                        $data['no']          = $now[$kes]['no'];
                        $data['title']       = $now[$kes]['en_name'];
                        $data['length']      = $now[$kes]['length'];
                        $data['data_type_code'] = $now[$kes]['data_type_code'];
                        if(!empty($bitem[$data['title']])){
                            switch ($data['data_type_code']) {
                                case 'int':
                                case 'upc_code':
                                    $data['interger_value'] = $info[$ks][$bitem[$data['title']]];
                                    break;
                                case 'char':
                                    $data['char_value']   = $info[$ks][$bitem[$data['title']]];
                                    break;
                                case 'dc':
                                    $data['decimal_value'] = $info[$ks][$bitem[$data['title']]];
                                    break;
                                case 'dt':
                                    $data['date_value']    = $info[$ks][$bitem[$data['title']]];
                                    break;
                                case 'bl':
                                    $data['boolean_value'] = $info[$ks][$bitem[$data['title']]];
                                    break;
                                case 'pic':
                                    $data['char_value']    = $info[$ks][$bitem[$data['title']]];
                                    break;
                            }
                        }
                        $data['enabled']       = 1;
                        $data['creator_id']    = $creator_id;
                        $data['created_time']  = date('Y-m_d H:i:s',time());
                        $data['modified_time'] = date('Y-m_d H:i:s',time());
                        $datas[] = $data;
                        $data    = array();
                        $u++;
                    }
                    $pid[] = $btproduct_id[$j];
                    $j++;
                }

            }
        }

        \Think\Product\ProductInfo::AddProductInfo('batch',$datas,$form_id,$pid);
    }


	/*
	 * 修改表格名称
	 */
	public function updaInfoForm(){

		$type_code = I('post.type_code');
		if(empty($type_code)){
			$data['status'] = 119;
            $data['msg']    = '系统错误';
			$this->response($data,'json');
			exit();
		}

		$id                    = I('post.id');
		$data['category_id']   = I('post.category_id');
		$data['template_id']   = I('post.template_id');
		$data['client_id']     = isset($_COOKIE["user_id"]) ? cookie("user_id") : 0;
		$data['title']         = I('post.title');
		$data['modified_time'] = date('Y-m-d H:i:s',time());
		$data['site_name']     = I('post.site_name');
		if(empty($id) || !preg_match("/^[0-9]*$/",$id)){//判断id是否为空或者id是否为数字
			$array['status'] = 102;
            $data['msg']    = '未选择表格';
			$this->response($array,'json');
			exit();
		}
		if($type_code == 'batch'){
			if(empty($data['site_name'])){
				$array['status'] = 102;
        	    $data['msg']    = '请选择站点';
				$this->response($array,'json');
				exit();
			}
		}
		
		if(empty($data['category_id']) || !preg_match("/^[0-9]*$/",$data['category_id'])){//判断类目id是否为空或者id是否为数字
			$array['status'] = 103;
            $array['msg']    = '未选择产品类目';
			$this->response($array,'json');
			exit();
		}elseif(empty($data['template_id']) || !preg_match("/^[0-9]*$/",$data['template_id'])){//判断模板id是否为空或者id是否为数字
			$array['status'] = 104;
            $array['msg']    = '未选择模板';
			$this->response($array,'json');
			exit();
		}elseif(empty($data['client_id']) || !preg_match("/^[0-9]*$/",$data['template_id'])){//判断客户id是否为空或者id是否为数字
			$data['client_id'] = 1;
		}
		if(empty($data['title'])){//判断表单名称是否为空
			$array['status'] = 105;
            $array['msg']    = '表格为必填';
			$this->response($array,'json');
			exit();
		}
		$res = \Think\Product\ProductInfoForm::UpdateInfoForm($type_code,$id,$data);
		if($res == 2){
			$array['status'] = 108;
            $array['msg']    = '该状态下不能操作';
		}elseif($res == 1){
			$array['status'] = 100;
		}else{
			$array['status'] = 101;
            $array['msg']    = '更新失败';
		}			
		$this->response($array,'json');
	}

	/*
	 * 删除表格
	 */
	public function delInfoForm(){
		$id        = I("post.id");
		$type_code = I('post.type_code');
		if(empty($type_code)){
			$data['status'] = 119;
            $data['msg']    = '系统错误';
			$this->response($data,'json');
			exit();
		}
		if(empty($id)){
			$data['status'] = 102;
            $data['msg']    = '未选择表格';
		}else{
			$res = \Think\Product\ProductInfoForm::DelInfoForm($type_code,$id);
			if($res == 1){
				$data['status'] = 100;
			}elseif($res == 2){
				$data['status'] = 108;
                $data['msg']    = '该状态下不能操作';
			}elseif($res == 3){
				$data['status'] = 108;
                $data['msg']    = '该表格下有关联数据';
			}else{
				$data['status'] = 101;
                $data['msg']    = '删除失败';
			}			
		}
		$this->response($data,'json');
	}

	/*
	 * 停用表格
	 */
	public function stopInfoForm(){
		$id        = I('post.id');
		$type_code = I('post.type_code');
		if(empty($type_code)){
			$data['status'] = 119;
            $data['msg']    = '系统错误';
			$this->response($data,'json');
			exit();
		}
		$res = \Think\Product\ProductInfoForm::StopInfoForm($type_code,$id);
		if(empty($id)){
			$data['status'] = 102;
            $data['msg']    = '未选择表格';
		}else{
			if($res == 1){
				$data['status'] = 100;
			}elseif($res == 2){
				$data['status'] = 109;
                $data['msg']    = '该状态下不能操作';
			}else{
				$data['status'] = 101;
                $data['msg']    = '操作失败';
			}
		}
		$this->response($data,'json');
	}

	/*
	 * 启用表格
	 */
	public function useInfoForm(){
		$id        = I('post.id');
		$type_code = I('post.type_code');
		if(empty($type_code)){
			$data['status'] = 119;
            $data['msg']    = '系统错误';
			$this->response($data,'json');
			exit();
		}
		if(empty($id)){
			$data['status'] = 102;
            $data['msg']    = '未选择表格';
		}else{
			$res = \Think\Product\ProductInfoForm::UseInfoForm($type_code,$id);
			if($res == 2){
				$data['status'] = 109;
                $data['msg']    = '该状态下不能操作';
			}elseif($res == 1){
				$data['status'] = 100;
			}else{
				$data['status'] = 101;
                $data['msg']    = '启用失败';
			}
		}
		$this->response($data,'json');
	}


    /*
     * 搜索资料表批量表
     *
     * */
    public function search_form(){
        $type_code    = I('type_code');
        $status_code  = I('status_code');
        $keyword      = strip_tags(trim(I('keyword')));
        $category_id  = I('post.category_id');
        $pageSize     = isset($_POST['num']) ? (int)$_POST['num'] : 20;
        $next         = isset($_POST['next']) ? (int)$_POST['next'] : 1;

        if(!preg_match("/^[0-9]+$/",$pageSize) || !preg_match("/^[0-9]+$/",$next)){
            $data['status'] = 102;
            $data['msg']    = '分页数据错误';
            $this->response($data , 'json');exit();
        }

        if(empty($type_code)){      // 确定是选的资料表还是批量表，两个不能同时混合出现
            $data['status'] = 102;
            $data['msg']    = 'type_code不能为空';
            $this->response($data , 'json');exit();
        }else if($type_code != 'info' && $type_code != 'batch'){  // 容错 || 恶意调用
            $type_code = 'info';
        }

        if(!empty($status_code) && !preg_match("/^[a-z0-9]+$/" , $status_code)){
            $data['status'] = 103;
            $data['msg']    = '表格状态错误';
            $this->response($data , 'json');exit();
        }

        $result = \Think\Product\ProductInfo::search_form($type_code,$status_code,$keyword,$category_id,$pageSize,$next);
        if($result['error'] == 0){
            $data['status']    = 100;
            $data['value']     = $result['value'];
            $data['count']     = $result['count'];
            $data['pageNow']   = $result['pageNow'];
            $data['countPage'] = $result['countPage'];
        }else{
            $data['status'] = $result['status'];
            $data['msg']    = $result['msg'];
        }
        $this->response($data , 'json');
    }



}