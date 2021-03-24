<?php
/**
* zht-web导航栏中展示页面
*/
namespace app\web\controller;
use \think\Db;
use think\Controller;
use think\Request;
use think\Session;
use \think\cache\driver\Redis;

class Other extends Common
{

    /**
     * 用户在线留言
     * @return string
     */
    public function user_content()
    {
        $name = input('name');
        $email = input('email');
        $content = input('message');  //留言内容
        $mobile = input('mobile'); //用户手机号
        $time = time();
        $data = [
            'name' => $name,
            'email' => $email,
            'content' => $content
        ];
        if (!empty($mobile)) {
            $data['mobile'] = $mobile;
        }
        $info = Db::name('Mt4Content')->where($data)->find();
        if (!empty($info)) {
            return json_encode(['code' => 302, 'msg' => '请勿重复提交数据!']);
        }
        $data['add_time'] = $time;
        $data['modify_time'] = $time;
        $result = Db::name('Mt4Content')->insert($data);
        if (!$result) {
            return json_encode(['code' => 302, 'msg' => '提交失败,请重新提交!']);
        }

        return json_encode(['code' => 200, 'msg' => '提交成功']);

    }

    /**
     * 用户在线留言
     * @return string
     */
    public function about($sid = 'introduce'){
        $this->assign('pageName', $sid);
        return $this->fetch();
    }

    # 在线预约
    public function yuyue_add()
    {
        $name = htmlspecialchars(trim(input('post.name')));
        $phone = htmlspecialchars(trim(input('post.phone')));

        # 手机号正则匹配
        if($name && preg_match('/^1[35-9]\d{9}$/', $phone)){
            if(Db::name('mt4_comment')->insert([
                'uid'   =>  -1,
                'username'  =>  $name,
                'comment'  =>  $phone,
                'add_time'  =>  time(),
                'modify_time'  =>  time(),
            ])){
                exit(json_encode(['code'=>200, 'msg'=>'操作成功']));
            }
        }

        exit(json_encode(['code'=>400, 'msg'=>'操作失败']));
    }

    /**
     * 折线图
     * @return string
     */
    public function wxchart(){
        $redis = new Redis();
        $key = sprintf('%s-%s', 'zhtEA', 'account_chart2png_wx');
        if (true || !$redis->has($key)){
            $arrAccount = Db::name('mt4_account')->column('mt4id, rand_profit');
            $this->assign('arrAccount', $arrAccount);
            $redis->set($key, time(), 3600*24);
            return $this->fetch();
        }
    }

    public function webchart(){
        $redis = new Redis();
        $key = sprintf('%s-%s', 'zhtEA', 'account_chart2png_web');
        if (true || !$redis->has($key)){
            $arrAccount = Db::name('mt4_account')->column('mt4id, rand_profit');
            $this->assign('arrAccount', $arrAccount);
            $redis->set($key, time(), 3600*24);
            return $this->fetch();
        }
    }

}
