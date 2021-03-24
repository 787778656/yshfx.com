<?php
namespace app\admin\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;
use \think\Db;
use \think\Config;
use \think\cache\driver\Redis;
use app\common\controller\Common as AppCommon;
use app\common\controller\Money;

class Main extends Command
{
    protected function configure()
    {
        $this->setName('main')->setDescription('Main trade.');
    }

    /**
    * 结算
    */
    protected function execute(Input $input, Output $output)
    {		
		// 获取分佣比例,必需先设置
		$reward = Db::name('reward_set')->value('reward');
		$arrReward = explode('|', $reward);

		if (empty($arrReward)){
			$output->writeln("Reward not set...");
		}
		// 获取手术
		$arrMt4 = Db::name('user_mt4')->where('is_demo', 0)->column('mt4id, uid');
		if ( ! empty($arrMt4)){
			$data['arrMt4'] = $arrMt4;
			$data['output'] = $output;
			$data['arrReward'] = $arrReward;
			Db::transaction(function() use($data){
				$arrMt4 = $data['arrMt4'];
				$output = $data['output'];
				$arrReward = $data['arrReward'];
				$redis = new Redis();
				$ulog = array();
				foreach ($arrMt4 as $mt4id => $uid) {
					$lotsKey = sprintf('%s-%s-%s', 'zhtEA', 'main_follow_lots', $mt4id);
					if ($redis->has($lotsKey)){
						$lots = $redis->get($lotsKey);
						if ($lots > 0){ // 分佣
							// 所有上线
							$pids = $this->get_pid($uid);
							if (!empty($pids)){
								$level = 0;
								foreach ($pids as $pid) {
									if (isset($arrReward[$level])){
										$reward = round((float)$arrReward[$level]*$lots, 2);
										if (Db::name('user')->where('uid', $pid)->setField(['imoney' => ['exp', "imoney+($reward)"]]) == true){
											$ulog[] = array(
											    'uid' => $pid,
											    'amount' => $reward,
											    'src_id' => sprintf('%s|%s|%s|%s', $uid, $mt4id, $lots, $level+1),
											    'class' => 'money-follow-reward',
											    'remark' => '跟单返佣',
											    'add_time' => time(),
											    'operator' => 'money-reward-api',
											);
										}
									}
									$level++;
								}
							}

							// 更新手术
							$nlots = $redis->get($lotsKey);
							$redis->set($lotsKey, $nlots-$lots);						
						}
					}
					$output->writeln("TradeCommand:success...".$mt4id);
				}

				// 写入user消费记录
				if ( ! empty($ulog)){
					Db::name('user_money_log')->insertAll($ulog);
				}
			});			
		}
    }

    /**
    * 获取上级uid
    */
    private function get_pid($uid, $pids = array()){
    	$pid = Db::name('user')->where('uid', $uid)->value('parent_id');
    	if ( ! empty($pid)){
    		if (!in_array($pid, $pids)){
	    		$pids[] = $pid;
	    		$pids = $this->get_pid($pid, $pids);
    		}
    	}
    	return $pids;
    }
}
