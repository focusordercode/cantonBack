<?php
namespace Home\Controller;
use Think\Controller;
/**
 * 产品资料模板
 * @author cxl,lrf
 * @modify 2016/12/21
 */
class TemplateController extends BaseController
{
    protected $rule_str    = "/^[a-z_A-Z()\s]+[0-9]{0,10}$/";
    protected $rule_num    = "/^[0-9]+$/";

    /*
     * 模板列表
     * @param enabled 是否启用
     * @param type_code info / batch
     * */
	public function get_template(){
		$enabled   = (int)I("post.enabled");    // 可用状态参数，默认为可用 1
		$type_code = I('post.type_code');
        $pageSize  = isset($_POST['num']) ? (int)I('post.num') : 8; // 页面大小
        $next      = isset($_POST['next']) ? (int)I('post.next') : 1; // 下一页

		if(preg_match($this->rule_num,$enabled)){
			$where = "enabled=".$enabled;
		}else{
            $where = "enabled=1";
		}
        // 判断是资料表还是批量表
		if($type_code != 'info' && $type_code != 'batch') $this->response(['status'=> 119, 'msg' => '系统错误']);
        // 调取所有模板信息所传的参数
        $all = strip_tags(trim(I("get_all_data")));
        //获取模板类型  传id的操作
        if(isset($_POST['id'])){
            $id = (int)I("id");
            $where = "id=".$id;
        }elseif($all == 'all'){
            $where .= " and status_code<>'disabled'";
        }else{
            $this->response(['status' => 102 ,'msg' => '错误请求']);
        }
        $get_tem = \Think\Product\Product_Template::get($type_code,$where,$pageSize,$next); // 查数据返回操作
        if($get_tem['error'] == 0){
            $data['status']    = 100;
            $data['value']     = $get_tem['value'];
            $data['count']     = $get_tem['count'];
            $data['countPage'] = $get_tem['countPage'];
            $data['pageNow']   = $get_tem['pageNow'];
            $data['num']       = $get_tem['num'];
        }else{
            $data['status'] = $get_tem['status'];
            $data['msg']    = $get_tem['msg'];
        }
        $this->response($data);
	}

	/*
	 * 通过类目id做二级联动
     * @param type_code  info / batch
     * @param category_id 类目id
	 */
	public function getLinkage(){
		$type_code    = I('post.type_code');
		$category_id  = (int)I('post.category_id');
		if(empty($category_id)) $this->response(['status'=> 103, 'msg' => '未选择产品类目']);
        if($type_code != 'info' && $type_code != 'batch') $this->response(['status'=> 119, 'msg' => '系统错误']);

		$res = \Think\Product\Product_Template::GetLinkage($type_code,$category_id);
		if($res){
			$data['status'] = 100;
			$data['value']  = $res;
		}else{
			$data['status'] = 101;
            $data['msg']    = '暂无相关信息';
		} 
		$this->response($data);
	}

	// 添加模板操作
    // @param cn_name 中文名
    // @param en_name 英文名
    // @param type_code info / batch
    // @param remark  模板备注
	public function add_template()
	{
		$cname     = strip_tags(trim(I("cn_name")));
		$ename     = strip_tags(trim(I("en_name")));
		$type_code = strip_tags(trim(I("type_code")));
		$remark    = strip_tags(trim(I("remark")));
        if($type_code != 'info' && $type_code != 'batch') $this->response(['status'=> 119, 'msg' => '系统错误']);

		$creator_id = I('post.creator_id'); // 创建者
        if(empty($creator_id)){
            $arr['status'] = 1012;
            $this->response($arr);
        }
		if($cname != "" && $ename != ""  && preg_match($this->rule_str,$ename)){ // 中英文、所属模板为必填字段
			$where = array(
                "cn_name"       	=> $cname,
                "en_name"       	=> $ename,
                "remark"        	=> $remark,
                "category_id"   	=> empty($_POST["category_id"]) ? 1 : I("category_id"),
                "enabled"       	=> isset($_POST["enabled"]) ? I("enabled") : 1,
                "creator_id"    	=> $creator_id,
                "created_time"  	=> date("Y-m-d H:i:s",time()),
                "modified_time" 	=> date("Y-m-d H:i:s",time()),
                "status_code"  	    => 'creating',  // 创建状态
            );
			$add_tem = \Think\Product\Product_Template::add($type_code,$where);  // 添加操作
			if($add_tem['error'] == 0){
				$data['status'] = 100;
				$data['value']  = $add_tem['value'];
			}else{
				$data['status'] = $add_tem['status'];
				$data['msg']    = $add_tem['msg'];
			}
		}else{
			$data['status'] = 102; // 参数错误
            $data['msg']    = '中英文输入有误';
		}
		$this->response($data);
	}

    // 模板编辑
    // @param cn_name 中文名
    // @param en_name 英文名
    // @param type_code info/batch
	public function edit_template()
	{
		$cname     = strip_tags(trim(I("cn_name")));
		$ename     = strip_tags(trim(I("en_name")));
		$type_code = strip_tags(trim(I("type_code")));
        if($type_code != 'info' && $type_code != 'batch') $this->response(['status'=> 119, 'msg' => '系统错误']);

		if($cname != "" && $ename != ""  && preg_match($this->rule_str,$ename)){ // 中英文、所属模板为必填字段
			$where = array(
                "cn_name"       	=> $cname,
                "en_name"       	=> $ename,
                "remark"        	=> strip_tags(trim(I("remark"))),
                "category_id"   	=> empty($_POST["category_id"]) ? 1 : I("category_id"),
                "enabled"       	=> isset($_POST["enabled"]) ? I("enabled") : 1,
                "modified_time" 	=> date("Y-m-d H:i:s",time()),
            );
			$id = (int)I("id");
			if(!empty($id) && preg_match($this->rule_num,$id)){   // 修改操作 id 字段的正则以及非空验证
				$edit_tem = \Think\Product\Product_Template::edit($type_code,$id,$where);  // 修改的具体操作
				if($edit_tem['error'] == 0){
					$data['status'] = 100;
					$data['value']  = $edit_tem['value'];
				}else{
					$data['status'] = $edit_tem['status'];
					$data['msg']    = $edit_tem['msg'];
				}
			}else{
				$data['status'] = 102; // 参数错误 id为空
                $data['msg']    = '未选择模板';
			}
		}else{
			$data['status'] = 102; // 参数错误
            $data['msg']    = '中英文输入有误';
		}
		$this->response($data);
	}

    // 模板删除
    // @param type_code info/batch
    // @param id 模板id
	public function del_template(){  // 删除
		$type_code = I("type_code");
        if($type_code != 'info' && $type_code != 'batch') $this->response(['status'=> 119, 'msg' => '系统错误']);
        // id去空去标签
		$id = (int)I("id");
		if(!empty($id) && preg_match($this->rule_num,$id)){  // id验证
			$del_tem = \Think\Product\Product_Template::del($type_code,$id);     // 删除操作
			if($del_tem['error'] == 0){
				$data['status'] = 100;
				$data['value'] = $del_tem['value'];
			}else{
				$data['status'] = $del_tem['status'];
				$data['msg']    = $del_tem['msg'];
			}
		}else{
			$data['status'] = 102; // 参数错误 id为空
            $data['msg']    = '未选择模板';
		}
		$this->response($data);
	}

	// 	启用模板
    // @param type_code info/batch
    // @param id 模板id
	public function use_template()    // 启用模板
	{
		$type_code = I("type_code");
        if($type_code != 'info' && $type_code != 'batch') $this->response(['status'=> 119, 'msg' => '系统错误']);

		$id = (int)I("id");
		if(!empty($id) && preg_match($this->rule_num,$id)){   //  验证id的操作
			$use_tem = \Think\Product\Product_Template::use_tem($type_code,$id);  
			$data['status'] = $use_tem['status'];	//  105 已经启用状态		
			$data['msg']    = $use_tem['msg'];	//  105 已经启用状态
		}else{
			$data['status'] = 102; // 参数错误 id为空
            $data['msg']    = '未选择模板';
		}
		$this->response($data);
	}

    // 停用模板
    // @param type_code info/batch
    // @param id 模板id
	public function stop_template(){
		$type_code = I("type_code");
        if($type_code != 'info' && $type_code != 'batch') $this->response(['status'=> 119, 'msg' => '系统错误']);
		$id = (int)I("id");
		if(!empty($id) && preg_match($this->rule_num,$id)){  // id验证
			$stop_tem = \Think\Product\Product_Template::stop_tem($type_code,$id);
			$data['status'] = $stop_tem['status']; // 停用成功
			$data['msg']    = $stop_tem['msg']; // 停用成功
		}else{
			$data['status'] = 102; // 参数错误 id为空
            $data['msg']    = '未选择模板';
		}
		$this->response($data);
	}

	// id获取模板
    // @param type_code info/batch
    // @param id 模板id
	public function get_template_by_id(){  
		$type_code = I("post.type_code");
        if($type_code != 'info' && $type_code != 'batch') $this->response(['status'=> 119, 'msg' => '系统错误']);
		$id = (int)I("id");
		if(!empty($id) && preg_match($this->rule_num,$id)){  // id验证
			if($type_code=='info'){
				$tem = M("product_template");
			}elseif ($type_code=='batch') {
				$tem = M("product_batch_template");
			}
			$arr = $tem->where("id=$id")->select();
			if($arr){
				$data['status'] = 100;
				$data['value'] = $arr;
			}else{
				$data['status'] = 101;
                $data['msg']    = '暂无相关数据';
			}
		}else{
			$data['status'] = 102; // 参数错误 id为空
            $data['msg']    = '未选择模板';
		}
		$this->response($data);
	}

	/*
	 * 模糊搜索模板
	 * @param type_code info/batch
	 * @param name 搜索关键词
	 * @param status_code 状态码
	 */
	public function vagueName(){
		$type_code = I("type_code");
		$pageSize  = isset($_POST['num']) ? (int)I('post.num') : 8; // 页面大小
        if($type_code != 'info' && $type_code != 'batch') $this->response(['status'=> 119, 'msg' => '系统错误']);
		$text = __sqlSafe__(I('post.name'));
        $status_code = __sqlSafe__(I("post.status_code"));

        $arr = \Think\Product\Product_Template::VagueTemName($type_code,$text,$status_code,$pageSize);
        if($arr['error'] == 0){
            $data['status']    = 100;
            $data['value']     = $arr['value'];
            $data['count']     = $arr['count'];
            $data['countPage'] = $arr['countPage'];
            $data['pageNow']   = $arr['pageNow'];
        }else{
            $data['status'] = $arr['status'];
            $data['msg']    = $arr['msg'];
        }
        $this->response($data);

	}

    // 获取某个类目下所有模板包括通用模板
    // @param type_code info/batch
    // @param category_id 类目id
    public function get_template_by_category()
    {
        $type_code    = I('post.type_code');
        $category_id  = (int)I('post.category_id');
        if(empty($category_id)){
            $res['status'] = 102;
            $res['msg']    = '未选择产品类目';
            $this->response($res);
            exit();
        }
        if($type_code == 'info'){          // 判断是需要·哪个表数据
            $tem = M("product_template");
        }elseif($type_code == 'batch') {
            $tem = M("product_batch_template");
        }else{
            $res['status'] = 102;
            $res['msg']    = '系统错误';
            $this->response($res);
        }

        if($category_id == 1){
            $query  = $tem->where(array("category_id" => 1 , "status_code" => "enabled"))->select();             // 通用模板
        }else{
            $result = $tem->where(array("category_id" => $category_id , "status_code" => "enabled"))->select();  // 启用状态模板
            $query  = $tem->where(array("category_id" => 1 , "status_code" => "enabled"))->select();             // 通用模板
            if($query){
                foreach($query as $k => $v){
                    $query[$k]['category_name'] = '通用模板';
                }
            }
        }
        if($result){
            // 差类目合并类目名
            foreach($result as $key => $value){
                $l = M('product_category')->field('cn_name')->find($value['category_id']);
                $result[$key]['category_name'] = $l['cn_name'];
            }
            if(!empty($query)){
                $result1 = array_merge($result,$query);       // 数组合并，返回通用模板和所选类目模板
            }else{
                $result1 = $result;
            }
            $res['status'] = 100;
            $res['value']  = $result1;
        }else{
            if(!empty($query)){
                $res['status'] = 100;
                $res['value']  = $query;
            }else{
                $res['status'] = 101;
                $res['msg']    = '暂无相关信息';
            }
        }
        $this->response($res);
    }

    //获取模板信息可模糊搜索
    // @param type_code info/batch
    public function getitemValue(){
    	$enabled   = I("post.enabled");    // 可用状态参数，默认为可用 1
		$type_code = I('post.type_code');
        $num       = isset($_POST['num']) ? (int)I('post.num') : 8;
        $next      = isset($_POST['next']) ? (int)I('post.next') : 1;
        $vague     = __sqlSafe__(I('post.vague'));
        $category_id = (int)I('post.category_id');

		if($type_code != 'info' && $type_code != 'batch') $this->response(['status'=> 119, 'msg' => '系统错误']);
		$res = \Think\Product\Product_Template::GetItemValue($type_code,$enabled,$num,$next,$vague,$category_id);
		$this->response($res);
    }

}