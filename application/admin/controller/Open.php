<?php
/**
* 后台登录文件
* @author efon.cheng<efon@icheng.xyz>
* 2017-08-31
*/
namespace app\admin\controller;
use \think\Config; 
use \think\Controller;
use \think\Db; 
use \think\Session;

class Open extends Controller
{
    public function login()
    {
    	if(input('?post.username')){
            // 登录
            $Admin = model('Administrator');
            $username = input('post.username');
            $password = input('post.password');
            $administrator = $Admin->where(array('status' => 1,'username' =>$username, 'password' => md5($password)))->find();
            
            if (!empty($administrator)){
                // 分解权限
                $administrator['rights'] = explode('|', $administrator['rights']);
                Session::set('admin', $administrator);
                $Admin->save(array('logintimes' => $administrator['logintimes']+1, 'login_time' => time()), ['id' => $administrator['id']]);
            }else{
                echo '用户名或密码错误！';
                // $this->error('用户名或密码错误！');
            }
    	}else{
            // 显示登录视图
    		return $this->fetch();
    	}    	
    }

    public function logout()
    {
        // 退出登录
        Session::delete('admin');
        $this->redirect('login');
    }
}
