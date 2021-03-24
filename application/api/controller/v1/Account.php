<?php
/**
* 用户mt4账号
*/
namespace app\api\controller\v1;
use \think\Db;
use app\common\controller\Common as AppCommon;

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
        $zhmt4uid = input('zhmt4uid');
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
                    Db::name('user_mt4')->where(['id' => input('post.src_id'), 'uid' => this->userInfo['uid']])->delete();
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
                            $mobile = '16675190411';
                            $msg = '新用户一键生成账号：' . trim(input('post.zhmt4uid'));
                            AppCommon::sendmsg_czsms($mobile, $msg, 'binding_sms_demo' . $mt4Data['uid']);
                            echo json_encode(array('code'=> 200, 'msg' => '数据保存成功!'));
                        }
                    } catch (\Exception $e) {
                        echo json_encode(array('code'=> 302, 'msg' => '数据保存失败!'));
                    }
                }else{ // 其它账号添加user等待后台审核
                    $userData['isbuy'] = 0;
                    if (Db::name('user')->where('uid', $mt4Data['uid'])->update($userData)){

                        // 发送短信
                        $mobile = '16675190411';
                        $msg = '新用户绑定账号审核提醒,用户id：' . $mt4Data['uid'];
                        AppCommon::sendmsg_czsms($mobile, $msg, 'binding_sms_' . $mt4Data['uid']);

                        echo json_encode(array('code'=> 200, 'msg' => '数据保存成功,请等待管理员审核!'));
                    }
                }
            });
        }
    }
}