<?php
namespace Home\Controller;
use Think\Controller;

/**
* 分区操作
* @author lrf
* @modify 2016/12/22
*/
class SubareaController extends BaseController
{
	/*
	 * 获取数据库全部没有创建分区的表
	 */ 
	public function getAllTable(){
		$sql=M()->query("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = '".C('DB_NAME')."'  and table_type='base table'");//查询所有表名
		if($sql){
			foreach ($sql as $keys => $values) {
				$array[]=$values['table_name'];
			}
			$table=M(C('DB_TABLE_NAME'));
			$query=$table->field("tbl_name")->select();
			foreach ($query as $key => $value) {
				$keys = array_search($value['tbl_name'], $array);
				if ($keys !== false){
    				array_splice($array, $keys, 1);//剔除已经分区的表
				}
			}
			$arr['status']=100;
			$arr['values']=$array;
		}else{
			$arr['status']=101;
		}
		$this->response($arr,'json');
	}

	/*
	 * 获取已经分区的表名
	 */
	public function getTable(){
		$sub=M(C('DB_TABLE_NAME'));
		$sql=$sub->field("id,tbl_name")->select();
		if($sql){
			$arr['status']=100;
			$arr['values']=$sql;
		}else{
			$arr['status']=101;
		}
		$this->response($arr,'json');
	}

	/*
	 * 获取数据表的字段
	 */
	public function getFields(){
		$id=I('post.id');
		if(empty($id)){
			$tbl_name=I('post.tbl_name');
            $tbl_name = __sqlSafe__($tbl_name);
		}else{
			$table=M(C('DB_TABLE_NAME'));
			$tbl=$table->field("tbl_name")->where("id=%d",array($id))->find();
			$tbl_name=$tbl['tbl_name'];
		}
		$sql=M()->query("select COLUMN_NAME from information_schema.COLUMNS where data_type='int' and table_name = '".$tbl_name."' and table_schema ='".C('DB_NAME')."'");//获取类型为int的字段
		if($sql){
			$arr['status']=100;
			$arr['values']=$sql;
		}else{
			$arr['status']=101;
		}
		$this->response($arr,'json');
	}
 
	/*
	 * 为表创建分区
	 * @param tbl_name  数据表名称
	 * @param type 分区类型
	 * @param key 做分区的键值
	 * @param num 分区数量
	 * @param interval 分区区间
	 * @param subnum  子分区数量
	 * @param subkey 子分区的键值
	 * @param subtype 子分区的类型
 	 */
	public function setSubarea(){
		$tbl_name=I('post.tbl_name');
		$type=I('post.type');
		$key=I('post.key');
		$num=I('post.num');
		$interval=I('post.interval');
		$subtype=I('post.subtype');
		$subnum=I('post.subnum');
		$subkey=I('post.subkey');
		if(empty($type)){
			$arr['status']=104;
			$this->response($arr,'json');
			exit();
		}
		if(empty($key)){
			$arr['status']=105;
			$this->response($arr,'json');
			exit();
		}
		if(empty($num)){
			$arr['status']=106;
			$this->response($arr,'json');
			exit();
		}
		if(empty($tbl_name)){
			$arr['status']=102;
		}else{
			$query="select min(PARTITION_DESCRIPTION) `interval`, max(PARTITION_ORDINAL_POSITION) num,max(SUBPARTITION_ORDINAL_POSITION) subnum,PARTITION_METHOD type,SUBPARTITION_METHOD subtype,PARTITION_EXPRESSION `key`,SUBPARTITION_EXPRESSION subkey from information_schema.partitions where table_schema=database() and table_name='".$tbl_name."';";//查询表的分区情况
			$array=M()->query($query);
			//判断是否从未分区，已经分区就返回
			if(!empty($array[0]['type'])){
			
				$query11="show columns from ".$tbl_name;//查询表的结构
				$arr=M()->query($query11);
				foreach ($arr as $keys => $value) {
					if($value['key']=='PRI'){
						$k[]=$value['field'];//将主键保存下来
					}
				}
				//保存分区信息
				$sub=M(C('DB_TABLE_NAME'));
				$data['tbl_name']=$tbl_name;
				$data['type']=$array[0]['type'];
				$countkey=count($array[0]['key']);
				$ke=substr($array[0]['key'],1,$countkey-1);
				$data['type_key']=$ke;
				$data['num']=$array[0]['num'];
				$csubkey=count($array[0]['subtype']);
				$suke=substr($array[0]['subtype'],1,$csubkey-1);
				$data['subtype']=$suke;
				$data['subkey']=$array[0]['subkey'];
				$data['subnum']=$array[0]['subnum'];
				$data['interval']=$array[0]['interval'];
				$data['pkey']=$k[0];
				$sql=$sub->data($data)->add();
				$arr['status']=109;
				$arr['va']=$ke;
				$this->response($arr,'json');
				exit();
			}
			//判断是否可以提交
			if(S($tbl_name.'_s')=='SUCCESS' || empty(S($tbl_name.'_s'))){
				//保存分区信息
				switch ($type) {
					case 'RANGE':
						if(empty($interval)){
							$arr['status']=107;
							$this->response($arr,'json');
							exit();
						}
						$data['type']=$type;
						$data['type_key']=$key;
						$data['num']=$num;
						$data['interval']=$interval;
						$data['subtype']=0;
						$data['subnum']=0;
						$data['subkey']=0;
					  break;
					case 'HASH':case 'LINEAR HASH':case 'KEY':
						$data['type']=$type;
						$data['type_key']=$key;
						$data['num']=$num;
						$data['interval']=0;
						$data['subtype']=0;
						$data['subnum']=0;
						$data['subkey']=0;
					  break;
					case 'COMPOSITE':
						if(empty($interval)){
							$arr['status']=107;
							$this->response($arr,'json');
							exit();
						}
						if(!empty($subtype) && !empty($subnum) && !empty($subkey)){
							$data['type']='RANGE';
							$data['type_key']=$key;
							$data['num']=$num;
							$data['interval']=$interval;
							$data['subtype']=$subtype;
							$data['subnum']=$subnum;
							$data['subkey']=$subkey;
						}else{
							$data['type']='RANGE';
							$data['type_key']=$key;
							$data['num']=$num;
							$data['interval']=$interval;
							$data['subtype']=0;
							$data['subnum']=0;
							$data['subkey']=0;
						}
						break;
				}
				$data['tbl_name']=$tbl_name;
				$table=M(C('DB_TABLE_NAME'));
				$a['tbl_name']=$tbl_name;
				$q=$table->where($a)->find();
				if(empty($q)){
					$sql=$table->data($data)->add();
				}else{
					$upda=$data->data($data)->where($a)->save();
					if($upda!=='flase'){
						$sql=1;
					}else{
						$sql=flase;
					}
				}
				if($sql){
					$url = __ROOT__.'/Asyn/CreatePartition';
					$data['types']=$type;
					$data['id']=$sql;
					$check=doRequest($url,$data);//将操作异步启动
					if($check===true){
						$arr['status']=100;
					}else{
						$arr['status']=101;
					}
				}else{
					$arr['status']=101;
				}
			}else{
				$arr['status']=103;
			}
		}
		$this->response($arr,'json');		
	}

	/*
	 * 分区扩容
	 * @param id 数据表id
	 * @param num 分区数量
	 * @param subnum  子分区数量
	 */
	public function Dilatation(){
		$id=I('post.id');
		$num=I('post.num');
		$subnum=I('post.subnum');
		if(empty($num)){
			$arr['status']=106;
			$this->response($arr,'json');
		}
		if(empty($id)){
			$arr['status']=102;
		}else{
			$table=M(C('DB_TABLE_NAME'));
			$array=$table->field("tbl_name,num,subnum")->where("id=%d",array($id))->find();
			if(S($array['tbl_name'].'_d')=='ONGOING'){
				$arr['status']=103;
			}else{
				$data['num']=$num+$array['num'];
				$data['subnum']=$subnum+$array['subnum'];
				$sql=$table->data($data)->where("id=%d",array($id))->save();
				if($sql!=='flase'){
					$data['id']=$id;
					$url = __ROOT__.'/Asyn/DilatationPartition';
					$check=doRequest($url,$data);//将操作异步启动
					if($check===true){
						$arr['status']=100;
					}else{
						$arr['status']=101;
					}
				}else{
					$arr['status']=101;
				}
			}
		}
		$this->response($arr,'json');		
	}

	/*
	 * 修改分区
	 * @param id 数据id
	 * @param type 分区类型
	 * @param key 做分区的键值
	 * @param num 分区数量
	 * @param interval 分区区间
	 * @param subnum  子分区数量
	 * @param subkey 子分区的键值
	 * @param subtype 子分区的类型
 	 */
	public function updateSubarea(){
		$id=I('post.id');
		if(empty($id)){
			$arr['status']=102;
			$this->response($arr,'json');
			exit();
		}
		$table=M(C('DB_TABLE_NAME'));
		$tbl=$table->where("id=%d",array($id))->find();
		$tbl_name=$tbl['tbl_name'];
		$type=I('post.type');
		$key=I('post.key');
		$num=I('post.num');
		$interval=I('post.interval');
		$subtype=I('post.subtype');
		$subnum=I('post.subnum');
		$subkey=I('post.subkey');
		if(empty($type)){
			$arr['status']=104;
			$this->response($arr,'json');
			exit();
		}
		if(empty($key)){
			$arr['status']=105;
			$this->response($arr,'json');
			exit();
		}
		if(empty($num)){
			$arr['status']=106;
			$this->response($arr,'json');
			exit();
		}
		if(S($tbl_name.'_u')=='ONGOING'){//判断是否正在操作
			$arr['status']=103;
		}else{
			//将分区操作写入数据库
			switch ($type) {
				case 'RANGE':
					if(empty($interval)){
						$arr['status']=107;
						$this->response($arr,'json');
						exit();
					}
					$data['type']=$type;
					$data['type_key']=$key;
					$data['num']=$num;
					$data['interval']=$interval;
					$data['subtype']=0;
					$data['subnum']=0;
					$data['subkey']=0;
					if($tbl['type']==$type && $tbl['type_key']==$key && $tbl['num']==$num && $tbl['interval']==$interval){
						$arr['status']=100;
						S($tbl_name.'_u','SUCCESS');
						$this->response($arr,'json');
						exit();
					}
				  break;
				case 'HASH':case 'LINEAR HASH':case 'KEY':
					$data['type']=$type;
					$data['type_key']=$key;
					$data['num']=$num;
					$data['interval']=0;
					$data['subtype']=0;
					$data['subnum']=0;
					$data['subkey']=$subkey;
					if($tbl['type']==$type && $tbl['type_key']==$key && $tbl['num']==$num){
						$arr['status']=100;
						S($tbl_name.'_u','SUCCESS');
						$this->response($arr,'json');
						exit();
					}
				  break;
				case 'COMPOSITE':
					if(empty($interval)){
						$arr['status']=107;
						$this->response($arr,'json');
						exit();
					}
					if(!empty($subtype) && !empty($subnum) && !empty($subkey)){
						$data['type']=$type;
						$data['type_key']=$key;
						$data['num']=$num;
						$data['interval']=$interval;
						$data['subtype']=$subtype;
						$data['subnum']=$subnum;
						$data['subkey']=$subkey;
						if($tbl['type']==$type && $tbl['type_key']==$key && $tbl['num']==$num && $tbl['interval']==$interval && $tbl['subtype']==$subtype && $tbl['subnum']==$subnum && $tbl['subkey']==$subkey){
							$arr['status']=100;
							S($tbl_name.'_u','SUCCESS');
							$this->response($arr,'json');
							exit();
						}
					}else{
						$data['type']='RANGE';
						$data['type_key']=$key;
						$data['num']=$num;
						$data['interval']=$interval;
						$data['subtype']=0;
						$data['subnum']=0;
						$data['subkey']=0;
						if($tbl['type']==$type && $tbl['type_key']==$key && $tbl['num']==$num && $tbl['interval']==$interval){
							$arr['status']=100;
							S($tbl_name.'_u','SUCCESS');
							$this->response($arr,'json');
							exit();
						}
					}
					break;
			}
			$table=M(C('DB_TABLE_NAME'));
			$sql=$table->data($data)->where("id=%d",array($id))->save();	
			if($sql!=='flase'){
				$array['id']=$id;
				$array['types']=$type;
				$url = __ROOT__.'/Asyn/UpdatePartition';
				$check=doRequest($url,$array);//将操作异步启动
				if($check===true){
					$arr['status']=100;
				}else{
					$arr['status']=101;
				}
			}else{
				$arr['status']=101;
			}
		}
		
		$this->response($arr,'json');		
	}

	/*
	 * 检测分区是否操作是否成功或者是否正在执行
	 * @param tbl_name 数据表名称
	 * @param operation  操作
	 */
	public function check(){
		$tbl_name=I('post.tbl_name');
		if(empty($tbl_name)){
			$arr['status']=102;
		}else{
			if(preg_match("/^[0-9]*$/",$tbl_name)){
				$table=M(C('DB_TABLE_NAME'));
				$tbl=$table->field("tbl_name")->where("id=%d",array($tbl_name))->find();
				$tbl_name=$tbl['tbl_name'];
			}else{
				$tbl_name=I('post.tbl_name');
			}
			$operation=I('post.operation');
			switch ($operation) {
				case 'd':
					if(S($tbl_name.'_d')=='ONGOING'){
						$arr['status']=101;
					}else{
						if(empty(S($tbl_name.'_d')) ){
							$arr['status']=110;
						}elseif(S($tbl_name.'_d')=='SUCCESS'){
							$arr['status']=100;
							S($tbl_name.'_d',null);
						}
					}
				  break;
				case 'u':
					if(S($tbl_name.'_u')=='ONGOING'){
						$arr['status']=101;
					}else{
						if(empty(S($tbl_name.'_u'))){
							$arr['status']=110;
						}elseif(S($tbl_name.'_u')=='SUCCESS'){
							$arr['status']=100;
							S($tbl_name.'_u',null);
						}
					}
				  break;
				case 's':
					if(S($tbl_name.'_s')=='ONGOING'){
						$arr['status']=101;
					}else{
						if(empty(S($tbl_name.'_s'))){
							$arr['status']=110;
						}elseif(S($tbl_name.'_s')=='SUCCESS'){
							$arr['status']=100;
							S($tbl_name.'_s',null);
						}
					}
				  break;
			}
		}
		$this->response($arr,'json');
	}
	/*
	 * 分区出现错误时的操作
	 * @param tbl_name 数据表名称
	 */
	public function Eliminate(){
		$tbl_name=I('get.tbl_name');
		if(S($tbl_name.'_s')=='ONGOING'){
			$table=M(C('DB_TABLE_NAME'));
			$data['tbl_name']=$tbl_name;
			$sql=$table->where($data)->delete();
		}
		S($tbl_name.'_s',null);
		S($tbl_name.'_d',null);
		S($tbl_name.'_u',null);
		if(empty(S($tbl_name.'_u')) && empty(S($tbl_name.'_d')) && empty(S($tbl_name.'_s'))){
			$arr['status']=100;
		}else{
			$arr['status']=101;
		}
		$this->response($tbl_name,'json');
	} 

	
	/*
	 * 获取每张表的统计
	 */
	public function getTableCount(){
		$db = M();
		$db->startTrans();
		$sql=M()->query("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = '".C('DB_NAME')."'  and table_type='base table'");//查询所有表名
		foreach ($sql as $key => $value) {
			$data[$key]['table_name'] = $value['table_name'];
			$count = $db->table($value['table_name'])->count();
			$data[$key]['count'] = $count;
			$where['tbl_name'] = $value['table_name'];
			$query = $db->table('tbl_table_to_subarea')->field("num,subnum")->where($where)->find();
			if($query){
				$num = ($query['num']+$query['subnum'])*500000;
				$data[$key]['subarea_num'] = $query['num'];
				$data[$key]['subarea_subnum'] = $query['subnum'];
			}else{
				$num = 1000000;
				$data[$key]['subarea_num'] = 0;
				$data[$key]['subarea_subnum'] = 0;
			}
			$data[$key]['percentage']  = sprintf("%.2f", ($count / $num)*100);
		}
		$db->commit();
		$arr['status'] = 100;
		$arr['value'] = $data;
		$this->response($arr,'json');
	}
}