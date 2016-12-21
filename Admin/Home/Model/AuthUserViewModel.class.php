<?php
namespace Home\Model;
use Think\Model\ViewModel;

class AuthUserViewModel extends ViewModel{
	public $viewFields=array(
		'auth_user' => array('id','username'),
		'auth_role_user' => array('role_id','_on' => 'auth_user.id = auth_role_user.user_id'),
		'auth_role' => array('name' => 'role_name', '_on' => 'auth_role_user.role_id = auth_role.id'),
		'auth_role_org' => array('org_id','_on' => 'auth_role.id = auth_role_org.role_id'),
		'auth_org' => array('name' => 'org_name','_on' => 'auth_org.id = auth_role_org.org_id'),
	);
}