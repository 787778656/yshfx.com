<?php
/**
* 主信号相关功能控制器
*/
namespace app\web\controller;
use think\Config;
use \think\Db;
use think\Session;

class Master extends Common
{
    /**
     * 主信号首页
     * @return mixed
     */
    public function index()
    {
        $data = Db::name('Mt4Account')->paginate(10);
        $user = Session::get('zhtweb');
        $this->assign('data', $data);
        return $this->fetch();
    }

    /**
     * 创建主信号
     */
    public function create()
    {
        $user = Session::get('zhtweb');
        $mt4server = Config::load(APP_PATH.'zht/extra/options.php')['mt4server'];
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
        $mt4server = Config::load(APP_PATH.'zht/extra/options.php')['mt4server'];
        $key = in_array($data['mt4server'], $mt4server);
        if (!$key) {
            return json_encode(['code' => 302, 'msg' => '请选择正确的服务器']);
        }
        $data['add_time'] = $time;
        $data['modify_time'] = $time;
        $uid = $user['uid'];

        if ($uid){
            unset($data['business']);
            $data['uid'] = $uid;
            $info = Db::name('Mt4Signal')->where('uid', $uid)->find();
            if(empty($info)){
                $res = Db::name('Mt4Signal')->insert($data);
                if ($res) {
                    return json_encode(['code' => 200, 'msg' => '提交成功']);

                }else {
                    return json_encode(['code' => 302, 'msg' => '提交失败']);

                }
            }else{
                Db::name('Mt4Signal')->where('uid', $uid)->update($data);
                return json_encode(['code' => 200, 'msg' => '修改成功']);
            }
        }

    }

    /**
     * 主信号用户详情页
     * @return mixed
     */
    public function detail($account = 0){
        if ( ! empty($account)){
            $accountInfo = Db::name('mt4_account')->where('mt4id', $account)->find();
            $this->assign('accountInfo', $accountInfo);

            $arrTrade = Db::name('mt4_account_statistics')->where('account', $account)->order('id desc')->find();
            $this->assign('arrTrade', $arrTrade);
            $this->assign('account', $account);
            return $this->fetch();
        }
    }

    /**
     * 主信号交易数据
     * @return mixed
     */
    public function iorders($account = 0, $tab = 'histroy'){
        if ( ! empty($account)){
            if ($tab == 'history'){
                // 获取表名(历史表有分表处理)
                $suffix = intval($account%10);
                $table = sprintf('mt4_history_order_%d', $suffix);
                // 历史交易
                $arrHistory = Db::name($table)->where('account', $account)->order('id desc')->paginate(20);
                $this->assign('arrHistory', $arrHistory);                
            }else{
                // 持仓
                $arrHolding = Db::name('mt4_master_order')->where('account', $account)->order('id desc')->paginate(20);
                $this->assign('arrHolding', $arrHolding);
            }
            $this->assign('tab', $tab);
            return $this->fetch();
        }
    }

    /**
     * vip会员系统页面
     * @return mixed
     */
    public function vip()
    {
        $uid = Session::get('zhtweb')['uid'];
        $this->assign('uid', $uid);
        return $this->fetch();
    }

    /**
     * 确认跟单
     * @return string
     */
    public function confirm()
    {
        $this->verifyLogin();
        $data = json_decode(input('data'),true);
        $time = time();
        foreach ($data as $k => $d) {
            $data[$k]['add_time'] = $time;
            $data[$k]['modify_time'] = $time;
        }
        Db::name('Mt4DiyAccount')->insertAll($data);
        return json_encode(['code' => 200, 'msg' => '数据添加成功']);

    }

    /**
     * 获取用户跟单状态
     * @return string
     */
    public function get_status()
    {
        $user = Session::get('zhtweb');
        if ($user) {
            $info = Db::name('User')->where('uid', $user['uid'])->find();
            $status = (time()-$info['lastwtime'])>180 ? 0 : 1;
            return json_encode(['code' => 200, 'status' => $status, 'msg' => '查询成功']);
        }
        return json_encode(['code' => 302, 'status' => 0, 'msg' => '用户暂未登录']);

    }

}
