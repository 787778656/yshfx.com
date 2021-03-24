<?php
/**
* 工作室
* @author efon.cheng<efon@icheng.xyz>
* 2017-12-19
*/
namespace app\web\controller;
use \think\Config;
use \think\Db;
use \think\Controller;
use app\common\controller\Common as AppCommon;
use \think\Cookie;
use \think\Session;
use \think\Request;
use \think\cache\driver\Redis;

class Studio extends Common
{

    protected $userInfo;
    protected $redis;
    public function __construct(){
        parent::__construct();

        // redis
        $this->redis = new Redis();
        // 国家配置
        $mt4country = Config::get('options.mt4country');
        $this->assign('mt4country', array_flip($mt4country));

        // 服务商
        $mt4service = Config::get('options.mt4service');
        $this->assign('mt4service', array_flip($mt4service));

        $this->userInfo = Session::get('zhtweb');
        $this->userInfo = Db::name('user')->where('uid', $this->userInfo['uid'])->find();
    }

    /**
    * 工作室
    */
    public function index($uid = 0)
    {
        if ( ! empty($uid)){
            $arrStudio = $this->show_studio($uid);
            if ( ! empty($arrStudio)){
                // 加入直播
                $live = Db('studio_live')->where('uid', $uid)->order('start_time desc')->limit(4)->select();  

                // 补充不足的直播预告
                $arrStudio['live'] = $live;
                if (count($live) < 4){
                    for ($i = 0; $i < 4-count($live); $i++) {
                        $arrStudio['live'][] = array(
                                'id' => 0, 
                                'uid' => $this->userInfo['uid'], 
                                'studio_id' => $arrStudio['id'], 
                                'title' => '',
                                'type' => '',
                                'start_time' => '',
                                'end_time' => ''
                            );
                    }
                }

                $this->assign('arrStudio', $arrStudio);

                $showManage = ($uid == $this->userInfo['uid']) ? true : false;                
                $this->assign('showManage', $showManage);

                // 生成访客
                $this->assign('arrVisitor', $this->tvisitor($arrStudio['id'], $arrStudio['level']));

                // 留言信息
                $arrMsg = Db('studio_msg')->where('studio_id', $arrStudio['id'])->order('id desc')->limit(10)->select();
                $this->assign('arrMsg', $arrMsg);
                
                // 跟单待审信息
                $arrFollow = Db('studio_follow')->where('studio_id', $arrStudio['id'])->where('status', 0)->order('id desc')->select();
                $this->assign('arrFollow', $arrFollow);

                // 转载信号
                $arrAccout = Db::name('mt4_account')->where('show',1)->order('score desc')->select();
                $this->assign('arrAccout', $arrAccout);

                // 工作室信号
                $arrStudioAccount = Db::name('studio_account')->alias('a')->where('a.studio_id', $arrStudio['id'])->join('mt4_account b','b.mt4id= a.account')->field('b.*, a.id as aid,a.account as account, a.name as studio_account_name, a.type as type, a.param as param, a.expire_time as expire_time')->order('a.id desc')->select();
                
                $this->assign('arrStudioAccount', $arrStudioAccount);

                // 服务商
                $mt4service = Config::get('options.mt4service');

                $this->assign('mt4service', array_flip($mt4service));

                return $this->fetch();
            }else{
                $this->error('没有相关数据！', url('/'));
            }
        }else{
            $this->error('没有相关数据！', url('/'));
        }
    }

    /**
    * 工作室资料编辑
    * @return string
    */
    public function edit(){
        $arrStudio = Db::name('studio')->where('uid', $this->userInfo['uid'])->find();
        if ( ! empty($arrStudio)){
            $this->assign('arrStudio', $arrStudio);
        }else{
            $this->error('您还未开通工作室！', url('studio/create'));
        }
        return $this->fetch();
    }

    /**
     * 主信号用户详情页
     * @return mixed
     */
    public function detail($sid = 0, $account = 0){
        // 工作室
        $arrStudio = Db::name('studio')->where('id', $sid)->find();
        $this->assign('arrStudio', $arrStudio);

        if ( ! empty($account)){
            $arrAccount = Db::name('mt4_account')->where('mt4id', $account)->find();
            $arrStudio = Db::name('studio_account')->where(['studio_id' => $sid, 'account' => $account])->find();

            if ( empty($arrAccount) || empty($arrStudio)) $this->error('没有相关数据！', sprintf('/studio/%s', $sid));
            $this->assign('studioAccount', $arrStudio);
            $this->assign('accountInfo', $arrAccount);

            // 账号缓存信息
            $key = sprintf('%s-%s-%s', 'zhtEA', 'mt4_account_detail', $account);
            $arrCache = array();
            $redis = new Redis();
            if ($redis->get($key)){
                $arrCache = unserialize($redis->get($key));
            }
            $this->assign('accountDtail', $arrCache);
            
            // 获取总统计
            $arrTrade = Db::name('mt4_account_statistics')->where('account', $account)->order('id desc')->find();
            $this->assign('arrTrade', $arrTrade);

            // 获取月统计
            $formatMdata=[];
            $arrMdata = Db::name('mt4_account_mstatistics')->where('account', $account)->order('year,month')->select();
            foreach ($arrMdata as $key => $mdata) {
                $formatMdata[$mdata['year']][$mdata['month']] = $mdata;
            }

            $this->assign('mdata', $formatMdata);

            // 获取真实跟随者
            $tFollow = Db::name('mt4_diy_account')->where('mt4id', $account)->count();
            $this->assign('follow', $tFollow);

            //评论数量统计
            $count = Db::name('Mt4Comment')->where(['mt4id' => $account, 'status' => 1])->count();
            $this->assign('count', $count);
            $this->assign('account', $account);

            return $this->fetch();
        }
    }

    /**
    * 信号列表
    */
    public function signal($sid = 0){
        $arrStudio = $this->show_studio($sid);
        // 工作室信号
        $arrStudioAccount = Db::name('studio_account')->alias('a')->where('a.studio_id', $arrStudio['id'])->join('mt4_account b','b.mt4id= a.account')->field('b.*,a.account as account, a.name as studio_account_name, a.type as type, a.param as param, a.expire_time as expire_time')->order('a.id desc')->select();

        // 获取已跟单信号
        $arrFA = Db::name('mt4_diy_account')->where('uid', $this->userInfo['uid'])->column('mt4id');
        $arrFASH = Db::name('studio_follow')->where('uid', $this->userInfo['uid'])->where('status', 0)->column('mt4id');
        $this->assign('arrFA', $arrFA);
        $this->assign('arrFASH', $arrFASH);

        $this->assign('arrStudioAccount', $arrStudioAccount);
        return $this->fetch();
    }

    /**
    * 我的交易
    */
    public function trade($sid = 0)
    {
        $this->show_studio($sid);
        $account = $this->userInfo['zhmt4uid'];
        $account = trim($account);
        $beginMoney = 0;
        if ( ! empty($account)){
            $key = sprintf('%s-%s-%s', 'zhtEA', 'mt4_account_detail', $account);
            $arrCache = array();
            $redis = new Redis();
            if ($redis->get($key)){
                $arrCache = unserialize($redis->get($key));
            }
            $this->assign('accountDtail', $arrCache);

            // 初始入金
            $beginMoney = Db::name('mt4_account_detail')->where('account', $account)->order('id')->value('balance');           
            
            // 获取总统计
            $arrTrade = Db::name('mt4_account_statistics')->where('account', $account)->order('id desc')->find();
            $this->assign('arrTrade', $arrTrade);            

            $arrFollow = Db::name('mt4_diy_account')->alias('a')->join('mt4_account b', 'a.mt4id = b.mt4id')->field('a.weight, a.maxhold, a.maxloss, b.name, b.country')->where('a.uid', $this->userInfo['uid'])->order('a.id desc')->paginate(30);
            $this->assign('arrFollow', $arrFollow);
            
            // 获取月统计
            $formatMdata=[];
            $arrMdata = Db::name('mt4_account_mstatistics')->where('account', $account)->order('year,month')->select();
            foreach ($arrMdata as $key => $mdata) {
                $formatMdata[$mdata['year']][$mdata['month']] = $mdata;
            }
        }

        $this->assign('beginMoney', $beginMoney);
        $this->assign('account', $account);

        // 国家配置
        $mt4country = Config::get('options.mt4country');
        $this->assign('mt4country', array_flip($mt4country));
        
        // 服务商
        $mt4service = Config::get('options.mt4service');
        $this->assign('mt4service', array_flip($mt4service));

        return $this->fetch();
    }

    /**
    * 我的行情
    */
    public function market($sid = 0)
    {
        $arrStudio = $this->show_studio($sid);
        return $this->fetch();
    }

    /**
    * 我的资费
    */
    public function create($sid = 0){
        $arrStudio = $this->show_studio($sid);
        return $this->fetch();
    }


    /**
    * 我的资费
    */
    public function postage($sid = 0){
        $arrStudio = $this->show_studio($sid);
        return $this->fetch();
    }

    /**
    * 访客
    */
    private function visitor($num = 0)
    {
        $arrVisitor = array();
        if ( ! empty($num)){
            for ($i = 0; $i < $num; $i++) {
                $time = time()-rand(1, 10)*60;
                $arrNick = array_merge(Config::get('vdata')['vnick'], Config::get('vdata')['nick'], Config::get('vdata')['vnick']);
                $nickKey = rand(0, count($arrNick)-1);
                $arrVisitor[$time] = array(
                        'nick' => $arrNick[$nickKey],
                        'vphoto' => rand(1,120),
                        'ip' => AppCommon::getRankIp(),
                        'time' => $time
                    );

                // 游客
                if ($arrNick[$nickKey] == '游客'){
                    $arrVisitor[$time]['vphoto'] = rand(96,200);
                }
            }
        }
        krsort($arrVisitor);
        return $arrVisitor;
    }


    /**
    * 真实访客
    */
    private function tvisitor($sid = 0, $num = 0)
    {
        // 记录访客
        $key = sprintf('%s-%s-%s', 'yshfx', 'studio_visitor', $sid);
        $time = time();
        $arrNick = array_merge(Config::get('vdata')['vnick'], Config::get('vdata')['nick'], Config::get('vdata')['vnick']);
        $nickKey = rand(0, count($arrNick)-1);
        $request = Request::instance();
        $ip = $request->ip();

        $arrVisitor = array();
        if ($this->redis->has($key)){
            $arrVisitor = unserialize($this->redis->get($key));
        }

        if ( ! empty($this->userInfo)){ // 登录用户
            $arrVisitor[$this->userInfo['uid']] = array(
                    'nick' => $this->userInfo['nickname'],
                    'photo' => $this->userInfo['u_img'],
                    //'vphoto' => 0,
                    'ip' => $ip,
                    'time' => $time
                );
        }else{ // 游客
            $arrVisitor[$ip] = array(
                    'nick' => $arrNick[$nickKey],
                    'vphoto' => rand(1,120),
                    'ip' => $ip,
                    'time' => $time
                );            
        }

        // 保存12小时
        $this->redis->set($key, serialize($arrVisitor), 3600*24);
        $arrVisitor = array_merge($arrVisitor, $this->visitor(10*$num));
        return $arrVisitor;
    }

    /**
    * 工作室头部显示
    */
    private function show_studio($sid){
        $arrStudio = Db::name('studio')->alias('a')->join('user as b', 'a.uid = b.uid')->field('a.*,b.u_img as u_img')->where('b.uid', $sid)->find();
        $this->assign('arrStudio', $arrStudio);
        return $arrStudio;
    }
}