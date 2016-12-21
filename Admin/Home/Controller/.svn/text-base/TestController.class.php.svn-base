<?php
namespace Home\Controller;
use Think\Controller\RestController;
header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Methods:POST,GET');
header('Access-Control-Allow-Credentials:true'); 
header("Content-Type: application/json;charset=utf-8");

class TestController extends RestController{
	public function domExcle(){
       // $filename="amazon";
       //  $headArr=122;
       //  $data=$_SESSION['data'];
       //  print_r($data);
       //  $this->getExcel($filename,$headArr,$data);
       //  //去THinkPHP手册中进行查找  
       $array=GetSysId('product_information',10);
       print_r($array);
    }


function getExcel($fileName,$headArr,$data){
    $a=array();
    $array=array();
	//导入PHPExcel类库，因为PHPExcel没有用命名空间，只能inport导入
    import("Org.Util.PHPExcel");
    import("Org.Util.PHPExcel.Writer.Excel5");
    import("Org.Util.PHPExcel.IOFactory.php");
    
    //创建文件名
    $date = date("Y_m_d",time());
    $fileName .= "_{$date}.xls";

    //创建PHPExcel对象，注意不能少了
    $objPHPExcel = new \PHPExcel();
    $objProps = $objPHPExcel->getProperties();


    //设置表头
    $key = ord("A");
     foreach ($data as $ke => $value) {
       foreach ($value as $k => $values) {
        $colum = chr($key);
        $objPHPExcel->setActiveSheetIndex(0) ->setCellValue($colum.'6', $k);
        $a[]=$k;
        $array[$k]=$colum;
        $key += 1;
    }
    }
    print_r($array);
    $i=7;
     $objActSheet = $objPHPExcel->getActiveSheet();
    foreach ($data as $key => $value) {
       foreach ($value as $k => $values) {
        foreach($a as $v){
            if($k==$v){
                $objActSheet->setCellValue($array[$v].$i,$values);  
                
            }
        }
     } 
     
       $i++;
    }
    
 
    $fileName = iconv("utf-8", "gb2312", $fileName);
        //重命名表
        //$objPHPExcel->getActiveSheet()->setTitle('test');
        //设置活动单指数到第一个表,所以Excel打开这是第一个表
        $objPHPExcel->setActiveSheetIndex(0);
        ob_clean();
        header('Content-Type: application/vnd.ms-excel');
        header("Content-Disposition: attachment;filename=\"$fileName\"");
        header('Cache-Control: max-age=0');

        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output'); //文件通过浏览器下载
        exit;
}

    public function testPOST(){
        $test=file_get_contents("php://input");
        //$data = IS_POST('gridData');
             //$arr = json_decode($data);
          //$test=I('gridData');
          //$test = $_POST['gridData'];   
            //$test=$GLOBALS['HTTP_RAW_POST_DATA'];
     $xml = urldecode($test); 
    //$x=simplexml_load_string($test);
    // $j=0;
    // for($i=0;$i<3;$i++){
       $g=strstr($xml,'gridData[20]');
       parse_str($g,$allData);
       //$arr[]=$allData;
       $j=$j+19;
    // }
    
     //$m=explode('&',$xml);
     
      //$t=$xml.gridColumns;
            $num=count($gridData);
            $array['status']=100;
            //$array['num']=$num;
            $array['value']=$allData;
            $this->response($array,'json');
        
    }

    public function testjava(){
      $curl = new curl();
      require_once("Java.inc");
      $ta  = java("Org.Java.TestJava");

      echo java_values($ta->ts());//输出“from ts”
    } 

    public function testCheckDel(){
      $model = I('post.model');
      $id = I('post.id');
      $res = checkDeleteLimit($model,$id);
      $this->response($res,'json');
    }

    public function testMem(){
      $cache = \Think\Cache::getInstance('Memcache');
      $a = $cache->set('key',123456,3600);
    }

    public function getMem()
    {
      $cache = \Think\Cache::getInstance('Memcache');
      $a = $cache->get('key');
      echo($a);
    }

    public function testr(){
      $res  = \Think\Category::GetSub(1);
      foreach ($res as $key => $value) {
        if(is_dir('./Pictures/' . trim($value['en_name']))){
          @mkdir('./Pictures/' . trim($value['en_name']));
        }
      }
      print_r($res);
    }

      //整理文件夹
     public function testrss(){
      set_time_limit(0);
      $res  = \Think\Category::GetSub(1);
      $gallery = M('product_gallery');
      $pic = M('product_picture');
      foreach ($res as $key => $value) {
        if(!is_dir('./Pictures/'.str_replace(' ','_',trim($value['en_name'])))){
          mkdir('./Pictures/'.str_replace(' ','_',trim($value['en_name'])));
        }

        $file_name = './Pictures/'.str_replace(' ','_',trim($value['en_name']));
        $sql = $gallery->where("category_id=%d",array($value['id']))->select();

        foreach ($sql as $keys => $values) {
          if(!empty($values['dir'])){
            rename('./Pictures/'.str_replace(' ','_',trim($values['en_name'])),$file_name.'/'.str_replace(' ','_',trim($values['en_name'])));
              $data['dir'] = $file_name.'/'.str_replace(' ','_',trim($values['en_name']));
              $gallery->where("id=%d",array($values['id']))->data($data)->save(); 
          }
          $das['path'] = $data['dir'];
          $upda = $pic->where("gallery_id=%d",array($values['id']))->data($das)->save();          
        }
      }
      print_r($res);
    }

    public function updadir()
    {
      $gallery = M('product_gallery');
      $category = M('product_category');
      $sql = $gallery->select();
      foreach ($sql as $key => $value) {
        $query = $category->field("en_name")->where("id=%d",array($value['category_id']))->find();
        $data['dir'] = './Pictures/'.str_replace(' ','_',trim($query['en_name'])).'/'.str_replace(' ','_',trim($value['en_name']));
        $upda = $gallery->data($data)->where("id=%d",array($value['id']))->save();
      }
    }
}