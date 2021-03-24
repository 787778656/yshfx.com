<?php
/**
* LRANK提供的支付接口
* @author tq
* 2018-08-01
*/
return array(
    'payUrl' => 'http://p.haixiab.pw',
    'md5key' => 'xafT7FjNAUH6F9971jfzPPYECh7AKNUS',
    'partner'=> '484668',
    'bankcode'=> 'WXZF',
    'notifyUrl' => 'http://api.lrankings.com/pay.lrankpay/notify_url',    //服务端返回地址
    'callbackUrl' => 'http://api.lrankings.com/pay.lrankpay/callback_url',      //页面跳转返回地址
    'server' => array(
            // vip
            'vip1' => array(30 => 199, 90 => 560, 365 => 1980),
            'vip2' => array(30 => 245, 90 => 660, 365 => 2200),
            'vip3' => array(30 => 500, 90 => 1360, 365 => 5000),
            // 转载信号
            'studio_reproduce_account' => array(30 => 500, 90 => 1000, 365 => 1800),
            // 显示联系方式
            'studio_show_contact' => array(30 => 588, 90 => 1280, 365 => 3880),
            // 充值
            'recharge' => array(0=>500, 1 => 1000, 2 => 2000, 3 => 5000, 4 => 10000, 5 => 20000, 6 => 0.01),
        ),
    'trade' => array(
            'subject' => 'LRANK服务',
            'body' => '你正在开通LRANK服务',
        ),
);