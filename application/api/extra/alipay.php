<?php
/**
* 支付宝
* @author efon.cheng<efon@icheng.xyz>
* 2017-10-10
*/
return array(
    'payUrl' => 'http://pay.yshfx.com/alipay/pagepay/pagepay.php',
    'mpayUrl' => 'http://pay.yshfx.com/alipay/app/index.php',
    'returnUrl' => 'http://pay.yshfx.com/alipay/return_url.php',
    'notifyUrl' => 'http://pay.yshfx.com/alipay/notify_url.php',
    'redirectUrl' => 'user/vip@www.yshfx.com',
    'server' => array(
            // vip
            'vip1' => array(30 => 599, 90 => 1529, 365 => 4319),
            'vip2' => array(30 => 999, 90 => 2549, 365 => 7199),
            'vip3' => array(30 => 1599, 90 => 4029, 365 => 11519),
            // 转载信号
            'studio_reproduce_account' => array(30 => 500, 90 => 1000, 365 => 1800),
            // 显示联系方式
            'studio_show_contact' => array(30 => 588, 90 => 1280, 365 => 3880),
            // 充值
            'recharge' => array(0=>500, 1 => 1000, 2 => 2000, 3 => 5000, 4 => 10000, 5 => 20000, 6 => 0.01),
        ),
    'trade' => array(
            'subject' => '亿思汇服务',
            'body' => '你正在开通亿思汇服务',
        ),
);