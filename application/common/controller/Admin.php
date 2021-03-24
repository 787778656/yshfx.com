<?php
/**
* 后台所有程序公用类
* @author efon.cheng<efon@icheng.xyz>
* 2017-08-31
*/
namespace app\common\controller;
use \think\Config; 
use \think\Controller;
use \think\Db; 
use \think\Session;
use \think\Request;

class Admin extends Controller
{
	public function __construct()
    {
        parent::__construct();
        // 加载系统配制
        $this->assign('system', Config::get('system'));
        // 此处将检测登录权限
        $administrator = Session::get('admin');
        if (empty($administrator)){        	
            echo "<script type='text/javascript'>top.location='/admin/open/login.html';</script>";
            exit();
            // $this->error('你没有此操作权限！', 'Open/login');
            // $this->redirect('admin/open/login');
        }

        // 相关控制器
        $controller = Request::instance()->controller();
        $action = Request::instance()->action();
        // 获取权限
        $administrator = Session::get('admin');
        $rights = $administrator['rights'];
        $page = sprintf('%s-%s', $controller, $action);
        if (in_array('user-7', $rights)){ // 对外开放权限
            $allowPage = array('User-look_user', 'Index-index', 'Index-main');
            if (!in_array($page, $allowPage)){
                exit('permission denied.');
            }            
        }
    }

    /**
    * 获取权限内的导航
    */
    protected static function getMenu($public = true)
    {
        $administrator = Session::get('admin');
        $arrMenus = Config::get('menu');
        $rights = $administrator['rights'];
        // 分离一级和二级权限
        $arrRights = array();
        foreach ($rights as $key => $value) {
            $arr = explode('-', $value);
            $arrRights[$arr[0]][] = $value;
        }
        $mRights = array_keys($arrRights);
        // 拥有管理员权限则加载所有模块
        if (!$public) unset($arrMenus['public']);
        $mainMenus = array();
        if (in_array('admin', $mRights)){
            $mainMenus = array_values($arrMenus);
        }else{
            $rights[] = 'public'; //加入公共模块
            // 剔除未授权的模块
            foreach ($arrMenus as $key => $value) {
                if (in_array($key, $mRights)){
                    foreach ($value['sub'] as $k => $v) {
                        // 剔除未授权的子模块
                        if (isset($v['rights']) && !in_array($v['rights'], $rights)){
                            unset($value['sub'][$k]);
                        }
                    }
                    $mainMenus[] = $value;
                }
            } 
        }
        return $mainMenus;
    }

    /**
    * 获取子导航
    */
    protected static function getSubMenu()
    {
        $arrMenus = array_values(Config::get('menu'));        
        $subRights = array();
        foreach ($arrMenus as $key => $value) {
            foreach ($value['sub'] as $k => $v) {
                // 所有子模块
                if (isset($v['rights'])){
                    $subRights[$v['rights']] = $v['name'];
                }
            }
        }
        return $subRights;
    }
}
