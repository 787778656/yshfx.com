<?php
/**
* 后台用户管理
* @author efon.cheng<efon@icheng.xyz>
* 2017-08-31
*/
namespace app\admin\controller;
use \think\Config;
use \think\Db;
use \think\Session;
use app\common\controller\Admin;

class User extends Admin
{
    /**
    * 账号列表
    */
    public function index()
    {
        // 导入分页类
        import ( 'ORG.Util.Page' );
        $arrUser = Db::name('administrator')->where(array('id' =>array('NEQ', 1)))->order('id desc')->paginate(10);
        $this->assign('arrUser', $arrUser);
        // 所有权限模块
        $rights = parent::getSubMenu();
        $this->assign('rights', $rights);
        
        return $this->fetch();
    }

    /**
    * 增加管理员
    */
    public function add()
    {
        // 获取所有导航权限节点
        $mainMenus = parent::getMenu(false);
        $this->assign('mainMenus', $mainMenus);
        if (input('?post.username')){
            $Administrator = model('Administrator');
            $arrUser = $Administrator::get(array('username' => input('post.username')));
            if (!empty($arrUser)){
                $this->error('已有相同用户名的管理账号，不能重复添加！');
            }else{
                $rights = input('post.rights/a');
                $rights = empty($rights)?'':implode('|', $rights);
                $data = array(
                        'username' => input('post.username'),
                        'password' => md5(input('post.password')),
                        'nickname' => input('post.nickname'),
                        'true_name' => input('post.truename'),
                        'rights' => $rights,
                        'remark' => input('post.remark'),
                        'operator' => Session::get('admin.username')
                    );
                if ($Administrator->save($data)){
                    $this->success('管理员添加成功！', url('User/index'));
                }else{
                    $this->error('管理员添加失败！');
                }
            }

        }
        return $this->fetch();
    }

    /**
    * 修改账号
    */
    public function edit($id){
        if (!empty($id)){
            $Administrator = model('Administrator');
            if (input("?post.id")){
                $rights = input('post.rights/a');
                $rights = empty($rights)?'':implode('|', $rights);
                $data = array(                        
                        'nickname' => input('post.nickname'),
                        'true_name' => input('post.truename'),
                        'rights' => $rights,
                        'remark' => input('post.remark'),
                        'operator' => Session::get('admin.username')
                    );
                $password = input('post.password');
                if (!empty($password)) $data['password'] = md5($password);
                if ($Administrator->save($data, ['id' => input('post.id')])){
                    $this->success('信息修改成功！', url('User/index'));
                }else{
                    $this->error('信息修改失败！');
                }
            }
            // 获取所有导航权限节点
            $mainMenus = parent::getMenu(false);
            $this->assign('mainMenus', $mainMenus);
            // 获取用户信息            
            $arrUser = $Administrator::get($id);
            $this->assign('arrUser', $arrUser);
            return $this->fetch();            
        }
    }

    /**
    * 修改管理员个人资料
    */
    public function myself(){
        $id = Session::get('admin.id');
        if (!empty($id)){
            $Administrator = model('Administrator');
            if (input("?post.id")){
                $rights = input('post.rights/a');
                $rights = empty($rights)?'':implode('|', $rights);
                $data = array(                        
                        'nickname' => input('post.nickname'),
                        'true_name' => input('post.truename'),
                        'remark' => input('post.remark'),
                        'operator' => Session::get('admin.username')
                    );
                $password = input('post.password');
                if (!empty($password)) $data['password'] = md5($password);
                if ($Administrator->save($data, ['id' => input('post.id')])){
                    $this->success('信息修改成功！');
                }else{
                    $this->error('信息修改失败！');
                }
            }
            // 获取用户信息            
            $arrUser = $Administrator::get($id);
            $this->assign('arrUser', $arrUser);
            return $this->fetch();            
        }
    }

    /**
    * 更新状态
    */
    public function status(){
        if (input('?post.aid')){
            $id = input('post.aid');
            $status = input('post.status');
            $status = ($status == 1) ? 2 : 1;
            $Administrator = model('Administrator');
            $Administrator->save(['status' => $status], ['id' => $id]);
            echo $Administrator->getLastSql();
        }
    }

    /**
    * 删除账号
    */
    public function del(){
        if (input('?post.aid')){
            $id = input('post.aid');
            if ($id != 1) { // 排除系统管理员
                $Administrator = model('Administrator');
                $Administrator::destroy($id);
            }
        }
    }

}
