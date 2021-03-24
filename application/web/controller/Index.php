<?php
/**
* zht-web首页
*/
namespace app\web\controller;
use think\Cookie;
use \think\Db;
use think\Controller;
use think\Request;
use think\Session;
use think\Config;
use app\web\services\Userinfo;
use app\common\controller\Common as AppCommon;
use app\common\controller\Msg as AppMsg;
use app\common\controller\Upgrade as AppUpgrade;
use app\common\controller\Gop as AppGop;

class Index extends Common
{
    public function index()
    {
        $arrAccount = Db::name('mt4_account')->where('show',1)->whereOr('show', 3)->order('score desc')->limit(5)->select();
        $this->assign('arrAccount', $arrAccount);

        // 国家配置
        $mt4country = Config::get('options.mt4country');
        $this->assign('mt4country', array_flip($mt4country));

        // 服务商
        $mt4service = Config::get('options.mt4service');
        $this->assign('mt4service', array_flip($mt4service));

        return $this->fetch();
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
            return json_encode(['code' => 200, 'msg' => '登录成功！']);
        }else{
            return json_encode(['code' => 302, 'msg' => '用户名或密码错误！']);
        }

    }

    /**
     * 注册
     * @return string
     */
    public function register()
    {
        $login = input('login'); //登录账户或者手机号
        $password = input('password'); //密码
        $code = input('code'); //邀请码
        $verify = input('verify'); //短信验证码
        $time = time();
        $type = input('type');  //标识是否为demo账号
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
        $sms_verify = Db::name('Mt4Smslog')->where(['login' => $login, 'status'=>'0','verify' => $verify,'add_time'=>['>',time()-30*60]])->find();
        if (empty($sms_verify)) {
            return json_encode(['code' => 302, 'msg' => '短信验证码错误!']);
        }

        # status  0：发送成功并且未使用过的 :1是未发送成功  :2的是已经使用了   
        Db::name('Mt4Smslog')->where(['id'=>$sms_verify['id']])->setField('status',2);

        $user_info = [
            'login' => $login,
            'password' => md5($password),
            'sign_ip' => $this->request->ip(),
            'modify_time' => $time,
            'add_time' => $time
        ];
        if (!empty($code)) {
            $user_info['parent_id'] = $code;
        }
        $demo = $data = [];
        // if (!empty($type)) {
        //     $demo = Db::name('Mt4Demo')->field('id,mt4id,mt4server,mt4pwd,mt4lpwd,remark')->where('status', 0)->order('add_time asc')->find();
        // }
        $result = Db::name('User')->insertGetId($user_info);
        if ((int)$result <= 0) {
            return json_encode(['code' => 302, 'msg' => '注册失败']);
        }
        // 送vip 7 天
        // if ( ! empty($code)){
        //     AppUpgrade::updateVip(7, intval($code), '邀请注册会员赠送', 'reg-api');
        // }

        // # 新用户送vip2 30天
        Db::name('User')->where('id', $result)->update(['server'=>'vip2','server_expire'=>time()+30*24*3600]);

        $demo_id = $isbuy = 0;
        if(!empty($demo)) {
            $data['zhmt4uid'] = $demo['mt4id'];  //mt4id
            $data['zhmt4pwd'] = $demo['mt4pwd'];  //mt4密码
            $data['zhmt4lpwd'] = $demo['mt4lpwd'];  //mt4观摩密码
            $data['zhmt4server'] = $demo['mt4server'];  //mt4服务器
            $data['aliyun'] = $demo['remark'];  //mt4服务器
            $demo_id = $demo['id'];
            $isbuy = 1;
        }
        Db::name('User')->where('id', $result)->update(array_merge(['uid' => $result,'isbuy' => $isbuy],$data));
        // Db::name('Mt4Demo')->where('id', $demo_id)->update(['status' => 1,'uid' => (int)$result, 'modify_time' => time()]);
        $this->login($login, $password);
        AppMsg::send($result, 'reg');
        return json_encode(['code' => 200, 'msg' => '注册成功', 'uid' => $result, 'demo' => $demo]);

    }

    /**
     *
     * 修改密码
     * @return string
     */
    public function update_pwd()
    {
        $this->verifyLogin();
        $old_password = input('old_pwd'); //原始密码
        $new_password = input('new_pwd'); //新密码
        $time = time();
        $uid = Session::get('zhtweb')['uid'];
        $user = Db::name('User')->where('uid', $uid)->find();
        if ($user['password'] !== md5($old_password)) {
            return json_encode(['code' => 302, 'msg' => '请输入正确的原始密码']);
        }

        $data = Db::name('User')->where('uid', $uid)->update(['password' => md5($new_password), 'modify_time' => $time]);
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
        $uid = input('uid');
        $zhmt4uid = input('zhmt4uid');  //mt4账号
        $mt4server = input('mt4server');  //mt4服务器
        $zhmt4pwd = input('zhmt4pwd');  //mt4密码
        $type = input('type');  //标识是否为demo账号
        $data = [
            'zhmt4uid' => $zhmt4uid,
            'zhmt4server' => $mt4server,
            'zhmt4pwd' => $zhmt4pwd,
        ];
        $user = Db::name('User')->where('zhmt4uid', $zhmt4uid)->find();
        $slave_user = Db::name('UserMt4')->where('mt4id', $zhmt4uid)->count();
        $account = Db::name('Mt4Account')->where('mt4id', $zhmt4uid)->count();
        if (!empty($user)) {
            return json_encode(['code' => 302, 'msg' => '该mt4id账号已存在']);
        }
        if (!empty($slave_user)) {
            return json_encode(['code' => 302, 'msg' => '该mt4id账号已存在']);
        }
        if (!empty($account)) {
            return json_encode(['code' => 302, 'msg' => '该mt4id账号已绑定主信号，无法绑定跟单账号']);
        }
        $user_info = Db::name('User')->where('uid', $uid)->find();
        if (!empty($user_info) && !empty($user_info['zhmt4uid']) && $user_info['isbuy'] == 0) {
            return json_encode(['code' => 302, 'msg' => '您还有mt4账号未通过审核，请审核通过后再添加mt4账号']);
        }

        if (!empty($type)) {
            $demo = Db::name('Mt4Demo')->where(['mt4id' => $zhmt4uid,'status' => 0])->find();
            if (!empty($demo)) {
                $where = [
                    'uid' => $uid,
                    'mt4id' => $zhmt4uid,
                    'mt4pwd' => $zhmt4pwd,
                    'mt4server' => $mt4server,
                    'remark' => $demo['remark'],
                    'operator' => 'account-demo',
                    'add_time' => time(),
                    'modify_time' => time(),
                ];
                $count = db('UserMt4')->where('uid', $uid)->count();
                if ($count >=5) {
                    return json_encode(array('code'=> 302, 'msg' => '最多只能绑定5个mt4账号!'));
                }elseif ($count === 0) {
                    $where['is_default'] = 1;
                    db('UserMt4')->insert($where);
                    //通过一键生成demo账户，默认是账户审核通过
                    $data['isbuy'] = 1;
                    $data['aliyun'] = $demo['remark'];
                    $update = Db::name('User')->where('uid', $uid)->update($data);
                    if ($update === 0) {
                        return json_encode(['code' => 302, 'msg' => 'mt4账号绑定失败']);
                    }
                } else {
                    db('UserMt4')->insert($where);
                }

                //标记该mt4id已使用
                Db::name('Mt4Demo')->where('mt4id', $zhmt4uid)->update(['status' => 1, 'uid' => $uid, 'modify_time' => time()]);
                AppMsg::send($uid, 'bind');
            }
        } else {
            $count = Db::name('User')->where('uid', $uid)->update($data);
            if ($count === 0) {
                return json_encode(['code' => 302, 'msg' => 'mt4账号绑定失败']);
            }
            $mobile = '18164026961';
            $msg = '新用户绑定账号审核提醒,用户id：' . $uid;
            // AppCommon::sendmsg_czsms($mobile, $msg, 'binding_sms_' . $uid);
        }
        $this->userInfo['zhmt4uid'] = $zhmt4uid;
        return json_encode(['code' => 200, 'msg' => 'mt4账号绑定成功']);
    }

    /**
     * 用户改绑mt4账号
     * @return string
     */
    public function unbinding()
    {
        $this->verifyLogin();
        $uid = Session::get('zhtweb')['uid'];
        $zhmt4uid = htmlspecialchars(input('mt4uid'));  //mt4账号
        $zhmt4server = htmlspecialchars(input('mt4server'));  //mt4服务器
        $zhmt4pwd = htmlspecialchars(input('mt4pwd'));  //mt4密码
        $time = time();
        $user = Db::name('User')->where('zhmt4uid', $zhmt4uid)->count();
        $slave_user = Db::name('UserMt4')->where('mt4id', $zhmt4uid)->count();
        $account = Db::name('Mt4Account')->where('mt4id', $zhmt4uid)->count();
        if (!empty($user)) {
            return json_encode(['code' => 302, 'msg' => '该mt4id账号已存在']);
        }
        if (!empty($slave_user)) {
            return json_encode(['code' => 302, 'msg' => '该mt4id从账号已存在']);
        }
        if (!empty($account)) {
            return json_encode(['code' => 302, 'msg' => '该mt4id账号已绑定主信号，无法绑定跟单账号']);
        }
        $data = Db::name('User')->where('uid', $uid)->update([
            'zhmt4uid' => $zhmt4uid,
            'zhmt4server' => $zhmt4server,
            'zhmt4pwd' => $zhmt4pwd,
            'isbuy' => 0,
            'modify_time' => $time
        ]);
        if ($data >0) {
            // 保存日志
            $logData = array(
                'uid' => $uid,
                'zhmt4uid' => $zhmt4uid,
                'zhmt4pwd' => $zhmt4pwd,
                'zhmt4server' => $zhmt4server,
                'add_time' => time(),
            );
            Db::name('usersevers')->insert($logData);
            Db::name('Mt4DiyAccount')->where('uid', $uid)->delete();  //改绑成功，删除该用户之前的所有跟单数据
            Db::name('User')->where('uid', $uid)->update(['status' => 2]);  //用户跟单状态status 2：离线
            $mobile = '18164026961';
            $msg = '新用户绑定账号审核提醒,用户id：'.$uid;
            // AppCommon::sendmsg_czsms($mobile, $msg, 'unbinding_sms_'.$uid);

        }
        return json_encode(['code' => 200, 'msg' => 'mt4账号改绑申请成功，等待审核!']);
    }

    /**
     * 退出登录
     */
    public function signout($callback = '/')
    {
        Session::delete('zhtweb');
        if ($callback == 'master'){
            $this->redirect('master/index');
        }else{
            $this->redirect('/');
        }
        
    }

    /**
     * 用户收支记录
     */
    public function money()
    {
        $this->verifyLogin();
        $uid = Session::get('zhtweb')['uid'];
        if (!empty($uid)){
            // 导入分页类
            import ( 'ORG.Util.Page' );
            $arrOrder = Db::name('Mt4Userinfo')->where(array('uid'=>$uid))->order('id desc')->paginate(10, false, ['var_page' => 'page'])->toArray();
            return json_encode(array_merge(['code' => 200], $arrOrder));
        }
    }


    /**
     * 获取用户创建信号
     * @return string
     */
    public function get_signal()
    {
        $this->verifyLogin();
        $signal = Db::name('Mt4Signal')->where('uid', Session::get('zhtweb')['uid'])->order('add_time desc')->paginate(10)->toArray();
        return json_encode(array_merge(['code' => 200, 'msg' => '查询成功'],$signal));
    }

    /**
     * 用户组合跟单数据
     * @return string
     */
    public function my_order()
    {
        $this->verifyLogin();
        $uid = Session::get('zhtweb')['uid'];
        $data = Db::name('Mt4DiyAccount')->where('uid', $uid)->order('add_time desc')->paginate(10)->toArray();
        return json_encode(array_merge(['code' => 200, 'msg' => '查询成功'], $data));

    }

    /**
     * 获取短信验证码
     * @return string
     */
    public function get_smscode()
    {
        $mobile = input('mobile');  //手机号
        if(!preg_match('/^1[3-8]\d{9}$/', $mobile)){
            return json_encode(['code' => 400, 'msg' => '手机号格式不对']);
        }
        // $type = input('type');   // type: 1:账号注册  2:修改密码（忘记密码）
        $verify = mt_rand(123456, 999999);//获取随机验证码

        // switch ($type) {
        //     case 1 :
        //         $msg = '网页短信验证码为：' . $verify . '，请勿将验证码提供给他人。';
        //         break;
        //     case 2:
        //         $msg = '您正在修改密码，短信验证码为：' . $verify . '，请勿将验证码提供给他人。';
        //         break;
        //     default :
        //         $msg = '网页短信验证码为：' . $verify . '，请勿将验证码提供给他人。';
        //         break;
        // }

        // if (AppGop::check()) {
            $res_send = AppCommon::sendmsg_czsms($mobile, $verify, 'smscode_' . $mobile); # 短信发送结果
            # 短信表中的status：'0'表示发送成功 '1'失败
            if($res_send === '1'){
                return json_encode(['code' => 400, 'msg' => '发送失败']);
            }else{
                $insert_data = [
                    'login'     => $mobile,
                    'verify'    => $verify,
                    'add_time'  => time(),
                    'status'    => 0 ,
                ];

                Db::name('Mt4Smslog')->insert($insert_data);
                return json_encode(['code' => 200, 'msg' => '发送成功']);
            }
        // }else{
        //     return json_encode(['code' => 402, 'msg' => '发送失败']);
        // }
    }

    /**
     * 修改用户信息
     * @return string
     */
    public function update_userinfo()
    {
        $nickname = htmlspecialchars(input('nickname'));  //昵称
        $area = input('area');   //区域
        $img = input('u_img','');  //用户图像地址
        $uid = input('uid',0);
        $u_img = ''; //用户图像
        $conf = [
            'nickname' => $nickname,
            'area' => $area,
        ];
        $user = Db::name('User')->where('uid',$uid)->find();
        if (!empty($user)) {
            if ($img !== $user['u_img']) {
                $u_img = $user['u_img'];
                $conf['u_img'] = $img;
            }
        }
        $data = Db::name('User')->where('uid',$uid)->update($conf);
        $rootPath = dirname(dirname(dirname(ROOT_PATH))).'/static.v.yshfx.com/upload/image/';  //上传图片根路径
        if ($data > 0) {
            /*if ($u_img !== $img) {
                shell_exec('rm -rf '.$rootPath.$u_img);
            }*/
            return json_encode(['code' => 200, 'msg' => '修改成功']);
        }
        return json_encode(['code' => 302, 'msg' => '暂无任何修改']);
    }


    public function test(){
        throw new \think\exception\HttpException(404, '异常消息');
    }
}
