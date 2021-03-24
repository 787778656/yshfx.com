<?php
/**
* zht-web导航栏中展示页面
*/
namespace app\web\controller;
use \think\Db;
use think\Controller;
use think\Request;
use think\Session;
use think\Config;
use \think\cache\driver\Redis;
use app\common\controller\Common as AppCommon;

class Demo extends Common
{
    private static $uploadRoot = '/static.v.yshfx.com/upload/';
    public function __construct(){
        parent::__construct();
        $this->userInfo = Session::get('zhtweb');
        if (isset($this->userInfo['uid'])){
            $this->userInfo = Db::name('user')->where('uid', $this->userInfo['uid'])->find();
        } 
    }

    /**
    * master
    */
    public function master(){
        // 默认mt4
        $account = Db::name('user_mt4')->where('uid', $this->userInfo['uid'])->order('id desc')->value('mt4id');
        $this->assign('account', $account);
        return $this->fetch();
    }


    public function signal_list(){
        // 排序操作
        $strOrder = trim(input('get.order'));
        switch (abs($strOrder)) {
            case 1:
                $order = '`trade_profit`/`money`'; // 收益
                break;
            case 2:
                $order = 'trade_win'; // 胜率
                break;
            case 3:
                $order = 'trade_week'; // 交易时长
                break;
            case 4:
                $order = '`trade_drawdown`+`trade_minbalance`+`trade_minprofit`'; // 最大回撤
                break;
            case 5:
                $order = '`follow`+`following`'; // 跟随人数
                break;
            case 6:
                $order = 'score'; // 跟随人数
                break;            
            default: // 按评分排序
                $order = 'score desc';
                break;
        }

        // 倒序排序
        if (!empty($order) && $strOrder < 0){
            $order = sprintf('%s desc', $order); 
        }

        // 关键字搜索
        $keyword = trim(input('get.keyword'));
        if (!empty($keyword)){
            $map['name|mt4server'] = ['like', "%$keyword%"];
        }

        // 信号列表
        $map['show'] = ['in', [1,3,11]];
        $arrAccount = Db::name('mt4_account')->field('*,`trade_profit`/`money` as profit, follow+following as follow')->where($map)->order($order)->paginate(20, false, ['query' =>request()->param()]);

        $this->assign('arrAccount', $arrAccount);

        // 当前mt4id
        $account = input('?get.account')?input('get.account'):0;
        $this->assign('account', $account);

        // 跟单中的信号
        $arrFollow = Db::name('mt4_diy_account')->where(['uid'=>$this->userInfo['uid'],'slave_mt4id' => $account])->column('mt4id');

        $this->assign('arrFollow', $arrFollow);

        // 跟单状态
        $followStatus = Db::name('user_mt4')->where('mt4id', $account)->value('status');
        $this->assign('followStatus', $followStatus);

        // 服务商
        $mt4service = Config::get('options.mt4service');
        $this->assign('mt4service', array_flip($mt4service));

        return $this->fetch();
    }
}
