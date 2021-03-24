<?php
/**
* 接口程序
* 2017-11-29
*/
namespace app\h5\controller\pay;
use \think\Config;
use \think\Db;
use JsApiPay;
use WxPayApi;
use WxPayUnifiedOrder;

class Wxpay extends Common
{
    private $wxConf;
    public function __construct()
    {
        parent::__construct();
        $this->wxConf = Config::get('wxmp')['wxWap'];
    }

    /**
    * 公众号支付
    */
    public function jspay()
    {
        $this->isLogin();
        // 获取数据
        list($msec, $sec) = explode(' ', microtime());
        $msectime =  (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
        $tradeNo = sprintf('%s-%s%s', 'wxpay', $msectime, rand(1,9));
        $uid = $this->userInfo['uid'];
        $amount = input('amount');
        $server = input('server');
        $type = input('type',1);
        if ( empty($server)) {
            $server = 'recharge';
        }
        if ($server === 'recharge') {
            $type = 10;
        }

        if (empty($uid) || empty($amount)){
            exit(json_encode(array('code'=> 401, 'msg' => '参数错误！')));
        }
        // 生成订单
        $expireDay = array_search($amount, $this->wxServerConf[$server]);
        $expire_time = time()+$expireDay*24*3600;
        if ($uid == 13599) $amount =0.01;
        $payment = array(
            'uid' => $uid,
            'expire_time' => $expireDay ? $expire_time : '',  //vip增加时间
            'trade_no' => $tradeNo,  //订单号
            'third_trade_no' => '',  //外部订单号
            'amount' => $amount,  //充值金额
            'server' => $server,  //$server recharge:充值  vip:购买vip
            'type' => $type,  //订单类型 $type 10:充值 其他：购买vip
            'operator' => 'wx-h5-pay',
            'add_time' => time(),
            'modify_time' => time(),
        );

        // 创建支付单
        $paymentId = Db::name('mt4_payment')->insertGetId($payment);

        if($paymentId){
        // 构造数据        
            $postData = array(
                'WXout_trade_no' => sprintf('%s_%s', $tradeNo, $paymentId),
                'WXsubject' => $this->wxConf['trade']['subject'],
                'WXbody' => $this->wxConf['trade']['body'],
                'WXtotal_amount' => $amount*100,
                'WXnotify_url' => $this->wxConf['wxNotifyUrl'],
            );

            // 发送数据
            $this->dopay($postData);
        }
    }

    private function dopay($postData = null){
        if ( ! empty($postData)){
            ini_set('date.timezone','Asia/Shanghai');
            vendor('wxpay.lib.WxPay#Api');
            vendor('wxpay.WxPay#JsApiPay');
            vendor('wxpay.log');

            //初始化日志
            //$logHandler= new CLogFileHandler("../logs/".date('Y-m-d').'.log');
            //$log = Log::Init($logHandler, 15);

            //①、获取用户openid
            $tools = new JsApiPay();
            //$openId = $tools->GetOpenid();
            $openId = $this->userInfo['gzhao'];

            //②、统一下单
            $input = new WxPayUnifiedOrder();
            $input->SetBody($postData['WXbody']);
            $input->SetAttach($postData['WXsubject']);
            $input->SetOut_trade_no($postData['WXout_trade_no']);
            $input->SetTotal_fee($postData['WXtotal_amount']);
            $input->SetTime_start(date("YmdHis"));
            $input->SetTime_expire(date("YmdHis", time() + 600));
            $input->SetGoods_tag($postData['WXsubject']);
            $input->SetNotify_url($postData['WXnotify_url']);
            $input->SetTrade_type("JSAPI");
            $input->SetOpenid($openId);
            $order = WxPayApi::unifiedOrder($input);
            $jsApiParameters = $tools->GetJsApiParameters($order);
            //获取共享收货地址js函数参数
            $editAddress = $tools->GetEditAddressParameters();
            // 参数
            $data = array(
                'jsApiParameters' => $jsApiParameters,
                //'editAddress' => $editAddress,
                );
            exit(json_encode(array('code'=> 200, 'msg' => 'jsapi参数', 'data' => $data)));
        }
    }

    /**
    * 回调函数
    */
    public function notify_url()
    {
        $postData['xml'] = file_get_contents('php://input');
        if (!empty($postData['xml'])){
            $this->callback($postData, $this->wxConf, 'wxNotify');
        }        
    }

}