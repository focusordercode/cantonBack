<?php
/**
 * User: Administrator
 * Date: 2016/11/1 0001
 * Time: 下午 5:09
 */
return [
	'URL_ROUTER_ON'   => true,

     //为rest相关操作设置路由，并设置默认路由返回404
    'URL_ROUTE_RULES' => [

        // login
        'login'   => 'Login/dologin', // 登陆
        'logout'  => 'Login/logout', // 登陆

        // Subarea
		'get/table'              => 'Subarea/getAllTable',    // 获取所有没有分区的表
        'get/zone'               => 'Subarea/getTable',       // 获取已经分区的所有表
        'get/fields'             => 'Subarea/getFields',      // 获取数据表的字段
        'establish/partition'    => 'Subarea/setSubarea',     // 为表创建分区
        'dilatation/partition'   => 'Subarea/Dilatation',     // 分区扩容
        'update/partition'       => 'Subarea/updateSubarea',  // 修改分区
        'check'                  => 'Subarea/check',          // 检测分区的情况（是否可以提交，提交是否成功等）
        'qingchu'                => 'Subarea/Eliminate',      // 分区出现失误时，清除缓存，重新使用
        'get/tablecount'         => 'Subarea/getTableCount',  // 获取每张表的数据统计

        // GetGlobalID
        'get/sysId'              => 'GetGlobalID/getSysId',

        // Custom
        'get/custom'             => 'Custom/getCustom',
        'delete/custom'          => 'Custom/delete',
        'update/custom'          => 'Custom/update',
        'post/custom'            => 'Custom/add',
        'vague/custom'           => 'Custom/selVague',

        // Logging
        'get/nowlog'             => 'Logging/getNowLog',      //获取当月的所有日志
        'get/fixlog'             => 'Logging/getFixLog',      //获取某个时间的所有日志
        'delete/log'             => 'Logging/delLog',         //删除日志
        'download/log'           => 'Logging/downloadLog',    //打包zip下载日志
        'detection/debug'        => 'Logging/checkDebug',     //检测调试模式的状态
        'debug'                  => 'Logging/OpenDebug',      //自主选择开启或关闭调试模式
        'get/track'              => 'Logging/userTrack',      //用户行为查询
        'delete/track'           => 'Logging/deleteTrack',    //用户行为删除

        // FileManager
		'get/folder'             => 'FileManager/GetFolder',  //获取不同类型的文件目录
        'get/file'               => 'FileManager/GetFlie',    //根据地址获取文件
        'del/file'               => 'FileManager/DeleteFile', //删除所选的文件

        // BusinessCode
        'set/businesscode'       => 'BusinessCode/setBusinessCode',  //生成业务编码
        'set/modelcode'          => 'BusinessCode/setModelCode',     //生成模块编码

        // BusinessModel
        'add/businessmodel'      => 'BusinessModel/addBusinessmodel',   //添加业务模块
        'get/businessmodel'      => 'BusinessModel/getBusinessmodel',   //获取业务模块
        'delete/businessmodel'   => 'BusinessModel/delBusinessmodel',   //删除业务模块
        'update/businessmodel'   => 'BusinessModel/updateBusinessmodel',//修改业务模块

    	// ProductCenter
		'set/productcenter'      => 'ProductCenter/setProductCenter',    //添加修改产品中心产品信息接口
        'get/allproductcenter'   => 'ProductCenter/getallProductCenter', //获取产品中心产品列表接口
        'get/productcenter'      => 'ProductCenter/getProductCenterInfo',//获取产品中心产品信息接口
        'delete/productcenter'   => 'ProductCenter/delProductCenter',    //删除产品中心产品信息接口
        'upload/productcenter'   => 'ProductCenter/uploadProductCenter', //上传文件读取数据
        'get/product2value'      => 'ProductCenter/getProduct2Value',    //获取已经关联了词库的产品

        // ProductInfo
        'autofill/product'       => 'ProductInfo/product_AutoFill',     // 自动填写资料表
        'receive/value'          => 'ProductInfo/receiveValue',         // 接收匹配完成的图片与词库内容
        'fill/batch'             => 'ProductInfo/batch_info',           // 自动填充产品批量表数据
        'get/completeInfo'       => 'ProductInfo/getFormInfo',          // 图片上传完服务器之后返回数据保存图片地址
        'data/check'             => 'ProductInfo/dataCheck',            // 数据检查
        'update/check_msg'       => 'ProductInfo/update_check_msg',     // 获取检查出错误的数据
        'update/checkinfo'       => 'ProductInfo/update_info',          // 调取检查出错误的数据
        'rollback/checkinfo'     => 'ProductInfo/rollbackProduct',      // 资料表撤销返回
        'post/info'              => 'ProductInfo/infoControl',
        'post/variant'           => 'ProductInfo/addVariant',
        'get/info'               => 'ProductInfo/getOneFormInfo',
        'del/info'               => 'ProductInfo/delInfo',
        'update/info'            => 'ProductInfo/updateInfo',
        'export'                 => 'ProductInfo/exportExcel',
        'count_product'          => 'ProductInfo/get_product_count',
        'back'                   => 'ProductInfo/back_step',
        'delete/product'         => 'ProductInfo/del_product',
        'view/info'              => 'ProductInfo/get_five',
        'search/batchTel'        => 'ProductInfo/GetBatchTel',           // 搜索与资料表关联的批量表模板
        'set/excel'              => 'ProductInfo/makeExcel',             // 生成批量表excel文件

        // ProductInfoForm
        'search/form'            => 'ProductInfoForm/search_form',       // 表格搜索
        'get/infoform'           => 'ProductInfoForm/getInfoForm',
        'get/infotemform'        => 'ProductInfoForm/getCTInfoForm',
        'get/oneform'            => 'ProductInfoForm/getOneForm',
        'get/tempinfoform'       => 'ProductInfoForm/getTemInfoForm',
        'add/infoform'           => 'ProductInfoForm/addInfoForm',
        'vague/formtitle'        => 'ProductInfoForm/vagueTitle',
        'del/infoform'           => 'ProductInfoForm/delInfoForm',
        'use/infoform'           => 'ProductInfoForm/useInfoForm',
        'stop/infoform'          => 'ProductInfoForm/stopInfoForm',
        'update/infoform'        => 'ProductInfoForm/updaInfoForm',

        // Upc
        'post/upc'               => 'Upc/upload_upc_file',
        'get/upc'                => 'Upc/get_upc_list',
        'use/upc'                => 'Upc/use_upc',
        'marry_upc'              => 'Upc/marry_upc',
        'unlock/upc'             => 'Upc/unLockUpc',      // upc解锁

        //Category
        'get/ancestors'          => 'Category/getAncestors',
        'get/sub'                => 'Category/getChildren',
        'post/sub'               => 'Category/addSub',
        'post/ancestors'         => 'Category/add',
        'delete/sub'             => 'Category/Delete' ,
        'update/name'            => 'Category/updaName',
        'vague/name'             => 'Category/selVague',
        'get/treeCategory'       => 'Category/treeCategory',

        //ImageCategory
        'get/imageancestors'     => 'ImageCategory/getAncestors',
        'get/imagesub'           => 'ImageCategory/getChildren',
        'post/imagesub'          => 'ImageCategory/addSub',
        'post/imageancestors'    => 'ImageCategory/add',
        'delete/imagesub'        => 'ImageCategory/Delete' ,
        'update/imagesub'        => 'ImageCategory/updaName',
        'vague/gallery'          => 'ImageCategory/selVague',
        'get/imagepath'          => 'ImageCategory/get_parent_path',
        'get/imagegallery'       => 'ImageCategory/get_gallery_by_id',
        'get/treeGallery'        => 'ImageCategory/treeGallery',

        // Picture
        'get/image'              => 'Picture/get_picture',
        'upload/image'           => 'Picture/upload',
        'update/image'           => 'Picture/edit_pic',
        'delete/image'           => 'Picture/del_picture',
        'recover/image'          => 'Picture/recover_pic',
        'clear/image'            => 'Picture/clear_rubbish',
        'get/imageInfo'          => 'Picture/update_pic_t',
        'update/imageInfo'       => 'Picture/do_update_pic_t',
        'marry/image'            => 'Picture/marry_pic',
        'ready/uploadImages'     => 'Picture/pic_upload_paration', // 上传图片准备读取展示接口
        'move/image'             => 'Picture/movePicture',//移动图片
        'add/tags'               => 'Picture/addTags',//批量添加图片标签

        // TemplateItem
        'get/templateitem'       => 'TemplateItem/get_item_template',
        'get/eliminateItem'      => 'TemplateItem/get_item_eliminate',//获取剔除模板默认关联后的模板表头数据
        'get/relationItem'       => 'TemplateItem/get_item_relation',//获取模板默认关联的字段数据
        'add/templateitem'       => 'TemplateItem/add_item_template',
        'delete/templateitem'    => 'TemplateItem/del_item_template',
        'update/templateitem'    => 'TemplateItem/edit_item_template',
        'copy/templateitem'      => 'TemplateItem/copy_item_template',
        'get/bootstrap'          => 'TemplateItem/getBootsttrap',
        'get/title_valid'        => 'TemplateItem/getTitleAndValid',
        'post/default'           => 'TemplateItem/create_default_template',
        'copy/batchItemTemplate' => 'TemplateItem/copy_batch_item_template',          // 批量表模板复制
        'marry/item'             => 'TemplateItem/marry_information_batch_by_form',   // 模板关联
        'validValue'             => 'TemplateItem/create_valid_value',                // 模板关联
        'upload/item'            => 'TemplateItem/upload_excel_header_file',          // 批量表模板上传
        'template_back'          => 'TemplateItem/template_back_step',
        'get/editdefault'        => 'TemplateItem/getEditDefault',        //获取需要编辑的默认值字段
        'get/checkrule'          => 'TemplateItem/getCheckRule',          //获取数据检查的字段与规则
        'get/contact'            => 'TemplateItem/getcontact',

        // Template
        'get/template'           => 'Template/get_template',
        'add/template'           => 'Template/add_template',
        'delete/template'        => 'Template/del_template',
        'update/template'        => 'Template/edit_template',
        'use/template'           => 'Template/use_template',
        'stop/template'          => 'Template/stop_template',
        'getById/template'       => 'Template/get_template_by_id',
        'get/linkage'            => 'Template/getLinkage',
        'vague/templatename'     => 'Template/vagueName',
        'get/template10'         => 'Template/get_template_by_category',
        'get/temvalue'           => 'Template/getitemValue',            //获取模板数据，可以获取全部也可以模糊获取

        // CenterItem
        'add/centeritem'         => 'CenterItem/addCenterItem',         //添加词库项目
        'update/centeritem'      => 'CenterItem/updaCenterItem',        //修改词库项目
        'get/allcenteritem'      => 'CenterItem/getAllCenterItem',      //获取词库项目列表
        'get/centeritem'         => 'CenterItem/getCenterItemInfo',     //获取词库项目信息
        'delete/centeritem'      => 'CenterItem/delCenterItem',         //删除词库项目
        'add/centeritemvalue'    => 'CenterItem/addCenterItemValue',    //添加词库信息
        'update/centeritemvalue' => 'CenterItem/updaCenterItemValue',   //修改词库信息
        'get/centeritemvalue'    => 'CenterItem/getCenterItemValue',    //获取词库项目全部信息
        'delete/centeritemvalue' => 'CenterItem/delCenterItemValue',    //删除词库项目信息
        'add/center2good'        => 'CenterItem/addCenter2Good',        //添加词库与产品关系
        'delete/center2good'     => 'CenterItem/delCenter2Good',        //删除词库与产品关系
        'get/center2good'        => 'CenterItem/getCenter2Good',        //获取词库与产品关系
        'get/good2centervalue'   => 'CenterItem/getGood2CenterValue',   //获取产品所关联的词库内容

        // User
        'get/user'               => 'User/getUserList',                 // 查询用户列表
        'add/user'               => 'User/doregister',                  // 添加用户列表
        'edit/user'              => 'User/doedit',                      // 编辑用户列表
        'delete/user'            => 'User/dodelete',                    // 删除用户列表

        // Org
        'get/org'                => 'Org/getOrgTree',                   // 获取机构列表包括角色分组
        'search/org'             => 'Org/searchOrg',                    // 搜索机构
        'getbyid/org'            => 'Org/getOrgById',                   // 获取机构 id
        'edit/org'               => 'Org/editOrg',                      // 编辑机构
        'add/org'                => 'Org/InsertOrg',                    // 添加机构
        'delete/org'             => 'Org/DeleteOrg',                    // 删除机构

        // AuthNodes
        'get/rule'               => 'AuthNodes/getRulesTree',           // 获取权限节点
        'getbyid/rule'           => 'AuthNodes/getRulesById',           // 通过id获取权限节点
        'edit/rule'              => 'AuthNodes/editRule',               // 编辑节点
        'add/rule'               => 'AuthNodes/insertRule',             // 添加节点
        'delete/rule'            => 'AuthNodes/deleteRule',             // 删除节点

        'get/roles'              => 'Roles/getRoles',//获取角色列表
        'update/roles'           => 'Roles/updateRoles',//修改角色信息
        'add/roles'              => 'Roles/addRoles',//添加角色
        'del/roles'              => 'Roles/delRoles',//删除角色
        'get/rule2role'          => 'Roles/getRule2Role',//读取角色的权限
        'allot/rule2role'        => 'Roles/allotRule2Role',//给角色分配权限

        'get/temformat'          => 'ProductInfoExtend/getTemFormat',//获取模板的数据格式
        'commit/data'            => 'ProductInfoExtend/dataCommit',//暂存

        'upload/pic'             => 'Picture/uploadPic',//上传图片
        'get/progress'           => 'Picture/getProgress',//获取上传图片的进度
        'copy/datas'             => 'TestCaseOperation/copyDatas',//复制数据做测试
        'del/datas'              => 'TestCaseOperation/delDatas',//删除测试数据
            
        // Ucenter
        'edit/personal'          => 'Ucenter/userEdit',                 // 普通用户编辑权限
        'usernav'                => 'Ucenter/navManage',                // 普通用户编辑权限
        'get/userbyid'           => 'Ucenter/getUserInfoById',             // 查询用户

    ],
];