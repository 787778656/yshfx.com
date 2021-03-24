<?php
/**
* 行情
*/
namespace app\web\controller;
use think\Config;
use \think\Db;
use think\Session;
use app\common\controller\Common as AppCommon;
use \think\cache\driver\Redis;

class Market extends Common
{
    /**
    * 我的行情
    */
    public function index()
    {
        return $this->fetch();
    }
}
