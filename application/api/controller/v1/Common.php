<?php
/**
* iOS和Android接口程序 权限控制
*/
namespace app\api\controller\v1;
use think\Config;
use \think\Db;
use \think\Controller;
use app\common\controller\Common as AppCommon;

class Common extends Controller
{
    protected $token = 'znforex';
    protected $expire = 10800;  //redis过期时间3小时
    protected $client_uid = 0;
    protected $userInfo = [];

    protected $apiConf;
    //图片地址
    protected $domain = [
        //'__STATIC__'=>'http://res.v.znforex.com/',
        '__BASIC__'=>'http://static.v.znforex.com/'
    ];

    public function __construct()
    {
        parent::__construct();
        $this->apiConf = Config::get('api');
        // 验证有效性
        if (input('?post.sign')){
            $sign = substr(input('post.sign'), 0, 32);
            $time = substr(input('post.sign'), 32);
            if ($sign != $this->apiConf['sign']) {
                exit(json_encode(['code' => 302, 'msg' => 'cmd-close: Authentication Failed[1].'])); // 验证sign
            }

            if (time() > $time+60*10) {
                exit(json_encode(['code' => 302, 'msg' => 'cmd-close: Authentication Failed[2].']));// 过期验证
            }
        }else{
            exit(json_encode(['code' => 302, 'msg' => 'cmd-close: Authentication Failed[4].']));
        }

        $this->userInfo = $this->getUserInfo();
        if (!empty($this->userInfo) && isset($this->userInfo['uid'])) {
            $this->client_uid = $this->userInfo['uid'];
        }
        // 信用金计算
        if ( ! empty($this->userInfo)){
            // 可用信用金
            $this->userInfo['credit'] = $this->userInfo['credit_limit'] - $this->userInfo['credited'];
        }
        $this->token = $this->token.time();

    }

    /**
     * 获取用户信息
     * @return mixed
     */
    public function getUserInfo()
    {
         $info = Db::name('User')->field('id,uid,login,isbuy,server,server_expire,zhmt4uid,imoney,smoney,credit_limit,credited')->where('token', input('token'))->find();
        // vip过期判断
        if ($info['server_expire'] < time()){
            $info['server'] = '';
        }
        return $info;
    }


}