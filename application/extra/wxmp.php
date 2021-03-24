<?php
/**
 * 微信公众号
 * @author efon.cheng<efon@icheng.xyz>
 * 2018-04-08
 */
return array(
    'appID' => 'wx7fff15608467a849',
    'appSecret' => '742cdf407674351e4d38ceb2efaa0b72',
    'api' => array(
        'access_token' => 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=%s&secret=%s',
        'tpl_msg' => 'https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=%s',
        'gzhao' => 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=%s&redirect_uri=%s&response_type=code&scope=snsapi_userinfo&state=yshfx#wechat_redirect',
        'token' => 'https://api.weixin.qq.com/sns/oauth2/access_token?appid=%s&secret=%s&code=%s&grant_type=authorization_code',
        'userinfo' => 'https://api.weixin.qq.com/sns/userinfo?access_token=%s&openid=%s&lang=zh_CN',
    ),

    // 微信wap
    'wxWap' => array(
        'wxNotifyUrl' => 'http://h5.yshfx.com/pay.wxpay/notify_url.html',
        'notifyUrl' => 'http://h5.yshfx.com/pay.wxpay/notify_url.html',
        'trade' => array(
            'subject' => '亿思汇',
            'body' => '你正使用亿思汇充值服务',
        ),
    ),

    'server' => array(
        // vip
        'vip1' => array(30 => 599, 90 => 1529, 365 => 4319),
        'vip2' => array(30 => 999, 90 => 2549, 365 => 7199),
        'vip3' => array(30 => 1599, 90 => 4029, 365 => 11519),
        // 充值
        'recharge' => array(0=>500, 1 => 1000, 2 => 2000, 3 => 5000, 4 => 10000, 5 => 20000),
    ),
);