<?php
namespace app\admin\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;
use \think\Db;
use app\common\controller\Common as AppCommon;
use \think\cache\driver\Redis;

class Tradedetail extends Command
{
    protected function configure()
    {
        $this->setName('tradedetail')->setDescription('EA history tradedetail.');
    }

    protected function execute(Input $input, Output $output)
    {
        $redis = new Redis();
        $key = sprintf('%s-%s', 'zhtEA', 'lastwtime');
        $arrCache = array();
        $arrCache = $redis->get($key);
        if ($arrCache && !empty($arrCache)){
            $arrCache = unserialize($arrCache);
        }

        if ( ! empty($arrCache)){
            //var_dump($arrCache);exit();
            foreach ($arrCache as $account => $time) {
                //if ($account != '61132555') continue;
                if (time() - $time < 60*10){ // 10分钟内有变化
                    $this->total($account, $redis);
                    $output->writeln("TradeCommand:success...$account");
                }                
            }
        }

    }

    /* 记录净值等信息变化 */
    private function total($account = null, $redis)
    {
    	if ( ! empty($account)){
            $key = sprintf('%s-%s-%s', 'zhtEA', 'mt4_account_detail', $account);
            if ($redis->get($key)){
                $dataAccount = unserialize($redis->get($key));
                $map['account'] = $account;
                $map['add_time'] = array('gt', time()-12*3600);
                $preData = Db::name('mt4_account_detail')->where($map)->order('id desc')->find();        
                $change = false;
                if ( ! empty($preData)){
                    $changeEquity = $dataAccount['equity']?abs($dataAccount['equity'] - $preData['equity']):0;
                    $changeMargin = $dataAccount['equity']?abs($dataAccount['margin'] - $preData['margin']):0;

                    if (($dataAccount['margin'] == 0 && $preData['margin'] != 0) || floatval($changeEquity) > abs(0.002*$dataAccount['balance']) || floatval($changeMargin) > abs(0.005*$dataAccount['equity'])){
                        $change = true;
                    }
                }else{
                    $change = true;
                }

                if ($change){ // 变化大于**则记录
                    $dataAccount['add_time'] = time();
                    $dataAccount['modify_time'] = time();
                    $dataAccount['operator'] = 'ea-api';
                    //var_dump($dataAccount);
                    //exit();
                    if (Db::name('mt4_account_detail')->insert($dataAccount)){
                        $result = 'accout data was saved.';
                    }else{
                        $result = 'accout info no saved.';
                    }
                }
            }
	    }
    }
}