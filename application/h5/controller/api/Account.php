<?php
/**
* 用户mt4账号
*/
namespace app\h5\controller\api;
use \think\Db;
use app\common\controller\Common as AppCommon;
use app\common\controller\Msg as AppMsg;
use app\common\controller\Upgrade as AppUpgrade;

class Account extends Common
{


    /**
     * 获取用户mt4账号
     * @return string
     */
    public function get(){
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
        $arrMt4 = array_values($arrMt4);
        return json_encode(array('code'=> 200, 'msg' => '查询成功', 'result' => $arrMt4));
    }

    /**
     * 添加多mt4账号
     */
    public function add_mt4()
    {
        $zhmt4uid = trim(input('zhmt4uid'));
        $slave_user = Db::name('UserMt4')->where('mt4id', $zhmt4uid)->count();
        $account = Db::name('Mt4Account')->where('mt4id', $zhmt4uid)->count();
        if (!empty($slave_user)) {
            echo json_encode(['code' => 302, 'msg' => '该mt4id账号已存在']);
            exit();
        }
        if (!empty($account)) {
            echo json_encode(['code' => 302, 'msg' => '该mt4id账号已绑定主信号，无法绑定跟单账号']);
            exit();
        }
        if ($this->userInfo['isbuy'] == 0 && !empty($this->userInfo['zhmt4uid'])){
            echo json_encode(array('code'=> 303, 'msg' => '您有mt4账号正在审核中,不能提交新的账号!'));
            exit();
        }

        if ($slave_user > 0 && $this->userInfo['server'] !== 'vip3') {
            echo json_encode(array('code'=> 304, 'msg' => '当前VIP等级无法绑定多mt4账户'));
            exit();
        }

        if (input('?post.zhmt4uid')){
            $mt4Data = array(
                'uid' => $this->client_uid,
                'mt4id' => trim(input('post.zhmt4uid')),
                'mt4pwd' => trim(input('post.zhmt4pwd')),
                'mt4server' => input('post.zhmt4server'),
                'modify_time' => time(),
                'add_time' => time(),
                'operator' => 'account-app-api',
            );

            // 处理数据
            Db::transaction(function() use($mt4Data){
                if (input('?post.src_id')){ // 改绑时删除老账号
                    Db::name('user_mt4')->where(['id' => input('post.src_id'), 'uid' => $this->userInfo['uid']])->delete();
                }

                $userData = array(
                    'zhmt4uid' => trim(input('post.zhmt4uid')),
                    'zhmt4pwd' => trim(input('post.zhmt4pwd')),
                    'zhmt4server' => input('post.zhmt4server'),
                    'modify_time' => time(),
                );

                // 保存数据
                $num = Db::name('user_mt4')->where('uid', $mt4Data['uid'])->count();

                // 相关vip限制
                $limitMt4 = ($this->userInfo['server'] == 'vip3')? 4 : 0;

                if ($num > $limitMt4){
                    echo json_encode(array('code'=> 302, 'msg' => sprintf('您最多只能绑定%s个mt4账号!', $limitMt4+1)));
                    exit();
                }

                $type = input('post.type');
                if ($type == 'auto_demo'){ // demo账号直接添加并通过审核
                    $demo = Db::name('mt4_demo')->where('mt4id', trim(input('post.zhmt4uid')))->find();
                    if (!empty($demo)) {
                        $userData['aliyun'] = $demo['remark'];
                        $userData['sblfix'] = $demo['sblfix'];
                        $mt4Data['remark'] = $demo['remark'];
                        $mt4Data['sblfix'] = $demo['sblfix'];
                    }
                    if ($num == 0){
                        $mt4Data['is_default'] = 1;
                        // 同时更新user
                        $userData['isbuy'] = 1;
                        Db::name('user')->where('uid', $mt4Data['uid'])->update($userData);
                    }
                    // 添加到列表库
                    try {
                        if (Db::name('user_mt4')->insert($mt4Data)){
                            // 更新状态
                            Db::name('mt4_demo')->where('mt4id', trim(input('post.zhmt4uid')))->update(['uid'=> $mt4Data['uid'], 'status' =>1, 'modify_time' => time()]);
                            // 发送短信
                            $mobile = '18164026961';
                            $msg = '新用户一键生成账号：' . trim(input('post.zhmt4uid'));
                            // AppCommon::sendmsg_czsms($mobile, $msg, 'binding_sms_demo' . $mt4Data['uid']);
                            echo json_encode(array('code'=> 200, 'msg' => '数据保存成功!'));
                        }
                    } catch (\Exception $e) {
                        echo json_encode(array('code'=> 302, 'msg' => '数据保存失败!'));
                    }
                }else{ // 其它账号添加user等待后台审核
                    $userData['isbuy'] = 0;
                    if (Db::name('user')->where('uid', $mt4Data['uid'])->update($userData)){

                        // 发送短信
                        $mobile = '18164026961';
                        $msg = '新用户绑定账号审核提醒,用户id：' . $mt4Data['uid'];
                        // AppCommon::sendmsg_czsms($mobile, $msg, 'binding_sms_' . $mt4Data['uid']);

                        echo json_encode(array('code'=> 200, 'msg' => '数据保存成功,请等待管理员审核!'));
                    }
                }
            });
        }
    }

    /**
     * 上传图片
     */
    public function uploadImg()
    {
        $rootPath = dirname(dirname(dirname(ROOT_PATH))).'/static.v.yshfx.com/upload/image/';

        $content = input('post.content');

        $images = AppCommon::base64img($content); //图像名称

        if (file_get_contents($rootPath.$images)) {
            return json_encode(['code' => 200, 'msg' => '上传成功', 'result' => ['img' => $images]]);
        }
        return json_encode(['code' => 302, 'msg' => '上传失败']);
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
                'operator' => 'cert_wxcode_api'
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
                'operator' => 'strategy_wxcode_api'
            );
            if (Db::name('user_strategy')->insert($data)){
                echo json_encode(array('code'=> 200, 'msg' => '提交成功,请等待审核!'));
            }else{
                echo json_encode(array('code'=> 302, 'msg' => sprintf('提交失败, 请稍候再试.')));
            }
        }
    }

    /**
     * 获取站内信
     * @return string
     */
    public function get_msg(){
        $type =  input('type');
        $arrMsg = array();
        if ( ! empty($this->client_uid)){
            $uid = $this->client_uid;
            if ($type == 'count'){
                $arrMsg = Db::name('user_msg')->where('uid', $uid)->where('status', 1)->count();
                // 系统群发消息
                $sysMsg = Db::name('user_msg')->where('uid', 0)->where('status', 1)->count();
                $arrMsg += $sysMsg;
            }else{
                $arrMsg = Db::name('user_msg')->where('uid', $uid)->whereOr('uid', 0)->order('id desc')->select();
            }
        }
        return json_encode(array('code'=> 200, 'msg' => '查询成功', 'data' => $arrMsg));
    }

    /**
     * 读取站内信
     * @return string
     */
    public function read_msg(){
        $id = input('id');
        $uid = $this->client_uid;
        $result = Db::name('user_msg')->where('uid', $uid)->where('id', $id)->update(['status' => 2, 'modify_time' => time()]);
        if ($result != 0){
            $msg = 'success';
        }else{
            $msg = 'fail';
        }
        return json_encode(array('code'=> 200, 'msg' => $msg));
    }

    /**
     * 信用金明细
     */
    public function credit(){
        $page = input('post.page');
        if ($page < 1) $page = 1;
        $arrCredit = Db::name('user_credit_log')->where('uid', $this->userInfo['uid'])->order('id desc')->limit(($page-1)*5, 5)->select();
        return json_encode(array('code'=> 200, 'msg' => '查询成功', 'data' => $arrCredit));
    }

    /**营销图片列表
     * @return string
     */
    public function marketing_pic()
    {
        $where = [
            'is_deleted' => 0,
            'show' => 1
        ];
        $pictures = Db::name('marketing_pic')->where($where)->order('add_time desc')->paginate(5);
        return json_encode(['code' => 200, 'msg' => '查询成功', 'result' => $pictures]);
    }

    /**用户通知记录添加
     * @param int $uid
     * @param int $src_id
     * @param string $class
     * @param string $gzhao
     * @return int|string
     */
    public function user_notify($uid=0, $src_id=0, $class='', $gzhao='')
    {
        $time = time();
        $data = [
            'uid' => $uid,
            'src_id' => $src_id,
            'class' => $class,
            'gzhao' => $gzhao,
            'add_time' => $time,
            'modify_time' => $time
        ];
        if (Db::name('user_notice')->where(['uid' => $uid, 'class' => $class])->count() > 0) {
            exit(json_encode(['code' => 307, 'msg' => '请勿重复添加通知记录']));
        }
        return Db::name('user_notice')->insert($data);
    }

    /**招募大师表单提交
     * @return string
     */
    public function recurit()
    {
        parent::verifyLogin();
        $name = input('post.name');
        $phone = input('post.phone');
        $verify = input('post.verify');
        parent::verifySms($phone,$verify);
        $data = [
            'uid' => $this->client_uid,
            'name' => $name,
            'phone' => $phone,
            'operator' => 'apis-account',
            'add_time' => time(),
            'modify_time' => time(),
        ];
        if (Db::name('recruit')->where(['uid' => $this->client_uid,'status' => 0])->count()) {
            return json_encode(['code' => 401, 'msg' => '您尚有待处理的审核，请勿频繁提交']);
        }
        Db::name('recruit')->insert($data);
        return json_encode(['code' => 200, 'msg' => '提交成功']);
    }

    /**申请修改下级
     * @return string
     */
    public function sid_update()
    {
        parent::verifyLogin();
        $name = input('post.name');
        $phone = input('post.phone');
        $data = [
            'uid' => $this->client_uid,
            'name' => $name,
            'phone' => $phone,
            'operator' => 'apis-account',
            'add_time' => time(),
            'modify_time' => time(),
        ];
        if (Db::name('sid_manage')->where(['uid' => $this->client_uid,'name' => $name, 'phone' => $phone])->count()) {
            return json_encode(['code' => 401, 'msg' => '您已提交审核，请勿重复提交']);
        }
        Db::name('sid_manage')->insert($data);
        return json_encode(['code' => 200, 'msg' => '提交成功']);
    }

    /**
     * 强制用户绑定手机号
     */
    public function binging_mobile()
    {
        $login = input('login');
        $password = input('password');
        $token = input('token');
        $verify = input('verify');
        $parent_id = input('uid'); // pid
        parent::verifySms($login,$verify);
        if (Db::name('user')->where('login', $login)->count()) {
            Db::name('user')->where('login',$login)->update(['gzhao' => base64_decode($token)]);
        } else {
            $data = [
                'login' => $login,
                'password' => md5($password),
                'gzhao' => base64_decode($token),
                'sign_ip' => $this->request->ip(),
                'add_time' => time(),
                'modify_time' => time()
            ];
            $id = Db::name('user')->insertGetId($data);
            if ($id) {
                Db::name('user')->where('id',$id)->update(['uid' => $id]);
                // 送vip 7 天
                AppUpgrade::updateVip(7, $id, '注册会员赠送', 'reg-h5', 'vip2');
                AppMsg::send($id, 'reg');
            }
        }
        $url = url('wap/index');
        if (!empty($parent_id)) {
            $url .= '?uid=' . $parent_id;
        }
        return json_encode(['code' => 200, 'msg' => '绑定成功', 'url' => $url]);
    }
    
}