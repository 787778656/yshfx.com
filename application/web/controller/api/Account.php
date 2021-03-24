<?php
/**
* 接口程序
* @author efon.cheng<efon@icheng.xyz>
* 2017-12-06
*/
namespace app\web\controller\api;
use \think\Config;
use \think\Db;
use \think\Controller;
use app\common\controller\Common as AppCommon;
use \think\Cookie;
use \think\Session;
use \think\cache\driver\Redis;

class Account extends Controller
{

    protected $userInfo;
    public function __construct(){
        parent::__construct();
        $this->userInfo = Session::get('zhtweb');
        // 未登录
        if (empty($this->userInfo)){
            exit(json_encode(array('code'=> 301, 'msg' => '请先登录!')));
        }

        $this->userInfo = Db::name('user')->where('uid', $this->userInfo['uid'])->find();

        // vip过期
        if ($this->userInfo['server_expire'] < time()){
           $this->userInfo['server'] = '';
        }
    }

    /**
    * 添加mt4账号
    * @return string
    */
    public function add(){

        $zhmt4uid = input('zhmt4uid');
        // vip3验证
        $slaveMt4 = Db::name('UserMt4')->where('uid', $this->userInfo['uid'])->count();

        if (!input('?post.src_id') && $slaveMt4 > 0 && $this->userInfo['server'] != 'vip3'){ //vip3验证
            echo json_encode(['code' => 302, 'msg' => '只有vip3才能绑定多个mt4账号，请升级成vip3后再操作!']);
            exit();
        }

        $slave_user = Db::name('UserMt4')->where('mt4id', $zhmt4uid)->count();
        $account = Db::name('Mt4Account')->where('mt4id', $zhmt4uid)->count();

        if (!empty($slave_user) && !input('?post.src_id')) {
            echo json_encode(['code' => 302, 'msg' => '该mt4id账号已存在!']);
            exit();
        }
        if (!empty($account)) {
            echo json_encode(['code' => 302, 'msg' => '该mt4id账号已绑定主信号，无法绑定跟单账号!']);
            exit();
        }
        if ($this->userInfo['isbuy'] == 0 && !empty($this->userInfo['zhmt4uid'])){
            echo json_encode(array('code'=> 303, 'msg' => '您有mt4账号正在审核中,不能提交新的账号!'));
            exit();
        }
        
        if (input('?post.zhmt4uid')){
            $mt4Data = array(
                'uid' => $this->userInfo['uid'],
                'mt4id' => trim(htmlspecialchars(input('post.zhmt4uid'))),
                'mt4pwd' => trim(htmlspecialchars(input('post.zhmt4pwd'))),
                'mt4server' => htmlspecialchars(input('post.zhmt4server')),
                'modify_time' => time(),
                'add_time' => time(),
                'operator' => 'account-api',
            );

            // 处理数据
            Db::transaction(function() use($mt4Data,$slaveMt4){
                $res_delete = 0; // 删除老账号的记录值
                if (input('?post.src_id')){ // 改绑时删除老账号
                    $res_delete = Db::name('user_mt4')->where(['id' => input('post.src_id'), 'uid' => $this->userInfo['uid']])->delete();
                }
                
                // 如果user_mt4表中的记录超过5次，同时老账号没有删除成功
                if($slaveMt4 >= 5 && !$res_delete){
                    echo json_encode(['code' => 302, 'msg' => '一个用户最多只能绑定5个mt4id账号']);
                    exit();
                }

                $userData = array(
                    'zhmt4uid' => trim(htmlspecialchars(input('post.zhmt4uid'))),
                    'zhmt4pwd' => trim(htmlspecialchars(input('post.zhmt4pwd'))),
                    'zhmt4server' => htmlspecialchars(input('post.zhmt4server')),
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
                if ($type == 'auto_demo'){ // 自动生成账号直接添加并通过审核
                    $demo = db('mt4_demo')->where('mt4id', trim(input('post.zhmt4uid')))->find();
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
    * 获取账号
    * @return string
    */    
    public function get(){
        $arrMt4 = Db::name('user_mt4')->where('uid', $this->userInfo['uid'])->field('id,uid,mt4id,mt4server,mt4pwd,sh,status')->order('id desc')->select();
        // 加入待审mt4
        if ($this->userInfo['isbuy'] == 0 && !empty($this->userInfo['zhmt4uid'])){
            $arrMt4[] = array('id' => 0, 'uid' => $this->userInfo['uid'], 'mt4id' => $this->userInfo['zhmt4uid'], 'mt4server' => $this->userInfo['zhmt4server'], 'mt4pwd' => $this->userInfo['zhmt4pwd'], 'sh' => $this->userInfo['isbuy']);
        }

        $callback = input('get.callback');
        $result = sprintf('%s(%s)', $callback, json_encode(array('code'=> 200, 'msg' => '', 'data' => $arrMt4)));
        echo $result;
    }

    /**申请修改下级
     * @return string
     */
    public function sid_update()
    {
        $name = input('post.name');
        $phone = input('post.phone');
        $uid = $this->userInfo['uid'];
        $data = [
            'uid' => $uid,
            'name' => $name,
            'phone' => $phone,
            'operator' => 'webapi-account',
            'add_time' => time(),
            'modify_time' => time(),
        ];
        if (Db::name('sid_manage')->where(['uid' => $uid,'name' => $name, 'phone' => $phone])->count()) {
            return json_encode(['code' => 401, 'msg' => '您已提交审核，请勿重复提交']);
        }
        Db::name('sid_manage')->insert($data);
        return json_encode(['code' => 200, 'msg' => '提交成功']);
    }
}