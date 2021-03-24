<?php
/**
* iOS和Android接口程序 信号列表和信号详细
*/
namespace app\h5\controller\api;
use think\Config;
use \think\Db;
use app\common\controller\Common as AppCommon;
use \think\cache\driver\Redis;

class Signal extends Common
{
    /**
     * 主信号列表数据
     * @return string
     */
    public function index()
    {
        $page = input('page', 1); //当前页
        $offset = ($page-1)*10;  //偏移量
        $type = input('type', 1);  //type 1: 综合  2：利润率  3：跟单人数 4：回撤（风向） 5：预期收益率 6：交易时长
        switch ($type){
            case 1 :
                $order = ['score' => 'desc'];   //综合
                break;
            case 2 :
                $order = ['profit' => 'desc'];   //利润率
                break;
            case 3 :
                $order = ['follow' => 'desc'];   //跟单人数
                break;
            case 4 :
                $order = ['trade_drawdown' => 'asc'];   //回撤（风险）
                break;
            case 5 :
                $order = ['avg_mprofit' => 'desc'];   //预期收益率
                break;
            case 6 :
                $order = ['trade_week' => 'desc'];   //交易时长
                break;
            default :
                $order = ['score' => 'desc'];
                break;
        }
        // 国家配置
        $mt4country = array_flip(Config::get('options.mt4country'));

        // 服务商
        $mt4service = array_flip(Config::get('options.mt4service'));
        $data = Db::name('Mt4Account')->field('id,name,mt4id,mt4server,img,bn,score,follow+following as follow,GREATEST(trade_drawdown,trade_minbalance,trade_minprofit) as trade_drawdown,trade_minprofit,trade_minbalance,avg_mprofit,trade_week,trade_win,rand_profit,country,`trade_profit`/`money`as profit,show,detail')->where('show', 'in', [1,3,11])->order(array_merge($order,['id' => 'desc']))->limit($offset,10)->select();
        if (!empty($data)) {
            foreach ($data as $k => $v) {
                $data[$k]['profit'] = round($v['profit']*100,2);
                //$trade_drawdown = max($v['trade_drawdown'], $v['trade_minbalance'], $v['trade_minprofit']);
                // 获取真实跟随者
                /*$tFollow = Db::name('mt4_diy_account')->where('mt4id', $v['mt4id'])->count();
                $data[$k]['follow'] = $v['follow']+$tFollow;*/

                $data[$k]['trade_drawdown'] = number_format($v['trade_drawdown']*100,2);
                $data[$k]['trade_win'] = $v['trade_win']*100;
                $data[$k]['avg_mprofit'] = $v['avg_mprofit']*12;
                if (!empty($v['img'])) {
                    if(false !== stripos($v['img'], 'http')) {
                        $data[$k]['img'] = $v['img'];
                    } else {
                        $data[$k]['img'] = $this->domain['__BASIC__'] . 'upload/image/' . $v['img'];
                    }
                } else {
                    $data[$k]['img'] = $this->domain['__BASIC__'].'image/touxiang.png';
                }
                if (isset($mt4country[$v['country']]) && !empty($mt4country[$v['country']])) {
                    $country_num = $mt4country[$v['country']];
                } else {
                    $country_num = $mt4country['中国'];
                }
                if (isset($mt4service[$v['mt4server']]) && !empty($mt4service[$v['mt4server']])) {
                    $service_num = $mt4service[$v['mt4server']];
                } else {
                    $service_num = $mt4service['forex'];
                }
                if (empty($v['rand_profit'])) {
                    $data[$k]['rand_profit'] = '0,0,0,0,0,0,0,0,0,0';
                }
                $data[$k]['country_img'] = $this->domain['__BASIC__'].'app/countryflag/'.$country_num.'.png';
                $data[$k]['service_img'] = $this->domain['__BASIC__'].'app/broker/'.$service_num.'.png';
            }
        }
        $count = Db::name('Mt4Account')->count();

        return json_encode(['code' => 200, 'msg' => '查询成功', 'result' => ['data' => $data, 'current_page' => (int)$page, 'total_num' => $count]]);
    }

    /**
     * 主信号用户详情页
     * @return mixed
     */
    public function detail(){
        $account = input('mt4id', 0);
        if (!empty($account)){
            $accountInfo = Db::name('mt4_account')->field('mt4id,img, country,name,bn,mt4server,money,follow, GREATEST(trade_drawdown,trade_minbalance,trade_minprofit) as trade_drawdown,trade_minprofit,trade_minbalance,trade_week,trade_month,avg_mprofit,detail')->where('mt4id', $account)->find();
            if (!empty($accountInfo)) {
                $accountInfo['trade_drawdown'] = ($accountInfo['trade_drawdown']*100).'%';  //最大回撤
                // 获取真实跟随者
                $tFollow = Db::name('mt4_diy_account')->where('mt4id', $accountInfo['mt4id'])->count();
                $accountInfo['follow'] += $tFollow;
            }

            // 账号缓存信息
            $key = sprintf('%s-%s-%s', 'zhtEA', 'mt4_account_detail', $account);
            $arrCache = array();
            $redis = new Redis();
            if ($redis->get($key)){
                $arrCache = unserialize($redis->get($key));
            }
            $equity = 0.00;
            if (!empty($arrCache)) {
                $equity = $arrCache['equity'];
            }
            $accountInfo['equity'] = number_format($equity,2);  //账户净值

            // 获取总统计
            $arrTrade = Db::name('mt4_account_statistics')->where('account', $account)->order('id desc')->find();

            $profit = $arrTrade['gross_profit']+$arrTrade['gross_loss']+$arrTrade['commission']+$arrTrade['swap'];
            $accountInfo['profit_margin'] = $accountInfo['money']?sprintf('%s%s', number_format($profit/floatval($accountInfo['money']),4)*100, '%'):'--';  //利润率
            $accountInfo['profit'] = number_format($profit,2);  //利润
            $accountInfo['week_trade_num'] = $accountInfo['trade_week']?floor(@$arrTrade['trade_total']/$accountInfo['trade_week'])."次":'--';  //每周交易次数
            $accountInfo['holding_time'] = number_format($arrTrade['holding_time']/3600,2).'小时';
            $accountInfo['max_time'] = '--';
            if ($arrTrade['max_time']){
                $accountInfo['max_time'] = date('Y-m-d H:i', $arrTrade['max_time']);  //最近交易时间
            }

            if (!empty($accountInfo['img'])) {
                $accountInfo['img'] = $this->domain['__BASIC__'].'upload/image/'.$accountInfo['img'];
            } else {
                $accountInfo['img'] = $this->domain['__BASIC__'].'image/touxiang.png';
            }

            // 国家配置
            $mt4country = array_flip(Config::get('options.mt4country'));

            // 服务商
            $mt4service = array_flip(Config::get('options.mt4service'));

            if (isset($mt4country[$accountInfo['country']]) && !empty($mt4country[$accountInfo['country']])) {
                $country_num = $mt4country[$accountInfo['country']];
            } else {
                $country_num = $mt4country['中国'];
            }
            if (isset($mt4service[$accountInfo['mt4server']]) && !empty($mt4service[$accountInfo['mt4server']])) {
                $service_num = $mt4service[$accountInfo['mt4server']];
            } else {
                $service_num = $mt4service['forex'];
            }

            $accountInfo['country_img'] = $this->domain['__BASIC__'].'app/countryflag/'.$country_num.'.png';  //国旗
            $accountInfo['service_img'] = $this->domain['__BASIC__'].'app/broker/'.$service_num.'.png';   //服务商图片

            $accountInfo['trade_total'] = $arrTrade['trade_total'];  // 总交易
            $result = 0;
            if (isset($arrTrade['trade_total'])){
                $result = number_format(($arrTrade['trade_profit']/floatval($arrTrade['trade_total'])), 4);
                $result = strval($result*100).'%';
            }
            $accountInfo['profit_trade'] = sprintf('%s(%s)', $arrTrade['trade_profit'], $result);  //盈利交易

            $result = 0;
            if (isset($arrTrade['trade_total'])){
                $result = number_format(($arrTrade['trade_loss']/floatval($arrTrade['trade_total'])), 4);
                $result = strval($result*100).'%';
            }
            $accountInfo['loss_trade'] = sprintf('%s(%s)', $arrTrade['trade_loss'], $result);  //亏损交易

            $accountInfo['profit_factor'] = $arrTrade['profit_factor'];  //利润因子
            $accountInfo['trade_best'] = $arrTrade['trade_best'];  //最好交易
            $accountInfo['trade_worst'] = $arrTrade['trade_worst'];  //最差交易
            $accountInfo['trade_return'] = ($arrTrade['trade_total']==0)?'--':number_format($profit/floatval($arrTrade['trade_total']),2);  //预期回报
            $accountInfo['sharpe_ratio'] = $arrTrade['sharpe_ratio'];  //夏普比率
            $accountInfo['sprofit_max'] = $arrTrade['sprofit_max'].' USD '.$arrTrade['sprofit_max_num'];  //最大连续赢利
            $accountInfo['sprofit_max_num'] = $arrTrade['sprofit_more_num'].'('.$arrTrade['sprofit_more'].') USD';  //最大连续盈利
            $accountInfo['sloss_max'] = $arrTrade['sloss_max'] .' USD ('.$arrTrade['sloss_max_num'].')';  //最大连续亏损
            $accountInfo['mistakes_max'] = $arrTrade['sloss_more'].' ('.$arrTrade['sloss_more_num'].' USD)';  //最大连续失误
            $accountInfo['gross_profit'] = $arrTrade['gross_profit'];  //毛利money
            $accountInfo['gross_profit_num'] = $arrTrade['gross_profit_num'];  //毛利点数
            $accountInfo['gross_loss'] = $arrTrade['gross_loss'];  //毛利亏损
            $accountInfo['gross_loss_num'] = $arrTrade['gross_loss_num'];  //毛利亏损点数
            $accountInfo['avg_profit'] = $arrTrade['avg_profit'];  //平均利润
            $accountInfo['avg_profit_num'] = $arrTrade['avg_profit_num'];  //平均利润点数
            $accountInfo['avg_loss'] = $arrTrade['avg_loss'];  //平均损失
            $accountInfo['avg_loss_num'] = $arrTrade['avg_loss_num'];  //平均损失点数
            $accountInfo['avg_mprofit_year'] = ($accountInfo['avg_mprofit']*12).'%';  //年度预测
            $accountInfo['avg_mprofit'] = $accountInfo['avg_mprofit'].'%';  //每月增长
            $accountInfo['tBalance'] = $this->getFollowMoney($account);  //跟随资金



            // 获取月统计
            $formatMdata=[];
            $arrMdata = Db::name('mt4_account_mstatistics')->where('account', $account)->order('year,month')->select();
            if (!empty($arrMdata)) {
                foreach ($arrMdata as $key => $mdata) {
                    $formatMdata[$mdata['year']][$mdata['month']] = $mdata;
                }
            }
            $month_profit = [];
            $total = $mtotal = 0;
            if (!empty($formatMdata)) {
                $total_money = $accountInfo['money'];
                foreach ($formatMdata as $year => $ydata) {
                    for ($i=1; $i<13; $i++) {
                        if(isset($ydata[$i])){
                            $avg_profit = number_format((@$ydata[$i]['profit']/floatval($total_money))*100,2);
                            $total += $avg_profit;  //总利润
                            $mtotal += $avg_profit;
                            // $total_money += $ydata[$i]['profit'];
                            $total_money = floatval($total_money) + $ydata[$i]['profit'];
                            $month_profit[$year][$i] = $avg_profit.'%';
                        }else{
                            $month_profit[$year][$i] = '--';
                        }
                    }
                    $month_profit[$year]['year_total_profit'] = $mtotal.'%';
                    $mtotal = 0;
                }
            }
            $profit = $arrTrade['gross_profit']+$arrTrade['gross_loss']+$arrTrade['commission']+$arrTrade['swap'];

            $month_profit['total_profit'] = $accountInfo['money']>0? (number_format($profit/floatval($accountInfo['money']),4)*100).'%' : '0%';



            //评论数量统计
            $count = Db::name('Mt4Comment')->where(['mt4id'=> $account,'status' => 1])->count();  //评论总数

            return json_encode(['code' => 200, 'msg' => '查询成功','result' => ['account' => $accountInfo,'trade' => $month_profit, 'count' => $count]]);
        }
        return json_encode(['code' => 302, 'msg' => '查询失败，请传mt4id']);
    }

    /**
     *
     * 跟随交易者资金
     * @param $account  //mt4账号
     * @return string
     */
    public function getFollowMoney($account)
    {
        if ( ! empty($account)){
            $vfollow = Db::name('mt4_account')->where('mt4id', $account)->value('follow');
            $tFollow = Db::name('mt4_diy_account')->where('mt4id', $account)->count();
            $follow = $vfollow + $tFollow;  //跟单人数

            $arrBalance = array();  //g
            if ( ! empty($vfollow)){
                // 补足余额
                for($vfollow = count($arrBalance); $vfollow <= $follow; $vfollow++){
                    $balance = rand(10000, 500000);
                    $arrBalance[] = $balance/100;
                }
            }

            return number_format(array_sum($arrBalance),2);
        }
    }
    /**
     * 交易历史图标
     * @return string
     */
    public function history($account = 0)
    {
        if ( ! empty($account)){
            $arrAccount = explode('-', $account);
            $arrOrders = array();
            foreach ($arrAccount as $key => $account) {
                $table = AppCommon::tableName('mt4_history_order', $account);
                $orders = Db::name($table)->where(['account' => $account])->order('close_time')->select();
                if (empty($orders)) continue;
                foreach ($orders as $value) {
                    $arrOrders[] = $value;
                }
            }
            return json_encode(array('code'=> 200, 'msg' => '查询成功', 'result' => $arrOrders));
        }
        return json_encode(array('code'=> 200, 'msg' => '请填写正确的mt4id'));
    }

    /**
     * 主信号交易数据
     * @return mixed
     */
    /**
     * 主信号交易数据（历史和持仓）
     * @return mixed
     */
    public function iorders()
    {
        $account = input('post.account', 0);
        $tab = input('post.tab', 'history');
        $table = 'mt4_master_order';
        $type = input('type'); //区分我的交易和主信号详情页 type=ismy : 我的交易
        if ( ! empty($account)){
            if ($tab == 'history'){
                // 获取表名(历史表有分表处理)
                $table = AppCommon::tableName('mt4_history_order', (int)$account);
                $arrId = Db::name($table)->where('account', $account)->group('ticket')->column('id'); // 去重
                if (empty($arrId)) $arrId = array(0);
                // 历史交易
                $arrHistory = Db::name($table)->where('id', 'in', $arrId)->whereOr(function ($query) use ($account) {$query->where(['account' => $account, 'operator'=>'import-api']);})->order('close_time desc')->paginate(20);
                return json_encode(['code' => 200, 'msg' => '查询成功', 'result' => $arrHistory]);
            }else{
                if (!empty($type) && $type==='ismy') {
                    $table = 'mt4_slave_order';
                }
                // 持仓
                $arrHolding = Db::name($table)->where('account', $account)->order('open_time desc')->paginate(50)->toArray();
                $tHolding = Db::name($table)->where('account', $account)->column('account, sum(commission) as commission, sum(swap) as swap, sum(profit) as profit');
                //$this->assign('tHolding', @$tHolding[$account]);
                unset($tHolding[$account]['account']);
                if (empty(@$tHolding[$account])) {
                    @$tHolding[$account]=[
                        'commission' => 0,
                        'swap' => 0,
                        'profit' =>0
                    ];
                } elseif (isset($tHolding[$account]['swap'],$tHolding[$account]['profit'])) {
                    $tHolding[$account]['swap'] = number_format($tHolding[$account]['swap'],2);
                    $tHolding[$account]['profit'] = number_format($tHolding[$account]['profit'],2);
                }
                return json_encode(['code' => 200, 'msg' => '查询成功', 'result' => array_merge($arrHolding, @$tHolding[$account])]);
            }
        }
        return json_encode(['code' => 302, 'msg' => '查询失败']);
    }

    /**
     * 用户评论列表
     * @return mixed
     */
    public function icomment()
    {
        $mt4id = input('mt4id',0);
        $comment = []; //评论列表
        $username = $u_img = '';
        //用户评论
        if (!empty($mt4id)) {
            $map = [
                'mt4id' => $mt4id,
                'status' => 1
            ];
            $comment = Db::name('Mt4Comment')->field('id,uid,login,username,comment,modify_time')->where($map)->order('modify_time desc')->paginate(10)->toArray();
            if (!empty($comment['data'])) {
                foreach ($comment['data'] as $key => $com) {
                    $data = Db::name('User')->where('uid',$com['uid'])->find();
                    if (!empty($data)) {
                        $username = $data['nickname'];
                        $u_img = $data['u_img'];
                    }
                    $comment['data'][$key]['username'] = $username;
                    $comment['data'][$key]['u_img'] = $u_img;
                    $username = $u_img = '';
                }
            }
        }
        return json_encode(['code' => 200, 'msg' => '查询成功', 'result' => $comment]);
    }

    /**
     * 用户评论
     */
    public function comment()
    {
        $comment = input('content'); //评论内容
        $mt4id = input('mt4id'); //mt4id
        $time = time();
        $data = [
            'uid' => $this->client_uid,
            'comment' => $comment,
            'status' => 0,
            'mt4id' => $mt4id,
            'add_time' => $time,
            'modify_time' => $time,
        ];
        if (empty($this->client_uid)) {
            return json_encode(['code' => 302, 'msg' => '请先登录']);
        }
        $user = Db::name('User')->where('uid', $this->client_uid)->find();
        $data['login'] = $user['login'];
        if (!empty($user) && !empty($user['nickname'])) {
            $data['username'] = $user['nickname'];
        }
        $result = Db::name('Mt4Comment')->insert($data);
        if ($result>0){
            return json_encode(['code' => 200, 'msg' => '评论成功，请等待管理员审核评论！']);
        }
        return json_encode(['code' => 302, 'msg' => '评论失败！']);
    }

    /**
     * 信号详情页图标（业绩）
     * @return string
     */
    public function history_profit()
    {
        $account = input('post.account', 0); //mt4id
        $time = input('post.type', 'day'); //数据展示类型
        if ( ! empty($time) && ! empty($account)){
            $table = AppCommon::tableName('mt4_history_order', $account);
            $orders= Db::name($table)->where(['account' => $account])->order('id desc')->select();
            $arrResult = array();
            if ( ! empty($orders)){
                $result = array();
                foreach ($orders as $key => $order) {
                    if (empty($order['close_time'])) continue; // 跳过没有平单时间的记录
                    $year = date('Y', $order['close_time']);
                    $month = date('n', $order['close_time']);
                    $day = date('j', $order['close_time']);
                    $week = strftime('%W', $order['close_time']);

                    switch ($time) {
                        case 'year':
                            $key = sprintf('%s', $year);
                            $result[$key]['time'] = $order['close_time'];
                            $result[$key]['date'] = sprintf('%s年', $year);
                            $result[$key]['profit'][] = floatval($order['profit']);
                            break;
                        case 'month':
                            $key = sprintf('%s-%s', $year, $month);
                            $result[$key]['time'] = $order['close_time'];
                            $result[$key]['date'] = sprintf('%s年%s月', $year, $month);
                            $result[$key]['profit'][] = floatval($order['profit']);
                            break;
                        case 'week':
                            $key = sprintf('%s-%s', $year, $week);
                            $result[$key]['time'] = $order['close_time'];
                            $result[$key]['date'] = sprintf('%s年%s周', $year, $week);
                            $result[$key]['profit'][] = floatval($order['profit']);
                            break;
                        case 'day':
                            $key = sprintf('%s-%s-%s', $year, $month, $day);
                            $result[$key]['time'] = $order['close_time'];
                            $result[$key]['date'] = sprintf('%s-%s-%s', $year, $month, $day);
                            $result[$key]['profit'][] = floatval($order['profit']);
                            break;
                    }
                }
                if ( ! empty($result)){
                    sort($result);
                    $i = 0;
                    foreach ($result as $key => $value) {
                        $arrResult[$i]['date'] = $value['date'];
                        $arrResult[$i]['profit'] = array_sum($value['profit']);
                        $i++;
                    }
                }
            }

            return json_encode(array('code'=> 200, 'msg' => '查询成功', 'result' => $arrResult));
        }
        return json_encode(array('code'=> 200, 'msg' => '查询失败，请输入正确的mt4id'));
    }

    /**
     * 信号详情页图标（净值、浮盈/浮亏、仓位）
     */
    public function history_total(){
        $account = input('post.account', 0);  //mt4id
        if ( ! empty($account)){
            $result = Db::name('mt4_account_detail')->where('account', $account)->order('id')->field('account, balance, equity, margin, free_margin, profit, add_time as date')->select();

            return json_encode(array('code'=> 200, 'msg' => '查询成功', 'data' => $result));
        }
        return json_encode(array('code'=> 200, 'msg' => '查询失败，请输入正确的mt4id'));
    }

    /**
     * 历史交易类型统计(信号详情页图标中品种)
     */
    public function history_symbol()
    {
        $account = input('post.account',0);
        if (!empty($account)){
            // 开始更新表
            $table = AppCommon::tableName('mt4_history_order', $account);
            $orders= Db::name($table)->where(['account' => $account])->order('id')->select();

            if ( ! empty($orders)){
                $result = array();
                $result_buy = array();
                $result_sell = array();
                foreach ($orders as $key => $order) {
                    if (empty($order['symbol'])) continue; // 跳过没有symbol的记录

                    if ($order['op'] == 1){
                        $result_buy[$order['symbol']]['buy'][] = floatval($order['profit']);
                    }else{
                        $result_sell[$order['symbol']]['sell'][] = floatval($order['profit']);
                    }

                    $result[$order['symbol']][] = floatval($order['profit']);

                }

                // 汇总
                if ( ! empty($result)){
                    foreach ($result as $symbol => $arrProfit) {
                        $data[] = array(
                            'symbol' => $symbol,
                            'buy' => count(@$result_buy[$symbol]['buy']),
                            'sell' => count(@$result_sell[$symbol]['sell']),
                            'num' => count($arrProfit),
                            'profit' => array_sum($arrProfit),
                        );
                    }
                }
            }
        }
        return json_encode(array('code'=> 200, 'msg' => '查询成功', 'data' => @$data));
    }

    /**
     * 跟随交易者
     * @return mixed
     */
    public function follow()
    {
        $account = input('post.account',0); //mt4id
        if ( ! empty($account)){
            $pageSize = 10;
            $vfollow = Db::name('mt4_account')->where('mt4id', $account)->value('follow');
            $tFollow = Db::name('mt4_diy_account')->where('mt4id', $account)->count();
            $follow = $vfollow + $tFollow;  //跟单人数

            $arrFollow = Db::name('mt4_diy_account')->alias('a')->join('user b', 'a.uid = b.uid')->field('a.slave_mt4id,a.weight,a.maxhold,b.u_img,b.login,b.zhmt4server,b.zhmt4uid,b.nickname')->where('mt4id', $account)->order('a.id desc')->paginate($pageSize, intval($follow));

            // 服务商
            $mt4service = Config::get('options.mt4service');
            $arrBalance = array();  //g
            if ( ! empty($vfollow)){

                // 生成虚拟跟单者
                $i = 0;
                $num = count($arrFollow);
                $page = ceil($follow/$pageSize);
                $lastSize = $follow%$pageSize;
                if ($lastSize == 0) $lastSize = $pageSize;
                $pageLimit = $pageSize;
                if (input('get.page')==$page){
                    $pageLimit = $lastSize;
                }

                // 不足一页时
                if ($follow < $pageSize ){
                    $pageLimit = $follow;
                }

                $arrWeight = array('-0.1', '-0.2', '-0.5', '-1', '0.01', '0.02', '0.05', '0.1', '0.2', '0.5', '1', '-10%','-30%', '-50%','-100%','10%', '20%', '30%', '40%', '50%', '60%', '70%', '80%', '90%','100%');

                while ($i+$num < $pageLimit) {
                    $weight = array_rand($arrWeight,1);
                    $balance = rand(10000, 500000);
                    $balance = $balance/100;
                    $arrBalance[] = $balance;
                    $balance = number_format($balance, 2);
                    $sid = rand(1, 18);
                    $arrFollow[] = array(
                        'weight' => $arrWeight[$weight],
                        'maxhold' => rand(1,10),
                        'login' => rand(133, 139).rand(10000000, 99999999),
                        'zhmt4server' => $mt4service[$sid],
                        'zhmt4uid' => 0,
                        'balance' => $balance,
                        'vphoto' => rand(1,300),
                        'nickname' => '',
                        'u_img' => ''
                    );
                    $i++;
                }

                // 补足余额
                for($vfollow = count($arrBalance); $vfollow <= $follow; $vfollow++){
                    $balance = rand(10000, 500000);
                    $arrBalance[] = $balance/100;
                }
            }
            $arrFollow = $arrFollow->toArray();
            $redis = new Redis();
            if (!empty($arrFollow['data'])) {
                foreach ($arrFollow['data'] as $k => $info) {
                    if (!isset($info['balance'])){
                        $key = sprintf('%s-%s-%s', 'zhtEA', 'mt4_account_detail', $info['slave_mt4id']);
                        $account = unserialize($redis->get($key));
                        $order_balance = number_format($account['balance'],2);
                    }else{
                        $order_balance = $info['balance'];
                    }
                    $arrFollow['data'][$k]['balance'] = $order_balance;
                }
            }
            return json_encode(['code' => 200, 'msg' => '查询成功', 'result' => array_merge($arrFollow, ['follow' => $follow,'tBalance' => number_format(array_sum($arrBalance),2)])]);
        }
    }

    /**
     * 账号利润率汇总(信号详情页盈利和风险评级)
     * @return string
     */

    public function account_score(){
        $account = input('mt4id', 0);
        if ( ! empty($account)){

            // 账户详情
            $accountInfo = Db::name('mt4_account')->where('mt4id', $account)->find();
            if (empty($accountInfo)){
                $balance = Db::name('mt4_account_detail')->where('account', $account)->order('id')->value('balance');
                $min_balance = Db::name('mt4_account_detail')->where('account', $account)->value('min(balance)');
                $accountInfo['money'] = $balance;
                $accountInfo['trade_drawdown'] = empty($balance)? 0:abs($min_balance - $balance)/$balance;
                $accountInfo['trade_drawdown'] = number_format($accountInfo['trade_drawdown'], 2)*100;

            }

            // 获取总统计
            $arrTrade = Db::name('mt4_account_statistics')->where('account', $account)->order('id desc')->find();

            // 账户统计
            $detail = Db::name('mt4_account_detail')->where('account', $account)->field('max(profit/balance) as profit1, min(profit/balance) as profit2, max(margin/(free_margin+margin)) as use_margin, balance')->find();
            $sTrade = Db::name('mt4_account_statistics')->where('account', $account)->find();

            // 获取月统计
            $mdata=[];
            $arrMdata = Db::name('mt4_account_mstatistics')->where('account', $account)->order('year,month')->select();
            foreach ($arrMdata as $key => $vdata) {
                $mdata[$vdata['year']][$vdata['month']] = $vdata;
            }

            $arrProfit = array();
            $total = 0;
            if (!empty($mdata)){
                $total_money = floatval($accountInfo['money']);
                foreach ($mdata as $year => $ydata) {
                    $mtotal = 0;
                    for ($i=1; $i<13; $i++) {
                        if(isset($ydata[$i]['profit'])){
                            $avg_profit = number_format(($ydata[$i]['profit']/floatval($total_money))*100,2);
                            $total += $avg_profit;
                            $total_money += $ydata[$i]['profit'];
                            $arrProfit[$year][$i] = $total_money;
                        }
                    }
                }
            }

            // 交易历史
            $arrHProfit = array();
            $table = AppCommon::tableName('mt4_history_order', $account);
            $orders= Db::name($table)->where(['account' => $account])->order('close_time')->select();
            $arrResult = array();
            $arrMonth = array();
            if ( ! empty($orders)){
                $Htotal_money = floatval($accountInfo['money']);
                $hResult = array();
                foreach ($orders as $key => $order) {
                    if (empty($order['close_time'])) continue; // 跳过没有平单时间的记录
                    $year = date('Y', $order['close_time']);
                    $month = date('n', $order['close_time']);
                    $day = date('j', $order['close_time']);

                    $key = sprintf('%s-%s-%s', $year, $month, $day);
                    if (isset($hResult[$key])){
                        $hResult[$key] += floatval($order['profit']);
                    }else{
                        $hResult[$key] = floatval($order['profit']);
                    }
                    // 月收益
                    $Htotal_money += floatval($order['profit']);
                    $arrHProfit[$year][$month] = $Htotal_money;

                    // 统计交易月数
                    $key2 = sprintf('%s-%s', $year, $month);
                    $arrMonth[$key2] = floatval($order['profit']);
                }
            }
            $trade_month = isset($accountInfo['trade_month'])? $accountInfo['trade_month'] :count($arrMonth);
            $avg_mproft = empty($trade_month)? 0 : $total/$trade_month;
            $avg_mproft = number_format($avg_mproft, 2);

            // 浮赢比数
            $detailNum1 = Db::name('mt4_account_detail')->where('account', $account)->where('profit', 'gt', 0)->count();
            $detailNum2 = Db::name('mt4_account_detail')->where('account', $account)->where('profit', 'lt', 0)->count();
            $detailTNum = Db::name('mt4_account_detail')->where('account', $account)->count();

            // 盈利能力
            $pscore = 0;

            if (empty($arrMdata)){ // 换成利润率
                $key = sprintf('%s-%s-%s', 'zhtEA', 'mt4_account_detail', $account);
                $arrCache = array();
                $redis = new Redis();
                if ($redis->get($key)){
                    $accountDtail = unserialize($redis->get($key));
                }

                $now_balance = isset($accountDtail['balance']) ? $accountDtail['balance']:0;

                /*
                $result['year_proft'] = number_format(($now_balance-$balance)/$balance*100,2);
                $pscore += $this->vscore($result['year_proft'], array(80, 40, 20, 10, 5), 5);
                */

                $result['year_proft'] = empty($balance)? 0:number_format((($now_balance-$balance)/floatval($balance))*1200/floatval($trade_month),2);
                $pscore += $this->vscore($result['year_proft'], array(100, 80, 60, 40, 20), 5);

            }else{
                $result['year_proft'] = number_format($avg_mproft*12, 2);
                $pscore += $this->vscore($result['year_proft'], array(100, 80, 60, 40, 20), 5);
            }

            $result['trade_win'] = empty($arrTrade['trade_total'])?0:number_format($arrTrade['trade_profit']/floatval($arrTrade['trade_total'])*100, 2);
            $result['equity_increase'] = ($detail['profit1']>0)?number_format($detail['profit1']*100, 2):0;
            $result['equity_increase'] = number_format($result['equity_increase'],2);
            $result['profit_offset'] = empty($detailTNum)?0:number_format($detailNum1/floatval($detailTNum)*100, 2);
            $result['profit_factor'] = empty($arrTrade['profit_factor'])?0:$arrTrade['profit_factor'];


            $pscore += $this->vscore($result['trade_win'], array(70, 60, 50, 40, 30), 5);
            $pscore += $this->vscore($result['equity_increase'], array(8, 6, 4, 2, 1), 5);
            $pscore += $this->vscore($result['profit_offset'], array(80, 60, 40, 20, 10), 5);
            $pscore += $this->vscore($result['profit_factor'], array(1.8, 1.6, 1.4, 1.2, 1), 5);
            $result['profit_score'] = number_format($pscore);

            // 风险系数
            $sTrade['drawdown'] = abs($sTrade['drawdown']);
            $result['balance_drawdown'] = $accountInfo['trade_drawdown']>$sTrade['drawdown']?$accountInfo['trade_drawdown']:$sTrade['drawdown'];
            $result['balance_drawdown'] = number_format($result['balance_drawdown']*100, 2);

            $result['equity_drawdown'] = number_format(abs($detail['profit2']*100), 2);
            $result['max_money'] = number_format($detail['use_margin']*100, 2);
            $result['loss_max'] = empty($detailTNum)?0:number_format($detailNum2/floatval($detailTNum)*100, 2);

            $result['loss_day_max'] = 0;
            // 获取当月总入金
            if ( ! empty($hResult)){
                $lossDayMax = min($hResult);
                $key = array_search($lossDayMax, $hResult);
                if ( ! empty($key)){
                    $arrKey = explode('-', $key);
                    if (count($arrKey) == 3){
                        if (isset($arrProfit[$arrKey[0]][$arrKey[1]])){
                            $result['loss_day_max'] = number_format($lossDayMax/floatval($arrProfit[$arrKey[0]][$arrKey[1]])*100, 2);
                            $result['loss_day_max'] = abs($result['loss_day_max']);
                        }elseif(isset($arrHProfit[$arrKey[0]][$arrKey[1]])){
                            $result['loss_day_max'] = number_format($lossDayMax/floatval($arrHProfit[$arrKey[0]][$arrKey[1]])*100, 2);
                            $result['loss_day_max'] = abs($result['loss_day_max']);
                        }
                    }
                }
            }

            // 新值
            $result['balance_drawdown'] = $result['loss_day_max']*1.35;
            $result['balance_drawdown'] = number_format($result['balance_drawdown'],4);

            $result['loss_day_max'] = number_format($result['loss_day_max'],4);
            $lscore = 0;
            $lscore += $this->vscore($result['balance_drawdown'], array(40, 30, 20, 10, 5));
            $lscore += $this->vscore($result['equity_drawdown'], array(25, 20, 15, 10, 5));
            $lscore += $this->vscore($result['max_money'], array(20, 16, 12, 8, 3));
            $lscore += $this->vscore($result['loss_max'], array(80, 60, 40, 20, 10));
            $lscore += $this->vscore($result['loss_day_max'], array(15, 12, 8, 5, 2));

            $result['loss_score'] = number_format($lscore);

            return json_encode(array('code'=> 200, 'msg' => '查询成功', 'result' => $result));
        }
    }

    /**
     * 计算分值
     */
    private function vscore($num = 0, $arr = null, $inc = 1)
    {
        if ( ! empty($arr)){
            foreach ($arr as $key => $value) {
                if (floatval($num) > $value){
                    return $inc*(count($arr)-$key);
                }
            }
            return $inc;
        }
    }

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
     * 账号实时信息
     */
    public function history_total_active()
    {
        $account = input('post.slave_mt4id'); //用户mt4id
        $result = [];
        if ($this->client_uid) {
            if ( ! empty($account)){
                $redis = new Redis();
                $key = sprintf('%s-%s-%s', 'zhtEA', 'mt4_account_detail', $account);
                $result = array();
                if ($redis->get($key)){
                    $result = unserialize($redis->get($key));
                    if (isset($result['margin'])) {
                        $result['margin'] = (double)$result['margin'];
                    } else {
                        $result['margin'] = 0.00;
                    }
                    if (!isset($result['equity'])) {
                        $result['equity'] = 0.00; //净值
                    }else {
                        $result['equity'] = (double)$result['equity'];
                    }
                    $result['margin-pre'] = !empty($result['equity']) ? number_format(@$result['margin']/floatval($result['equity'])*100, 2).'%' : '0%';
                    $result['time'] = time();
                }
                if (empty($result)) {
                    $result['equity'] = 0.00; //净值
                    $result['balance'] = 0.00;  //余额
                    $result['margin'] = 0.00;  //已用预付款
                    $result['margin_pre'] = '0%';  //已用预付款所占比例
                    $result['profit'] = 0.00;  //实时获利
                    $result['profit_rate'] = '0.00%';  //利润率
                }
                $beginMoney = Db::name('mt4_account_detail')->where('account', $account)->order('id')->value('balance');
                if (!$beginMoney) $beginMoney = 0;
                $result['beginMoney'] = $beginMoney;
                $result['profit_rate'] = '0.00%';
                if ($beginMoney && $result['balance']) {
                    $result['profit_rate'] = number_format(($result['balance']-$beginMoney)/floatval($result['balance'])*100,2).'%';
                }
                $data = Db::name('user_mt4')->where('mt4id', $account)->find();
                $sh = 1;
                if (empty($data)) {
                    $data_user = Db::name('user')->where('zhmt4uid', $account)->find();
                    $sh = $data_user['isbuy'];
                    $mt4pwd = !empty($data_user['zhmt4pwd']) ? $data_user['zhmt4pwd'] : '';
                    $mt4server = $data_user['zhmt4server'];
                }else {
                    $mt4pwd = !empty($data['mt4pwd']) ? $data['mt4pwd'] : '';
                    $mt4server = $data['mt4server'];
                }
                if (!stripos($mt4server,'DEMO')) {
                    $mt4pwd = '******';
                }
                $result['mt4id'] = $account;
                $result['mt4pwd'] = $mt4pwd;
                $result['mt4server'] = $mt4server ? $mt4server : '';
                $result['sh'] = $sh ? $sh : '';
            }
        }
        if (empty($result)) {
            $result['equity'] = 0.00; //净值
            $result['balance'] = 0.00;  //余额
            $result['margin'] = 0.00;  //已用预付款
            $result['margin_pre'] = '0%';  //已用预付款所占比例
            $result['profit'] = 0.00;  //实时获利
            $result['profit_rate'] = '0.00%';  //利润率
        }


        return json_encode(array('code'=> 200, 'msg' => '查询成功', 'result' => $result));
    }

    /**
     * 通过name|bn|business查询主信号
     * @return string
     */
    public function find_key()
    {
        $keyword = input('post.keyword');
        $account = [];
        // 国家配置
        $mt4country = array_flip(Config::get('options.mt4country'));

        // 服务商
        $mt4service = array_flip(Config::get('options.mt4service'));
        if (!empty($keyword)) {
            $account = Db::name('Mt4Account')->field('id,name,mt4id,mt4server,img,bn,score,follow+following as follow,GREATEST(trade_drawdown,trade_minbalance,trade_minprofit) as trade_drawdown,trade_minprofit,trade_minbalance,avg_mprofit,trade_week,trade_win,rand_profit,country,`trade_profit`/`money`as profit,show')->where('show', 'in', [1,3,11])->where('name|bn|business', 'like', '%' . strip_tags($keyword) . '%')->order('modify_time desc')->paginate(10)->toArray();
        }
        if (isset($account['data']) && !empty($account['data'])) {
            foreach ($account['data'] as $k => $v) {
                $account['data'][$k]['profit'] = round($v['profit']*100,2);
                //$trade_drawdown = max($v['trade_drawdown'], $v['trade_minbalance'], $v['trade_minprofit']);
                // 获取真实跟随者
                /*$tFollow = Db::name('mt4_diy_account')->where('mt4id', $v['mt4id'])->count();
                $data[$k]['follow'] = $v['follow']+$tFollow;*/

                $account['data'][$k]['trade_drawdown'] = number_format($v['trade_drawdown']*100,2);
                $account['data'][$k]['trade_win'] = $v['trade_win']*100;
                $account['data'][$k]['avg_mprofit'] = $v['avg_mprofit']*12;
                if (!empty($v['img'])) {
                    if(false !== stripos($v['img'], 'http')) {
                        $account['data'][$k]['img'] = $v['img'];
                    } else {
                        $account['data'][$k]['img'] = $this->domain['__BASIC__'] . 'upload/image/' . $v['img'];
                    }
                } else {
                    $account['data'][$k]['img'] = $this->domain['__BASIC__'].'image/touxiang.png';
                }
                if (isset($mt4country[$v['country']]) && !empty($mt4country[$v['country']])) {
                    $country_num = $mt4country[$v['country']];
                } else {
                    $country_num = $mt4country['中国'];
                }
                if (isset($mt4service[$v['mt4server']]) && !empty($mt4service[$v['mt4server']])) {
                    $service_num = $mt4service[$v['mt4server']];
                } else {
                    $service_num = $mt4service['forex'];
                }
                if (empty($v['rand_profit'])) {
                    $account['data'][$k]['rand_profit'] = '0,0,0,0,0,0,0,0,0,0';
                }
                $account['data'][$k]['country_img'] = $this->domain['__BASIC__'].'app/countryflag/'.$country_num.'.png';
                $account['data'][$k]['service_img'] = $this->domain['__BASIC__'].'app/broker/'.$service_num.'.png';
            }
            return json_encode(['code' => 200, 'msg' => '查询成功', 'result' => array_merge(['keyword' => $keyword], $account)]);
        }
        return json_encode(['code' => 302, 'msg' => '暂无数据']);
    }

    /**我的主信号列表
     * @return string
     */
    public function get_my_master_signal()
    {
        $where = [
            'uid' => $this->client_uid,
            //'status' => ['in', [0,1]],
        ];
        $signal_arr = Db::name('mt4_signal')->where($where)->order('id desc')->paginate(10)->toArray();
        if (!empty($signal_arr['data'])) {
            foreach ($signal_arr['data'] as $key => $signal) {
                $bn = '---';
                $status = $follow = 0;
                $signal_name = $signal['sign_name'];
                if ($signal['status'] == 1) {
                    $data = Db::name('mt4_account')->where('mt4id', $signal['mt4id'])->find();
                    $follow = $data['follow'] + $data['following'];
                    if (!empty($data)) {
                        $bn = $data['bn'];
                        $signal_name = $data['name'];
                        if ($data['show'] == 2) {  //show 2:隐藏 3：即将下架
                            $status = 3;
                        } elseif ($data['show'] == 3) {
                            $status = 4;
                        } else {
                            $status = $data['show'];
                        }
                    }
                }
                $signal_arr['data'][$key]['sign_name'] = $signal_name;  //信号名称
                $signal_arr['data'][$key]['bn'] = $bn;  //信号编号
                $signal_arr['data'][$key]['status'] = $status;  //信号状态 0:待审核 1：通过 2：驳回 3：隐藏 4：即将下架
                $signal_arr['data'][$key]['follow'] = $follow;  //跟随人数
            }
        }
        return json_encode(['code' => 200, 'msg' => '查询成功', 'data' => $signal_arr]);
    }

    /**上传主信号
     * @return string
     */
    public function add_master_signal()
    {
        if ($this->client_uid) {
            $time = time();
            $data = json_decode(input('data'), true);
            if (isset($data['sign_name'])) {
                $data['sign_name'] = trim($data['sign_name']);
            }
            if (isset($data['mt4id'])) {
                $data['mt4id'] = trim($data['mt4id']);
            }
            if (isset($data['mt4pwd'])) {
                $data['mt4pwd'] = trim($data['mt4pwd']);
            }
            $data['add_time'] = $time;
            $data['modify_time'] = $time;
            $uid = $this->client_uid;

            //去除经纪商
            unset($data['business']);
            $data['uid'] = $uid;
            $info = Db::name('Mt4Signal')->where(['uid' => $uid, 'mt4id' => $data['mt4id']])->find();
            $account = Db::name('Mt4Account')->where('mt4id', $data['mt4id'])->count();
            $account1 = Db::name('User')->where('zhmt4uid', $data['mt4id'])->count();
            $slave = Db::name('UserMt4')->where('mt4id', $data['mt4id'])->count();
            if (!empty($account) || !empty($account1)) {
                return json_encode(['code' => 302, 'msg' => '该mt4id账号已签约信号']);
            }
            if (!empty($slave)) {
                return json_encode(['code' => 302, 'msg' => '该mt4id账号已绑定用户']);
            }
            if (empty($info)) {
                $res = Db::name('Mt4Signal')->insert($data);
                if ($res) {
                    return json_encode(['code' => 200, 'msg' => '提交成功']);

                }
                return json_encode(['code' => 302, 'msg' => '提交失败']);

            }
            $data['status'] = 0; //将签约信号置为审核状态
            Db::name('Mt4Signal')->where(['uid' => $uid, 'mt4id' => $data['mt4id']])->update($data);  //更新签约信号中用户信息
            return json_encode(['code' => 200, 'msg' => '修改成功']);
        }
        return json_encode(['code' => 302, 'msg' => '参数错误']);
    }

    /**修改用户主信号密码
     * @return string
     */
    public function edit_master_signal()
    {
        $mt4id = input('mt4id');
        $mt4pwd = input('mt4pwd');
        $show = input('show');
        $where = [
            'from_uid' => $this->client_uid,
            'mt4id' => $mt4id
        ];
        $count = 0;
        $data = [
            'operator' => 'apis-wxcode'
        ];
        if (!empty($mt4pwd)) {  //修改主信号密码
            $data['mt4pwd'] = $mt4pwd;
            $count = Db::name('mt4_account')->where($where)->update($data);
        }
        if (!empty($show)) {  //下架主信号
            $data['show'] = $show;
            $count = Db::name('mt4_account')->where($where)->update($data);
        }
        if ($count) {
            return json_encode(['code' => 200, 'msg' => '修改成功']);
        }
        return json_encode(['code' => 302, 'msg' => '修改失败']);

    }
}