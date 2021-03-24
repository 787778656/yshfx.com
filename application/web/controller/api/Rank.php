<?php
/**
* Rank操作相关
* @author efon.cheng<efon@icheng.xyz>
* 2018-01-12
*/
namespace app\web\controller\api;
use \think\Config;
use \think\Db;
use \think\Controller;
use app\common\controller\Common as AppCommon;
use \think\Cookie;
use \think\Session;
use \think\cache\driver\Redis;
use app\common\controller\Msg as AppMsg;

class Rank extends Controller
{
    protected $userInfo;
    protected $redis;
    protected $static = 'http://static.v.yshfx.com/';
    public function __construct(){
        $this->userInfo = Session::get('zhtweb');
        // 未登录
        if ( ! empty($this->userInfo)){
            $this->userInfo = Db::name('user')->where('uid', $this->userInfo['uid'])->find();
        }
        // redis
        $this->redis = new Redis();
    }

    /**
     * 匹配
     */
    public function matching()
    {

        if (empty($this->userInfo)){
            exit(json_encode(array('code' => 301, 'msg' => '请先登录!')));
        }
        
        // 是否已经在对站中
        $pk = Db::name('pk_list')->where('uid', $this->userInfo['uid'])->where('status', 0)->count();
        if ($pk > 0){
            exit(json_encode(array('code'=> 303, 'msg' => '您正在对阵中!')));
        }
        // 获取当前用户mt4账号
        $mt4id = Db::name('user_mt4')->where('uid', $this->userInfo['uid'])->order('id')->value('mt4id');
        if ( ! $mt4id){
            exit(json_encode(array('code'=> 302, 'msg' => '请先绑定mt4账号!')));
        }
        // 获取当前用户级别
        $level = Db::name('pk_rank')->where('uid', $this->userInfo['uid'])->value('level');

        if (empty($level)){ // 首次pk,加入到排行榜
            $level = 1;
            $dataRank = array(
                    'uid' => $this->userInfo['uid'], 
                    'mt4id' => $mt4id,
                    'level' => $level,
                    'operator' => 'rank-web-api',
                    'add_time' => time(),
                    'modify_time' => time()
                );
            Db::name('pk_rank')->insert($dataRank);
        }

        // 写入pk等待队列
        $key = sprintf('%s-%s', 'zhtWeb', 'pk_waiting');
        $pkWaiting = array();
        if ($this->redis->has($key)){
            $pkWaiting = unserialize($this->redis->get($key));
        }
        // 加入当前匹配
        $pkWaiting[$this->userInfo['uid']] = $level;
        $this->redis->set($key, serialize($pkWaiting));

        sleep(5); // 等待5s,区配中...

        // 开始匹配
        $pkWaiting = unserialize($this->redis->get($key));
        if (isset($pkWaiting[$this->userInfo['uid']])){
            // 剔除自己
            unset($pkWaiting[$this->userInfo['uid']]);
            // 更新队列
            $this->redis->set($key, serialize($pkWaiting));

            $pkUid = array_search($level, $pkWaiting);

            $pkInfo = array();
            if ($pkUid){
                // 从等待队列中剔除
                unset($pkWaiting[$pkUid]);
                $this->redis->set($key, serialize($pkWaiting));
                
                $pkInfo['mt4id'] = Db::name('user_mt4')->where('uid', $pkUid)->order('id')->value('mt4id');
                //用户信息
                $arrPk = Db::name('user')->where('uid', $pkUid)->find(); 
                $pkInfo['remark'] = array(
                        'u_img' => $arrPk['u_img'],
                        'nickname' => empty($arrPk['nickname']) ? $arrPk['login'] : $arrPk['nickname'],
                    );
            }else{
                // 没有真实用户，返回机器人
                $pkUid = 0;
                $pkInfo['mt4id'] = 0;
                // 用户信息
                $arrNick = Config::get('vdata')['nick'];
                $nickKey = rand(0, count($arrNick)-1);
                $pkInfo['remark'] = array(
                        'vphoto' => rand(1,95),
                        'nickname' => $arrNick[$nickKey],
                    );                
            }
            // 序列化，以便保存
            $pkInfo['remark'] = serialize($pkInfo['remark']);

            $pkInfo['uid'] = $pkUid;

            // 写入pk列表
            $session = time();
            $remark = array(
                    'u_img' => $this->userInfo['u_img'],
                    'nickname' => empty($this->userInfo['nickname']) ? $this->userInfo['login'] : $this->userInfo['nickname'],
                );

            // 记录数据
            $remark = serialize($remark);
            $arrMatch = array(
                    array(
                        'uid' => $this->userInfo['uid'],
                        'mt4id' => $mt4id,
                        'session' => $session,
                        'remark' => $remark,
                        'level' => $level,
                        'balance' => 10000,
                        'equity' => 10000,
                        'operator' => 'rank-web-api',
                        'add_time' => time()
                    ),
                    array(
                        'uid' => $pkInfo['uid'],
                        'mt4id' => $pkInfo['mt4id'],
                        'session' => $session,
                        'remark' => $pkInfo['remark'],
                        'level' => $level,
                        'balance' => 10000,
                        'equity' => 10000,
                        'operator' => 'rank-web-api',
                        'add_time' => time()
                    ),
                );

            if (Db::name('pk_list')->insertAll($arrMatch)){
                $data = unserialize($pkInfo['remark']);
                // 获取图片
                if (isset($data['u_img'])){
                    if (stristr($data['u_img'], 'http')){
                        $data['vphoto'] = $data['u_img'];
                    }else{
                        if ( ! empty($data['u_img'])){
                            $data['vphoto'] = $this->static."upload/image/{$pkinfo['u_img']}";
                        }else{
                            $data['vphoto'] = $this->static."image/combinationHead4.png";
                        }
                    }
                }else{
                    if ($data['vphoto'] <= 95){
                        $data['vphoto'] = $this->static."image/vphoto/{$data['vphoto']}.jpg";
                    }else{
                        $data['vphoto'] = $this->static."image/combinationHead4.png";
                    }
                }
                echo json_encode(array('code'=> 200, 'msg' => '匹配成功,即将开始pk!', 'data' => $data));
            }else{
                echo json_encode(array('code'=> 303, 'msg' => '匹配失败,请稍候再试!'));
            }
        }else{ // 已被别人匹配,直接输出结果
            $data = array('vphoto' => $this->static."image/combinationHead4.png", 'nickname' => '神秘选手'); // 默认数据
            echo json_encode(array('code'=> 200, 'msg' => '匹配成功,即将开始pk.', 'data' => $data));
        }
    }

    /**
    * 时时账户信息
    */
    public function detail($account = 0, $session = 0){
        $result = Db::name('pk_list')->field('balance, balance_now, equity, equity_now')->where(['mt4id' => $account, 'session' => $session])->find();
        // 输出结果
        if ( ! empty($result)){
            $result = json_encode(array('code'=> 200, 'msg' => '', 'data' => $result));
        }else{
            $result = json_encode(array('code'=> 301, 'msg' => '参数错误!'));
        }        
        echo $result;
    }

    /**
    * 账号评分
    */
    public function account_score($account = 0){
        if ( ! empty($account)){
            $result = file_get_contents(sprintf('http://api.yshfx.com/v1.web/account_score?account=%s', $account));            
            //$result = json_decode($result);
            //preg_match('/(*)/', $result, $matches);
            preg_match('/({.*})/', $result, $matches, PREG_OFFSET_CAPTURE);

            $result = json_decode($matches[0][0]);

            $callback = input('get.callback');
            $result = sprintf('%s(%s)', $callback, json_encode(array('code'=> 200, 'msg' => '', 'data' => $result->data)));
            echo $result;
        }else{
            $result['year_proft'] = rand(-100, 100);
            $result['trade_win'] = rand(-100, 100);
            $result['equity_increase'] = rand(-100, 100);
            $result['profit_offset'] = rand(-100, 100);
            $result['profit_factor'] = rand(-100, 100);
            $result['profit_score'] = rand(-100, 100);
            $result['balance_drawdown'] = rand(-100, 100);
            $result['equity_drawdown'] = rand(-100, 100);
            $result['max_money'] = rand(-100, 100);
            $result['loss_max'] = rand(-100, 100);
            $result['loss_day_max'] = rand(-100, 100);
            $result['loss_score'] = rand(-100, 100);

            $callback = input('get.callback');
            $result = sprintf('%s(%s)', $callback, json_encode(array('code'=> 200, 'msg' => '', 'data' => $result)));
            echo $result;
        }
    }

    /**
    * 时时账户信息
    **/
    public function finish($session = 0){
        $session = input('post.session');
        if ( ! empty($session)){
            $data['status'] = 1;
            if (Db::name('pk_list')->where('session', $session)->update($data)){
                echo json_encode(array('code'=> 200, 'msg' => '操作成功！'));
            }else{
                echo json_encode(array('code'=> 301, 'msg' => '操作失败，请稍候再试！'));
            }
        }
    }


    /**
    * 自动生成匹配
    */
    public function auto_matching(){

        // 没有真实用户，返回机器人
        $pkUid = 0;
        $pkInfo['mt4id'] = 100000;
        $session = time();
        // 随机级别
        $level = rand(1,20);
        // 用户信息
        $arrNick = Config::get('vdata')['nick'];
        $nickKey = rand(0, count($arrNick)-1);
        $pkInfo['remark'] = array(
            'vphoto' => rand(1,95),
            'nickname' => $arrNick[$nickKey],
        );

        // 序列化，以便保存
        $pkInfo['remark'] = serialize($pkInfo['remark']);
        $pkInfo['uid'] = 0;

        // 生成随机信息
        $nickKey = rand(0, count($arrNick)-1);
        $remark = array(
            'vphoto' => rand(1,95),
            'nickname' => $arrNick[$nickKey],
        );

        $remark = serialize($remark);

        $arrMatch = array(
                array(
                    'uid' => 0,
                    'mt4id' => 0,
                    'session' => $session,
                    'remark' => $remark,
                    'level' => $level,
                    'balance' => 10000,
                    'equity' => 10000,
                    'operator' => 'rank-web-api',
                    'add_time' => time()
                ),
                array(
                    'uid' => $pkInfo['uid'],
                    'mt4id' => $pkInfo['mt4id'],
                    'session' => $session,
                    'remark' => $pkInfo['remark'],
                    'level' => $level,
                    'balance' => 10000,
                    'equity' => 10000,
                    'operator' => 'rank-web-api',
                    'add_time' => time()
                ),
            );
        Db::name('pk_list')->insertAll($arrMatch);

        echo '<pre>';
        var_dump($arrMatch);
    }
}