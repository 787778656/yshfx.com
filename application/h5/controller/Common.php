<?php
/**
* zht-web导航栏中展示页面
*/
namespace app\h5\controller;
use \think\Db;
use think\Controller;
use think\Request;
use think\Session;
use think\Config;
use \think\cache\driver\Redis;
use app\common\controller\Common as AppCommon;

define('SIGN', 'yshfx!@#$%^');// 接口签名
class Common extends controller
{

	public function __construct(){
        parent::__construct();
        $this->assign('apiSign', md5(md5(SIGN)));
    }
    /**
     * 空方法
     */
    public function _empty(){
        return $this->fetch();
    }
}
