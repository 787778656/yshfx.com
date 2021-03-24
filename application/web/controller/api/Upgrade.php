<?php
/**
* 接口程序
* @author efon.cheng<efon@icheng.xyz>
* 2017-12-28
*/
namespace app\web\controller\api;
use \think\Config;
use \think\Db;
use \think\Controller;
use app\common\controller\Common as AppCommon;
use \think\Cookie;
use \think\Session;
use \think\Request;
use \think\cache\driver\Redis;

class Upgrade extends Controller
{

    protected $userInfo;
    public function __construct(){
        parent::__construct();
        $this->userInfo = Session::get('zhtweb');
        // 未登录
        if (empty($this->userInfo)){
            exit(json_encode(array('code'=> 301, 'msg' => '请先登录!')));
        }
    }

    /**
    * 上传截图
    * @return string
    */
    public function add(){
        if (input('?post.pic')){
            $data = array(
                    'uid' => $this->userInfo['uid'],
                    'pic' => input('post.pic'),
                    'add_time' => time(),
                    'operator' => 'screen_web_api'
                );
            if (Db::name('user_screen')->insert($data)){
                echo json_encode(array('code'=> 200, 'msg' => '提交成功,请等待审核!'));
            }else{
                echo json_encode(array('code'=> 302, 'msg' => sprintf('提交失败, 请稍候再试.')));
            }
        }
    }

    /**
    * 机构认证
    * @return string
    */
    public function cert(){
        if (input('?post.company')){
            $data = array(
                    'uid' => $this->userInfo['uid'],                    
                    'company' => trim(input('post.company')),
                    'website' => trim(input('post.website')),
                    'pic' => input('post.pic'),
                    'add_time' => time(),
                    'operator' => 'cert_web_api'
                );
            if (Db::name('user_cert')->insert($data)){
                echo json_encode(array('code'=> 200, 'msg' => '提交成功,请等待审核!'));
            }else{
                echo json_encode(array('code'=> 302, 'msg' => sprintf('提交失败, 请稍候再试.')));
            }
        }
    }

    /**
    * 优质策略
    * @return string
    */
    public function strategy(){
        if (input('?post.mt4id')){
            $data = array(
                    'uid' => $this->userInfo['uid'],                    
                    'broker' => trim(input('post.broker')),
                    'mt4server' => trim(input('post.mt4server')),
                    'mt4id' => input('post.mt4id'),
                    'mt4lpwd' => input('post.mt4lpwd'),
                    'add_time' => time(),
                    'operator' => 'strategy_web_api'
                );
            if (Db::name('user_strategy')->insert($data)){
                echo json_encode(array('code'=> 200, 'msg' => '提交成功,请等待审核!'));
            }else{
                echo json_encode(array('code'=> 302, 'msg' => sprintf('提交失败, 请稍候再试.')));
            }
        }
    }
}