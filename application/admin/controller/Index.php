<?php
/**
* 管理后台首页
* @author efon.cheng<efon@icheng.xyz>
* 2017-08-31
*/
namespace app\admin\controller;
use \think\Config;
use \think\Db;
use \think\Session;
use app\common\controller\Admin;
use app\common\controller\Common;

class Index extends Admin
{
    public function index()
    {
        // 获取登录信息
    	$administrator = Session::get('admin');
    	$this->assign('admin', $administrator);

        // 获得菜单配置
        $mainMenus = parent::getMenu();

        // 传值
    	$this->assign('mainMenus', $mainMenus);
    	return $this->fetch();
    }

    public function main()
    {
        $this->assign('serverInfo', array_slice($_SERVER, 10));
        return $this->fetch();
    }

    // 图片上传
    public function uploadImg(){
        Common::uploadImg();
    }

    // 文件上传
    public function uploadFile(){
        Common::uploadFile();
    }
}
