<?php
/**
* iOS和Android接口程序
*/
namespace app\api\controller\v1;
use \think\Db;
use app\common\controller\Common as AppCommon;
use app\common\controller\Upgrade as AppUpgrade;
use app\common\controller\Msg as AppMsg;

class Mobile extends Common
{
    /**
     * 登录
     */
    public function signin()
    {
        $mobile = input('login');
        $password = input('password');

        if (empty($mobile) || empty($password)) {
            return json_encode(['code' => 302, 'msg' => '请输入账号或密码']);
        }
        $result = $this->login($mobile, $password);
        if ($result){
            $info = Db::name('User')->field('imoney, login, uid, zhmt4uid, zhmt4server,nickname,u_img, server, server_expire, isbuy, token')->where('login', $mobile)->find();
            // vip过期判断
            if ($info['server_expire'] < time()){
                $info['server'] = '';
            }
            $mt4_arr = $this->get($info['uid']);
            $info['mt4_arr'] = $mt4_arr;
            return json_encode(['code' => 200, 'msg' => '登录成功！', 'result' => $info]);
        }
        return json_encode(['code' => 302, 'msg' => '用户名或密码错误！']);
    }

    /**
     * 注册
     * @return string
     */
    public function register()
    {
        $login = input('login',''); //登录账户或者手机号
        $password = input('password',''); //密码
        $code = input('code',0); //邀请码
        $verify = input('verify'); //短信验证码
        $time = time();
        if ($login == '') {
            return json_encode(['code' => 302, 'msg' => '请填写手机号']);
        }
        if ($password == '') {
            return json_encode(['code' => 302, 'msg' => '请填写密码']);
        }
        $data = Db::name('User')->where('login', $login)->find();
        if (!empty($data)) {
            return json_encode(['code' => 302, 'msg' => '该手机号已存在']);
        }
        $sms_verify = Db::name('Mt4Smslog')->where(['login' => $login, 'verify' => $verify])->find();
        if (empty($sms_verify)) {
            return json_encode(['code' => 302, 'msg' => '短信验证码错误!']);
        }
        if(strpos($_SERVER['HTTP_USER_AGENT'], 'iPhone')||strpos($_SERVER['HTTP_USER_AGENT'], 'iPad')){
            $from = 'iOS';
        }else{
            $from = 'Android';
        }
        $user_info = [
            'login' => $login,
            'password' => md5($password),
            'sign_ip' => $this->request->ip(),
            'modify_time' => $time,
            'add_time' => $time,
            'from' => $from
        ];
        if (!empty($code)) {
            $user_info['code'] = $code;
        }

        $result = Db::name('User')->insertGetId($user_info);
        if ($result <= 0) {
            return json_encode(['code' => 302, 'msg' => '注册失败']);
        }
        Db::name('User')->where('id', $result)->update(['uid' => $result]);
        // 送vip 7 天
        if ( ! empty($code)){
            AppUpgrade::updateVip(7, intval($code), '邀请注册会员赠送', 'reg-api-mobile', 'vip2');
        }
        AppMsg::send($result, 'reg');
        //$this->login($login, $password);
        return json_encode(['code' => 200, 'msg' => '注册成功']);

    }

    /**
     *
     * 修改密码
     * @return string
     */
    public function update_pwd()
    {
        $old_password = input('old_pwd'); //原始密码
        $new_password = input('new_pwd'); //新密码
        $time = time();
        $user = Db::name('User')->where('uid', $this->client_uid)->find();
        if ($user['password'] !== md5($old_password)) {
            return json_encode(['code' => 302, 'msg' => '请输入正确的原始密码']);
        }

        $data = Db::name('User')->where('uid', $this->client_uid)->update(['password' => md5($new_password), 'modify_time' => $time]);
        if ($data > 0) {
            return json_encode(['code' => 200, 'msg' => '密码修改成功']);
        }
        return json_encode(['code' => 302, 'msg' => '密码修改失败']);

    }

    /**
     *
     * 重置密码（忘记密码）
     * @return string
     */
    public function update_init()
    {
        $mobile = input('mobile'); //手机号
        $password = input('password'); //密码
        $verify = input('verify',0); //短信验证码
        $time = time();
        if (empty($mobile) || empty($password)) {
            return json_encode(['code' => 302, 'msg' => '请输入手机号或密码']);
        }
        $user = Db::name('User')->where('login', $mobile)->find();
        if (empty($user)) {
            return json_encode(['code' => 302, 'msg' => '该手机号用户不存在']);
        }
        $sms_verify = Db::name('Mt4Smslog')->where(['login' => $mobile, 'verify' => $verify])->find();
        if (empty($sms_verify)) {
            return json_encode(['code' => 302, 'msg' => '短信验证码错误!']);
        }

        Db::name('User')->where('login', $mobile)->update(['password' => md5($password), 'modify_time' => $time]);

        return json_encode(['code' => 200, 'msg' => '密码修改成功']);

    }

    /**
     * 注册用户绑定mt4账号
     * @return string
     */
    public function binding()
    {
        return json_encode(['code' => 302, 'msg' => '请更新到最新版本']);
    }

    /**
     * 用户改绑mt4账号
     * @return string
     */
    public function unbinding()
    {
        return json_encode(['code' => 302, 'msg' => '请更新到最新版本']);
    }
    /**
     * 登录
     * @param $mobile
     * @param $password
     */
    public function login($mobile, $password)
    {
        if (empty($mobile) || empty($password)) {
            return false;
        }

        return $this->logining($mobile, $password);

    }

    public function logining($mobile, $password)
    {
        $user = Db::name('User')->field('login, uid')->where(array('login' =>$mobile, 'password' => md5($password)))->find();
        if (!empty($user)){
            Db::name('User')->update(['sign_time' => time(), 'id' => $user['uid'], 'token' => md5($this->token)]);
            return true;
        }else{
            return false;
        }
    }

    /**
     * 获取短信验证码
     * @return string
     */
    public function get_smscode()
    {
        $mobile = input('mobile');  //手机号
        $type = input('type');   // type: 1:账号注册  2:修改密码（忘记密码）
        $verify = mt_rand(123456, 999999);//获取随机验证码

        switch ($type) {
            case 1 :
                $msg = '客户端短信验证码为：' . $verify . '，请勿将验证码提供给他人。';
                break;
            case 2:
                $msg = '您正在修改密码，短信验证码为：' . $verify . '，请勿将验证码提供给他人。';
                break;
            default :
                $msg = '客户端短信验证码为：' . $verify . '，请勿将验证码提供给他人。';
                break;
        }
        $status = AppCommon::sendmsg_czsms($mobile,$msg, 'clinent_smscode_'.$mobile);
        
        $where = [
            'login' => $mobile,
            'verify' => $verify,
            'add_time' => time(),
            'status' => $status
        ];
        Db::name('Mt4Smslog')->insert($where);
        if ($status == 0) {
            return json_encode(['code' => 200, 'msg' => '发送成功']);
        }
        return json_encode(['code' => 302, 'msg' => '发送失败']);
    }

    /**
     * 获取用户个人信息
     * @return string
     */
    public function get_userinfo()
    {
        $info = Db::name('User')->field('imoney, login, uid, zhmt4uid, zhmt4server, nickname,u_img, server,server_expire, isbuy, token')->where('uid', $this->client_uid)->find();
        if (empty($info)) {
            return json_encode(['code' => 302, 'msg' => 'uid错误，查询失败']);
        }
        // vip过期判断
        if ($info['server_expire'] < time()){
            $info['server'] = '';
        }
        $mt4_arr = $this->get();
        $info['mt4_arr'] = $mt4_arr;
        return json_encode(['code' => 200, 'msg' => '查询成功', 'result' => $info]);
    }

    /**
     * 获取用户mt4账号
     * @return string
     */
    public function get($uid=0){
        if(!$this->client_uid) {
            $this->client_uid = $uid;
        }
        $arrMt4 = Db::name('user_mt4')->where('uid', $this->client_uid)->field('id,uid,mt4id,mt4server,mt4pwd,sh,status')->order('id desc')->select();
        $user_mt4 = db('user')->field('id,uid,zhmt4uid,zhmt4pwd,zhmt4server,isbuy')->where('uid', $this->client_uid)->find();
        $count = db('user_mt4')->where(['uid' => $this->client_uid, 'mt4id' => $user_mt4['zhmt4uid']])->count();
        if (!$count) {
            if (!empty($user_mt4) && $user_mt4['isbuy'] == 0 && !empty($user_mt4['zhmt4uid'])) {
                $mt4Arr = [
                    'id' => $user_mt4['id'],
                    'uid' => $user_mt4['uid'],
                    'mt4id' => $user_mt4['zhmt4uid'],
                    'mt4server' => $user_mt4['zhmt4server'],
                    'mt4pwd' => $user_mt4['zhmt4pwd'],
                    'sh' => $user_mt4['isbuy'],
                ];
                $arrMt4[] = $mt4Arr;
            }
        }
        return array_values($arrMt4);
    }

    /**
     * 分佣关系统计
     * @return json
     */
    public function get_invite_data(){
        // 直接邀请注册的
        $data = array();
        $arrSub2 = $arrSubMt2 = 0;
        if ( ! empty($this->client_uid)){
            $arrSub = db('user')->where('parent_id', $this->client_uid)->column('uid');
            $arrSubMt4 = db('user')->where('parent_id', $this->client_uid)->where('isbuy', 1)->count();
            // 间接的邀请注册的
            if ( ! empty($arrSub)){
                $arrSub2 = db('user')->where('parent_id', 'in', $arrSub)->count();
                $arrSubMt2 = db('user')->where('parent_id', 'in', $arrSub)->where('isbuy', 1)->count();
            }

            // 邀请购买的
            $arrSubBuy = db('user_reward')->where('src_id', 'like', $this->client_uid.'%')->count();
            $arrSubBuy2 = db('user_reward')->where('src_id', 'like', '%-'.$this->client_uid)->count();

            $data['arr_sub'] = count($arrSub);
            $data['arr_sub_mt4'] = count($arrSub);
            $data['arr_sub_buy'] = $arrSubBuy;
            $data['arr_sub2'] = $arrSub2;
            $data['arr_sub2_mt4'] = $arrSubMt2;
            $data['arr_sub2_buy'] = $arrSubBuy2;
        }
        return json_encode(array('code'=> 200, 'msg' => '查询成功', 'result' => $data));
    }

    /**
     * 安卓app更新
     * @return string
     */
    public function appUpdate()
    {
        $result = [
            'versionCode' => 11,
            'versionName' => '3.0.4',
            'updateInfo' => '1.修复已知BUG;|2.界面UI优化更新|3.增加我的交易功能',
            'updateUrl' => 'http://srefx.com/download/app/sharefx_3.0.apk '
        ];
        return json_encode(['code' => 200, 'msg' => '获取成功', 'result' => $result]);
    }

    /**
     * 退出登录
     */
    public function signout()
    {
        Db::name('User')->where('uid', $this->client_uid)->update(['token' => '']);
        return json_encode(['code' => 200, 'msg' => '退出成功']);
    }

    /**
     * 上传用户图像图片
     */
    public function uploadImg()
    {
        $rootPath = dirname(dirname(dirname(ROOT_PATH))).'/static.v.znforex.com/upload/image/';

        $content = input('post.content');

        $images = AppCommon::base64img($content); //图像名称

        if (file_get_contents($rootPath.$images)) {
            $count = db('user')->where('uid', $this->client_uid)->update(['u_img' => $images]);
            if ($count > 0) {
                return json_encode(['code' => 200, 'msg' => '上传成功', 'result' => ['img' => $images]]);
            }
        }
        return json_encode(['code' => 302, 'msg' => '上传失败']);
    }



    /**
     * 修改用户信息
     * @return string
     */
    public function update_userinfo()
    {
        $nickname = input('post.nickname');  //昵称
        $conf = [
            'nickname' => $nickname,
        ];

        $data = Db::name('User')->where('uid',$this->client_uid)->update($conf);
        if ($data > 0) {
            return json_encode(['code' => 200, 'msg' => '修改成功']);
        }
        return json_encode(['code' => 302, 'msg' => '暂无任何修改']);
    }

    /**
     * 添加提现
     * @return string
     */
    public function withdraw(){
        $amount = input('post.amount',0.00); //提现金额
        $account = input('post.account','');  //账号
        $imoney = db('user')->where('uid', $this->client_uid)->value('imoney');  //用户账户余额
        if (intval($imoney) < intval($amount)) {
            return json_encode(array('code'=> 302, 'msg' => '申请提现金额不能大于账号余额!'));
        }
        if (intval($amount) < 10) {
            return json_encode(array('code'=> 302, 'msg' => '提现金额不能低于10元!'));
        }
        if ($amount > $this->userInfo['imoney']){
            return json_encode(array('code'=> 305, 'msg' => '提现金额不能大于账户净余额!'));
        }
        if ($amount && $account){
            $data = array(
                'uid' => $this->client_uid,
                'amount' => $amount,
                'account' => $account,
                'add_time' => time(),
                'modify_time' => time(),
                'operator' => 'withdraw-app-api'
            );
            $count = Db::name('user_withdraw')->where(['uid' => $this->client_uid, 'status' => ['in',[0,1]]])->count();
            if ($count == 0) {
                if (Db::name('user_withdraw')->insert($data)) {
                    AppCommon::sendmsg_czsms('16675190411', '用户提现，请及时处理！');
                    return json_encode(array('code' => 200, 'msg' => '申请提交成功!'));
                }
                return json_encode(array('code' => 302, 'msg' => '申请提交失败, 请稍候再试.'));
            }

            return json_encode(array('code'=> 302, 'msg' => '您有提现申请尚未处理,不能重复提交.'));
        }
        return json_encode(array('code'=> 302, 'msg' => '参数错误'));
    }

    /**
     * 获取明细
     * @return string
     */
    public function withdrawInfo(){
        $arrWithdraw = Db::name('user_money_log')->field('class,remark,add_time,amount')->where('uid', $this->client_uid)->order('id desc')->paginate(10)->toArray();
        return json_encode(array('code'=> 200, 'msg' => '查询成功', 'result' => $arrWithdraw));
    }
}