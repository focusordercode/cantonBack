<?php
return array(
    'LOAD_EXT_CONFIG' => 'default_config,auth_conf,routes',
	//'配置项'=>'配置值'
	'DB_TYPE'               =>  'mysql',     // 数据库类型
    'DB_HOST'               =>  'localhost', // 服务器地址
    'DB_NAME'               =>  'canton',    // 数据库名
    'DB_USER'               =>  'root',      // 用户名
    'DB_PWD'                =>  'root',      // 密码
    'DB_PORT'               =>  '3306',      // 端口
    'DB_PREFIX'             =>  'tbl_',      // 数据库表前缀
    
    'MODULE_ALLOW_LIST'    =>    array('Home','Admin','User'),
    'DEFAULT_MODULE'       =>    'Home',

    'DB_TABLE_NAME'         =>  'table_to_subarea',//用于记录分区情况的表名
    'EXCEL_TEMPLATE_NAME'  =>    'Template',


    'LOG_RECORD' => true,  // 进行日志记录
    'LOG_RECORD_LEVEL'     =>   array('EMERG','ALERT','CRIT','ERR','WARN','NOTIC','INFO','DEBUG','SQL'),  // 允许记录的日志级别
    'DB_FIELDS_CACHE'=> false, //数据库字段缓存


    'SPVE_PATH'            =>    './Public/data/',
    'DOWNLOAD_URL'         =>    'http://192.168.1.42/canton',
    'ENTRANCE'			   =>    'E:\www\canton\index.php',
    'MY_HTTP_PATH'         =>    'http://192.168.1.42/canton',
    'BATCH_SAVE_PATH'      =>    './Public/Template/Data/',
    'SAVE_PATH'            =>    'e:/www/canton',

    'ENCODE_USERID'         => 'user_md5',
    'ENCODE_KEY'            => 'key_md5',
    'ENCODE_KEY_CODE'       => 'iuHIhUHKhpoMKLgu0423',   // 登陆加密密钥 key

    'GALLERY_CONUT'          =>  5,
); 