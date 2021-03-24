<?php
/**
* iOS和Android接口程序
*/
namespace app\h5\controller\api;
use app\common\controller\Money;
use think\cache\driver\Redis;
use \think\Db;
use app\common\controller\Common as AppCommon;
use QRcode;
use think\Session;

class Mobile extends Common
{
    /**
     * 新版首页
     * follow ： 跟随交易 protection：保障交易  ranking ： 交易排位赛
     * @return string
     */
    public function index()
    {
        return json_encode(['code' => 200, 'msg' => '查询成功', 'follow' => '70', 'protection' => '78', 'ranking' => '1.62']);
    }
    
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
            $info = Db::name('User')->field('credit_limit,credited,smoney,imoney, login, uid, zhmt4uid, zhmt4server,nickname,u_img, server, server_expire, isbuy, token')->where('login', $mobile)->find();
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
        $login = input('post.login',''); //登录账户或者手机号
        $password = input('post.password',''); //密码
        $uid = input('post.uid',0); //邀请码
        $verify = input('post.verify'); //短信验证码
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
        parent::verifySms($login,$verify);
        $user_info = [
            'login' => $login,
            'password' => md5($password),
            'sign_ip' => $this->request->ip(),
            'modify_time' => $time,
            'add_time' => $time
        ];
        if (!empty($uid) && Db::name('user')->where('uid', $uid)->count()) {
            $user_info['parent_id'] = $uid;
        }

        $result = Db::name('User')->insertGetId($user_info);
        if ($result <= 0) {
            return json_encode(['code' => 302, 'msg' => '注册失败']);
        }
        Db::name('User')->where('id', $result)->update(['uid' => $result]);
        // 送vip 7 天
        /*if ( ! empty($code)){
            AppUpgrade::updateVip(7, intval($code), '邀请注册会员赠送', 'reg-apis-wxcode');
        }
        AppMsg::send($result, 'reg');*/
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
            $token = md5($this->token);
            Db::name('User')->update(['sign_time' => time(), 'id' => $user['uid'], 'token' => $token]);
            Session::set('mytoken', $token);
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

        // switch ($type) {
        //     case 1 :
        //         $msg = $verify;
        //         break;
        //     case 2:
        //         $msg = '您正在修改密码，短信验证码为：' . $verify . '，请勿将验证码提供给他人。';
        //         break;
        //     default :
        //         $msg = $verify;
        //         break;
        // }
        // $status = AppCommon::sendmsg_czsms($mobile,$msg);
        $status = AppCommon::sendmsg_czsms($mobile,$verify);
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
        parent::verifyLogin();
        $info = Db::name('User')->field('credit_limit,credited,imoney,smoney, login, uid, zhmt4uid, zhmt4server, nickname,u_img, server,server_expire, isbuy, token')->where('uid', $this->client_uid)->find();
        if (empty($info)) {
            return json_encode(['code' => 302, 'msg' => 'uid错误，查询失败']);
        }
        $info['u_img'] = strpos($info['u_img'],'http') !== false ? $info['u_img'] : $this->domain['__BASIC__'].'upload/image/'.$info['u_img'];

        // vip过期判断
        if ($info['server_expire'] < time()){
            $info['server'] = '';
        }
        $mt4_arr = $this->get();
        $info['mt4_arr'] = $mt4_arr;
        $info['is_remark'] = 0;
        $redis = new Redis();
        if ($redis->has($this->wxacode_prefix.'-'.$this->client_uid)) {
            $info['is_remark'] = $redis->get($this->wxacode_prefix.'-'.$this->client_uid);
        }
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
        $user_mt4 = Db::name('user')->field('id,uid,zhmt4uid,zhmt4pwd,zhmt4server,isbuy')->where('uid', $this->client_uid)->find();
        $count = Db::name('user_mt4')->where(['uid' => $this->client_uid, 'mt4id' => $user_mt4['zhmt4uid']])->count();
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
            $arrSub = Db::name('user')->where('parent_id', $this->client_uid)->column('uid');
            $arrSubMt4 = Db::name('user')->where('parent_id', $this->client_uid)->where('isbuy', 1)->count();
            // 间接的邀请注册的
            if ( ! empty($arrSub)){
                $arrSub2 = Db::name('user')->where('parent_id', 'in', $arrSub)->count();
                $arrSubMt2 = Db::name('user')->where('parent_id', 'in', $arrSub)->where('isbuy', 1)->count();
            }
            $arrSubBuy = $arrSubBuy2 = 0;
            $first_sid = $this->sons_user_ids(1);
            $second_sid = $this->sons_user_ids(2);
            //邀请购买
            if (!empty($first_sid)) {
                $arrSubBuy = Db::name('user_money_log')->where(['uid' => $this->client_uid, 'class' => 'recharge', 'src_id' => ['in', $first_sid]])->count();
            }
            $second_srcid = [];
            if (!empty($first_sid) && !empty($second_sid)) {
                for ($i=0;$i<count($first_sid);$i++) {
                    for ($j=0;$j<count($second_sid);$j++) {
                        $second_srcid[] = $first_sid[$i].'-'.$second_sid[$j];
                    }
                }
            }
            if (!empty($second_srcid)) {
                $arrSubBuy2 = Db::name('user_money_log')->where(['uid' => $this->client_uid, 'class' => 'recharge', 'src_id' => ['in', $second_srcid]])->count();
            }

            //我的返佣金额
            $total_money = Db::name('user_money_log')->where(['uid' => $this->client_uid, 'status' =>1, 'class' => ['in',['money-follow-reward','recharge']]])->sum('amount');

            $data['arr_sub'] = count($arrSub);
            $data['arr_sub_mt4'] = $arrSubMt4;
            $data['arr_sub_buy'] = $arrSubBuy;
            $data['arr_sub2'] = $arrSub2;
            $data['arr_sub2_mt4'] = $arrSubMt2;
            $data['arr_sub2_buy'] = $arrSubBuy2;
            $data['total_money'] = number_format($total_money,2);
        }
        return json_encode(array('code'=> 200, 'msg' => '查询成功', 'result' => $data));
    }

    /**
     * 获取下级用户
     * @return string
     */
    public function sons_user()
    {
        $type = (int)input('type');
        parent::VerifyLogin();
        $pids = $this->get_pid($type, $this->client_uid);
        $sons = $this->get_sons($pids);
        return json_encode(['code' => 200, 'msg' => '查询成功', 'sons' => $sons]);

    }

    private function get_sons($pids)
    {
        $son_arr = ['data' => []];
        $where = [
            'parent_id' =>['in', $pids]
        ];
        if (!empty($pids)) {
            $son_arr = Db::name('user')->field('uid,nickname,login,u_img,imoney,parent_id,zhmt4uid,server')->where($where)->paginate(10)->toArray();
        }
        return $son_arr;
    }

    /**
     * 获取下级用户id
     * @param $nums  查询$nums级的下级用户
     * @return array
     */
    public function sons_user_ids($nums)
    {
        $pids = $this->get_pid($nums, $this->client_uid);
        return $this->get_sids($pids);
    }

    private function get_sids($pids)
    {
        $son_arr = [];
        $where = [
            'parent_id' =>['in', $pids]
        ];
        if (!empty($pids)) {
            $son_arr = Db::name('user')->where($where)->column('uid');
        }
        return $son_arr;
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
        $rootPath = dirname(dirname(dirname(ROOT_PATH))).'/static.v.yshfx.com/upload/image/';

        $content = input('post.content');

        $images = AppCommon::base64img($content); //图像名称

        if (file_get_contents($rootPath.$images)) {
            $count = Db::name('user')->where('uid', $this->client_uid)->update(['u_img' => $images]);
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
        exit(json_encode(['code' => 304, 'msg' => '敬请期待']));
        $amount = input('post.amount',0.00); //提现金额
        $account = input('post.account','');  //账号
        $remark = input('post.remark','');  //真实姓名
        $imoney = Db::name('user')->where('uid', $this->client_uid)->value('imoney');  //用户账户余额
        if (empty($remark)) {
            return json_encode(array('code'=> 302, 'msg' => '请填写真实姓名'));
        }
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
            $uid = $this->client_uid;
            $data = array(
                'uid' => $uid,
                'amount' => $amount,
                'account' => $account,
                'remark' => $remark,
                'add_time' => time(),
                'modify_time' => time(),
                'operator' => 'withdraw-h5-api'
            );
            Db::transaction(function () use ($data,$amount,$uid) {
                if (Db::name('user')->where(['id' => $uid, 'imoney' => ['>=', $amount]])->setDec('imoney', $amount)) {
                    Db::name('user_withdraw')->insert($data);
                    // 写入消费日志
                    Money::record($uid, $amount * (-1), 0, 'withdraw-apply', '提现');
                    // AppCommon::sendmsg_czsms('18164026961', '用户提现，请及时处理！');
                } else {
                    exit(json_encode(array('code' => 302, 'msg' => '申请提交失败, 请稍候再试.')));
                }
            });
            return json_encode(array('code' => 200, 'msg' => '申请提交成功!'));
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

    /**
     * 微信小程序用户账号密码绑定
     * @return string
     */
    public function wxUpdatePassword()
    {
        $password = input('password', ''); //密码
        $openid = input('openid'); //wx小程序用户openid
        $count = Db::name('user')->where('wxcode', $openid)->update(['password' => md5($password)]);
        if ($count) {
            return json_encode(['code' => 200, 'msg' => '密码设置成功']);
        }
        return json_encode(['code' => 302, 'msg' => '密码设置失败']);
    }

    /**生成二维码
     * @return string
     */
    public function getQRcode()
    {
        parent::verifyLogin();
        vendor('phpqrcode.phpqrcode');
        $uploadRoot = '/static.v.yshfx.com/upload/';

        $img_path = dirname(dirname(dirname(ROOT_PATH))).$uploadRoot.'wxcode/'.$this->client_uid.'_qr.png';
        if (!is_file($img_path)) {
            $url = 'http://h5.yshfx.com/wap/index.html?uid='.$this->client_uid;
            QRcode::png($url,$img_path,'QR_ECLEVEL_L',4,2);
        }
        return $img_path;
    }

    /**
     * 标识当前用户已点击收藏小程序码按钮
     */
    public function getRemarkWxacode()
    {
        $redis = new Redis();
        if (!$redis->has($this->wxacode_prefix.'-'.$this->client_uid)) {
            $redis->set($this->wxacode_prefix.'-'.$this->client_uid, 1);
            return json_encode(['code' => 200, 'msg' => '标识成功']);
        }
        return json_encode(['code' => 302, 'msg' => '标识失败']);
    }
}