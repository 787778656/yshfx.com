<?php
namespace app\admin\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;
use \think\Db;
use app\common\controller\Common as AppCommon;
use app\admin\model\Mt4AccountMstatistics;

class Trademonth extends Command
{
    protected function configure()
    {
        $this->setName('trademonth')->setDescription('EA history trademonth.');
    }

    protected function execute(Input $input, Output $output)
    {
    	$arrAccount = Db::name('mt4_account')->column('mt4id,money');
    	if (!empty($arrAccount)){
    		// 清除数据
	    	Db::execute("truncate `zncenter_mt4_account_mstatistics`;");

	    	foreach ($arrAccount as $key => $value) {
	    		echo '---------'.$key.'------------';
	    		if ($key == '810007') continue;
	    		$this->total($key, $value);
	    		$output->writeln("TradeCommand:success...$key");
	    	}
    	}
    }

    private function total($account = 0, $money = 0)
    {
    	if (!empty($account)){
        	// 开始更新表
	        $table = AppCommon::tableName('mt4_history_order', $account);
			$orders= Db::name($table)->where('account', $account)->order('id')->select();

	    	if ( ! empty($orders)){
	    		$result = array();
	    		$arrTicket = array();
	    		foreach ($orders as $key => $order){
	    			if (empty($order['close_time'])) continue; // 跳过没有平单时间的记录
	    			// 去重
	    			if (!empty($order['ticket'])){
	    				if (in_array($order['ticket'], $arrTicket)) continue;
	    				$arrTicket[] = $order['ticket'];
	    			}
		    			
	    			$year = date('Y', $order['close_time']);
	    			$month = date('n', $order['close_time']);

	    			$result[$year][$month]['account'] = $order['account'];
	    			$result[$year][$month]['profit'][] = floatval($order['profit']);
	    			$result[$year][$month]['year'] = $year;
	    			$result[$year][$month]['month'] = $month;
	    			$max_id = $order['id'];
	    		}

	    		// 重新排序
	    		foreach ($result as $key => $value) {
	    			if (!empty($value)) ksort($value);
	    			 $result[$key] = $value;
	    		}
	    		ksort($result);

	    		$i = 0;
	    		$trade_mprofit = 0;
	    		$total_mprofit = 0;
	    		foreach ($result as $year => $yItem) {
	    			foreach ($yItem as $month => $mItem) {
	    				$total_mprofit = array_sum($mItem['profit']);
						$data[] = array(
							'account' => $account,
							'year' => $year,
							'month' => $month,
							'profit' => $total_mprofit,
							'max_id' => $max_id,
							'operator' => 'command-api'
						);

						// 汇总
		    			if (!empty($money)){
							// float <==> int 会出现bug
			    			// $trade_mprofit += number_format(($total_mprofit/$money)*100,2);
						// number_format() 的返回值是一个string, 类型转换可能会报错
			    			$trade_mprofit = floatval($trade_mprofit) + floatval(number_format(($total_mprofit/floatval($money))*100,2));
			    			// $money += $total_mprofit;
			    			$money = floatval($money) + $total_mprofit;
		    			}

						$i++;
	    			}	    			
	    		}

				$Account = new Mt4AccountMstatistics;
				if (!empty($data)){
					if($Account->saveAll($data)){
						// 同时更新主信号表
						$dataAccount['avg_mprofit'] = number_format($trade_mprofit/$i, 2);
						$dataAccount['trade_month'] = $i;						
						Db::name('mt4_account')->where(['mt4id' => $account])->update($dataAccount);
					}
				}
	    	}
	    }
    }
}
