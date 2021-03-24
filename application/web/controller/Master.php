<?php
/**
* 主信号相关功能控制器
*/
namespace app\web\controller;
use think\Config;
use \think\Db;
use think\Session;
use app\common\controller\Common as AppCommon;
use \think\cache\driver\Redis;

class Master extends Common
{
    public function __construct()
    {
        parent::__construct();
    }
    /**
     * 主信号首页
     * @return mixed
     */
    public function index()
    {
        // 默认mt4
        $account = Db::name('user_mt4')->where('uid', $this->userInfo['uid'])->order('id desc')->value('mt4id');
        $this->assign('account', $account);

        // 指定的代理商
        $designated_broker = Config::get('designated_broker');
        $this->assign('designated_broker',$designated_broker);

        return $this->fetch();
    }

    /**
    * 信号列表iframe
    */
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

    /**
     * 创建主信号
     */
    public function create()
    {
        $mt4server = Config::get('options.mt4server');
        unset($mt4server[0]);
        $this->assign('mt4server', $mt4server);
        return $this->fetch();
    }

    /**
     * 自主创建信号表单提交
     * @return string
     */
    public function do_create(){
        $user = Session::get('zhtweb');
        if (empty($user)) {
            return json_encode(['code' => 302, 'msg' => '请先登录']);
        }
        $time = time();
        $data = json_decode(input('data'),true);
        if (isset($data['sign_name'])) {
            $data['sign_name'] = trim(htmlspecialchars($data['sign_name']));
        }
        if (isset($data['mt4id'])) {
            $data['mt4id'] = trim(htmlspecialchars($data['mt4id']));
        }
        if (isset($data['mt4pwd'])) {
            $data['mt4pwd'] = trim(htmlspecialchars($data['mt4pwd']));
        }
        $data['add_time'] = $time;
        $data['modify_time'] = $time;
        $data['detail'] = $data['detail']?htmlspecialchars($data['detail']):'';
        $uid = $user['uid'];

        //去除经纪商
        unset($data['business']);
        $data['uid'] = $uid;
        $info = Db::name('Mt4Signal')->where(['uid' => $uid, 'mt4id' => $data['mt4id']])->find();
        $account = Db::name('Mt4Account')->where('mt4id', $data['mt4id'])->count();
        $account1 = Db::name('User')->where('zhmt4uid', $data['mt4id'])->count();
        $slave = Db::name('UserMt4')->where('mt4id', $data['mt4id'])->count();
        if (!empty($account) || !empty($account1) || !empty($slave)) {
            return json_encode(['code' => 302, 'msg' => '该mt4id账号已签约信号']);
        }
        if(empty($info)){
            $res = Db::name('Mt4Signal')->insert($data);
            if ($res) {
                return json_encode(['code' => 200, 'msg' => '提交成功']);

            }
            return json_encode(['code' => 302, 'msg' => '提交失败']);

        }
        $data['status']=0; //将签约信号置为审核状态
        Db::name('Mt4Signal')->where(['uid' => $uid, 'mt4id' => $data['mt4id']])->update($data);  //更新签约信号中用户信息
        return json_encode(['code' => 200, 'msg' => '修改成功']);

    }

    /**
     * 主信号用户详情页
     * @return mixed
     */
    public function detail($account = 0){
        if ( ! empty($account)){
            $accountInfo = Db::name('mt4_account')->where('mt4id', $account)->find();
            if ( empty($accountInfo)) $this->error('没有相关数据！', url('master/index'));
            $this->assign('accountInfo', $accountInfo);

            // 账号缓存信息
            $key = sprintf('%s-%s-%s', 'zhtEA', 'mt4_account_detail', $account);
            $arrCache = array();
            $redis = new Redis();
            if ($redis->get($key)){
                $arrCache = unserialize($redis->get($key));
            }
            $this->assign('accountDtail', $arrCache);

            // 实时账号信息
            //$minProfit = Db::name('mt4_account_detail')->where('account', $account)->value('min(profit/balance) as profit');
            //$this->assign('minProfit', $minProfit);

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
            
            // 国家配置
            $mt4country = Config::get('options.mt4country');
            $this->assign('mt4country', array_flip($mt4country));

            // 货币的标识
            $countrymoneysymbol = Config::get('options.countrymoneysymbol');
            $moneysymbol = 'USD';
            if($accountInfo['country'] && $accountInfo['country']!='美国'){
                if($mt4country_key = array_search($accountInfo['country'],$mt4country)){
                    if(array_key_exists($mt4country_key,$countrymoneysymbol)){
                        $moneysymbol = $countrymoneysymbol[$mt4country_key];
                    }
                }
            }
            $this->assign('moneysymbol', $moneysymbol);

            // 服务商
            $mt4service = Config::get('options.mt4service');
            $this->assign('mt4service', array_flip($mt4service));

            // 获取真实跟随者
            $tFollow = Db::name('mt4_diy_account')->where('mt4id', $account)->count();
            $this->assign('follow', $tFollow);

            //评论数量统计
            $count = Db::name('Mt4Comment')->where(['mt4id' => $account,'status' => 1])->count();
            $this->assign('count', $count);
            $this->assign('account', $account);

            return $this->fetch();
        }
    }


    /**
     * 跟随交易者
     * @return mixed
     */
    public function follow($account = 0){
        if ( ! empty($account)){
            $pageSize = 10;
            $vfollow = Db::name('mt4_account')->where('mt4id', $account)->value('follow');
            $follower = Db::name('mt4_diy_account')->where('mt4id', $account)->column('slave_mt4id');
            $tFollow = count($follower);
            $follow = $vfollow + $tFollow;

            $arrFollow = Db::name('mt4_diy_account')->alias('a')->join('user b', 'a.uid = b.uid')->field('a.slave_mt4id,a.weight,a.maxhold,b.u_img,b.login,b.zhmt4server,b.zhmt4uid,b.nickname')->where('mt4id', $account)->order('a.id desc')->paginate($pageSize, intval($follow));

            // 获取相关的mt4信息
            if (empty($follower)) $follower = array(0);
            $arrMt4 = Db::name('user_mt4')->where('mt4id', 'in', $follower)->column('mt4id,mt4server');
            $this->assign('arrMt4', $arrMt4);

            $this->assign('follow', $follow);
            
            // 服务商
            $mt4service = Config::get('options.follow_service'); 
            $arrBalance = array();
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
                    $sid = rand(1, 50);
                    $arrFollow[] = array(
                            'weight' => $arrWeight[$weight],
                            'maxhold' => rand(1,10),
                            'login' => rand(133, 139).rand(10000000, 99999999),
                            'zhmt4server' => $mt4service[$sid],
                            'zhmt4uid' => 0,
                            'balance' => $balance,
                            'vphoto' => rand(1,300)
                        );
                    $i++;
                }

                // 补足余额
                for($vfollow = count($arrBalance); $vfollow <= $follow; $vfollow++){
                    $balance = rand(10000, 500000);
                    $arrBalance[] = $balance/100;
                }
            }

            $this->assign('tBalance', array_sum($arrBalance));
            $this->assign('mt4service', array_flip($mt4service));
            $this->assign('arrFollow', $arrFollow);
            $this->assign('follow', $follow);

            // redis
            $redis = new Redis();
            $this->assign('redis', $redis);
            return $this->fetch();
        }
    }

    /**
     * 主信号交易数据
     * @return mixed
     */
    public function iorders($account = 0, $tab = 'histroy', $table = 'mt4_master_order'){
        if ( ! empty($account)){
            if ($tab == 'history'){
                // 获取表名(历史表有分表处理)
                $table = AppCommon::tableName('mt4_history_order', $account);
                // $arrId = Db::name($table)->where('account', $account)->group('ticket')->column('id'); // 去重
                // if (empty($arrId)) $arrId = array(0);
                // 历史交易
                // $arrHistory = Db::name($table)->where('id', 'in', $arrId)->whereOr(function ($query) use ($account) {$query->where(['account' => $account, 'operator'=>'import-api']);})->order('close_time desc')->paginate(20);
                
                // $arrHistoryIds = Db::name($table)->where(['account' => $account, 'operator'=>'import-api'])->column('id');
                // $arrHistoryIds = array_merge($arrHistoryIds, $arrId);
                // $arrHistory = Db::name($table)->where('id', 'in', $arrHistoryIds)->order('close_time desc')->paginate(20);
                
                $arrHistory = Db::name($table)->where('account', $account)->order('close_time desc')->paginate(20);
                $this->assign('arrHistory', $arrHistory);           
            }else{
                // 持仓
                $arrHolding = Db::name($table)->where('account', $account)->order('open_time desc')->select();
                $tHolding = Db::name($table)->where('account', $account)->column('account, sum(commission) as commission, sum(swap) as swap, sum(profit) as profit');
                $this->assign('tHolding', @$tHolding[$account]);
                $this->assign('arrHolding', $arrHolding);
            }
            $this->assign('tab', $tab);
            return $this->fetch();
        }
    }

    /**
     * 用户评论列表
     * @return mixed
     */
    public function icomment($mamId = 0)
    {
        $mt4id = input('mt4id');
        $mamId = input('mamId');
        $username = $u_img = '';
        //用户评论
        if (!empty($mt4id)) {
            $map['mt4id'] = $mt4id;
            $map['status'] = 1;
            if (!empty($mamId)){
                $arrMam = Db::name('mam_list')->where('id', $mamId)->find();
                if (!empty($arrMam)){
                    $map['add_time'] = ['gt', $arrMam['add_time']];
                }
            }
            $comment = Db::name('Mt4Comment')->where($map)->order('modify_time desc')->paginate(10);
            $comment_arr = $comment->toArray()['data'];
            if (!empty($comment_arr)) {
                foreach ($comment_arr as $key => $com) {
                    $data = Db::name('User')->where('uid',$com['uid'])->find();
                    if (!empty($data)) {
                        $username = $data['nickname'];
                        $u_img = $data['u_img'];
                    }
                    $comment_arr[$key]['username'] = $username;
                    $comment_arr[$key]['u_img'] = $u_img;
                    $username = $u_img = '';
                }
            }
            $this->assign('comment_arr', $comment_arr);
            $this->assign('comment', $comment);
        }
        return $this->fetch();
    }

    /**
     * 综合评测
     * @return mixed
     */
    public function evaluation($account = null)
    {
        if ( ! empty($account)){
            $this->assign('srcAccount', $account);
            $this->assign('account', str_replace(',', '-', $account));

            $arrAccount = array_unique(explode(',', $account));
            $this->assign('aNum', count($arrAccount));
            $accountInfo = Db::name('mt4_account')->where('mt4id', 'in', $account)->field('sum(money) as money, sum(follow) as follow, sum(trade_drawdown) as trade_drawdown, max(trade_week) as trade_week, sum(trade_month) as trade_month')->find();

            if ( empty($accountInfo)) $this->error('没有相关数据！', url('master/index'));
            $this->assign('accountInfo', $accountInfo);

            // 获取真实跟随者
            $tFollow = Db::name('mt4_diy_account')->where('mt4id', 'in', $account)->count();
            $this->assign('follow', $tFollow);

            // 获取总统计
            $arrTrade = Db::name('mt4_account_statistics')->where('account', 'in', $account)->field('sum(trade_total) as trade_total, sum(trade_profit) as trade_profit, max(sprofit_max) as sprofit_max, max(sprofit_max_num) as sprofit_max_num, max(sprofit_more_num) as sprofit_more_num, max(sprofit_max) as sprofit_max, max(sprofit_more) as sprofit_more, min(sloss_max) as sloss_max, max(sloss_max_num) as sloss_max_num, min(sloss_more) as sloss_more, max(sloss_more_num) as sloss_more_num, max(gross_profit_num) as gross_profit_num, max(gross_loss_num) as gross_loss_num, sum(avg_profit) as avg_profit, sum(avg_profit_num) as avg_profit_num, sum(avg_loss) as avg_loss, sum(avg_loss_num) as avg_loss_num, sum(trade_loss) as trade_loss, sum(profit_factor) as profit_factor, max(trade_best) as trade_best, min(trade_worst) as trade_worst, sum(sharpe_ratio) as sharpe_ratio, sum(gross_profit) as gross_profit, sum(gross_loss) as gross_loss, sum(commission) as commission, sum(swap) as swap, max(max_time) as max_time, sum(holding_time) as holding_time')->find();
            $this->assign('arrTrade', $arrTrade);

            // 获取月统计
            $formatMdata=[];
            $arrMdata = Db::name('mt4_account_mstatistics')->where('account', 'in', $account)->order('year,month')->select();

            foreach ($arrMdata as $key => $mdata) {
                $formatMdata[$mdata['year']][$mdata['month']][] = $mdata['profit'];
            }

            $this->assign('mdata', $formatMdata);

            $this->assign('tName', sprintf('Test：%s', date('ymdHis')));
            return $this->fetch();
        }
    }

    public function evaluation_history($account = null)
    {
        $arrAccount = array_unique(explode(',', $account));
        $arrHistory = array();
        $pageTable = '';
        foreach ($arrAccount as $account) {
            // 获取表名(历史表有分表处理)
            $table = AppCommon::tableName('mt4_history_order', $account);
            // 历史交易

            $history = Db::name($table)->where('account', $account)->field('close_time, open_time, dlen, symbol, op, lots, open_price, takeprofit, stoploss, close_price, commission, swap, profit')->select();            
            if (empty($history)) continue;
            $arrHistory = array_merge($arrHistory, $history);
        }

        if ( ! empty($table)) $showHistory = Db::name($table)->where('account', 0)->order('close_time desc')->paginate(20, count($arrHistory));
        $page = input('?get.page') ? input('get.page'): 1;
        $page -= 1;
        if ($page < 0) $page = 1;
        rsort($arrHistory);
        $showData = array_slice($arrHistory, $page*20, 20);
        foreach ($showData as $key => $value) {
            $showHistory[] = $value;
        }

        $this->assign('arrHistory', $showHistory);
        return $this->fetch();
    }

    /**
     * 确认跟单
     * @return string
     */
    public function confirm()
    {
        $this->verifyLogin();
        $uid =Session::get('zhtweb')['uid'];
        $slave_mt4id = input('slave_mt4id'); //用户跟单的mt4id
        if (!$slave_mt4id) {
            $userinfo = Db::name('user')->where('uid', $uid)->find();
            if ($userinfo['isbuy']==0) {
                return json_encode(['code' => 302, 'msg' => 'mt4账号审核中,暂无法跟单!']);
            }
            // $count_mam = Db::name('mam_follow')->where(['slave_mt4id' => $userinfo['zhmt4uid'], 'status' => 1])->count(); //计算该mt4id是否有正在运行的mam交易
            // if ($count_mam) {
            //     return json_encode(['code' => 302, 'msg' => '请先停止mam交易跟单']);
            // }
        } else {
            $count = Db::name('user_mt4')->where(['uid' => $uid, 'mt4id' => $slave_mt4id])->count();
            if (!$count) {
                return json_encode(['code' => 302, 'msg' => 'mt4账号审核中,暂无法跟单!']);
            }
            // $count_mam = Db::name('mam_follow')->where(['slave_mt4id' => $slave_mt4id, 'status' => 1])->count(); //计算该mt4id是否有正在运行的mam交易
            // if ($count_mam) {
            //     return json_encode(['code' => 302, 'msg' => '请先停止mam交易跟单']);
            // }
        }
        $data = json_decode(input('data'),true);

        # 判断跟单的信号总数量
        $data_count = $data ? count($data) : 0;
        if($data_count > 10){
            return json_encode(['code' => 302, 'msg' => '一个mt4id账号最多只能跟10个信号']);
        }

        $time = time();
        $count = 0;
        if( ! empty($data)) {
            foreach ($data as $k => $d) {
                $map = array('mt4id' => $d['mt4id'], 'uid' => $uid);
                $d['operator'] = 'web-api';
                $d['status'] =1;
                // 多mt4账号
                if (!empty($slave_mt4id)){
                    $map['slave_mt4id'] = $slave_mt4id;
                    $d['slave_mt4id'] = $slave_mt4id;
                }

                $info = Db::name('Mt4DiyAccount')->where($map)->find();
                if (!empty($info)) {
                    $result = Db::name('Mt4DiyAccount')->where($map)->update(array_merge($d,['modify_time' => $time]));
                }else {
                    $result = Db::name('Mt4DiyAccount')->insert(array_merge($d,['add_time' => $time,'modify_time' => $time]));
                }
                if ($result) {
                    $count += db('UserMt4')->where(['mt4id' => $slave_mt4id])->update(['status' => 1]);
                }
            }
            /*$max_id = Db::name('Mt4DiyAccount')->max('id');  //获取当前表中最大id
            $where = [
                'uid' => $uid,
                'id' => ['elt', $max_id]
            ];

            $result = Db::name('Mt4DiyAccount')->insertAll($data);
            if ($result > 0) {
                // 删除该用户之前的跟单记录
                //Db::name('Mt4DiyAccount')->where($where)->delete();
                Db::name('User')->where('uid', Session::get('zhtweb')['uid'])->update(['status' => 1]);
            }
            $status = Db::name('User')->where('uid', $uid)->update(['status' => 1]);
            db('Mt4DiyAccount')->where('uid',$uid)->update(['status' => 1]);*/
            if ($count > 0) {
                $this->add_log($data,'批量修改或更新跟单信号'); //添加组合跟单的日志记录
                return json_encode(['code' => 200, 'msg' => '数据添加成功']);
            }
            return json_encode(['code' => 302, 'msg' => '暂无任何修改']);
        }
        return json_encode(['code' => 302, 'msg' => '数据添加失败']);
    }

    /**
     * 获取用户跟单状态
     * @return string
     */
    public function get_status()
    {
        $user = Session::get('zhtweb');
        $slave_mt4id = input('slave_mt4id'); //从mt4id
        $uid = 0;
        if (!empty($user)) {
            $uid = $user['uid'];
        }
        $map = [
            'uid' => $uid,
            'mt4id' => $slave_mt4id
        ];
        if ($uid) {
            $info = Db::name('UserMt4')->where($map)->find();
            if (!empty($info)) {
                return json_encode(['code' => 200, 'status' => $info['status'], 'msg' => '查询成功']);
            }
            return json_encode(['code' => 302, 'status' => 2, 'msg' => '暂无该数据']);
        }
        return json_encode(['code' => 302, 'status' => 2, 'msg' => '用户暂未登录']);

    }

    /**
     * 组合信号中用户停止跟单
     * @return string
     */
    public function update_order_status()
    {
        $this->verifyLogin();
        $uid = Session::get('zhtweb')['uid'];
        $slave_mt4id = input('slave_mt4id'); //用户从mt4id
        $where = [
            'uid' => $uid,
            'mt4id' => $slave_mt4id
        ];
        $where_diy = [
            'uid' => $uid,
            'slave_mt4id' => $slave_mt4id
        ];
        Db::name('Mt4DiyAccount')->where($where_diy)->update(['status' => 0]);
        $count = Db::name('UserMt4')->where($where)->update(['status' => 2]);
        if ($count > 0) {
            $this->add_log("['status' => 2]", '用户停止跟单');  //添加跟单信号日志
            return json_encode(['code' => 200, 'msg' => '停止跟单成功']);
        }
        return json_encode(['code' => 302, 'msg' => '暂无任何修改']);
    }

    /**
     * 删除单个跟单信号
     * @return string
     */
    public function del_order()
    {
        $this->verifyLogin();
        $uid = Session::get('zhtweb')['uid'];
        $mt4id = input('mt4id');
        $delMap = array('uid' => $uid, 'mt4id' => $mt4id);
        // 多mt4账号
        if (input('?post.slave_mt4id')){
            $isDefault = Db::name('UserMt4')->where('mt4id', input('post.slave_mt4id'))->value('is_default');
            if($isDefault!=1) {
                $delMap['slave_mt4id'] = input('post.slave_mt4id');
            } else {
                $delMap['slave_mt4id'] = array(['EXP', 'IS NULL'], ['eq',  input('post.slave_mt4id')], 'or');
            }
        } else {
            $delMap['slave_mt4id'] = ['EXP', 'IS NULL'];
        }

        $count = Db::name('Mt4DiyAccount')->where($delMap)->delete();
        if ($count) {
            unset($delMap['mt4id']);
            $diyaccount = Db::name('Mt4DiyAccount')->where($delMap)->count();
            if (empty($diyaccount)) {
                $update_count = Db::name('user_mt4')->where(['mt4id' => input('post.slave_mt4id')])->update(['status' => 2]);
                if ($update_count) {
                    $this->add_log("['status' => 2]", '用户平仓');
                }
            }
        }
        return json_encode(['code' => 200, 'msg' => '删除成功']);
    }

    /**
     * 修改单个跟单信号数据
     * @return string
     */
    public function update_order()
    {
        $this->verifyLogin();
        $data = json_decode(input('data'),true);
        $time = time();

        $map = array('uid' => $data['uid'], 'mt4id' => $data['mt4id']);
        // 多mt4账号
        /*if (isset($data['slave_mt4id'])){
            $isDefault = Db::name('UserMt4')->where('mt4id', $data['slave_mt4id'])->value('is_default');
            if($isDefault != 1) {
                $map['slave_mt4id'] = $data['slave_mt4id'];
            }
        }*/
        if (isset($data['slave_mt4id'])){
            $map['slave_mt4id'] = $data['slave_mt4id'];
        }

        if (input('?post.slave_mt4id')){
            $isDefault = Db::name('UserMt4')->where('mt4id', input('post.slave_mt4id'))->value('is_default');
            if($isDefault!=1) {
                $delMap['slave_mt4id'] = input('post.slave_mt4id');
            }
        }


        $data['operator'] = 'web-api';
        $info = Db::name('Mt4DiyAccount')->where($map)->find();
        if (!empty($info)) {
            $data['modify_time'] = $time;
            Db::name('Mt4DiyAccount')->where($map)->update($data);
        } else {
            $data['add_time'] = $time;
            $data['modify_time'] = $time;
            Db::name('Mt4DiyAccount')->insert($data);
        }
        return json_encode(['code' => 200, 'msg' => '单个跟单信号修改成功!']);
    }


    /**
     * 用户评论
     */
    public function comment()
    {
        $model = Model('Mt4Comment');
        $uid = Session::get('zhtweb')['uid'];  //用户Id
        $comment = htmlspecialchars(input('post.comment')); //评论内容
        $mt4id = input('post.mt4id'); //mt4id
        $mobile = input('post.mobile'); //用户名称（手机号）
        if (empty($uid)) {
            return json_encode(['code' => 302, 'msg' => '请先登录！']);
        }
        if (empty($comment)) {
            return json_encode(['code' => 302, 'msg' => '评论内容不能为空！']);
        }
        $data = [
            'uid' => $uid,
            'comment' => $comment,
            'status' => 0,
            'mt4id' => $mt4id,
            'login' => $mobile
        ];
        $user = Db::name('User')->where('uid',$uid)->find();
        if (!empty($user) && !empty($user['nickname'])) {
            $data['username'] = $user['nickname'];
        }
        if ($model->save($data)){
            return json_encode(['code' => 200, 'msg' => '评论成功，<br>请等待管理员审核评论！']);
        }else{
            return json_encode(['code' => 302, 'msg' => '评论失败！']);

        }
    }


    /**
     * 给合达人
     */
    public function combination()
    {
        return $this->fetch();
    }

    /**
     * 上传图片证明
     */
    public function uploadImg()
    {
        AppCommon::uploadImg();
    }

    /**
     * 获取mt4-demo账户
     */
    public function get_mt4_demo()
    {
        $where = [
            'status' => 0,
            'add_time' => ['>', strtotime('-1 month')]
        ];
        $demo = Db::name('Mt4Demo')->field('mt4id, mt4server,mt4pwd')->where($where)->order('add_time asc')->find();
        if (!empty($demo)) {
            return json_encode(['code' => 200, 'msg' => '获取成功', 'demo' => $demo]);
        }
        return json_encode(['code' => 302, 'msg' => '暂无demo账号']);
    }

    /**
     * 添加组合跟单日志记录
     * @param $data
     * @return bool
     */
    public function add_log($data,$remark)
    {
        $time = time();
        $conf = [
            'uid' => Session::get('zhtweb')['uid'],
            'content' => json_encode($data),
            'remark' => $remark,
            'add_time' => $time,
            'modify_time' => $time
        ];
        Db::name('Mt4DiyAccountLog')->insert($conf);
        return true;
    }

}
