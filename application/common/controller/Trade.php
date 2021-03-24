<?php
namespace app\common\controller;
use \think\Config; 
use \think\Controller;
use \think\Db;
use app\common\controller\Common as AppCommon;
use app\admin\model\Mt4AccountStatistics;
use \think\cache\driver\Redis;

class Trade extends Controller
{
    public static function execute($account = null)
    {
    	if ( ! empty($account)){
    		// 清除该账号已有数据
    		Db::name('mt4_account_statistics')->where('account', $account)->delete();
    		self::total($account);
    		return true;
    	}
    }

    private static function total($account = 0)
    {
    	if (!empty($account)){
        	// 开始更新表
	        $table = AppCommon::tableName('mt4_history_order', $account);
	    	$orders= Db::name($table)->where(['account' => $account])->order('id')->select();

	    	if ( ! empty($orders)){
	    		$result = array();
	    		$i = 0;$j = 0;$k = 0;
	    		$profit = 0;
	    		foreach ($orders as $key => $order) {
	    			if(empty($order['close_price'])) continue;

	    			// 计算小数位
			        if (strstr(trim(rtrim($order['open_price'],0),'.'),'.')){
			            $order['dlen'] = strlen(rtrim($order['open_price'],0)) - strrpos(rtrim($order['open_price'],0),'.')-1;
			        }else{
			            $order['dlen'] = 0;        
			        }

	    			// 交易手数
	    			$result['lots'][] = $order['lots'];

	    			$result['id'][] = $order['id'];
	    			$result['close_time'][] = $order['close_time'];
	    			// 手续费
	    			$result['commission'][] = $order['commission'];
	    			// 库存费
	    			$result['swap'][] = $order['swap'];
	    			// 持有时间
	    			$result['holding_time'][] = $order['close_time'] - $order['open_time'];
	    			$result['tprice'][] = $order['open_price']*$order['lots'];

	    			// 总收益
	    			$profit += $order['profit'];
	    			$result['total_profit'][] = $profit;

	    			if ($order['profit'] >= 0){
	    				$result['profit'][] = $order['profit'];
	    				//$result['profit_num'][] = abs($order['close_price']-$order['open_price'])*pow(10,$order['dlen']);
	    				$result['profit_num'][] = abs($order['close_price']-$order['open_price'])*pow(10,$order['dlen']);
	    				if ($key && $orders[$key-1]['profit'] < 0){
	    					$i++;
	    				}
	    				$result['sprofit'][$i][] = $order['profit'];
	    				$result['sprofit_num'][$i][] = abs($order['close_price']-$order['open_price'])*pow(10,$order['dlen']); 
	    			}else{
	    				$result['loss'][] = $order['profit'];
	    				$result['loss_num'][] = abs($order['close_price']-$order['open_price'])*pow(10,$order['dlen']);
	    				if ($key && $orders[$key-1]['profit'] > 0){
	    					$j++;
	    				}
	    				$result['sloss'][$j][] = abs($order['profit']);
	    				$result['sloss_num'][$i][] = abs($order['close_price']-$order['open_price'])*pow(10,$order['dlen']);
	    			}

	    			// 最大和最小交易时间
	    			$result['min_time'] = min($result['close_time']);
	    			$result['max_time'] = max($result['close_time']);
	    			$result['max_id'] = $order['id'];

	    			// 最大亏损
					$all_key = date('Ymd', $order['close_time']);

					if (isset($result['all_profit'][$all_key])){
						$result['all_profit'][$all_key] += $order['profit'];
					}else{
						$result['all_profit'][$all_key] = $order['profit'];;
					}
	    		}

			    // 获取统计数据
			    $data['min_time'] = $result['min_time'];
			    $data['max_time'] = $result['max_time'];
			    $data['max_id'] = $result['max_id'];
			    $data['operator'] = 'command-api';

			    $data['account'] = $order['account'];
				
				$data['trade_profit'] = isset($result['profit'])?count($result['profit']):0; // 盈利交易
				$data['trade_loss'] = isset($result['loss'])?count($result['loss']):0; // 亏损交易
				$data['trade_total'] = $data['trade_profit'] + $data['trade_loss']; // 总交易数


				$result['loss'] = isset($result['loss'])?$result['loss']:array(0);
				$result['profit'] = isset($result['profit'])?$result['profit']:array(0);
				$result['sloss'] = isset($result['sloss'])?$result['sloss']:array(0);
				$result['sprofit'] = isset($result['sprofit'])?$result['sprofit']:array(0);
				$result['profit_num'] = isset($result['profit_num'])?$result['profit_num']:array(0);
				$result['loss_num'] = isset($result['loss_num'])?$result['loss_num']:array(0);
				
				$data['profit_factor'] = @abs(array_sum($result['profit'])/array_sum($result['loss'])); // 利润因子

				$data['trade_best'] = @max($result['profit']); // 最大盈利交易
				$data['trade_worst'] = @min($result['loss']); // 最大亏损交易

				$data['ex_return'] = array_sum($result['profit'])/array_sum($result['tprice']); // 预期收益

				$data['sharpe_ratio'] = rand(11, 99)/100; // 暂时随机一个数值（0.11-0.99）

				// 连续盈利/亏损
				list($maxNum, $max) = self::getMax($result['sprofit']);
				$data['sprofit_max'] = $max;
				$data['sprofit_max_num'] = $maxNum;


				list($maxNum, $max) = self::getMax($result['sloss']);
				$data['sloss_max'] = $max*(-1);
				$data['sloss_max_num'] = $maxNum;


				list($moreNum, $more) = self::getMore($result['sprofit']);
				$data['sprofit_more'] = $more;
				$data['sprofit_more_num'] = $moreNum;

				list($moreNum, $more) = self::getMore($result['sloss']);
				$data['sloss_more'] = $more*(-1);
				$data['sloss_more_num'] = $moreNum;

				// 毛利
				$data['gross_profit'] = array_sum($result['profit']);
				$data['gross_profit_num'] = array_sum($result['profit_num']);

				$data['gross_loss'] = array_sum($result['loss']);
				$data['gross_loss_num'] = array_sum($result['loss_num']);

				// 平均数
				$data['avg_profit'] = array_sum($result['profit'])/count($result['profit']);
				$data['avg_profit_num'] = array_sum($result['profit_num'])/count($result['profit_num']);

				$data['avg_loss'] = array_sum($result['loss'])/count($result['loss']);
				$data['avg_loss_num'] = array_sum($result['loss_num'])/count($result['loss_num']);

				$data['commission'] = array_sum($result['commission']);
				$data['swap'] = array_sum($result['swap']);
				$data['holding_time'] = array_sum($result['holding_time'])/$data['trade_total'];

				$Account = new Mt4AccountStatistics;

				// 最大结余回撤
				
				if ( ! empty($result['all_profit'])){
					$allProfit = array();
					foreach ($result['all_profit'] as $item) {
						$allProfit[] = $item;
					}
					$lastkey = array_search(min($allProfit), $allProfit);
					$preProfit = array_slice($allProfit, 0, $lastkey, true);
					$drawdown = array();
					for($k = $lastkey; $k >= 0; $k--){
						$drawdown[] = $allProfit[$k];
						if ($allProfit[$k] > 0) break;
					}

					// 当前结余
					$cacheKey = sprintf('%s-%s-%s', 'zhtEA', 'mt4_account_detail', $account);
					$redis = new Redis();
	                if ($redis->has($cacheKey)){
	                    $accountDtail = unserialize($redis->get($cacheKey));
	                }else{
	                	$accountDtail = Db::name('mt4_account_detail')->where('account', $account)->find();
	                }
	                if (isset($accountDtail['balance']) && ! empty($accountDtail['balance'])){
	                	$drawdown = array_sum($drawdown);
	                	$drawdown = $drawdown/$accountDtail['balance'];
	                }else{
	                	$drawdown = 0;
	                }

	                $data['drawdown'] = abs($drawdown);

				}

				$Account = new Mt4AccountStatistics;
				if (!empty($data)){
					if($Account->save($data)){

						$minProfit = Db::name('mt4_account_detail')->where('account', $account)->value('min(profit/balance) as profit');
						
						$tradeDrawdown = Db::name('mt4_account')->where('mt4id', $account)->value('trade_drawdown');
						if (abs($minProfit) > $tradeDrawdown){
							$dataAccount['trade_minprofit'] = number_format(abs($minProfit), 2);
						}

						$dataAccount['trade_minprofit'] = number_format(abs($minProfit), 2);

						// 同时更新主信号表

						$dataAccount['trade_minbalance'] = number_format(abs($drawdown), 4);
						$dataAccount['trade_profit'] = $data['gross_profit']+$data['gross_loss']+$data['commission']+$data['swap'];
						
						$dataAccount['trade_week'] = number_format(($result['max_time'] - $result['min_time'])/(7*24*3600), 2);

						$dataAccount['trade_win'] = number_format($data['trade_profit']/$data['trade_total'], 2);

						$dataAccount['rand_profit'] = implode(',', self::get_array_value($result['total_profit']));
						$dataAccount['following'] = Db::name('mt4_diy_account')->where('mt4id', $account)->count();
						Db::name('mt4_account')->where(['mt4id' => $account])->update($dataAccount);

						// 更新mam数据
						// $dataMam['rand_profit'] = $dataAccount['rand_profit'];
						// $dataMam['history_profit'] = $dataAccount['trade_profit'];
						// $dataMam['history_lots'] = array_sum($result['lots']);
						// $dataMam['trade_day'] = floor($dataAccount['trade_week']*7);

						// 历史利率
						//$beginMoney = Db::name('mt4_account_detail')->where('account', $account)->order('id')->value('balance');
						//$dataMam['history_profit'] = ($beginMoney == 0)?0:100*$dataAccount['trade_profit']/$beginMoney;
						// Db::name('mam_list')->where(['mt4id' => $account])->update($dataMam);
					}
				}
	    	}
	    }
    }

    // (总和)最大的连续(盈利/亏损)
    private static function getMax($arr = null)
    {
    	if (empty($arr)) return;
		foreach ($arr as $key => $value) {
			if (empty($value)) return;
			$sum [$key] = array_sum($value);
		}

		$max = max($sum);
		$maxkey = array_search($max, $sum);

		$sum = array_sum($arr[$maxkey]);
		return array(count($arr[$maxkey]), $sum);
    }

    // (笔数)最多的连续(盈利/亏损)
    private static function getMore($arr = null)
    {
    	if (empty($arr)) return;
		foreach ($arr as $key => $value) {
			if (empty($value)) return;
			$count [$key] = count($value);
		}

		$more = max($count);
		$morekey = array_search($more, $count);

		$sum = array_sum($arr[$morekey]);
		return array($more, $sum);
    }

    // 从数据中取等间距随机值
    private static function get_array_value($arr = null, $num = 10){
    	if ( ! empty($arr)){
			$arrRand = array(0, $num-1);
			$arrLen = count($arr);
			$nkey = ceil($arrLen/$num);
			for ($i = 0; $i < ($num-2); $i++) {
				$arrRand[] = ($i+1)*$nkey;
			}
			$result = array();
			foreach ($arrRand as $value) {
				$result[] = isset($arr[$value])? $arr[$value] : $arr[$arrLen-1];
			}
			return $result;
    	}
    }
}