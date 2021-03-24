<?php
/**
* iOS和Android接口程序 权限控制
*/
namespace app\h5\controller\api;
use app\apis\controller\tlogin\Wechat;
use app\h5\controller\Wap;
use think\cache\driver\Redis;
use think\Config;
use \think\Db;
use \think\Controller;
use app\common\controller\Common as AppCommon;

class Common extends Wap
{
    protected $token = 'yshfx';
    protected $expire = 10800;  //redis过期时间3小时
    protected $day_expire = 86400;  //redis过期时间1天
    protected $month_expire = 30*86400;  //redis过期时间1个月
    protected $client_uid = 0;
    protected $userInfo = [];
    protected $wxUserInfo = [];
    protected $wxacode_prefix = 'yshfx-wxacode'; //微信小程序redis前缀
    protected $answer_prefix = 'yshfx_answer'; //微信小程序答题redis前缀
    protected $answer_use_prefix = 'yshfx_answer_use'; //微信小程序答题增加的次数

    protected $apiConf = [
        'sign' => 'ab6fa6fee078d8858e333d950b190877' //签名头部文件前缀
    ];
    //图片地址
    protected $domain = [
        '__BASIC__'=>'http://static.v.yshfx.com/'
    ];

    public function __construct()
    {
        parent::__construct();
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

        if (input('token')) {
            $this->userInfo = $this->getUserInfo();  //客户端获取用户登录信息
            $this->wxUserInfo = $this->getUserInfoByWx();  //wx小程序获取用户登录信息
        }
        if (!empty($this->userInfo) && isset($this->userInfo['uid'])) {
            $this->client_uid = $this->userInfo['uid'];
        }
        if (!empty($this->wxUserInfo) && isset($this->wxUserInfo['uid'])) {
            $this->client_uid = $this->wxUserInfo['uid'];
            $this->userInfo = $this->wxUserInfo;
        }
        // 信用金计算
        if (isset($this->userInfo['credit_limit'])){
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
         $info = Db::name('User')->field('id,uid,login,isbuy,server,server_expire,zhmt4uid,zhmt4server,nickname,u_img,imoney,smoney,credit_limit,credited,gzhao,answer_nums,parent_id')->where('token', input('token'))->find();
        // vip过期判断
        if ($info['server_expire'] < time()){
            $info['server'] = '';
        }
        return $info;
    }

    /**
     * wx小程序获取用户信息
     * @return mixed
     */
    public function getUserInfoByWx()
    {
        $info = [];
        $redis = new Redis();
        $data = $redis->get(input('post.token'));
        if (!empty($data)) {
            $info = Db::name('User')->field('id,uid,login,isbuy,server,server_expire,zhmt4uid,zhmt4server,nickname,u_img,imoney,smoney,credit_limit,credited,gzhao,answer_nums,parent_id')->where('wxcode', $data['openid'])->find();
            // vip过期判断
            if ($info['server_expire'] < time()){
                $info['server'] = '';
            }
        }
        return $info;
    }

    /**
     * 获取小程序码
     * @param $scene
     * @param $page
     * @param $width
     * @param $auto_color
     * @param $line_color
     * @return mixed
     */
    public function getWxaCode($scene, $page, $width)
    {
        $config = Config::get('tlogin.wxmini');
        $wechat = new Wechat();
        $access_token = $wechat->getAccessToken();
        $url = sprintf($config['wxacode_url'], $access_token);
        $postData = [
            'scene' => $scene,
            'page' => $page,
            'width' => $width,
        ];
        return $this->http_curl($url,json_encode($postData));
    }

    protected function verifyLogin()
    {
        if (!$this->client_uid) {
            exit(json_encode(['code' => 302, 'msg' => '未登录']));
        }
    }

    protected function verifySms($login,$verify)
    {
        $where = [
            'login' => $login,
            'verify' => $verify
        ];
        $add_time = Db::name('mt4_smslog')->where($where)->value('add_time');
        if ($add_time + 120 < time()) {
            exit(json_encode(['code' => 304, 'msg' => '短信验证码已超时']));
        }
        if (!Db::name('mt4_smslog')->where($where)->count()) {
            exit(json_encode(['code' => 305, 'msg' => '短信验证码错误']));
        }
    }

    /**获取
     * @param $nums //下级层级数
     * @param $pids  //父id
     * @return array
     */
    protected function get_pid($nums,$pids)
    {
        for ($i=1;$i<$nums;$i++) {
            $pids = $this->get_parent_id($pids);
        }
        return $pids;
    }

    /**根据pid查询sid
     * @param $pids
     * @return array
     */
    private function get_parent_id($pids)
    {
        $where = [
            'parent_id' => ['in', $pids]
        ];
        return Db::name('user')->where($where)->column('uid');
    }

    /**获取openid
     * @param $code
     * @return mixed
     */
    protected function getOpenId($code)
    {
        $config = Config::get('tlogin.wxmini');
        // 发送数据
        $url = sprintf($config['codeUrl'],$config['AppID'],$config['AppSecret'],$code);
        return json_decode(file_get_contents($url),true);
    }

    /**
     *
     * 请求远程地址
     * @param string $url
     * @param $postData
     * @return mixed
     */
    public function http_curl($url='',$postData=[])
    {
        // 发送数据
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); //https请求 不验证证书 其实只用这个就可以了
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); //https请求 不验证HOST
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        $output = curl_exec($ch);
        curl_close($ch);
        // 输出表单
        return $output;
    }

    /**
     * 发送微信模板消息
     * @param $data
     */
    public function sendWxMessage($data)
    {
        $config = Config::get('tlogin.wxmini');
        $wechat = new Wechat();
        $access_token = $wechat->getAccessToken();
        $url = sprintf($config['template_url'], $access_token);
        if (!empty($access_token)) {
            echo $this->http_curl($url, json_encode($data));
        }

    }

}