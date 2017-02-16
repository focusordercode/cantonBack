<?php  
  class Pthread extends \Thread   
  {  
    public $return_data;
    public $receive_data;
    public function __construct($data)  
    {  
          $this->receive_data = $data;
    }  
  
    public function run()  
    { 
      $check = json_decode(imageCheck($this->receive_data['id'],$this->receive_data['category_id'],$this->receive_data['ids']));
      if($check->status == 100){
        foreach ($check->value as $key => $value) {
          $arr[$key] = $value;
          $arr[$key]->photo = __ROOT__.substr($this->receive_data['tmpFile'], 1);
        }
        $this->return_data = $arr;
      }elseif($check->status == 102){
        $upload = json_decode(imageUpload($this->receive_data['tmpName'],$this->receive_data['tmpFile'],$this->receive_data['tmpType'],$this->receive_data['form_id'],$this->receive_data['ids'],$this->receive_data['num'],$this->receive_data['category_id'],$this->receive_data['id']));
        if($upload->status == 100){
          foreach ($check->value as $key => $value) {
            $arr[$key] = $value;
            $arr[$key]->photo = __ROOT__.substr($this->receive_data['tmpFile'], 1);
          }
          $this->return_data = $arr;
        }else{
          $arrs['status_msg'] = '';
          $arrs['ids'] = $this->receive_data['ids'];
          $arrs['image_url'] = __ROOT__.substr($this->receive_data['tmpFile'], 1);
          $arrs['photo'] = __ROOT__.substr($this->receive_data['tmpFile'], 1);
          $this->return_data = $arrs;
        } 
      }else{
        $arrs['status_msg'] = '';
        $arrs['ids'] = $this->receive_data['ids'];
        $arrs['image_url'] = __ROOT__.substr($this->receive_data['tmpFile'], 1);
        $arrs['photo'] = __ROOT__.substr($this->receive_data['tmpFile'], 1);
        $this->return_data = $arrs;
      } 
    }  
  }

