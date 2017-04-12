<?php
/**
 * User: Administrator
 * Date: 2016/11/1 0001
 * Time: 下午 5:09
 * 权限节点配置
 */
return [
    'URL_OWNER_TYPE' => [
        'Upc'  => [
            'get/upc'    => [
                'Upc/get_upc_list',
            ],
            'insert/upc' => [
                'Upc/upload_upc_file','Upc/use_upc',
            ],
            'call/upc'   => [
                'Upc/marry_upc',
            ]
        ],

        'Subarea' => [
            'get/subarea'    => [
                'Subarea/getAllTable',
                'Subarea/getTable',
                'Subarea/getFields',
                'Subarea/check',
                
            ],
            'edit/subarea'   => [
                'Subarea/Dilatation',
                'Subarea/updateSubarea',
            ],
            'create/subarea' => [
                'Subarea/setSubarea',
            ],
            'delete/subarea' => [
                'Subarea/Eliminate',
            ],
            'count/table'    => [
                'Subarea/getTableCount',
            ],
        ],

        'Custom'  => [
            'get/custom'     => [
                'Custom/getCustom',
                'Custom/selVague',
            ],
            'delete/custom'  => [
                'Custom/delete',
            ],
            'edit/custom'    => [
                'Custom/update',
            ],
            'insert/custom'  => [
                'Custom/add',
            ],
        ],

        'Logging'  => [
            'get/logs'  => [
                'Logging/getNowLog',
                'Logging/getFixLog',
                'Logging/downloadLog',
                'Logging/checkDebug',
            ],
            'delete/logs'  => [
                'Logging/delLog',
            ],
            'edit/logs'  => [
                'Logging/OpenDebug',
            ],
            'get/track'  => [
                'Logging/userTrack',
            ],
            'delete/track'  => [
                'Logging/deleteTrack',
            ],
        ],

        'FileManager'  => [
            'get/file'  => [
                'FileManager/GetFolder',
                'FileManager/GetFlie',
            ],
            'delete/file' => [
                'FileManager/DeleteFile',
            ],
        ],

        'BusinessCode'  => [
            'set/model' => [
                'BusinessCode/setBusinessCode',
                'BusinessCode/setModelCode',
            ],
        ],

        'BusinessModel'  => [
            'insert/businessmodel'      => [
                'BusinessModel/addBusinessmodel',
            ],
            'get/businessmodel'      => [
                'BusinessModel/getBusinessmodel',
            ],
            'delete/businessmodel'   => [
                'BusinessModel/delBusinessmodel',
            ],
            'edit/businessmodel'   => [
                'BusinessModel/updateBusinessmodel',
            ],
        ],

    	'ProductCenter' => [
            'get/productcenter' => [
                'ProductCenter/getallProductCenter',
                'ProductCenter/getProductCenterInfo',
            ],
            'insert/productcenter' => [
                'ProductCenter/setProductCenter',
                'ProductCenter/getProduct2Value',
                'ProductCenter/uploadProductCenter',
            ],
            'delete/productcenter' => [
                'ProductCenter/delProductCenter',
            ],
        ],

        'ProductInfo'   => [
            'insert/info'  => [
                'GetGlobalID/getSysId',
                'ProductInfo/product_AutoFill',
                'ProductInfo/receiveValue',
                'ProductInfo/batch_info',
                'ProductInfo/getFormInfo',
                'ProductInfo/dataCheck',
                'ProductInfo/update_check_msg',
                'ProductInfo/addVariant',
                'ProductInfo/infoControl',
                'ProductInfo/getOneFormInfo',
                'ProductInfo/GetBatchTel',
                'ProductInfo/makeExcel',
                'ProductInfo/get_product_count',
                'ProductInfo/exportExcel',
                'Picture/pic_upload_paration',
                'Picture/marry_pic',
                'ProductInfoExtend/dataCommit',
                'InsertExtension/tableAutomatic',
            ],
            'edit/info'  => [
                'ProductInfo/rollbackProduct',
                'ProductInfo/back_step',
            ],
            'delete/info' => [
                'ProductInfo/del_product',
                'ProductInfo/delInfo',
            ],
        ],

        'ProductInfoForm'  => [
            'get/form'  => [
                'ProductInfoForm/search_form',
                'ProductInfoForm/getInfoForm',
                'ProductInfoForm/getCTInfoForm',
                'ProductInfoForm/getOneForm',
                'ProductInfoForm/getTemInfoForm',
                'ProductInfoForm/vagueTitle',
                'ProductInfoForm/getfrom',
            ],
            'edit/form'  => [
                'ProductInfoForm/useInfoForm',
                'ProductInfoForm/stopInfoForm',
                'ProductInfoForm/updaInfoForm',
            ],
            'delete/form'  => [
                'ProductInfoForm/delInfoForm',
            ],
            'insert/form'  => [
                'ProductInfoForm/addInfoForm',
                'GetGlobalID/get_form_number',
            ],
            'transfer/form'  => [
                'ProductInfoForm/transferForm',
                'Custom/TransformSearchCustomer',
            ],
        ],

        'Category' => [
            'get/category'    => [
                'Category/getAncestors',
                'Category/getChildren',
                'Category/selVague',
                'Category/treeCategory',
            ],
            'insert/category' => [
                'Category/addSub',
                'Category/add',
            ],
            'edit/category'   => [
                'Category/updaName',
            ],
            'delete/category' => [
                'Category/Delete',
            ],
        ],

        'ImageCategory' => [
            'get/imagecategory'    => [
                'ImageCategory/getAncestors',
                'ImageCategory/getChildren',
                'ImageCategory/selVague',
                'ImageCategory/get_parent_path',
                'ImageCategory/get_gallery_by_id',
                'ImageCategory/treeGallery',
            ],
            'insert/imagecategory' => [
                'ImageCategory/addSub',
                'ImageCategory/add',
            ],
            'edit/imagecategory'   => [
                'ImageCategory/updaName',
            ],
            'delete/imagecategory' => [
                'ImageCategory/Delete',
            ],
        ],

        'Picture'  => [
            'get/image'    => [
                'Picture/get_picture',
                'Picture/getProgress',
            ],
            'edit/image'   => [
                'Picture/edit_pic',
                'Picture/update_pic_t',
                'Picture/do_update_pic_t',
                'picture/addTags',
                'Picture/movePicture',
                'Picture/uploadPic',
            ],
            'insert/image' => [
                'Picture/upload',
            ],
            'delete/image' => [
                'Picture/del_picture',
                'Picture/recover_pic',
                'Picture/clear_rubbish',
            ]
        ],

        'TemplateItem' => [
            'get/item'    => [
                'TemplateItem/get_item_template',
                'TemplateItem/get_item_eliminate',
                'TemplateItem/get_item_relation',
                'TemplateItem/getBootsttrap',
                'TemplateItem/getTitleAndValid',
                'ProductInfoExtend/getTemFormat',
                'TemplateItem/getcontact',
            ],
            'insert/item' => [
                'TemplateItem/add_item_template',
                'TemplateItem/create_valid_value',
                'TemplateItem/marry_information_batch_by_form',
                'TemplateItem/upload_excel_header_file',
            ],
            'edit/item'   => [
                'TemplateItem/edit_item_template',
                'TemplateItem/template_back_step',
                'TemplateItem/getEditDefault',
                'TemplateItem/getCheckRule',
            ],
            'delete/item' => [
                'TemplateItem/del_item_template',
            ],
        ],

        'Template'    => [
            'get/template'    => [
                'Template/get_template',
                'Template/get_template_by_id',
                'Template/getitemValue',
                'Template/getLinkage',
                'Template/vagueName',
                'Template/get_template_by_category',
            ],
            'insert/template' => [
                'Template/add_template',
            ],
            'edit/template'   => [
                'Template/edit_template',
                'Template/use_template',
                'Template/stop_template',
            ],
            'delete/template' => [
                'Template/del_template',
            ],
        ],

        'CenterItem'   =>  [
            'get/centeritem'  => [
                'CenterItem/getAllCenterItem',
                'CenterItem/getCenterItemValue',
                'CenterItem/getCenter2Good',
                'CenterItem/getGood2CenterValue',
                'CenterItem/getCenterItemInfo',
            ],
            'edit/centeritem' => [
                'CenterItem/updaCenterItem',
                'CenterItem/updaCenterItemValue',
            ],
            'delete/centeritem' => [
                'CenterItem/delCenterItem',
                'CenterItem/delCenterItemValue',
                'CenterItem/delCenter2Good',
            ],
            'insert/centeritem' => [
                'CenterItem/addCenterItem',
                'CenterItem/addCenterItemValue',
                'CenterItem/addCenter2Good',
            ],
        ],

        'User' => [
            'get/user' => [
                'User/getUserList',
            ],
            'edit/user' => [
                'User/doedit',
            ],
            'insert/user' => [
                'User/doregister',
            ],
            'delete/user' => [
                'User/dodelete',
            ],
        ],

        'Org' => [
            'get/org' => [
                'Org/getOrgTree','Org/getOrgById','Org/searchOrg'
            ],
            'edit/org' => [
                'Org/editOrg',
            ],
            'insert/org' => [
                'Org/InsertOrg',
            ],
            'delete/org' => [
                'Org/DeleteOrg',
            ],
        ],

        'AuthNodes' => [
            'get/rule' => [
                'AuthNodes/getRulesTree',
                'AuthNodes/getRulesById'
            ],
            'edit/rule' => [
                'AuthNodes/editRule',
            ],
            'insert/rule' => [
                'AuthNodes/insertRule',
            ],
            'delete/rule' => [
                'AuthNodes/deleteRule',
            ],
        ],

        'Roles' => [
            'get/roles' => [
                'Roles/getRoles','Roles/getRule2Role',
            ],
            'edit/roles' => [
                'Roles/updateRoles',
            ],
            'delete/roles' => [
                'Roles/delRoles',
            ],
            'insert/roles' => [
                'Roles/addRoles',
            ],
            'allot/roles' => [
                'Roles/allotRule2Role',
            ],
        ],
        'Coupon' => [
            'coupon/couponscreate' => [
                'Coupon/couponscreate',
            ],
            'coupon/releasecoupon' => [
                'Coupon/releaseCoupon',
            ],
            'coupon/couponslist' => [
                'Coupon/couponsList',
            ],
            'coupon/couponsdelete' => [
                'Coupon/couponsDelete',
            ],
           'coupon/couponsvoid' => [
                'Coupon/couponsVoid',
            ],
            'coupon/couponfind' => [
                'Coupon/couponFind',
            ],
           'coupon/couponlist' => [
                'Coupon/couponList',
            ],
            'coupon/couponvoid' => [
                'Coupon/couponVoid',
            ],
           'coupon/couponedit' => [
                'Coupon/couponEdit',
            ],
            'coupon/coupondownload' => [
                'Coupon/couponDownload',
            ],
           
        ],
        
    ],
];