<?php
namespace Home\Controller;
use Think\Controller;
/**
* 获取日志控制器
* @author lrf
* @modify 2016/12/22
*/
class LoggingController extends BaseController
{
	/*
	 * 获取当前月份日志
	 */
	public function getNowLog(){
		$d  = date('Y',time());
        $m  = date('m',time());
        $di = $d. '/'.$m.'/';
        $value = read_file($di);
        foreach ($value as $key => $values) {
        	$value[$key]['url'] = 'http://'.$_SERVER["HTTP_HOST"].'/'.strstr(C('LOG_PATH'),substr(__ROOT__,1)).$values['url'];
        }
        if(empty($value)){
        	$arr['status'] = 101;
        	$arr['msg'] = "没有数据！";
        }else{
        	$arr['status'] = 100;
        	$arr['value'] = $value;
        }
        $this->response($arr,'json');
	}

	/*
	 * 获取某年某月某日的所有日志
	 * @param year 想要搜索的年份
	 * @param month 想要搜索的月份
	 * @param day 想要搜索的日子
	 */
	public function getFixLog(){
		$year=I('post.year');
		$month=I('post.month');
		$day=I('post.day');
		if(empty($year)){
			$arr['status']=102;
			$arr['msg']="请选择年份！";
			$this->response($arr,'json');
		}
		if(empty($month)){
			$arr['status']=102;
			$arr['msg']="请选择月份！";
			$this->response($arr,'json');
		}
		$di=$year.'/'.$month.'/';
		$value=read_file($di);//读取搜索的年份月份下的所有文件
		if(empty($value)){
        	$arr['status']=101;
        	$arr['msg']="没有数据！";
        }else{
        	if(empty($day)){
        		$arr['status']=100;
        		foreach ($value as $key => $values) {
        			$value[$key]['url'] = 'http://'.$_SERVER["HTTP_HOST"].'/'.strstr(C('LOG_PATH'),substr(__ROOT__,1)).$values['url'];
        		}
        		$arr['value']=$value;
			}else{
				$i=0;
				$char=substr($year,2).'_'.$month.'_'.$day;
				//筛选想要搜索的日子的文件
				foreach ($value as $keys => $data) {
					if(strpos($data['name'], $char)!==false){
						$array[$i]['name']=$data['name'];
						$array[$i]['url']=$data['url'];
						$i++;
					}
				}
				foreach ($array as $k => $val) {
        			$array[$k]['url'] = 'http://'.$_SERVER["HTTP_HOST"].'/'.strstr(C('LOG_PATH'),substr(__ROOT__,1)).$val['url'];
        		}
				$arr['status']=100;
       			$arr['value']=$array;
			}     
        }
        $this->response($arr,'json');
	}

	/*
	 * 删除日志
	 * @param url 日志地址
	 */
	public function delLog(){
		$url=I('post.url');
		$success=0;
		$fail=0;
		if(is_array($url)){
			foreach ($url as $key => $u) {
				$url_data = str_replace('http://'.$_SERVER["HTTP_HOST"].'/'.strstr(C('LOG_PATH'),substr(__ROOT__,1)),'',$u);
				if(unlink(C('LOG_PATH').$url_data)){
  					$success++;
				}else{
					$fail++;
				}
			}
		}else{
			$url_data = str_replace('http://'.$_SERVER["HTTP_HOST"].'/'.strstr(C('LOG_PATH'),substr(__ROOT__,1)),'',$url);
			if(unlink(C('LOG_PATH').$url_data)){
  				$success++;
			}else{
				$fail++;
			}
		}
		$arr['status']=100;
		$arr['success']=$success;
		$arr['fail']=$fail;
		$this->response($arr,'json');
	}

	/*
	 * 下载日志文件
	 * @param url 日志地址
	 */
	public function downloadLog(){
		import('ORG.Util.FileToZip');//引入zip下载类文件FileToZip
		$url=I('url');
		if(is_array($url)){
			foreach ($url as $key => $value) {
				$url_data = str_replace('http://'.$_SERVER["HTTP_HOST"].'/'.strstr(C('LOG_PATH'),substr(__ROOT__,1)),'',$value);
				$arr[]= substr($url_data, 0, 7);
				//$arr[] = $value;
				$download_file[]=substr($url_data,8);
			}
			$url=array_unique($arr);
			$cur_file = C('LOG_PATH').$url[0];
		}else{
			$arr=str_replace('http://'.$_SERVER["HTTP_HOST"].'/'.strstr(C('LOG_PATH'),substr(__ROOT__,1)),'',$url);
			$arr=substr($arr, 0, 7);
			//$arr = $url;
			$download_file[]=substr($arr,8);
			$cur_file = C('LOG_PATH').$arr;
		}
		$save_path='./Public/data/';
		if(is_dir($save_path)){
			mkdir($save_path);
		}
		$scandir = new \Org\Util\traverseDir($cur_file,$save_path); //$save_path zip包文件目录
		$a=$scandir->tozip($download_file);
		$a=substr($a, 1);
		$a=C('DOWNLOAD_URL').$a;
		$this->response($a,'json');exit();
	}

	/*
	 * 开启或者关闭调试功能
	 * @param status 	开启或者关闭(OPEN ,CLOSE)
	 */
	public function OpenDebug(){
		$status=I('post.state');
		$origin_str = file_get_contents(C('ENTRANCE'));
		//检测调试模式的状态
		$checktrue=strpos($origin_str,"define('APP_DEBUG',True)");
		$checkfalse=strpos($origin_str,"define('APP_DEBUG',false)");
		if(strtolower($status)=='open'){//开启调试模式
			if($checktrue){
				$arr['status']=100;
				$this->response($arr,'json');
			}elseif($checkfalse){
				$update_str = str_replace("define('APP_DEBUG',false)","define('APP_DEBUG',True)",$origin_str);
				file_put_contents(C('ENTRANCE'), $update_str);
			}
		}elseif(strtolower($status)=='close'){//关闭调试模式
			if($checktrue){
				$update_str = str_replace("define('APP_DEBUG',True)","define('APP_DEBUG',false)",$origin_str);
				file_put_contents(C('ENTRANCE'), $update_str);
			}else{
				$arr['status']=100;
				$this->response($arr,'json');
			}
		}
		$arr['status']=100;
		$this->response($arr,'json');	
	}

	/*
	 * 检测当前项目的调试模式是否开启
	 */
	public function checkDebug(){
		$origin_str = file_get_contents(C('ENTRANCE'));
		$checktrue=strpos($origin_str,"define('APP_DEBUG',True)");
		if($checktrue){
			$arr['state']='open';
		}else{
			$arr['state']='close';
		}
		$arr['status']=100;
		$this->response($arr,'json');
	}

	/*
     * 用户行为跟踪展示
     * @param searchText 搜索词
     * */
    public function userTrack()
    {
        // 是否需要展示角色分组
        $uid       = (int)I('post.uid');
        $startdate = I('post.startdate');
        $enddate   = I('post.enddate');
        $page      = isset($_POST['page']) ? (int)I('page') : 1;
        $pagesize  = isset($_POST['pagesize']) ? (int)I('pagesize') : 25;

        $where = "1 = 1";
        if($uid && $uid != 0){
            $where .= " AND uid = $uid";
        }
        if($startdate && $enddate){
            $sd = date("Y-m-d H:i:s" ,strtotime($startdate));
            $ed = date("Y-m-d H:i:s" ,strtotime($enddate));
            if($sd > $ed){
                $this->response(['status' => 101,'msg' => '开始日期不能大于结束日期'],'json');
            }
            if($sd == $ed){
            	$sd = date("Y-m-d H:i:s" ,strtotime($startdate));
            	$ed = date("Y-m-d H:i:s" ,strtotime($enddate." +24 hours"));
            }
            $where .= " AND request_time BETWEEN '$sd' AND '$ed'";
        }
        $start = ( $page - 1 ) * $pagesize;
        // 查询
        $count = M("user_track t")
            ->join(C("DB_PREFIX")."auth_user u ON u.id=t.uid",'LEFT')
            ->where($where)->count();
        $track = M("user_track t")
            ->join(C("DB_PREFIX")."auth_user u ON u.id=t.uid",'LEFT')
            ->where($where)
            ->field("t.*,u.username,u.real_name")
            ->limit($start,$pagesize)
            ->order('t.id desc')
            ->select();
        if($track){
            foreach($track as $key => $value){
                $track[$key]['checked'] = (boolean)'';
            }
            $this->response([
                'status'     => 100,
                'value'      => $track,
                'countTrack' => $count,
                'pageNow'    => $page,
                'countPage'  => ceil ( $count / $pagesize)
            ],'json');
        }else{
            $this->response(['status' => 101,'msg' => '无相关信息'],'json');
        }
    }

    /*
     * 用户行为删除
     * @param uid
     * */
    public function deleteTrack()
    {
        $m = M("user_track");
        $uid = I('id');
        $arr = array();
        if(!is_array($uid)){    // 按照数组批量删除的方式删除数据
            $arr[] = $uid;
        }elseif(is_array($uid)){
            $arr = $uid;
        }
        foreach($arr as $value){  // 循环删除
            $m->where(array('id' => $value))->delete();
        }
        $this->response(['status' => 100],'json');
    }
}