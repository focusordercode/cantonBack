<?php
namespace Home\Controller;
use Think\Controller;
/*
 * 用户中心
 * @author cxl,lrf
 * @modify 2016/12/22
 */
class OrgController extends BaseController
{
    /*
     * 组织机构树状结构
     * @param 包含机构的最底层包含角色
     * */
    public function getOrgTree(){
        $m = M('auth_org');
        // 是否需要展示角色分组
        $isGetRole = isset($_POST['isGetRole']) ? (int)I('isGetRole') : 0;
        $enabled   = isset($_POST['enabled']) ? (int)I('enabled') : 1;
        // 查询出所有的机构
        $rules = $m->where("enabled=$enabled")->field('id,name,p_id,introduce,enabled')->order('no asc')->select();
        if($isGetRole == 1){
            foreach($rules as $key => $value){
                // 查询每个机构所拥有的角色组
                $roles = M()
                    ->table("tbl_auth_role_org ro,tbl_auth_role r")
                    ->where("ro.`role_id`=r.`id` AND ro.`org_id`=".$value['id']." AND r.enabled=1")
                    ->field("r.id rid,r.`name` rname,r.`permissions`")
                    ->select();
                if($roles){
                    $rules[$key]['roles'] = $roles;
                }
            }
        }
        $this->response(['status' => 100,'value' => pre($rules)],'json');
    }

    /*
     * 修改组织机构
     * @param p_id    上级id
     * @param org_id  需编辑的id
     * @param no      排序
     * */
    public function editOrg(){
        if (IS_POST) {
            $father_id   = (int)I('p_id');
            $org_id      = (int)I('org_id');
            $name        = I('name');
            $introduce   = I('introduce');
            $no          = (int)I('no');
            $enabled     = (int)I('enabled');

            if(empty($name)){
                $this->response(['status' => 102, 'msg' => '机构名称必填'],'json');
            }
            if(!limitOperation('auth_org' ,$org_id ,180 ,$this->loginid)){
                $this->response(['status' => 101 ,'msg' => '有同事在操作该数据']);
            }

            $edit_data = [
                'name'          => $name,
                'no'            => $no,
                'introduce'     => $introduce,
                'enabled'       => $enabled,
                'modified_time' => date('Y-m-d H:i:s', time()),
            ];
            // 机构节点需要移动
            if($father_id != 0){
                $forg = M('auth_org')->where('id = %d',[$father_id])->find();
                $edit_data['p_id']         = $father_id;
                $edit_data['relationship'] = $forg['relationship'].",".$org_id;
            }
            $edit = M('auth_org')->where('id = %d',[$org_id])->save($edit_data);

            EndEditTime('auth_org' ,$org_id);
            if ($edit) {
                $this->response(['status' => 100],'json');
            } else {
                $this->response(['status' => 101, 'msg' => '编辑异常'],'json');
            }
        } else {
            $this->response(['status' => 103, 'msg' => '请求失败'],'json');
        }
    }

    /*
     * 添加组织机构
     * @param p_id    上级id
     * @param no      排序
     * */
    public function InsertOrg(){
        if (IS_POST) {
            $father_id   = (int)I('p_id');
            $name        = I('name');
            $introduce   = I('introduce');
            $no          = (int)I('no');
            // 创建者
            $creator_id = I('post.creator_id');
            if(empty($creator_id)){
                $arr['status'] = 1012;
                $this->response($arr,'json');
                exit();
            }
            if(empty($name)){
                $this->response(['status' => 102, 'msg' => '机构名称必填'],'json');
            }
            $insert_data = [
                'name'          => $name,
                'p_id'          => $father_id,
                'no'            => $no,
                'introduce'     => $introduce,
                'enabled'       => 1,
                'creator_id'    => $creator_id,
                'created_time'  => date('Y-m-d H:i:s', time()),
                'modified_time' => date('Y-m-d H:i:s', time()),
            ];
            $add = M('auth_org')->add($insert_data);
            if ($add) {
                $this->response(['status' => 100],'json');
            } else {
                $this->response(['status' => 101, 'msg' => '添加有误'],'json');
            }
        } else {
            $this->response(['status' => 103, 'msg' => '请求失败'],'json');
        }
    }

    /*
     * 删除组织机构
     * @param org_id  机构id
     * */
    public function DeleteOrg(){
        if (IS_POST) {
            $org_id = (int)I('org_id');
            if($org_id == 0) $this->response(['status' => 103, 'msg' => '请求失败'],'json');

            if(!limitOperation('auth_org' ,$org_id ,180 ,$this->loginid ,'R')){
                $this->response(['status' => 101 ,'msg' => '有同事在操作该数据']);
            }

            // 查询关联关系
            $roles = M('auth_role_org')->where(['org_id' => $org_id])->find();
            if($roles) $this->response(['status' => 103, 'msg' => '有关联的角色不可直接删除'],'json');

            $sonOrg = M('auth_org')->where(['p_id' => $org_id])->find();
            if($sonOrg) $this->response(['status' => 103, 'msg' => '有子机构不可直接删除'],'json');

            $del = M('auth_org')->where(['id' => $org_id])->delete();
            if ($del) {
                $this->response(['status' => 100],'json');
            } else {
                $this->response(['status' => 101, 'msg' => '删除失败'],'json');
            }
        } else {
            $this->response(['status' => 103, 'msg' => '请求失败'],'json');
        }
    }

    /*
     * 通过ID获取组织机构
     * @param org_id
     * */
    public function getOrgById(){
        if (IS_POST) {
            $org_id = (int)I('org_id');
            if($org_id == 0) $this->response(['status' => 103, 'msg' => '请求失败'],'json');

            $role = M('auth_org')
                ->where(['org_id' => $org_id])
                ->field('id,name,p_id,enabled,no,introduce')
                ->find();
            if ($role) {
                $this->response(['status' => 100,'value' => $role],'json');
            } else {
                $this->response(['status' => 101, 'msg' => '请求失败'],'json');
            }
        } else {
            $this->response(['status' => 103, 'msg' => '请求失败'],'json');
        }
    }

    /*
     * 组织机构搜索
     * @param searchText 搜索词
     * */
    public function searchOrg()
    {
        // 是否需要展示角色分组
        $searchText = I('post.searchText');

        // 查询出机构
        $searchText = __sqlSafe__($searchText);
        $org = M('auth_org')
            ->where("name LIKE '%$searchText%' OR introduce LIKE '%$searchText%'")
            ->field('id,name,p_id,introduce')
            ->order('id asc')
            ->select();
        if($org){
            $this->response(['status' => 100,'value' => $org],'json');
        }else{
            $this->response(['status' => 101,'msg' => '无相关信息'],'json');
        }
    }
}