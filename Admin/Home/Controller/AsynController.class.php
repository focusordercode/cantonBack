<?php
namespace Home\Controller;
use Think\Controller;

/**
*  异步处理
*/
class AsynController extends Controller
{
	//创建分区
	public function CreatePartition(){
		ignore_user_abort(true); // 忽略客户端断开  
    	set_time_limit(0);       // 设置执行不超时
    	$id=I('post.id');
		$tbl_name=I('post.tbl_name');
		$type=I('post.type');
		$key=I('post.type_key');
		$num=I('post.num');
		$interval=I('post.interval');
		$subtype=I('post.subtype');
		$subnum=I('post.subnum');
		$subkey=I('post.subkey');
		$types=I('post.types');
		S($tbl_name.'_s','ONGOING');
		$query11="show columns from ".$tbl_name;//查询表的结构
		$arr=M()->query($query11);
		foreach ($arr as $keys => $value) {
			if($value['key']=='PRI'){
				$ke[]=$value['field'];//将主键保存下来
			}
		}
        $k=$ke[0];
		$table=M(C('DB_TABLE_NAME'));
		$data['pkey']=$k;
		$add=$table->data($data)->where("id=%d",array($id))->save();
		if($add!=='flase'){
			// 修改表的主键，因为分区的列值必须是主键
			if(empty($subkey)){
				if($key!=$k){
					$upda="ALTER TABLE ".$tbl_name." DROP PRIMARY KEY ,ADD PRIMARY KEY (`".$k."`,`".$key."`)";
					M()->execute($upda);
				}
			}else{
				if($types=='COMPOSITE'){
					if ($k==$key) {
						if($k!=$subkey){
							$upda="ALTER TABLE ".$tbl_name." DROP PRIMARY KEY ,ADD PRIMARY KEY (`".$k."`,`".$subkey."`)";
							M()->execute($upda);
						}
					}else{
						if($k==$subkey){
							$upda="ALTER TABLE ".$tbl_name." DROP PRIMARY KEY ,ADD PRIMARY KEY (`".$k."`,`".$key."`)";
							M()->execute($upda);
						}else{
							$upda="ALTER TABLE ".$tbl_name." DROP PRIMARY KEY ,ADD PRIMARY KEY (`".$k."`,`".$key."`,`".$subkey."`)";
							M()->execute($upda);
						}
					}
				}else{
					if($key!=$k){
						$upda="ALTER TABLE ".$tbl_name." DROP PRIMARY KEY ,ADD PRIMARY KEY (`".$k."`,`".$key."`)";
						M()->execute($upda);
					}
				}
			}
			$nums=1;
			//分区语句
			switch ($types){
				case 'RANGE':
					$q="ALTER TABLE ".$tbl_name." PARTITION BY RANGE(`".$key."`)(";
					for ($i=0; $i < $num-1 ; $i++) {
						$nums=$nums+$interval; 
						$q.="partition pi".$i." VALUES LESS THAN (".$nums."),";
					}
					$i++;
					$q.=" partition pi".$i." VALUES LESS THAN MAXVALUE)";
				  break;
				case 'HASH':
					$q="ALTER TABLE ".$tbl_name." PARTITION BY HASH(`".$key."`) PARTITIONS ".$num;
				  break;
				case 'LINEAR HASH':
					$q="ALTER TABLE ".$tbl_name." PARTITION BY LINEAR HASH(`".$key."`) PARTITIONS ".$num;
				  break;
				case 'KEY':
					$q="ALTER TABLE ".$tbl_name." PARTITION BY KEY(`".$key."`) PARTITIONS ".$num;
				  break;
				case 'COMPOSITE':
					if(!empty($subtype) && !empty($subnum) && !empty($subkey)){
						$z=0;
						$q="ALTER TABLE ".$tbl_name." PARTITION BY ".$type."(`".$key."`) SUBPARTITION BY ".$subtype." (".$subkey.")(	";
						for ($i=0; $i < $num-1 ; $i++) {
							$nums=$nums+$interval; 
							$q.="partition pi".$i." VALUES LESS THAN (".$nums.")(";
							for ($j=0; $j < $subnum; $j++) { 
								$q.="SUBPARTITION S".$z.",";
								$z++;
							}
							$q=substr($q,0,strlen($q)-1)."),";
						}
						$i++;
						$q.="partition pi".$i." VALUES LESS THAN MAXVALUE(";
						for ($y=0; $y < $subnum ; $y++) { 
							$q.="SUBPARTITION S".$z.",";
							$z++;
						}
						$q=substr($q,0,strlen($q)-1);
						$q.="))";
					}else{
						$q="ALTER TABLE ".$tbl_name." PARTITION BY RANGE(`".$key."`)(";
						for ($i=0; $i < $num-1 ; $i++) {
							$nums=$nums+$interval; 
							$q.="partition pi".$i." VALUES LESS THAN (".$nums."),";
						}
						$i++;
						$q.=" partition pi".$i." VALUES LESS THAN MAXVALUE)";
					}
				  break;
			}
			M()->execute($q);
			S($tbl_name.'_s','SUCCESS');
		}
	}

	//分区扩容
	public function DilatationPartition(){
		ignore_user_abort(true); // 忽略客户端断开  
    	set_time_limit(0);       // 设置执行不超时
		$id=I('post.id');
		$num=I('post.num');
		$subnum=I('post.subnum');
		$table=M(C('DB_TABLE_NAME'));
		$query=$table->where("id=%d",array($id))->find();
		$nums=1;
		S($query['tbl_name'].'_d',"ONGOING");
		//分区语句
		if(empty($query['subtype'])){
			switch ($query['type']) {
				case 'RANGE':
					$sql="ALTER TABLE ".$query['tbl_name']." PARTITION BY RANGE(`".$query['type_key']."`)(";
					for ($i=0; $i < $num-1 ; $i++) {
						$nums=$nums+$query['interval']; 
						$sql.="partition pi".$i." VALUES LESS THAN (".$nums."),";
					}
					$i++;
					$sql.=" partition pi".$i." VALUES LESS THAN MAXVALUE)";
				  break;
				
				default:
					$sql="ALTER TABLE ".$query['tbl_name']." PARTITION BY ".$query['type']."(`".$query['type_key']."`) PARTITIONS ".$num;
				  break;
			}
		}else{
			if(empty($subnum)){
				$subnum=$query['subnum'];
			}
			$z=0;
			$sql="ALTER TABLE ".$query['tbl_name']." PARTITION BY ".$query['type']."(`".$query['type_key']."`) SUBPARTITION BY ".$query['subtype']." (`".$query['subkey']."`)(";
			for ($i=0; $i < $num-1 ; $i++) {
				$nums=$nums+$query['interval']; 
				$sql.="partition pi".$i." VALUES LESS THAN (".$nums.")(";
				for ($j=0; $j < $subnum; $j++) { 
					$sql.="SUBPARTITION S".$z.",";
					$z++;
				}
				$sql=substr($sql,0,strlen($sql)-1)."),";
			}
			$i++;
			$sql.="partition pi".$i." VALUES LESS THAN MAXVALUE(";
			for ($y=0; $y < $subnum ; $y++) { 
				$sql.="SUBPARTITION S".$z.",";
				$z++;
			}
			$sql=substr($sql,0,strlen($sql)-1);
			$sql.="))";
		}
		M()->execute($sql);
		S($query['tbl_name'].'_d',"SUCCESS");
	}

	//修改分区
	public function UpdatePartition(){
		ignore_user_abort(true); // 忽略客户端断开  
    	set_time_limit(0);       // 设置执行不超时
		$id=I('post.id');
		$types=I('post.types');
		// $id=2;
		// $types='COMPOSITE';
		$table=M(C('DB_TABLE_NAME'));
		$arr=$table->where("id=%d",array($id))->find();
		S($arr['tbl_name'].'_u',"ONGOING");
		$query1="select count(*) num from information_schema.partitions where table_schema=database() and table_name='".$arr['tbl_name']."';";//查询有多少个分区
		$array=M()->query($query1);
		if($array['num']!=1){
			$query="ALTER TABLE ".$arr['tbl_name']." PARTITION BY HASH (`".$arr['pkey']."`) PARTITIONS 1";//将表合并成一个为以原来的主键为分区列值的分区
			M()->execute($query);
		}
		
		//修改主键
		if(empty($arr['subkey'])){
			if($arr['type_key']!=$arr['pkey']){
				$upda="ALTER TABLE ".$arr['tbl_name']." DROP PRIMARY KEY ,ADD PRIMARY KEY (`".$arr['pkey']."`,`".$arr['type_key']."`)";
				M()->execute($upda);
			}
		}else{
			if($types=='COMPOSITE'){
				if ($arr['pkey']==$arr['type_key']) {
					if($arr['pkey']!=$arr['subkey']){
						$upda="ALTER TABLE ".$arr['tbl_name']." DROP PRIMARY KEY ,ADD PRIMARY KEY (`".$arr['pkey']."`,`".$arr['subkey']."`)";
						M()->execute($upda);
					}
				}else{
					if($arr['pkey']==$arr['subkey']){
						$upda="ALTER TABLE ".$arr['tbl_name']." DROP PRIMARY KEY ,ADD PRIMARY KEY (`".$arr['pkey']."`,`".$arr['type_key']."`)";
						M()->execute($upda);
					}else{
						$upda="ALTER TABLE ".$arr['tbl_name']." DROP PRIMARY KEY ,ADD PRIMARY KEY (`".$arr['pkey']."`,`".$arr['type_key']."`,`".$arr['subkey']."`)";
						M()->execute($upda);
					}
				}
			}else{
				if($arr['type_key']!=$arr['pkey']){
					$upda="ALTER TABLE ".$arr['tbl_name']." DROP PRIMARY KEY ,ADD PRIMARY KEY (`".$arr['pkey']."`,`".$arr['type_key']."`)";
					M()->execute($upda);
				}
			}
		}
        $nums=1;
		switch ($types) {
			case 'RANGE':
				$sql="ALTER TABLE ".$arr['tbl_name']." PARTITION BY RANGE(`".$arr['type_key']."`)(";
				for ($i=0; $i < $arr['num']-1 ; $i++) {
					$nums=$nums+$arr['interval']; 
					$sql.="partition pi".$i." VALUES LESS THAN (".$nums."),";
				}
				$i++;
				$sql.=" partition pi".$i." VALUES LESS THAN MAXVALUE)";
			  break;
			case 'HASH':
				$sql="ALTER TABLE ".$arr['tbl_name']." PARTITION BY HASH(`".$arr['type_key']."`) PARTITIONS ".$arr['num'];
			  break;
			case 'LINEAR HASH':
				$sql="ALTER TABLE ".$arr['tbl_name']." PARTITION BY LINEAR HASH(`".$arr['type_key']."`) PARTITIONS ".$arr['num'];
			  break;
			case 'KEY':
				$sql="ALTER TABLE ".$arr['tbl_name']." PARTITION BY KEY(`".$arr['type_key']."`) PARTITIONS ".$arr['num'];
			  break;
			case 'COMPOSITE':

				$z=0;
				if(!empty($arr['subtype']) && !empty($arr['subtype']) && !empty($arr['subtype'])){
					$sql="ALTER TABLE ".$arr['tbl_name']." PARTITION BY ".$arr['type']."(`".$arr['type_key']."`) SUBPARTITION BY ".$arr['subtype']." (`".$arr['subkey']."`)(	";
					for ($i=0; $i < $arr['num']-1 ; $i++) {
						$nums=$nums+$arr['interval']; 
						$sql.="partition pi".$i." VALUES LESS THAN (".$nums.")(";
						for ($j=0; $j < $arr['subnum']; $j++) { 
							$sql.="SUBPARTITION S".$z.",";
							$z++;
						}
						$sql=substr($sql,0,strlen($sql)-1)."),";
					}
					$i++;
					$sql.="partition pi".$i." VALUES LESS THAN MAXVALUE(";
					for ($y=0; $y < $arr['subnum'] ; $y++) { 
						$sql.="SUBPARTITION S".$z.",";
						$z++;
					}
					$sql=substr($sql,0,strlen($sql)-1);
					$sql.="))";
				}else{
					$sql="ALTER TABLE ".$arr['tbl_name']." PARTITION BY RANGE(`".$arr['type_key']."`)(";
					for ($i=0; $i < $arr['num']-1 ; $i++) {
						$nums=$nums+$arr['interval']; 
						$sql.="partition pi".$i." VALUES LESS THAN (".$nums."),";
					}
					$i++;
					$sql.=" partition pi".$i." VALUES LESS THAN MAXVALUE)";
				}
			  break;
		}
		M()->execute($sql);
		S($arr['tbl_name'].'_u',"SUCCESS");
	}
}