<?php
/**
* 接口程序
* @author coder<coder@qq.com>
* 2017-09-29
*/
namespace app\api\controller\v1;
use \think\Config;
use \think\Db;
use \think\Controller;
use app\common\controller\Common as AppCommon;
use app\admin\model\Mt4AccountStatistics;
use \think\Cookie;
use \think\cache\driver\Redis;

ini_set('memory_limit', '1024M');
class Web extends Controller
{
    /**
    * 市场信息
    * @return string
    */
    public function marketInfo($from = 'mt4', $account = '000000')
    {
        $redis = new Redis();
        $marketInfo = input('post.marketInfo');
        $key = sprintf('%s-%s-%s-%s', 'zhtEA', 'marketinfo', $from, $account);
        $result = $redis->get($key);

        $callback = input('get.callback');
        $result = sprintf('%s(%s)', $callback, json_encode(array('code'=> 200, 'msg' => '', 'data' => $result)));
        echo $result;
    }

    /**
    * 交易历史
    * @return string
    */
    public function history($account = 0)
    {
		if ( ! empty($account)){
			$arrAccount = explode('-', $account);
            $arrOrders = array();
            foreach ($arrAccount as $key => $account) {
                $table = AppCommon::tableName('mt4_history_order', $account);
                $orders = Db::name($table)->where(['account' => $account])->order('close_time')->field('close_time, open_time, dlen, symbol, op, lots, open_price, takeprofit, stoploss, close_price, commission, swap, profit')->select();
                if (empty($orders)) continue;
                $arrTicket = array();
                foreach ($orders as $value) {
                    // 去重
                    if (!empty($value['ticket'])){
                        if (in_array($value['ticket'], $arrTicket)) continue;
                        $arrTicket[] = $value['ticket'];
                    }
                        
                    $arrOrders[] = $value;
                }
            }
                        
            sort($arrOrders);
			$callback = input('get.callback');
			$result = sprintf('%s(%s)', $callback, json_encode(array('code'=> 200, 'msg' => '', 'data' => $arrOrders)));
			echo $result;
		}
    }


    /**
    * 历史交易类型统计
    */
    public function history_symbol($account = 0)
    {

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

                    if ($order['op'] == 0){
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
        $callback = input('get.callback');
        $result = sprintf('%s(%s)', $callback, json_encode(array('code'=> 200, 'msg' => '', 'data' => @$data)));
        echo $result;        
    }

    /**
    * 账号信息
    */
    public function history_total($account = 0){
        if ( ! empty($account)){
            $result = Db::name('mt4_account_detail')->where('account', $account)->order('id')->field('account, balance, (equity-credit) as equity, margin, free_margin, profit, add_time as date')->select();

            $callback = input('get.callback');
            $result = sprintf('%s(%s)', $callback, json_encode(array('code'=> 200, 'msg' => '', 'data' => $result)));
            echo $result;
        }
    }

    /**
    * 账号实时信息
    */
    public function history_total_active($account = 0){
        if ( ! empty($account)){
            $redis = new Redis();
            $key = sprintf('%s-%s-%s', 'zhtEA', 'mt4_account_detail', $account);
            $result = array();
            if ($redis->get($key)){
                $result = unserialize($redis->get($key));
                $result['time'] = time();
            }

            $beginMoney = Db::name('mt4_account_detail')->where('account', $account)->order('id')->value('balance');
            if (!$beginMoney) $beginMoney = 0;
            $result['beginMoney'] = $beginMoney;

            $callback = input('get.callback');
            $result = sprintf('%s(%s)', $callback, json_encode(array('code'=> 200, 'msg' => '', 'data' => $result)));
            echo $result;
        }
    }

    /**
    * 多mt4账号切换
    */
    public function mt4_change($account = 0){
        if ( ! empty($account)){
            $result = array(
                    'mytade' => url('user/mytrade@www.yshfx.com', ['account' => $account]),
                    'history' => url('master/iorders@www.yshfx.com', ['account' => $account, 'tab' => 'history']),
                    'holding' => url('master/iorders@www.yshfx.com', ['account' => $account, 'tab' => 'holding', 'table' => 'mt4_slave_order']),
                );

            $callback = input('get.callback');
            $result = sprintf('%s(%s)', $callback, json_encode(array('code'=> 200, 'msg' => '', 'data' => $result)));
            echo $result;
        }
    }

    /**
     * 利润率
     * @return string
     */
    public function history_profit($account = 0, $time = 'day')
    {
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
                    // 类型转换
                    $order['commission'] = floatval($order['commission']);
                    $order['swap'] = floatval($order['swap']);
                    switch ($time) {
                        case 'year':
                            $key = sprintf('%s', $year);
                            $result[$key]['time'] = $order['close_time'];
                            $result[$key]['date'] = sprintf('%s年', $year);
                            $result[$key]['profit'][] = floatval($order['profit']+$order['commission']+$order['swap']);
                            break;
                        case 'month':
                            $key = sprintf('%s-%s', $year, $month);
                            $result[$key]['time'] = $order['close_time'];
                            $result[$key]['date'] = sprintf('%s年%s月', $year, $month);
                            $result[$key]['profit'][] = floatval($order['profit']+$order['commission']+$order['swap']);
                            break;
                        case 'week':
                            $key = sprintf('%s-%s', $year, $week);
                            $result[$key]['time'] = $order['close_time'];
                            $result[$key]['date'] = sprintf('%s年%s周', $year, $week);
                            $result[$key]['profit'][] = floatval($order['profit']+$order['commission']+$order['swap']);
                            break;
                        case 'day':
                            $key = sprintf('%s-%s-%s', $year, $month, $day);
                            $result[$key]['time'] = $order['close_time'];
                            $result[$key]['date'] = sprintf('%s-%s-%s', $year, $month, $day);
                            $result[$key]['profit'][] = floatval($order['profit']+$order['commission']+$order['swap']);
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

            $callback = input('get.callback');
            $result = sprintf('%s(%s)', $callback, json_encode(array('code'=> 200, 'msg' => '', 'data' => $arrResult)));
            echo $result;
        }
    }

    /**
     * 利润率
     * @return string
     */
    public function history_lots($account = 0, $time = 'day')
    {
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
                    // 类型转换
                    $order['commission'] = floatval($order['commission']);
                    $order['swap'] = floatval($order['swap']);
                    switch ($time) {
                        case 'year':
                            $key = sprintf('%s', $year);
                            $result[$key]['time'] = $order['close_time'];
                            $result[$key]['date'] = sprintf('%s年', $year);
                            $result[$key]['lots'][] = floatval($order['lots']);
                            break;
                        case 'month':
                            $key = sprintf('%s-%s', $year, $month);
                            $result[$key]['time'] = $order['close_time'];
                            $result[$key]['date'] = sprintf('%s年%s月', $year, $month);
                            $result[$key]['lots'][] = floatval($order['lots']);
                            break;
                        case 'week':
                            $key = sprintf('%s-%s', $year, $week);
                            $result[$key]['time'] = $order['close_time'];
                            $result[$key]['date'] = sprintf('%s年%s周', $year, $week);
                            $result[$key]['lots'][] = floatval($order['lots']);
                            break;
                        case 'day':
                            $key = sprintf('%s-%s-%s', $year, $month, $day);
                            $result[$key]['time'] = $order['close_time'];
                            $result[$key]['date'] = sprintf('%s-%s-%s', $year, $month, $day);
                            $result[$key]['lots'][] = floatval($order['lots']);
                            break;
                    }
                }
                if ( ! empty($result)){
                    sort($result);
                    $i = 0;
                    foreach ($result as $key => $value) {
                        $arrResult[$i]['date'] = $value['date'];
                        $arrResult[$i]['lots'] = number_format(array_sum($value['lots']), 2);
                        $i++;
                    }
                }
            }

            $callback = input('get.callback');
            $result = sprintf('%s(%s)', $callback, json_encode(array('code'=> 200, 'msg' => '', 'data' => $arrResult)));
            echo $result;
        }
    }

    /**
     * 利润率
     * @return string
     */
    public function history_mam_profit($mamId = 0, $account = 0)
    {
        if ( ! empty($account) && ! empty($mamId)){
            $table = AppCommon::tableName('mt4_history_order', $account);
            $orders= Db::name($table)->where(['account' => $account])->order('id desc')->select();           

            $result = array();
            $arrResult = array();
            $result['day']['profit'][] = 0;
            $result['week']['profit'][] = 0;
            $result['month']['profit'][] = 0;
            $result['month3']['profit'][] = 0;
            $result['month6']['profit'][] = 0;
            $result['year']['profit'][] = 0;
            $result['all']['profit'][] = 0;

            if ( ! empty($orders)){
                foreach ($orders as $key => $order) {
                    if (empty($order['close_time'])) continue; // 跳过没有平单时间的记录
                    // 类型转换
                    $order['commission'] = floatval($order['commission']);
                    $order['swap'] = floatval($order['swap']);
                    // 当天24小时                    
                    if ($order['close_time']> time()-24*3600){
                        $result['day']['profit'][] = floatval($order['profit']+$order['commission']+$order['swap']);
                    }

                    // 一周
                    if ($order['close_time']> time()-7*24*3600){
                        $result['week']['profit'][] = floatval($order['profit']+$order['commission']+$order['swap']);
                    }

                    // 一月
                    if ($order['close_time']> time()-30*24*3600){
                        $result['month']['profit'][] = floatval($order['profit']+$order['commission']+$order['swap']);
                    }

                    // 三月
                    if ($order['close_time']> time()-3*30*24*3600){
                        $result['month3']['profit'][] = floatval($order['profit']+$order['commission']+$order['swap']);
                    }

                    // 六月
                    if ($order['close_time']> time()-6*30*24*3600){
                        $result['month6']['profit'][] = floatval($order['profit']+$order['commission']+$order['swap']);
                    }

                    // 一年
                    if ($order['close_time']> time()-365*24*3600){
                        $result['year']['profit'][] = floatval($order['profit']+$order['commission']+$order['swap']);
                    }

                    // 所有
                    $result['all']['profit'][] = floatval($order['profit']+$order['commission']+$order['swap']);
                }

                //var_dump($result);                
                if ( ! empty($result)){
                    $money = Db::name('mam_list')->where('id', $mamId)->value('money');
                    if ($money != 0){
                        foreach ($result as $key => $value) {
                            $arrResult[$key] = number_format(100*array_sum($value['profit'])/$money, 2);
                        }
                    }
                }
            }

            $callback = input('get.callback');
            $result = sprintf('%s(%s)', $callback, json_encode(array('code'=> 200, 'msg' => '', 'data' => $arrResult)));
            echo $result;
        }
    }

    /**
     * 账号利润率汇总
     * @return string
     */

    public function account_score($account = 0){
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
                            $avg_profit = number_format(($ydata[$i]['profit']/$total_money)*100,2);
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

                $result['year_proft'] = empty($balance)? 0:number_format((($now_balance-$balance)/$balance)*1200/$trade_month,2);
                $pscore += $this->vscore($result['year_proft'], array(100, 80, 60, 40, 20), 5);

            }else{
                $result['year_proft'] = number_format($avg_mproft*12, 2);
                $pscore += $this->vscore($result['year_proft'], array(100, 80, 60, 40, 20), 5);
            }

            $result['trade_win'] = empty($arrTrade['trade_total'])?0:number_format($arrTrade['trade_profit']/$arrTrade['trade_total']*100, 2);
            $result['equity_increase'] = ($detail['profit1']>0)?number_format($detail['profit1']*100, 2):0;
            $result['equity_increase'] = number_format($result['equity_increase'],2);
            $result['profit_offset'] = empty($detailTNum)?0:number_format($detailNum1/$detailTNum*100, 2);
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
            $result['loss_max'] = empty($detailTNum)?0:number_format($detailNum2/$detailTNum*100, 2);

            $result['loss_day_max'] = 0;
            // 获取当月总入金
            if ( ! empty($hResult)){
                $lossDayMax = min($hResult);
                $key = array_search($lossDayMax, $hResult);
                if ( ! empty($key)){
                    $arrKey = explode('-', $key);
                    if (count($arrKey) == 3){
                        if (isset($arrProfit[$arrKey[0]][$arrKey[1]])){
                            $result['loss_day_max'] = number_format($lossDayMax/$arrProfit[$arrKey[0]][$arrKey[1]]*100, 2);
                            $result['loss_day_max'] = abs($result['loss_day_max']);
                        }elseif(isset($arrHProfit[$arrKey[0]][$arrKey[1]])){
                            $result['loss_day_max'] = number_format($lossDayMax/$arrHProfit[$arrKey[0]][$arrKey[1]]*100, 2);
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

            $callback = input('get.callback');
            $result = sprintf('%s(%s)', $callback, json_encode(array('code'=> 200, 'msg' => '', 'data' => $result)));
            echo $result;
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
     * zht-web主信号页面加载更多
     * @return string
     */
    public function get_account()
    {
        $page = input('page', 1); //当前页
        $offset = ($page-1)*15;  //偏移量
        $type = input('type', 1);  //type 1: 综合  2：利润率  3：跟单人数 4：回撤（风向） 5：预期收益率 6：交易时长 7:交易胜率
        //var_dump($type);exit;
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
            case 7 :
                $order = ['trade_win' => 'desc'];   //交易胜率
                break;
            default :
                $order = ['score' => 'desc'];
                break;
        }
        $result = Db::name('Mt4Account')->field('*,`trade_profit`/`money`as profit,follow+following as follow, GREATEST(trade_drawdown,trade_minbalance,trade_minprofit) as trade_drawdown')->where('show', 'in', [1,3,11])->order(array_merge($order, ['id' => 'desc']))->limit($offset,15)->select();
        if (!empty($result)) {
            foreach ($result as $key => $userinfo) {
                // 获取真实跟随者
                /*$tFollow = Db::name('mt4_diy_account')->where('mt4id', $userinfo['mt4id'])->count();
                $result[$key]['follow'] = $userinfo['follow']+$tFollow;*/
                $avg_mproft = $userinfo['avg_mprofit'];
                $result[$key]['avg_mproft'] = $avg_mproft*12;
            }
        }
        $data['data'] = $result;
        $data['current_page'] = $page;
        // 国家配置
        $mt4country = Config::get('options.mt4country');

        // 服务商
        $mt4service = Config::get('options.mt4service');

        $callback = input('get.callback');
        $result = sprintf('%s(%s)', $callback, json_encode(array_merge($data, ['mt4country' => array_flip($mt4country), 'mt4service' =>array_flip($mt4service), 'code' => 200])));
        echo $result;
    }

    /**
     * zht-web主信号页面用户跟单数据
     * @return string
     */
    public function get_master_signal()
    {
        $uid = input('uid', 0);
        $slave_mt4id = input('slave_mt4id'); //从mt4id
        $join = [
            ['mt4_account a', 'diy.mt4id = a.mt4id']
        ];
        $arrFollow = [];

        $map['diy.uid'] = $uid;
        if (!empty($slave_mt4id)) {
            $arrFollow = Db::name('user_mt4')->where(['mt4id' => $slave_mt4id])->field('uid,mt4id,is_default')->find();
            //$map['diy.slave_mt4id'] = $slave_mt4id;
        }
        // 多mt4启用, 兼容单mt4账号没有slave_mt4id的情况
        if (!empty($arrFollow) && $arrFollow['is_default'] == 1){
            $map['diy.slave_mt4id'] = array(['EXP', 'IS NULL'], ['eq', $slave_mt4id], 'or');
        }else{
            $map['diy.slave_mt4id'] = ['eq', $slave_mt4id];
        }
        // 国家配置
        $mt4country = Config::get('options.mt4country');
        $data = Db::name('Mt4DiyAccount')->alias('diy')->join($join,'','left')->where($map)->field('diy.*, a.img as u_img, a.country')->order('diy.add_time desc')->column('diy.mt4id,diy.*,a.img as u_img, a.country');
        $data = array_values($data);
        $callback = input('get.callback');
        $result = sprintf('%s(%s)', $callback, json_encode(array_merge(['code' => 200, 'mt4country' => array_flip($mt4country)],['data' => $data])));
        echo $result;
    }

    /**
     * 首页滚动内容输出
     * @return json
     */  
    public function get_index_scroll()
    {
        $base = time()-strtotime('2017-10-17');

        $rank1 = rand(1,2);
        $base1 = ceil(30000+floor($base/1000));
        if (input('get.base1')){
            $base1 = input('get.base1');
            $rank1 = rand(1,10);
            $base1 += $rank1;
        }

        $base2 = ceil(150000000*(1+floor($base/86400)*0.05));
        if (input('get.base2')){
            $base2 = input('get.base2');
        }

        $rank2 = rand(1000, 9999);
        //$rank = 0;
        $data['base1'] = $base1 + $rank1;
        $data['base2'] = $base2 + $rank2;

        $callback = input('get.callback');
        $result = sprintf('%s(%s)', $callback, json_encode(array('code'=> 200, 'msg' => '', 'data' => $data)));
        echo $result;
    }

    /**
     * 弹层提示
     * @return json
     */
    public function tips_space($uid = 0){
        $uid = input('get.uid');
        $data['show'] = false;
        if ( ! empty($uid)){
            $redis = new Redis();
            $key = sprintf('%s-%s-%s', 'zhtEA', 'trade_tips_space', $uid);
            if (Cookie::has($key)){
                $data['show'] = false;
            }else{
                $data['show'] = true;
                Cookie::set($key, time(), 3600*24);
            }
        }

        $callback = input('get.callback');
        $result = sprintf('%s(%s)', $callback, json_encode(array('code'=> 200, 'msg' => '', 'data' => $data)));
        echo $result;
    }

    // /**
    //  * 分佣关系统计
    //  * @return json
    //  */
    // public function get_invite_data($uid = 0){
    //     // 直接邀请注册的
    //     $data = array();
    //     $arrSub2 = $arrSubMt2 = 0;
    //     if ( ! empty($uid)){
    //         $arrSub = db('user')->where('parent_id', $uid)->column('uid');
    //         $arrSubMt4 = db('user')->where('parent_id', $uid)->where('isbuy', 1)->count();
    //         // 间接的邀请注册的   
    //         if ( ! empty($arrSub)){
    //             $arrSub2 = db('user')->where('parent_id', 'in', $arrSub)->count();
    //             $arrSubMt2 = db('user')->where('parent_id', 'in', $arrSub)->where('isbuy', 1)->count();
    //         }

    //         // 邀请购买的
    //         $arrSubBuy = db('user_reward')->where('src_id', 'like', $uid.'%')->count();
    //         $arrSubBuy2 = db('user_reward')->where('src_id', 'like', '%-'.$uid)->count();

    //         $data['arr_sub'] = count($arrSub);
    //         $data['arr_sub_mt4'] = count($arrSub);
    //         $data['arr_sub_buy'] = $arrSubBuy;
    //         $data['arr_sub2'] = $arrSub2;
    //         $data['arr_sub2_mt4'] = $arrSubMt2;
    //         $data['arr_sub2_buy'] = $arrSubBuy2;
    //     }

    //     $callback = input('get.callback');
    //     $result = sprintf('%s(%s)', $callback, json_encode(array('code'=> 200, 'msg' => '', 'data' => $data)));
    //     echo $result;
    // }

    /**
     * 分佣关系统计
     * @return json
     */
    public function get_invite_data($uid = 0){
        // 直接邀请注册的
        $data = array();
        if ( ! empty($uid)){
            $arrSub2 = $arrSubMt2 = $arrSub3 = $arrSubMt3 = 0;
            $arrSub = db('user')->where('parent_id', $uid)->column('uid');
            $arrSubMt4 = db('user')->where('parent_id', $uid)->where('isbuy', 1)->count();

            # 直接下级的所有信息
            $arrSub_detail = db('user')->alias('a')->field('a.id,a.uid,a.nickname,a.login,a.zhmt4uid,count(b.id) mt4_count_num')->join('user_mt4 b','a.uid=b.uid','left')->where('parent_id', $uid)->group('a.uid')->select();
            $arrSubMt4_detail = db('user')->alias('a')->field('a.id,a.uid,a.nickname,a.login,a.zhmt4uid,count(b.id) mt4_count_num')->join('user_mt4 b','a.uid=b.uid','left')->where('parent_id', $uid)->where('isbuy', 1)->group('a.uid')->select();

            // 间接的邀请注册的
            if ( ! empty($arrSub)){
                $arrSub2 = db('user')->where('parent_id', 'in', $arrSub)->count();
                $arrSubMt2 = db('user')->where('parent_id', 'in', $arrSub)->where('isbuy', 1)->count();

                # 间接下级的所有信息 
                $arrSub2_detail = db('user')->alias('a')->field('a.id,a.uid,a.nickname,a.login,a.zhmt4uid,count(b.id) mt4_count_num')->join('user_mt4 b','a.uid=b.uid','left')->where('parent_id', 'in', $arrSub)->group('a.uid')->select();
                $arrSubMt2_detail = db('user')->alias('a')->field('a.id,a.uid,a.nickname,a.login,a.zhmt4uid,count(b.id) mt4_count_num')->join('user_mt4 b','a.uid=b.uid','left')->where('parent_id', 'in', $arrSub)->where('isbuy', 1)->group('a.uid')->select();
            
                ####################2018-09-26 新增三级下级#####################
                $arrSub2_id_arr = db('user')->where('parent_id', 'in', $arrSub)->column('uid');
                if($arrSub2_id_arr && count($arrSub2_id_arr) > 0){
                    $arrSub3 = db('user')->where('parent_id', 'in', $arrSub2_id_arr)->count();
                    $arrSubMt3 = db('user')->where('parent_id', 'in', $arrSub2_id_arr)->where('isbuy', 1)->count();

                    # 间接下级的所有信息 
                    $arrSub3_detail = db('user')->alias('a')->field('a.id,a.uid,a.nickname,a.login,a.zhmt4uid,count(b.id) mt4_count_num')->join('user_mt4 b','a.uid=b.uid','left')->where('parent_id', 'in', $arrSub2_id_arr)->group('a.uid')->select();
                    $arrSubMt3_detail = db('user')->alias('a')->field('a.id,a.uid,a.nickname,a.login,a.zhmt4uid,count(b.id) mt4_count_num')->join('user_mt4 b','a.uid=b.uid','left')->where('parent_id', 'in', $arrSub2_id_arr)->where('isbuy', 1)->group('a.uid')->select();
                }
            }

            # 获取所有的子集用户的信息(uid=>name键值对)
            $all_next_data = [];
            # 一级下级
            if($arrSub_detail){
                foreach($arrSub_detail as $v){
                    if($v['uid']){
                        $all_next_data[$v['uid']] = $v['nickname']?$v['nickname']:$v['login'];
                    }
                }

                # 二级下级
                if(isset($arrSub2_detail) && $arrSub2_detail){
                    foreach($arrSub2_detail as $v1){
                        if($v1['uid']){
                            $all_next_data[$v1['uid']] = $v1['nickname']?$v1['nickname']:$v1['login'];
                        }
                    }

                    # 三级下级
                    if(isset($arrSub3_detail) && $arrSub3_detail){
                        foreach($arrSub3_detail as $v2){
                            if($v2['uid']){
                                $all_next_data[$v2['uid']] = $v2['nickname']?$v2['nickname']:$v2['login'];
                            }
                        }
                    }
                }
            }

            ###################购买vip返佣、信号按手数返佣##################
            $all_buy_reward_sum = Db::name('user_reward')->where('uid',$uid)->where(['class'=>'recharge','status'=>1])->sum('amount'); # 下级用户的充值返佣总数
            $data['all_buy_reward_sum'] = $all_buy_reward_sum?$all_buy_reward_sum:'';
            $all_signal_reward_sum = Db::name('user_money_log')->where('uid',$uid)->where(['class'=>'money-follow-reward'])->sum('amount'); # 信号返佣的总数
            $data['all_signal_reward_sum'] = $all_signal_reward_sum?$all_signal_reward_sum:'';
            $all_signal = Db::name('user_money_log')->where('uid',$uid)->where(['class'=>'money-follow-reward'])->select(); # 信号返佣的详细

            $arrSubSignal = 0;  # 一级下级数量
            $arrSubSignal2 = 0; # 二级下级数量
            $arrSubSignal3 = 0; # 三级下级数量
            $arrSubSignal_data = $arrSubSignal2_data = $arrSubSignal3_data = [];  # 一级二级三级下级的具体数据
            if($all_signal){
                foreach($all_signal as $k=>$v){
                    if($v['src_id']){
                        // if(strpos($v['src_id'],'-')===false){
                        //     # 如果该一级下标已经存在 amount累加
                        //     if(array_key_exists($v['src_id'],$arrSubSignal_data)){
                        //         $arrSubSignal_data[$v['src_id']] += $v['amount'];
                        //     }else{
                        //         # 一级下级
                        //         $arrSubSignal++;
                        //         $arrSubSignal_data[$v['src_id']] = $v['amount'];
                        //     }
                        // }else{
                        //     # 二级src_id是按照 uid-parent_id来拼接的
                        //     $src_id_arr = explode('-',$v['src_id']);
                        //     if($src_id_arr && isset($src_id_arr[0]) && $src_id_arr[0]){
                        //         # 如果该二级下标已经存在 amount累加
                        //         if(array_key_exists($src_id_arr[0],$arrSubSignal2_data)){
                        //             $arrSubSignal2_data[$src_id_arr[0]] += $v['amount'];
                        //         }else{
                        //             # 二级下级
                        //             $arrSubSignal2++;
                        //             $arrSubSignal2_data[$src_id_arr[0]] = $v['amount'];
                        //         }
                        //     }                            
                        // }

                        # 将src_id分解
                        $src_arr = explode('|',$v['src_id']);
                        if($src_arr && count($src_arr) == 4 ){
                            if($src_arr[0]){
                                if($src_arr['3']==1){
                                    if(array_key_exists($src_arr[0],$arrSubSignal_data)){
                                        $arrSubSignal_data[$src_arr[0]] += $v['amount'];
                                    }else{
                                        # 一级下级
                                        $arrSubSignal++;
                                        $arrSubSignal_data[$src_arr[0]] = $v['amount'];
                                    }
                                }else if($src_arr['3']==2){
                                    if(array_key_exists($src_arr[0],$arrSubSignal2_data)){
                                        $arrSubSignal2_data[$src_arr[0]] += $v['amount'];
                                    }else{
                                        # 二级下级
                                        $arrSubSignal2++;
                                        $arrSubSignal2_data[$src_arr[0]] = $v['amount'];
                                    }
                                }else if($src_arr['3']==3){
                                    if(array_key_exists($src_arr[0],$arrSubSignal3_data)){
                                        $arrSubSignal3_data[$src_arr[0]] += $v['amount'];
                                    }else{
                                        # 三级下级
                                        $arrSubSignal3++;
                                        $arrSubSignal3_data[$src_arr[0]] = $v['amount'];
                                    }
                                }
                            }
                        }
                    }
                }
            }

            $data['arrSubSignal'] = $arrSubSignal;
            $data['arrSubSignal_data'] = $arrSubSignal_data?$arrSubSignal_data:'';
            $data['arrSubSignal2'] = $arrSubSignal2;
            $data['arrSubSignal2_data'] = $arrSubSignal2_data?$arrSubSignal2_data:'';
            $data['arrSubSignal3'] = $arrSubSignal3;
            $data['arrSubSignal3_data'] = $arrSubSignal3_data?$arrSubSignal3_data:'';

            #####################################   
            // 邀请购买的
            // $arrSubBuy = db('user_reward')->where('src_id', 'like', $uid.'%')->count();
            // $arrSubBuy2 = db('user_reward')->where('src_id', 'like', '%-'.$uid)->count();
            $buyData = Db::name('user_reward')->where('uid',$uid)->where(['class'=>'recharge','status'=>1])->select(); # 下级用户的充值返佣详情

            $arrSubBuy = 0;  # 一级下级数量
            $arrSubBuy2 = 0; # 二级下级数量
            $arrSubBuy_data = $arrSubBuy2_data = [];  # 一级二级下级的具体数据
            if($buyData){
                foreach($buyData as $k=>$v){
                    if($v['src_id']){
                        if(strpos($v['src_id'],'-')===false){
                            # 如果该一级下标已经存在 amount累加
                            if(array_key_exists($v['src_id'],$arrSubBuy_data)){
                                $arrSubBuy_data[$v['src_id']] += $v['amount'];
                            }else{
                                # 一级下级
                                $arrSubBuy++;
                                $arrSubBuy_data[$v['src_id']] = $v['amount'];
                            }
                        }else{
                            # 二级src_id是按照 uid-parent_id来拼接的
                            $src_id_arr = explode('-',$v['src_id']);
                            if($src_id_arr && isset($src_id_arr[0]) && $src_id_arr[0]){
                                # 如果该二级下标已经存在 amount累加
                                if(array_key_exists($src_id_arr[0],$arrSubBuy2_data)){
                                    $arrSubBuy2_data[$src_id_arr[0]] += $v['amount'];
                                }else{
                                    # 二级下级
                                    $arrSubBuy2++;
                                    $arrSubBuy2_data[$src_id_arr[0]] = $v['amount'];
                                }
                            }                            
                        }
                    }
                }
            }

            $data['arr_sub'] = count($arrSub);
            # $data['arr_sub_mt4'] = count($arrSub);
            // $data['arr_sub_mt4'] = count($arrSubMt4);
            $data['arr_sub_mt4'] = $arrSubMt4;
            $data['arr_sub_buy'] = $arrSubBuy;
            $data['arr_sub2'] = $arrSub2;
            $data['arr_sub2_mt4'] = $arrSubMt2;
            $data['arr_sub2_buy'] = $arrSubBuy2;

            $data['arr_sub3'] = $arrSub3;      # 三级下级的人数
            $data['arr_sub3_mt4'] = $arrSubMt3; # 三级下级购买了vip的人数

            # 新增的
            $data['arr_sub_detail'] = $arrSub_detail?$arrSub_detail:'';
            $data['arr_sub_mt4_detail'] = $arrSubMt4_detail?$arrSubMt4_detail:'';
            $data['arr_sub2_detail'] = isset($arrSub2_detail) && $arrSub2_detail?$arrSub2_detail:'';
            $data['arr_sub2_mt4_detail'] = isset($arrSubMt2_detail) && $arrSubMt2_detail?$arrSubMt2_detail:'';
            $data['arr_sub3_detail'] = isset($arrSub3_detail) && $arrSub3_detail?$arrSub3_detail:'';
            $data['arr_sub3_mt4_detail'] = isset($arrSubMt3_detail) && $arrSubMt3_detail?$arrSubMt3_detail:'';

            $data['arrSubBuy_data'] = $arrSubBuy_data ? $arrSubBuy_data : '';
            $data['arrSubBuy2_data'] = $arrSubBuy2_data ? $arrSubBuy2_data : '';
            $data['all_next_data'] = $all_next_data ? $all_next_data : '';
        }

        $callback = input('get.callback');
        $result = sprintf('%s(%s)', $callback, json_encode(array('code'=> 200, 'msg' => '', 'data' => $data)));
        echo $result;
    }
}