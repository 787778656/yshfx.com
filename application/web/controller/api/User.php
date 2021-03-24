<?php
/**
* 用户操作相关
* @author efon.cheng<efon@icheng.xyz>
* 2017-12-13
*/
namespace app\web\controller\api;
use \think\Config;
use \think\Db;
use \think\Controller;
use app\common\controller\Common as AppCommon;
use \think\Cookie;
use \think\Session;
use \think\cache\driver\Redis;
use app\common\controller\Msg as AppMsg;

class User extends Controller
{

    protected $userInfo;
    public function __construct(){
        $this->userInfo = Session::get('zhtweb');
        // 未登录
        if ( ! empty($this->userInfo)){
            $this->userInfo = Db::name('user')->where('uid', $this->userInfo['uid'])->find();
        }
    }

    /**
     * ajax退出登录
     */
    public function ajax_logout($callback = '/')
    {
        Session::delete('zhtweb');
        echo json_encode(array('code'=> 200));
    }

    /**
    * 信用金明细
    */    
    public function credit(){
        $page = input('post.page');
        if ($page < 1) $page = 1;
        $arrCredit = Db::name('user_credit_log')->where('uid', $this->userInfo['uid'])->order('id desc')->limit(($page-1)*5, 5)->select();
        echo json_encode(array('code'=> 200, 'msg' => '', 'data' => $arrCredit));
    }

    /**
    * 下级账号
    */    
    public function sub_account(){
        $page = input('post.page');
        if ($page < 1) $page = 1;
        $arrAccount = Db::name('user')->where('parent_id', $this->userInfo['uid'])->order('id desc')->limit(($page-1)*5, 5)->select();
        echo json_encode(array('code'=> 200, 'msg' => '', 'data' => $arrAccount));
    }

    /**
    * 生成数据图表缩略图
    */
    public function wxchart2png(){
        $data = $_POST;
        if ( ! empty($data)){
            foreach ($data as $key => $pic) {
                // 生成图片
                if (stristr($pic, 'data:image/png;base64,')){
                    $pic = str_replace('data:image/png;base64,', '', $pic);
                    $pic = AppCommon::base64img($pic, $key, 'wxchart', 'png');
                }
                echo $pic;
            }
        }
    }

    public function webchart2png(){
        $data = $_POST;
        if ( ! empty($data)){
            foreach ($data as $key => $pic) {
                // 生成图片
                if (stristr($pic, 'data:image/png;base64,')){
                    $pic = str_replace('data:image/png;base64,', '', $pic);
                    $pic = AppCommon::base64img($pic, $key, 'webchart', 'png');
                }
                echo $pic;
            }
        }
    }
}