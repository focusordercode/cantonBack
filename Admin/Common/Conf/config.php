<?php
return array(
    'LOAD_EXT_CONFIG'       => 'db,default_config,auth_conf,routes,path_conf',
    
    'MODULE_ALLOW_LIST'     => array('Home','Admin','User'),
    'DEFAULT_MODULE'        => 'Home',

    'DB_TABLE_NAME'         => 'table_to_subarea',//用于记录分区情况的表名
    'EXCEL_TEMPLATE_NAME'   => 'Template',


    'LOG_RECORD'            => true,  // 进行日志记录
    'LOG_RECORD_LEVEL'      => array('EMERG','ALERT','CRIT','ERR','WARN','NOTIC','INFO','DEBUG','SQL'),  // 允许记录的日志级别
    'DB_FIELDS_CACHE'       => false, //数据库字段缓存


    'SPVE_PATH'             => './Public/data/',
    'BATCH_SAVE_PATH'       => './Public/Template/Data/',

    'ENCODE_USERID'         => 'user_md5',
    'ENCODE_KEY'            => 'key_md5',
    'ENCODE_KEY_CODE'       => 'iuHIhUHKhpoMKLgu0423',   // 登陆加密密钥 key

    'GALLERY_CONUT'          =>  5,
); 