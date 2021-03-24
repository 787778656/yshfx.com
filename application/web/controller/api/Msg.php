<?php
/**
* 接口程序
* @author efon.cheng<efon@icheng.xyz>
* 2017-11-29
*/
namespace app\web\controller\api;
use \think\Config;
use \think\Db;
use \think\Controller;
use app\common\controller\Common as AppCommon;
use app\admin\model\Mt4AccountStatistics;
use \think\Cookie;
use \think\cache\driver\Redis;
use app\common\controller\Msg as AppMsg;

class Msg extends Controller
{
    /**
    * 获取站内信
    * @return string
    */
    public function get(){
    	$type =  input('get.type');
        $msg = AppMsg::get($type);
        $callback = input('get.callback');
        $result = sprintf('%s(%s)', $callback, json_encode(array('code'=> 200, 'msg' => '', 'data' => $msg)));
        echo $result;
    }

    /**
    * 读取站内信
    * @return string
    */
    public function read($id = 0){
        $msg = AppMsg::read($id);
        $callback = input('get.callback');
        $result = sprintf('%s(%s)', $callback, json_encode(array('code'=> 200, 'msg' => '', 'data' => $msg)));
        echo $result;
    }
}