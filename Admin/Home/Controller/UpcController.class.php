<?php
namespace Home\Controller;
use Think\Controller;
/**
 * upc
 * @author cxl,lrf
 * @modify 2016/12/21
 */
class UpcController extends BaseController
{
	protected $dir = "./Public/upc/";
    protected $rule_time = "/^\d{4}-\d{2}-\d{2}(\s)\d{2}:\d{2}:\d{2}$/s";

    /*
     * 读取上传upc文件
     * */
    public function read_upc_file ($file) {  // 读取文件并录入
        set_time_limit(0);
    	$u = M('product_upc_code');
    	$u->startTrans();
    	$error = 0;
    	$upc  = file_get_contents($file);
    	$upcs = explode("\n", $upc);
    	$data = array(
    		'enabled'         => 1,
    		'locked'          => 0,
    		'operation_id'    => isset($_SESSION['user_id']) ? session('user_id') : 0,
    		'operation_time'  => date('Y-m-d H:i:s',time()),
    		);
    	$same_upc = 0;  // 已经存在的upc
    	$inserted = 0;  // 录入成功的upc
    	foreach ($upcs as $key => $value) {
    		if(preg_match("/^\d{12,13}\b/", trim($value))){
                $v = trim($value);
	    		$data['upc_code'] = $v;
	    		$isset = $u->where("upc_code='$v'")->find(); // 查询upc是否已经存在
	    		if($isset){
	    			$same_upc += 1;
	    			continue;
	    		}
	    		$insert = $u->add($data);  // 添加
	    		if($insert){
	    			$inserted += 1;
	    		}
    		}else{
    			$error = 1;break;
    		}
    	}
    	if($error == 1){
    		unlink($file);
    		$u->rollback();
    		$data['status'] = 105; // upc非法
            $data['msg']    = 'UPC非法';
			$this->response($data,'json');exit;
    	}else{
    		$u->commit();
	    	$data['status'] = 100;
	    	$data['value'] = array(
	    		'same_upc' => $same_upc,
	    		'inserted' => $inserted
	    		);
			$this->response($data,'json');
    	}
    }

    /*
     * upc文件上传  txt文本文档类型
     * 每行一条upc  12-13个数字 已正则校验 否则不能匹配
     * */
    public function upload_upc_file()
    {
        set_time_limit(0);
    	$extension = 'txt';
    	if($_FILES){
    		$type = strtolower(substr(strrchr($_FILES['file']['name'], '.'), 1)); //获取文件类型
    		if($type == $extension){
				$size = (($_FILES['file']['size'])/1024)/1024; // mb兆
				if($size < 5){
					$upc_file_name = $this->dir."upc_".date("Ymd-His", time()). '.' .$extension;
					if(move_uploaded_file($_FILES['file']['tmp_name'], $upc_file_name)){
						$this->read_upc_file($upc_file_name);     // 上传成功读取录入upc
					}
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
    		$data['status'] = 102;
            $data['msg']    = '没有文件被上传';
    		$this->response($data,'json');exit();
    	}
    }

    /*
     * 拉取upc列表
     * @param pageNum 页码
     * @param locked  锁定
     * @param enabeld 有无效
     * @param pageSize 页面尺寸
     */
    public function get_upc_list()
    {
    	$nowPage = isset($_POST['pageNum']) ? (int)I("post.pageNum") : 1;
    	if(preg_match("/^[0-9]+$/", $nowPage)){

            if(isset($_POST['locked'])){
                $where = "locked = ".I("post.locked");
            }else{
                $where = "locked = 0 ";
            }

    		if(isset($_POST['enabled'])){
    			$where .= " and enabled = ".I("enabled")." ";
    		}

    		$pageSize = strip_tags(trim(I("pageSize")));
    		if(!empty($pageSize)){
    			if(preg_match("/^[0-9]+$/", $pageSize)){
    				$pa = $pageSize;
    			}
    		}else{
    			$pa = 25;
    		}
            $where = __sqlSafe__($where);
            if(isset($_POST['start_time']) && isset($_POST['end_time'])){
                $s_time = strip_tags(trim(I("start_time")));
                $e_time = strip_tags(trim(I("end_time")));
                if(preg_match($this->rule_time,$s_time) && preg_match($this->rule_time,$e_time)){
                    $where .= " and operation_time between '$s_time' and '$e_time'";
                }else{
                    $data['status'] = 103;  // 时间参数错误
                    $data['msg']    = '时间参数错误';
                    $this->response($data,'json');exit();
                }
            }
            // 配置完成读取条件读取
    		$get_upc = \Think\Product\Upc::get_upc($where,$nowPage,$pa);
    		if($get_upc['error'] == 0){
    			$data['status'] = 100;
    			$data['value'] = $get_upc['value'];
                $data['count'] = $get_upc['count'];
                $data['allupc'] = $get_upc['allupc'];
                $data['usedupc'] = $get_upc['usedupc'];
                $data['pageNow'] = $get_upc['pageNow'];
                $data['upc'] = $get_upc['upc'];
                $data['lockedupc'] = $get_upc['lockedupc'];
    		}else{
    			$data['status'] = $get_upc['status'];
    			$data['msg']    = $get_upc['msg'];
    		}
    	}else{
    		$data['status'] = 102;
            $data['msg']    = '页码有误';
    	}
    	$this->response($get_upc,'json');exit();
    }

    // 匹配UPC
    // @param form_id 表格id 自动匹配
    public function marry_upc(){
        $form_id = I('form_id');

        if(!preg_match("/^[0-9]+$/",$form_id)){
            $returnArr['status'] = 102;
            $returnArr['msg']    = '未选则表格数据';
            $this->response($returnArr,'json');exit;
        }

        $result = \Think\Product\Upc::marry_upcs($form_id);
        if($result['error'] == 0){
            $returnArr['status'] = 100;
        }else{
            $returnArr['status'] = $result['status'];
            $returnArr['msg']    = $result['msg'];
        }
        $this->response($returnArr , 'json');
    }

}