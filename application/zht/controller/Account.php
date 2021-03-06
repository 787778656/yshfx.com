<?php
/**
* 主账号管理
* @author efon.cheng<efon@icheng.xyz>
* 2017-08-31
*/
namespace app\zht\controller;
use \think\Config;
use \think\Db;
use \think\Session;
use app\common\controller\Admin;
use app\common\controller\Common as AppCommon;
use app\common\controller\Msg as AppMsg;
use app\common\controller\Trade as AppTrade;
use \think\cache\driver\Redis;

class Account extends Admin
{
    /**
    * 账号列表
    */  
    public function index(){
        // 导入分页类
        import ( 'ORG.Util.Page' );
        $keyword = trim(input('keyword'));
        $map = array();
        if ( ! empty($keyword)){
            $map['bn|name|mt4id'] = array('like', "%$keyword%");
        }

        $status = input('status');
        if ( ! empty($status)){
            if ($status == 3) {
                $map['show'] = 2;
            }else {
                $map['status'] = $status;
            }
        }
        
        $arrAccount = Db::name('mt4Account')->where($map)->order('modify_time desc, id desc')->paginate(10);
        $this->assign('arrAccount', $arrAccount);
        $this->assign('status', $status);
        $this->assign('keyword', $keyword);
        // redis
        $redis = new Redis();
        $this->assign('redis', $redis);
        
        return $this->fetch();
    }

    /**
    * 添加账号
    */
    public function add(){
        $Account = model('Mt4Account');
        if (input('?post.bn')){
            $data = [
                'bn' => input('post.bn'),
                'name' => input('post.name'),
                'mt4id' => input('post.mt4id'),
                'mt4pwd' => input('post.mt4pwd'),
                'sblfix' => input('post.sblfix'),
                /*'weight' => input('post.weight'),
                'maxloss' => input('post.maxloss'),
                'maxhold' => input('post.maxhold'),
                'maxtrade' => input('post.maxtrade'),*/
                'trade_drawdown' => (float)input('post.trade_drawdown'),
                'mt4server' => input('post.mt4server'),
                'img' => input('post.thumb'),
                'country' => input('post.country'),
                'money' => input('post.money'),
                'follow' => (int)input('post.follow'),
                'score' => (int)input('post.score'),
                'show' => input('post.show'),
                'remark' => trim(input('post.remark')),
                'detail' => trim(input('post.detail')),
            ];
            $data['detail'] = $data['detail']?htmlspecialchars($data['detail']):'';
            $data['operator'] = Session::get('admin.username');
            if ($Account->save($data)){
                $this->success('账号添加成功！', url('account/index'));
            }else{
                $this->error('账号添加失败！');
            }
        }
        // 自动编号
        $maxId = $Account->max('id');
        $this->assign('autoBn', sprintf('0008%s', $maxId+1));

        // view变量
        $this->assign('mt4service', Config::get('options.mt4service'));
        $this->assign('mt4country', Config::get('options.mt4country')); 
        $this->assign('countrymoneysymbol', Config::get('options.countrymoneysymbol'));
        return $this->fetch();
    }

    /**
    * 编辑账号
    */
    public function edit($id = 0){
        if(!empty($id)){
            $Account = model('Mt4Account');
            $arrAccount = $Account::get($id);
            $this->assign('arrAccount', $arrAccount);

            if(isset($_POST['id'])){
                $data = [
                    'bn' => input('post.bn'),
                    'name' => input('post.name'),
                    'mt4id' => input('post.mt4id'),
                    'mt4pwd' => input('post.mt4pwd'),
                    'sblfix' => input('post.sblfix'),
                    /*'weight' => input('post.weight'),
                    'maxloss' => input('post.maxloss'),
                    'maxhold' => input('post.maxhold'),
                    'maxtrade' => input('post.maxtrade'),*/
                    'trade_drawdown' => input('post.trade_drawdown'),
                    'mt4server' => input('post.mt4server'),
                    'img' => input('post.thumb'),
                    'country' => input('post.country'),
                    'money' => input('post.money'),
                    'follow' => input('post.follow'),
                    'score' => input('post.score'),
                    'show' => input('post.show'),
                    'remark' => trim(input('post.remark')),
                    'detail' => trim(input('post.detail')),
                ];
                $data['detail'] = $data['detail']?htmlspecialchars($data['detail']):'';
                // 加入时间添加人等信息
                $data['operator'] = Session::get('admin.username');
                if (empty($data['mt4id'])){
                    $this->error('账号修改失败！');
                }else{
                    if ($Account->save($data, ['id' => input('post.id')])){

                        if ($data['show'] == 1 && $arrAccount['show'] != 1 && !empty($arrAccount['from_uid'])) {
                            AppMsg::send($arrAccount['from_uid'], 'upload_accout_success');
                        }
                        $this->success('账号修改成功！', url('account/index'));
                    }else{
                        $this->error('账号修改失败！');
                    }
                }
            }
            
            // view变量
            $this->assign('mt4service', Config::get('options.mt4service'));
            $this->assign('mt4country', Config::get('options.mt4country')); 
            return $this->fetch();
        }
    }

    /**
    * 删除账号
    */
    public function del(){
        if (input('?post.aid')){
            $id = input('post.aid');
            $Account = model('Mt4Account');
            $mt4Account = $Account->where('id', $id)->value('mt4id');
            $count = $Account::destroy($id);
            if($count > 0){                
                if ( ! empty($mt4Account)){
                    // 同时删除跟单组合
                    Db::name('mt4_diy_account')->where('mt4id', $mt4Account)->delete();
                }
            }
        }
    }

    /**
    * 下单记录
    */
    public function order($account = 0){
        if (!empty($account)){
            // 导入分页类
            import ( 'ORG.Util.Page' );
            $arrOrder = Db::name('mt4_master_order')->where(array('account'=>$account))->order('open_time desc')->paginate(10, false, ['var_page' => 'opage']);
            $this->assign('arrOrder', $arrOrder);

            $this->assign('zhttype', Config::get('options.zhttype'));
            // 记录所传参数
            $query = request()->param();
            foreach ($query as $key => $value) {
                $arrQuery[] = sprintf('%s=%s', $key, trim($value));
            }
            $this->assign('strQuery', implode('&', $arrQuery));
            $this->assign('account', $account);
            return $this->fetch();
        }
    }


    /**
    * 历史记录
    */
    public function order_history($account = 0){
        if (!empty($account)){
            // 导入分页类
            import ( 'ORG.Util.Page' );
            $table = AppCommon::tableName('mt4_history_order', $account);
            $arrOrder = Db::name($table)->where(array('account'=>$account))->order('close_time desc')->paginate(10, false, ['var_page' => 'opage']);
            $this->assign('arrOrder', $arrOrder);

            $this->assign('zhttype', Config::get('options.zhttype'));
            // 记录所传参数
            $query = request()->param();
            foreach ($query as $key => $value) {
                $arrQuery[] = sprintf('%s=%s', $key, trim($value));
            }

            $this->assign('account', $account);
            $this->assign('strQuery', implode('&', $arrQuery));
            return $this->fetch();
        }
    }

    /**
    * 预警信息
    */
    public function warn(){
        // 导入分页类
        import ( 'ORG.Util.Page' );
        $keyword = input('keyword');
        $map = array();
        if ( ! empty($keyword)){
            $map['from'] = array('like', "%$keyword%");
        }
        $arrWarn = Db::name('mt4_warn')->where($map)->order('id desc')->paginate(10);
        $this->assign('arrWarn', $arrWarn);
        $this->assign('keyword', $keyword);
        return $this->fetch();
    }

    /**
    * 账号信息查找
    */
    public function search_mt4(){
        // 导入分页类
        $keyword = '';
        $msg = '';
        if (input('?post.keyword')){
            $redis = new Redis();
            $keyword = trim(input('post.keyword'));
            $mt4id = $keyword;
            $ipKey = sprintf('%s-%s', 'zhtEA', 'hash_online_ip');
            $timeKey = sprintf('%s-%s', 'zhtEA', 'hash_online_time');
            if ($redis->hexists($ipKey, $mt4id)){
                $inIp = $redis->hget($ipKey, $mt4id);                
                $time = date('Y-m-d H:i:s', $redis->hget($timeKey, $mt4id));
                $outIp = '--';
                $arrIp = Config::get('ip');
                if (isset($arrIp[$inIp])){
                    $outIp = $arrIp[$inIp];
                }
                $msg = sprintf('<br/>内网IP: %s<br/><br/>公网IP: %s<br/><br/>最后在线: %s', $inIp, $outIp, $time);
                $msg .= '<br/><br/>账号信号: '.json_encode(AppCommon::get_account_detail($mt4id));
            }else{
                $msg = '无相关mt4账号!';
            }
            
        }
        $this->assign('keyword', $keyword);
        $this->assign('msg', $msg);
        return $this->fetch();
    }

    /**
    * 预警信息状态
    */
    public function warn_status(){
        if (input('?post.id')){
            $id = input('post.id');
            $status = input('post.status');
            if(Db::name('mt4_warn')->where('id', $id)->update(['status' => $status, 'modify_time' => time(), 'operator' => Session::get('admin.username')])){
                echo '1';
            }else{
                echo '2';
            }
        }
    }



    /**
    * 生成交易数据
    */
    public function make_trade_data($account = null){
        if ( ! empty($account)){
            // 生成数据
            AppTrade::execute($account);
            echo 'success';
        }
    }

    /**
    * 删除历史记录
    */
    public function clean_history(){
        $account = input('post.account');
        if (!empty($account)){
            $table = AppCommon::tableName('mt4_history_order', $account);
            Db::name($table)->where(['account' => $account, 'ticket' =>['gt', 0]])->delete();
        }
    }

    /**
    * 导入excel历史交易
    */
    public function import_excel($id = 0){
        if(!empty($id)){
            $Account = model('Mt4Account');
            $arrAccount = $Account::get($id);
            $this->assign('arrAccount', $arrAccount);

            if (input('?post.thumb')){
                $isCovered = input('post.is_covered');
                $fileName = input('post.thumb');
                $account = input('post.mt4id');
                //$filePath = sprintf('%s/public/static.v.yshfx.com/upload/file/%s', ROOT_PATH, $fileName);
                $filePath = sprintf('/home/wwwroot/static.v.yshfx.com/upload/file/%s', $fileName);
                $arrOrder = AppCommon::read_excel($filePath);
                //Time;Type;Volume;Symbol;Price;Time;Price;Commission;Swap;Profit
                //Time;Type;Volume;Symbol;Price;S/L;T/P;Time;Price;Commission;Swap;Profit;Comment

                if ( ! empty($arrOrder)){
                    $i = 0; $dataKey = 0;
                    foreach ($arrOrder as $key => $order) {
                        if ($key == 0) continue;
                        $arrOrder[0] = str_replace(' ', '', $arrOrder[0]);
                        $order = str_replace(' ', '', $order);
                        switch ($arrOrder[0]) {
                            case 'Time;Type;Volume;Symbol;Price;Time;Price;Commission;Swap;Profit':
                                list($open_time, $op, $lots, $symbol, $open_price, $close_time, $close_price, $commission, $swap, $profit) = explode(';', $order);
                                //$lots = floatval($lots)/10000;
                                break;
                            case 'Time;Type;Volume;Symbol;Price;S/L;T/P;Time;Price;Commission;Swap;Profit;Comment':
                                list($open_time, $op, $lots, $symbol, $open_price, $stoploss, $takeprofit, $close_time, $close_price, $commission, $swap, $profit) = explode(';', $order);
                                break;                           
                            default:
                                exit('非预期的数据文件.');
                                break;
                        }

                        if (empty($symbol) || empty($profit) || empty($open_time) || empty($close_time)) continue;

                        $arrDlen = explode('.', $open_price);
                        $arrDlen2 = explode('.', $close_price);
                        if (isset($arrDlen[1])){
                            $dlen = strlen($arrDlen[1]);
                        }elseif (isset($arrDlen2[1])) {
                            $dlen = strlen($arrDlen2[1]);
                        }else{
                            $dlen = 0;
                        }

                        $op = ($op == 'Buy') ? 0 : 1;
                        // 重组数据           
                        $data[$dataKey][] = array(
                                'account' => $account,
                                //'ticket' => $value->ticket,
                                'symbol' => $symbol,
                                'lots' => $lots,
                                'op' => $op,
                                'dlen' => $dlen,
                                'open_price' => substr($open_price, 0, 7),
                                'close_price' => substr($close_price, 0, 7),
                                'profit' => $profit,
                                'commission' => $commission,
                                'swap' => empty($swap)?'0':$swap,
                                'takeprofit' => empty($takeprofit)?'0':$takeprofit,
                                'stoploss' => empty($stoploss)?'0':$stoploss,
                                'open_time' => strtotime(str_replace('.', '-', $open_time)),
                                'close_time' => strtotime(str_replace('.', '-', $close_time)),
                                'add_time' => time(),
                                'modify_time' => time(),
                                'operator' => 'import-api'
                            );
                        if (($i+1)%1000 == 0){
                            $dataKey++;
                        }
                        $i++;
                    }

                    // 保存交易数据
                    $table = AppCommon::tableName('mt4_history_order', $account);

                    if ( ! empty($data)){
                        $maxId = Db::name($table)->max('id');
                        // 开启事务
                        Db::startTrans();
                        foreach ($data as $rows) {
                            if ( ! Db::name($table)->insertAll($rows)){
                                Db::rollback(); // 回滚
                                $this->error('数据导入失败！');
                                exit();
                            }
                        }
                        // 事务提交
                        Db::commit();
                        $map['id']  = ['elt', $maxId];
                        $map['operator'] = 'import-api';
                        $map['account']  = $account;
                        if ($isCovered) Db::name($table)->where($map)->delete();// 删除之前的数据
                        $this->success('数据导入成功！', url('account/index'));
                    }else{
                        $this->error('数据导入失败！');
                    }
                }
            }


            // 记录所传参数
            $query = request()->param();
            foreach ($query as $key => $value) {
                $arrQuery[] = sprintf('%s=%s', $key, trim($value));
            }
            $this->assign('strQuery', implode('&', $arrQuery));
            return $this->fetch();
        }        
    }
}
