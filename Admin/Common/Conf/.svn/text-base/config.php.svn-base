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
    // 'SHOW_RUN_TIME' =>  false,  //运行时间显示
    // 'SHOW_ADV_TIME' =>  false,  //显示详细的运行时间
    // 'SHOW_DB_TIMES' =>  false,  //显示数据库的操作次数
    // 'SHOW_CACHE_TIMES'=>false,  //显示缓存操作次数
    // 'SHOW_USE_MEM'  =>  false,  //显示内存开销

    // 'SHOW_PAGE_TRACE' =>true, // 显示页面Trace信息

    'URL_ROUTER_ON'   => true,
 /*
     //为rest相关操作设置路由，并设置默认路由返回404
    'URL_ROUTE_RULES' => array(
        //数据分区
        array('get/table','Subarea/getAllTable'),//获取所有没有分区的表
        array('get/zone','Subarea/getTable'),//获取已经分区的所有表
        array('get/fields','Subarea/getFields'),//获取数据表的字段
        array('establish/partition','Subarea/setSubarea'),//为表创建分区
        array('dilatation/partition','Subarea/Dilatation'),//分区扩容
        array('update/partition','Subarea/updateSubarea'),//修改分区
        array('check','Subarea/check'),//检测分区的情况（是否可以提交，提交是否成功等）
        array('qingchu','Subarea/Eliminate'),//分区出现失误时，清除缓存，重新使用


    	//类目路由
        array('get/ancestors','Category/getAncestors'),
        array('get/sub','Category/getChildren'),
        array('post/sub','Category/addSub'),
        array('post/ancestors','Category/add'),
        array('delete/sub','Category/Delete' ),
        array('update/name','Category/updaName'),
        array('vague/name','Category/selVague'),
        array('get/treeCategory','Category/treeCategory'),

        //模块路由
        array('get/appcode','AppCode/getAppCode'),

        //产品路由
        array('get/title','Product/getTitle','status=1'),
        array('get/categotitle','Product/getCategoTitle'),
        array('get/productinfo','Product/getInfo'),
        //测试路由
        array('get/test','Test/domExcle'),
        //模板
        array('post/customtem','CustomTem/establishTem'),
        array('tem/:catego','CustomTem/formTem'),
        //array('dom','CustomTem/domExcle'),

        //测试导出成Excel文件
        array('dom/t','Test/domExcle'),
        array('testget','Test/testPOST'),

        //获取全局Id
        array('get/sysId','GetGlobalID/getSysId'),
        array('get/formNumber','GetGlobalID/get_form_number'),

        //产品资料
        array('post/info','ProductInfo/infoControl'),
        array('post/variant','ProductInfo/addVariant'),
        array('get/info','ProductInfo/getOneFormInfo'),
        array('del/info','ProductInfo/delInfo'),
        array('update/info','ProductInfo/updateInfo'),
        array('export','ProductInfo/exportExcel'),
        array('count_product','ProductInfo/get_product_count'),
        array('back','ProductInfo/back_step'),
        array('delete/product','ProductInfo/del_product'),
        array('marry_upc','ProductInfo/marry_upc'),


        //产品批量
        array('post/batchinfo','ProductBatchInfo/AddBatchInfo'),
        array('get/onebatch','ProductBatchInfo/selBatchInfo'),
        array('del/batchinfo','ProductBatchInfo/delBatchInfo'),
        array('update/batchinfo','ProductBatchInfo/updateBatchInfo'),

        //日志
        array('read/logs','ReadLog/ReadLogs'),

        //=============================================
        array('post/upc','Upc/upload_upc_file'),
        array('get/upc','Upc/get_upc_list'),
        array('use/upc','Upc/use_upc'),
        //产品资料模板路由
        array('get/template','Template/get_template'),
        array('add/template','Template/add_template'),
        array('delete/template','Template/del_template'),
        array('update/template','Template/edit_template'),
        array('use/template','Template/use_template'),
        array('stop/template','Template/stop_template'),
        array('getById/template','Template/get_template_by_id'),
        array('get/linkage','Template/getLinkage'),
        array('vague/templatename','Template/vagueName'),
        array('get/template10','Template/get_template_by_category'),

        array('get/templateitem','TemplateItem/get_item_template'),
        array('get/eliminateItem','TemplateItem/get_item_eliminate'),//获取剔除模板默认关联后的模板表头数据
        array('get/relationItem','TemplateItem/get_item_relation'),//获取模板默认关联的字段数据
        array('add/templateitem','TemplateItem/add_item_template'),
        array('delete/templateitem','TemplateItem/del_item_template'),
        array('update/templateitem','TemplateItem/edit_item_template'),
        array('copy/templateitem','TemplateItem/copy_item_template'),
        array('get/bootstrap','TemplateItem/getBootsttrap'),
        array('get/title_valid','TemplateItem/getTitleAndValid'),
        array('post/default','TemplateItem/create_default_template'),
        array('copy/batchItemTemplate','TemplateItem/copy_batch_item_template'), // 批量表模板复制
        array('marry/item','TemplateItem/marry_information_batch_by_form'),      // 模板关联
        array('validValue','TemplateItem/create_valid_value'),                   // 模板关联
        array('upload/item','TemplateItem/upload_excel_header_file'),            // 批量表模板上传
        array('template_back','TemplateItem/template_back_step'),
        array('get/contact','TemplateItem/getcontact'),//获取当前批量表模板与资料表模板的关联关系

        //产品资料表格
        array('get/infoform','ProductInfoForm/getInfoForm'),
        array('get/infotemform','ProductInfoForm/getCTInfoForm'),
        array('get/oneform','ProductInfoForm/getOneForm'),
        array('get/tempinfoform','ProductInfoForm/getTemInfoForm'),
        array('add/infoform','ProductInfoForm/addInfoForm'),
        array('vague/formtitle','ProductInfoForm/vagueTitle'),
        array('del/infoform','ProductInfoForm/delInfoForm'),
        array('use/infoform','ProductInfoForm/useInfoForm'),
        array('stop/infoform','ProductInfoForm/stopInfoForm'),
        array('update/infoform','ProductInfoForm/updaInfoForm'),
        array('get/completeInfo','ProductInfo/getFormInfo'),
        array('view/info','ProductInfo/get_five'),
        array('search/form','ProductInfoForm/search_form'),       // 表格搜索

        //图册路由
        array('get/imageancestors','ImageCategory/getAncestors'),
        array('get/imagesub','ImageCategory/getChildren'),
        array('post/imagesub','ImageCategory/addSub'),
        array('post/imageancestors','ImageCategory/add'),
        array('delete/imagesub','ImageCategory/Delete' ),
        array('update/imagesub','ImageCategory/updaName'),
        array('vague/gallery','ImageCategory/selVague'),
        array('get/imagepath','ImageCategory/get_parent_path'),
        array('get/imagegallery','ImageCategory/get_gallery_by_id'),
        array('get/treeGallery','ImageCategory/treeGallery'),

        // 图片
        array('get/image','Picture/get_picture'),
        array('upload/image','Picture/upload'),
        array('update/image','Picture/edit_pic'),
        array('delete/image','Picture/del_picture'),
        array('recover/image','Picture/recover_pic'),
        array('clear/image','Picture/clear_rubbish'),
        array('get/imageInfo','Picture/update_pic_t'),
        array('update/imageInfo','Picture/do_update_pic_t'),
        array('marry/image','Picture/marry_pic'),

        // 客户信息管理
        array('get/custom','Custom/getCustom'),
        array('delete/custom','Custom/delete'),
        array('update/custom','Custom/update'),
        array('post/custom','Custom/add'),
        array('vague/custom','Custom/selVague'),


        array('get/nowlog','Logging/getNowLog'),//获取当月的所有日志
        array('get/fixlog','Logging/getFixLog'),//获取某个时间的所有日志
        array('delete/log','Logging/delLog'),//删除日志
        array('download/log','Logging/downloadLog'),//打包zip下载日志
        array('detection/debug','Logging/checkDebug'),//检测调试模式的状态
        array('debug','Logging/OpenDebug'),//自主选择开启或关闭调试模式

        array('search/batchTel','ProductInfo/GetBatchTel'),      //搜索与资料表关联的批量表模板
        array('set/excel','ProductInfo/makeExcel'),              //生成批量表excel文件

        array('get/folder','FileManager/GetFolder'),//获取不同类型的文件目录
        array('get/file','FileManager/GetFlie'),//根据地址获取文件
        array('del/file','FileManager/DeleteFile'),//删除所选的文件


        //产品中心产品
        array('set/productcenter','ProductCenter/setProductCenter'),//添加修改产品中心产品信息接口
        array('get/allproductcenter','ProductCenter/getallProductCenter'),//获取产品中心产品列表接口
        array('get/productcenter','ProductCenter/getProductCenterInfo'),//获取产品中心产品信息接口
        array('delete/productcenter','ProductCenter/delProductCenter'),//删除产品中心产品信息接口
        array('upload/productcenter','ProductCenter/uploadProductCenter'),//上传文件读取数据
        array('get/product2value','ProductCenter/getProduct2Value'),//获取已经关联了词库的产品

        //词库项目
        array('add/centeritem','CenterItem/addCenterItem'),//添加词库项目
        array('update/centeritem','CenterItem/updaCenterItem'),//修改词库项目
        array('get/allcenteritem','CenterItem/getAllCenterItem'),//获取词库项目列表
        array('get/centeritem','CenterItem/getCenterItemInfo'),//获取词库项目信息
        array('delete/centeritem','CenterItem/delCenterItem'),//删除词库项目
        array('add/centeritemvalue','CenterItem/addCenterItemValue'),//添加词库信息
        array('update/centeritemvalue','CenterItem/updaCenterItemValue'),//修改词库信息
        array('get/centeritemvalue','CenterItem/getCenterItemValue'),//获取词库项目全部信息
        array('delete/centeritemvalue','CenterItem/delCenterItemValue'),//删除词库项目信息
        array('add/center2good','CenterItem/addCenter2Good'),//添加词库与产品关系
        array('delete/center2good','CenterItem/delCenter2Good'),//删除词库与产品关系
        array('get/center2good','CenterItem/getCenter2Good'),//获取词库与产品关系

        array('set/businesscode','BusinessCode/setBusinessCode'),//生成业务编码
        array('set/modelcode','BusinessCode/setModelCode'),//生成模块编码

        array('add/businessmodel','BusinessModel/addBusinessmodel'),//添加业务模块
        array('get/businessmodel','BusinessModel/getBusinessmodel'),//获取业务模块
        array('delete/businessmodel','BusinessModel/delBusinessmodel'),//删除业务模块
        array('update/businessmodel','BusinessModel/updateBusinessmodel'),//修改业务模块


        array('get/good2centervalue','CenterItem/getGood2CenterValue'),//获取产品所关联的词库内容
        array('get/editdefault','TemplateItem/getEditDefault'),//获取需要编辑的默认值字段
        array('get/checkrule','TemplateItem/getCheckRule'),//获取数据检查的字段与规则
        array('autofill/product','ProductInfo/product_AutoFill'),//自动填写资料表
        array('receive/value','ProductInfo/receiveValue'),//接收匹配完成的图片与词库内容


        array('fill/batch','ProductInfo/batch_info'),  // 自动填充产品批量表数据
        array('get/completeInfo','ProductInfo/getFormInfo'), // 图片上传完服务器之后返回数据保存图片地址
        array('ready/uploadImages','Picture/pic_upload_paration'), // 上传图片准备读取展示接口
        array('data/check','ProductInfo/dataCheck'),//数据检查

        array('update/check_msg' , 'ProductInfo/update_check_msg'), // 获取检查出错误的数据
        array('update/checkinfo' , 'ProductInfo/update_info'), // 调取检查出错误的数据
        array('rollback/checkinfo' , 'ProductInfo/rollbackProduct'), // 资料表撤销返回

        array('get/temvalue','Template/getitemValue'),//获取模板数据，可以获取全部也可以模糊获取

        array('get/temformat','ProductInfoExtend/getTemFormat'),//获取模板的数据格式
        array('commit/data','ProductInfoExtend/dataCommit'),//暂存
        array('upload/pic','Picture/uploadPic'),//上传图片
        array('get/progress','Picture/getProgress'),//获取上传图片的进度
        array('copy/datas','TestCaseOperation/copyDatas'),//复制数据做测试
        array('del/datas','TestCaseOperation/delDatas'),//删除测试数据

        
    ),
*/
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