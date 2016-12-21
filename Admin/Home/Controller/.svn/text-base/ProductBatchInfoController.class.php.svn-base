<?php
namespace Home\Controller;
use Think\Controller\RestController;
header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Methods:POST,GET');
header('Access-Control-Allow-Credentials:true'); 
header("Content-Type: application/json;charset=utf-8");
/*
 * 产品批量表控制器
 */
class ProductBatchInfoController extends RestController
{
   protected $dt   = "/^([1][7-9]{1}[0-9]{1}[0-9]{1}|[2][0-9]{1}[0-9]{1}[0-9]{1})(-)([0][1-9]{1}|[1][0-2]{1})(-)([0-2]{1}[1-9]{1}|[3]{1}[0-1]{1})*$/";
   protected $dt1  = "/^([1][7-9][0-9][0-9]|[2][0][0-9][0-9])(\.)([0][1-9]|[1][0-2])(\.)([0-2][1-9]|[3][0-1])*$/";
   protected $dt2  = "/^([1][7-9][0-9][0-9]|[2][0][0-9][0-9])([0][1-9]|[1][0-2])([0-2][1-9]|[3][0-1])*$/";
   protected $dt3  = "/^([1][7-9][0-9][0-9]|[2][0][0-9][0-9])(\/)([0][1-9]|[1][0-2])(\/)([0-2][1-9]|[3][0-1])*$/";

  /*
   * 删除单条产品批量表数据
   */
  public function delBatchInfo(){
      $productid = I('post.product_id');
      if($productid != null){
          $res = \Think\Product\ProductBatchInfo::DelBatchInfo($productid);
          if($res == 1){
    	     $data['status'] = 100;
	      }else{
		     $data['status'] = 101;
	      }
      }else{
          $data['status'] = 102;
      }
	  $this->response($data,'json');
  }

  /*
   * 添加单条产品批量表数据和变体
   */
  public function addBatchInfo(){
  	  $data = array();
  	  $arr  = array();
      $category_id = I('post.category_id');
      $template_id = I('post.template_id');
      $text        = I('post.allData');
      $num         = I('post.changeNum');
      $i = 0;
      foreach ($text as $key => $value) {
          $data[$i]['category_id']    = $category_id;
          $data[$i]['template_id']    = $template_id;
          $data[$i]['no']             = $value['no'];
          $data[$i]['title']          = $value['en_name'];
          $data[$i]['data_type_code'] = $value['data_type_code'];
          switch ($data[$i]['data_type_code']) {
              case 'int':
                  if(preg_match("/^[0-9]*$/",$value['save_text'])){
                      $data[$i]['interger_value'] = $value['save_text'];
                      break;
                  }else{
                      $arr['status'] = 103;
                      $this->response($arr,'json');
                      exit();
                  }
              case 'char':
                  $data[$i]['char_value'] = $value['save_text'];
                  break;
              case 'dc':
                  if(preg_match("/^(\d*\.)?\d{0,2}+$/",$value['save_text'])){
                      $data[$i]['decimal_value'] = $value['save_text'];
                      break;
                  }else{
                     $arr['status'] = 104;
                     $this->response($arr,'json');
                     exit();
                  }
              case 'dt':
                  if(preg_match($this->dt,$value['save_text']) || preg_match($this->dt1,$value['save_text']) || preg_match($this->dt2,$value['save_text']) || preg_match($this->dt3,$value['save_text'])){
                      $data[$i]['date_value'] = $value['save_text'];
                      break;
                  }else{
                      $arr['status'] = 105;
                      $this->response($arr,'json');
                      exit();
                  }
              case 'bl':
                  $data[$i]['boolean_value'] = $value['save_text'];
                  break;
              case 'pic':
                  $data[$i]['char_value']    = $value['save_text'];
                  break;
          }
          $data[$i]['created_time']   = date('Y-m-d H:i:s',time());
          $data[$i]['modified_time']  = date('Y-m-d H:i:s',time());
          $i++;
      }
      $res = \Think\Product\ProductBatchInfo::AddBatchInfo($data,$num);
      if($res == 1){
          $arr['status'] = 100;
      }else{
          $arr['status'] = 101;
      }
      $this->response($arr,'json');
  }

  /*
   * 修改产品批量表数据
   */
  public function updateBatchInfo(){
  	  $text = I('post.allData');
      $i = 0;
  	  foreach ($text as $key => $value) {
          foreach ($value as $k => $v) {

              $da[$i]['id']     = $v['id'];
              $data[$i]['no']   = $v['no'];
              $data_type_code   = $v['type'];

              switch ($data_type_code) {
                  case 'int':
                      if(preg_match("/^[0-9]*$/",$v['save_text'])){
                          $data[$i]['interger_value']  = $v['save_text'];
                          $data[$i]['char_value']      = null;
                          $data[$i]['decimal_value']   = null;
                          $data[$i]['date_value']      = null;
                          $data[$i]['boolean_value']   = null;
                          break;
                      }else{
                          $arr['status']=103;
                          $this->response($arr,'json');
                          exit();
                      }
                  case 'char':
                          $data[$i]['interger_value']   = null;
                          $data[$i]['char_value']       = $v['save_text'];
                          $data[$i]['decimal_value']    = null;
                          $data[$i]['date_value']       = null;
                          $data[$i]['boolean_value']    = null;
                          break;
                  case 'dc':
                      if(preg_match("/^(\d*\.)?\d{0,2}+$/",$v['save_text'])){
                          $data[$i]['interger_value']   = null;
                          $data[$i]['char_value']       = null;
                          $data[$i]['decimal_value']    = $v['save_text'];
                          $data[$i]['date_value']       = null;
                          $data[$i]['boolean_value']    = null;
                          break;
                      }else{
                          $arr['status'] = 104;
                          $this->response($arr,'json');
                          exit();
                      }
                  case 'dt':
                      if(preg_match($this->dt,$v['save_text']) || preg_match($this->dt1,$v['save_text']) || preg_match($this->dt2,$v['save_text']) || preg_match($this->dt3,$v['save_text'])){
                          $data[$i]['interger_value']   = null;
                          $data[$i]['char_value']       = null;
                          $data[$i]['decimal_value']    = null;
                          $data[$i]['boolean_value']    = null;
                          $data[$i]['date_value']       = $v['save_text'];
                          break;
                      }else{
                          $arr['status'] = 105;
                          $this->response($arr,'json');
                          exit();
                      }
                  case 'bl':
                          $data[$i]['interger_value']   = null;
                          $data[$i]['char_value']       = null;
                          $data[$i]['decimal_value']    = null;
                          $data[$i]['boolean_value']    = $v['save_text'];
                          $data[$i]['date_value']       = null;
                          break;
                  case 'pic':
                          $data[$i]['interger_value']   = null;
                          $data[$i]['char_value']       = $v['save_text'];
                          $data[$i]['decimal_value']    = null;
                          $data[$i]['date_value']       = null;
                          $data[$i]['boolean_value']    = null;
                          break;
              }
              $data[$i]['modified_time'] = date('Y-m-d H:i:s',time());
              $i++;
          }
  	  }
  	  $res = \Think\Product\ProductBatchInfo::UpdateBatchInfo($da,$data);
  	  if ($res == 1) {
  	  	  $arr['status'] = 100;
  	  }else{
  	  	  $arr['status'] = 101;
  	  }
  	  $this->response($arr,'json');
  }


  /*
   * 查询单条产品批量表数据和变体
   */
   public function selBatchInfo(){
   	$da   = array();
   	$arr  = array();
    $id   = I('post.product_id');
    if(empty($id)){
      return false;
    }
    $res  = \Think\Product\ProductBatchInfo::SelBatchInfo($id);
    $max  = count($res);
    if($res){
        $data['status']  = 100;
        $data['Blength'] = $max - 1;
        $c = 0;
        foreach ($res as $key => $value) {
            foreach ($value as $ke => $variable) {
            	$arr['id']          = $variable['id'];
            	$arr['category_id'] = $variable['category_id'];
            	$arr['template_id'] = $variable['template_id'];
            	$arr['product_id']  = $variable['product_id'];
            	$arr['parent_id']   = $variable['parent_id'];
            	$arr['no']          = $variable['no'];
            	$arr['type']        = $variable['data_type_code'];
            	$arr['title']       = $variable['title'];
            	switch ($variable['data_type_code']) {
            		case 'int':
                        $arr['save_text']   = $variable['interger_value'];
                       break;
                    case 'char':
                        $arr['save_text']   = $variable['char_value'];
                       break;
                    case 'dc':
                        $arr['save_text']   = $variable['decimal_value'];
                       break;
                    case 'dt':
                        $arr['save_text']   = date('Y-m-d',strtotime($variable['date_value']));
                       break;
                    case 'bl':
                        $arr['save_text']   = $variable['boolean_value'];
                       break;
                    case 'pic':
                        $arr['save_text']   = $variable['char_value'];
                       break;
            	}
            $da[] = $arr;
            $arr  = array();
            }
            if($variable['parent_id'] == 0){
                if(!empty($da)){
                    $data['value']['body'] = $da;
                }
                $da = array();
            }else{
                $data['value']['variant'.$c] = $da;
                $da = array();
                $c++;
            }
        }   
    }else{
        $data['status'] = 101;
    }
    $this->response($data,'json');
  }


    /*
    * 查询所有批量表数据
    */
    public function getAllBatch(){
        $template_id = strip_tags(trim(I('post.template_id')));
        $arr         = array();
        $array       = array();
        $data        = array();
        $res = \Think\Product\ProductBatchInfo::GetAllBatchInfo($template_id);
        $i = 0; 
        foreach ($res as $key => $value) {
            foreach ($value as $ke => $variable) {
                $arr['id']            = $variable['id'];
                $arr['category_id']   = $variable['category_id'];
                $arr['template_id']   = $variable['template_id'];
                $arr['product_id']    = $variable['product_id'];
                $arr['parent_id']     = $variable['parent_id'];
                $arr['no']            = $variable['no'];
                $arr['type']          = $variable['data_type_code'];
                $arr['title']         = $variable['title'];
                switch ($variable['data_type_code']) {
                    case 'int':
                        $arr['save_text'] = $variable['interger_value'];
                        break;
                    case 'char':
                        $arr['save_text'] = $variable['char_value'];
                        break;
                    case 'dc':
                        $arr['save_text'] = $variable['decimal_value'];
                        break;
                    case 'dt':
                        $arr['save_text'] = date('Y-m-d',strtotime($variable['date_value']));
                        break;
                    case 'bl':
                        $arr['save_text'] = $variable['boolean_value'];
                        break;
                    case 'pic':
                        $arr['save_text'] = $variable['char_value'];
                        break;
                    default:
                        break;
                }
                $da[] = $arr;
                $arr  = array();
            }
            $data['body'.$i] = $da;
            $da = array();
            $i ++;
        }
        if($res){
            $array['status'] = 100;
            $array['value']  = $data;
        }
        $this->response($array,'json'); 
    }

    /**
   * 添加变体
   */
    public function addBatchVariant(){
        $data = array();
        $arr  = array();
        $text = I('post.realBody');
        $num  = I('post.addNum');
        $i = 0;
        foreach ($text as $key => $value) {
            $product_id = $value['product_id'];
            $data[$i]['category_id']    = $value['category_id'];
            $data[$i]['template_id']    = $value['template_id'];
            $data[$i]['no']             = $value['no'];
            $data[$i]['title']          = $value['title'];
            $data[$i]['length']         = $value['length'];
            $data[$i]['data_type_code'] = $value['type'];
            switch ($data[$i]['data_type_code']) {
                case 'int':
                    if(preg_match("/^[0-9]*$/",$value['save_text'])){
                        $data[$i]['interger_value'] = $value['save_text'];
                        break;
                    }else{
                        $arr['status'] = 103;
                        $this->response($arr,'json');
                        exit();  
                    }
                case 'char':
                    $length = strlen($value['save_text']);
                    if($length <= $data[$i]['length']){
                        $data[$i]['char_value'] = $value['save_text'];
                        break;                        
                    }else{
                        $arr['status'] = 106;
                        $this->response($arr,'json');
                        exit();                        
                    }

                case 'dc':
                    if(preg_match("/^(\d*\.)?\d+$/",$value['save_text'])){
                        $data[$i]['decimal_value'] = $value['save_text'];
                        break;
                    }else{
                        $arr['status'] = 104;
                        $this->response($arr,'json');
                        exit();  
                    } 
                case 'dt':
                    if(preg_match($this->dt,$value['save_text']) || preg_match($this->dt1,$value['save_text']) || preg_match($this->dt2,$value['save_text']) || preg_match($this->dt3,$value['save_text'])){
                        $data[$i]['date_value'] = $value['save_text'];
                        break;
                    }else{
                        $arr['status'] = 105;
                        $this->response($arr,'json');
                        exit();
                    }
                case 'bl':
                    $data[$i]['boolean_value']  = $value['save_text'];
                    break;
                case 'pic':
                    $data[$i]['char_value']     = $value['save_text'];
                    break;
            }
            $data[$i]['created_time']           = date('Y-m-d H:i:s',time());
            $data[$i]['modified_time']          = date('Y-m-d H:i:s',time());
            $i ++;
        }
        $res = \Think\Product\ProductBatchInfo::AddBatchVariantInfo($num,$product_id,$data);
        if($res == 1){
            $arr['status'] = 100;
        }else{
            $arr['status'] = 101;
        }
        $this->response($arr,'json');
    }
}
