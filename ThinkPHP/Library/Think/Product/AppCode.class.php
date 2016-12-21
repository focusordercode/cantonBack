<?php
namespace Think\Product;
/**
 * 类目管理类
 */
class AppCode {
	protected $id;
	protected $app_code;
	protected $app_name;
	protected $reark;

	/**
     * 获取所有模块
     */
	static function GetAppCode(){
		$sys_app=M('sys_app');
		$filed="app_name,app_code";
		$sql=$sys_app->field($filed)->select();
		return($sql);
	}
}