<?php
/**
* 后台导航配置项
* @author efon.cheng<efon@icheng.xyz>
* 2017-08-31
*/
return array(
    
    // 'public' => array(
    //     'name' => '首页',
    //     'sub' => array(
    //         array(
    //             'name' => '服务器信息',
    //             'url' => url('Index/main'),
    //         ),
    //         array(
    //             'name' => 'PHPINFO信息',
    //             'url' => url('Index/phpinfo'),
    //         )
    //     ),
    //     'home' => url('Index/main'),
    //     'icon' => 'icon-desktop'
    // ),
        
    'admin' => array(
        'name' => '系统管理',
        'sub' => array(
            array(
                'name' => '后台用户管理',
                'url' => url('admin/user/index'),
                'rights' => 'admin-1'
            ),
        ),
        'home' => url('admin/match/index'),
        'icon' => 'icon-home'
    ),

    'account' => array(
        'name' => '信号管理',
        'sub' => array(
            array(
                'name' => '主信号管理',
                'url' => url('zht/account/index'),
                'rights' => 'account-1'
            ),
            // array(
            //     'name' => '主信号预警',
            //     'url' => url('zht/account/warn'),
            //     'rights' => 'account-2'
            // ),
            array(
                'name' => 'MT4&IP查找',
                'url' => url('zht/account/search_mt4'),
                'rights' => 'account-3'
            ),
        ),
        'home' => url('zht/account/index'),
        'icon' => 'icon-group'
    ),

    'user' => array(
        'name' => '注册用户',
        'sub' => array(
            array(
                'name' => '用户管理',
                'url' => url('zht/user/index'),
                'rights' => 'user-1'
            ),
            // array(
            //     'name' => '查看用户',
            //     'url' => url('zht/user/look_user'),
            //     'rights' => 'user-7'
            // ),
            array(
                'name' => '组合跟单',
                'url' => url('zht/order/index'),
                'rights' => 'user-2'
            ),
            array(
                'name' => '用户留言',
                'url' => url('zht/contact/index'),
                'rights' => 'user-3'
            ),
            // array(
            //     'name' => 'Demo账号',
            //     'url' => url('zht/demo/index'),
            //     'rights' => 'user-4'
            // ),
            array(
                'name' => 'VIP管理',
                'url' => url('zht/user/vip_list'),
                'rights' => 'user-5'
            ),
            array(
                'name' => '站内信',
                'url' => url('zht/msg/index'),
                'rights' => 'user-6'
            ),
            // array(
            //     'name' => '信用金',
            //     'url' => url('zht/credit/index'),
            //     'rights' => 'credit-1'
            // ),
            // array(
            //     'name' => '校正余额',
            //     'url' => url('zht/imoney/index'),
            //     'rights' => 'imoney-1'
            // ),
        ),
        'home' => url('zht/user/index'),
        'icon' => 'icon-user'
    ),

    'reward' => array(
        'name' => '佣金体系',
        'sub' => array(
            array(
                'name' => '充值记录',
                'url' => url('zht/reward/recharge_log'),
                'rights' => 'reward-5'
            ),
            array(
                'name' => '账户流水',
                'url' => url('zht/reward/water'),
                'rights' => 'reward-6'
            ),
            array(
                'name' => '充值返佣',
                'url' => url('zht/reward/recharge'),
                'rights' => 'reward-1'
            ),
            array(
                'name' => '返佣设置',
                'url' => url('zht/reward/index'),
                'rights' => 'reward-7'
            ),
            array(
                'name' => '信号收益',
                'url' => url('zht/reward/follow'),
                'rights' => 'reward-2'
            ),
            array(
                'name' => '提现申请',
                'url' => url('zht/reward/withdraw'),
                'rights' => 'reward-3'
            ),
            // array(
            //     'name' => '提现转账',
            //     'url' => url('zht/reward/do_withdraw'),
            //     'rights' => 'reward-4'
            // ),
        ),
        'home' => url('zht/reward/recharge'),
        'icon' => 'icon-coffee'
    ),

    // 'studio' => array(
    //     'name' => '工作室',
    //     'sub' => array(
    //         array(
    //             'name' => '工作室列表',
    //             'url' => url('zht/studio/index'),
    //             'rights' => 'studio-1'
    //         ),
    //     ),
    //     'home' => url('zht/studio/index'),
    //     'icon' => 'icon-building'
    // ),

    // 'mam' => array(
    //     'name' => 'mam交易',
    //     'sub' => array(
    //         array(
    //             'name' => '交易列表',
    //             'url' => url('zht/mam/index'),
    //             'rights' => 'mam-1'
    //         ),
    //         array(
    //             'name' => '机构认证',
    //             'url' => url('zht/mam/cert'),
    //             'rights' => 'mam-2'
    //         ),
    //         array(
    //             'name' => '优质策略',
    //             'url' => url('zht/mam/strategy'),
    //             'rights' => 'mam-3'
    //         ),
    //     ),
    //     'home' => url('zht/mam/index'),
    //     'icon' => 'icon-signal'
    // ),
    // 'activity' => array(
    //     'name' => '活动管理',
    //     'sub' => array(
    //         array(
    //             'name' => '活动列表',
    //             'url' => url('zht/activity/index'),
    //             'rights' => 'activity-1'
    //         ),
    //     ),
    //     'home' => url('zht/activity/index'),
    //     'icon' => 'icon-calendar'
    // ),
    'zhtpc' => array(
        'name' => '其它功能',
        'sub' => array(
            array(
                'name' => '评论管理',
                'url' => url('web/comment/index'),
                'rights' => 'zhtpc-1'
            ),
            array(
                'name' => '签约信号',
                'url' => url('web/signal/index'),
                'rights' => 'zhtpc-2'
            ),
            array(
                'name' => '在线预约',
                'url' => url('web/comment/yuyue_index'),
                'rights' => 'zhtpc-3'
            ),
            // array(
            //     'name' => '截图审核',
            //     'url' => url('zht/upgrade/screen'),
            //     'rights' => 'zhtpc-3'
            // ),
            // array(
            //     'name' => '招募大师',
            //     'url' => url('zht/contact/recruit'),
            //     'rights' => 'zhtpc-4'
            // ),
            // array(
            //     'name' => '下级添加管理',
            //     'url' => url('zht/user/sid_list'),
            //     'rights' => 'zhtpc-5'
            // ),
        ),
        'home' => url('web/comment/index'),
        'icon' => 'icon-suitcase'
    ),
    // 'wxadmin' => array(
    //     'name' => '小程序功能',
    //     'sub' => array(
    //         array(
    //             'name' => '营销图片',
    //             'url' => url('zht/marketing/index'),
    //             'rights' => 'wxadmin-1'
    //         ),
    //         array(
    //             'name' => '题库管理',
    //             'url' => url('zht/answer/index'),
    //             'rights' => 'wxadmin-2'
    //         ),
    //     ),
    //     'home' => url('zht/marketing/index'),
    //     'icon' => 'icon-code'
    // ),
);