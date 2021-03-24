## zht客户端接口文档

**客户端请求的api域名为api.znforex.com**

**每次请求需带有token，初始登录成功，会在result中返回token**
```
sign=ab6fa6fee078d8858e333d950b190877
传过来的sign=sign+时间戳;
```

> [Toc]


#### 一、用户注册 
#### 1、请求接口： /v1.mobile/register

| 字段        |   名称       |  类型   |
| :-----------:| :-------------: | :--:|
| login      | 用户注册的手机号（必填） |  string|
| password      |   密码（必填）    |   string |
| code      |   邀请码(选填)    |   string |
| verify      |   短信验证码（必填）    |   string |

***
 **返回结果**

```
{
    "code": 200,
    "msg": "注册成功！"
}
```
***

#### 二、用户登录 
#### 1、请求接口： /v1.mobile/signin

| 字段        |   名称       |  类型   |
| :-----------:| :-------------: | :--:|
| login      | 用户注册的手机号 |  string|
| password      |   密码    |   string |

***
**返回结果**

```
{
    "code": 200,
    "msg": "登录成功！",
    "result": {
        "zhmt4uid": "111",
        "zhmt4server": "levelmax-Primary(Live)",
        "isbuy": 1,
        "token": "f159704589f45c5be31bdae1e928c85b"
    }
}
```
***

#### 三、获取短信验证码 
#### 1、请求接口： /v1.mobile/get_smscode

| 字段        |   名称       |  类型   |
| :-----------:| :-------------: | :--:|
| mobile      | 手机号(必填) |  string|

***
**返回结果**

```
{
    "code": 200,
    "msg": "发送成功"
}
```
***

#### 四、修改密码 
#### 1、请求接口： /v1.mobile/update_pwd

| 字段        |   名称       |  类型   |
| :-----------:| :-------------: | :--:|
| old_pwd      | 旧密码 |  string|
| new_pwd      | 新密码 |  string|

***
**返回结果**

```
{
    "code": 200,
    "msg": "密码修改成功"
}
```
***

#### 五、忘记密码（重置密码） 
#### 1、请求接口： /v1.mobile/update_init

| 字段        |   名称       |  类型   |
| :-----------:| :-------------: | :--:|
| mobile      | 手机号 |  string|
| password      | 密码 |  string|
| verify      | 短信验证码 |  string|

***
**返回结果**

```
{
    "code": 200,
    "msg": "密码修改成功"
}
```
***

#### 六、注册用户绑定mt4账号 
#### 1、请求接口： /v1.mobile/binding

| 字段        |   名称       |  类型   |
| :-----------:| :-------------: | :--:|
| zhmt4uid      | mt4账号 |  string|
| mt4server      | mt4服务器 |  string|
| zhmt4pwd      | mt4密码 |  string|

***
**返回结果**

```
{
    "code": 200,
    "msg": "mt4账号绑定成功"
}
```
***

#### 七、用户改绑mt4账号 
#### 1、请求接口： /v1.mobile/unbinding

| 字段        |   名称       |  类型   |
| :-----------:| :-------------: | :--:|
| mt4uid      | mt4账号 |  string|
| mt4server      | mt4服务器 |  string|
| mt4pwd      | mt4密码 |  string|

***
**返回结果**

```
{
    "code": 200,
    "msg": "mt4账号改绑申请成功，等待审核!"
}
```
***

#### 八、获取用户信息详情
#### 1、请求接口： /v1.mobile/get_userinfo


***
**返回结果**

```
{
    "code": 200,
    "msg": "查询成功",
    "result": {
        "imoney": -47.85,
        "login": "15527125468",
        "uid": "8873",
        "zhmt4uid": "111",
        "zhmt4server": "levelmax-Primary(Live)",
        "server": "",
        "server_expire": 0,
        "isbuy": 1,
        "token": "f159704589f45c5be31bdae1e928c85b"
    }
}
```
***

#### 九、获取用户跟单数据
#### 1、请求接口： /v1.order/get_master_signal
| 字段        |   名称       |  类型   |
| :-----------:| :-------------: | :--:|
| device      | 设备号 |  string|

***
**返回结果**

```
{
    "code": 200,
    "msg": "查询成功",
    "result": {
        "data": [
            {
                "id": 10377,
                "uid": 8873,  //用户uid
                "bn": "000822",  //主信号编号
                "name": "Gravity",  //主信号名称
                "masterid": 0,
                "mt4id": "11966291",
                "mt4pwd": "",
                "weight": "10%",  //权重(手数)
                "maxloss": 100,    //最大浮亏
                "maxhold": 5,    //最大持仓
                "maxtrade": null,
                "operator": "",
                "status": 0,
                "authtype": "",
                "mt4server": "福汇",
                "img": "",
                "modify_time": 1510649066,
                "add_time": 1510649066,
                "u_img": "http://static.v.znforex.com/upload/image/20171020/022eed8cdf0b6589a93ea28507db0955.jpg",  //用户图像
                "country": "日本",
                "country_img": "http://static.v.znforex.com/app/countryflag/9.png",  //国旗
                "service_img": "http://static.v.znforex.com/app/broker/12.png"  //经纪商图片
            }
        ]
    }
}
```
***

#### 十、立即跟单
#### 1、请求接口： /v1.order/confirm

| 字段        |   名称       |  类型   |备注|
| :-----------:| :-------------: | :--:|:--:|
| device      | 设备号 |  string |
| data      | 跟单的集合（包含下列所有字段） |  json |
| bn      | 主信号编号 |  string |
| name      | 主信号名称 |  string |
| weight      | 权重(手数) |  int | 跟单方向 （weight正数：正， 负数：反）|
| maxloss      | 最大浮亏 |  int |
| maxhold      | 最大持仓 |  int |
| mt4id      | 被跟单者mt4id |  string |



***
**返回结果**

```
{
    "code": 200,
    "msg": "数据添加成功"
}
```
***

#### 十一、停止跟单
#### 1、请求接口： /v1.order/update_order_status


***
**返回结果**

```
{
    "code": 200,
    "msg": "停止跟单成功"
}
```
***

#### 十二、用户跟单状态
#### 1、请求接口： /v1.order/get_status


***
**返回结果**

```
{
    "code": 200,
    "result": {
        "status": 1   //1:在线  2：离线
    },
    "msg": "查询成功"
}
```
***

#### 十三、主信号列表
#### 1、请求接口： /v1.signal/index

| 字段        |   名称       |  类型   |备注|
| :-----------:| :-------------: | :--:|:--:
| page      | 当前页 |  int | 每次请求接口会返回当前页，字段为：current_page
| type      | 排序类型 |  int | 1: 综合  2：利润率  3：跟单人数 4：回撤（风向） 5：预期收益率 6：交易时长




***
**返回结果**

```
{
    "code": 200,
    "msg": "查询成功",
    "result": {
        "data": [
            {
                "id": 52,
                "name": "The Incredible Hulk",   //主信号名称
                "mt4id": "2089552465",   //mt4id
                "mt4server": "tickmill",   // mt4服务器
                "img": "http://www.znforex.com/static/upload/image/20171020/b14bdbaa71078432c3ae4fafec211f73.jpg",  //用户图像
                "bn": "000804",   //主信号编号
                "score": 100,   //综合评分
                "follow": 256,   //跟单人数
                "trade_drawdown": "41.00",   // 回撤（风险）
                "avg_mprofit": 398.28,   //预期收益率
                "trade_week": "113",   //交易时长
                "trade_win": 78,   //交易胜率
                "rand_profit": "22,160,187,205,231,288,309,333,337,366",
                "country": "瑞士",
                "profit": 63246,   //利润率
                "show": 1,  //前端主信号显示控制（1：显示  2：隐藏 3:即将下架）
                "country_img": "http://res.v.znforex.com/image/countryflag/10.png",  //国旗
                "service_img": "http://res.v.znforex.com/image/broker/7.png"  //服务商图片
            }
        ],
        "current_page": 1  //当前页数
        "total_num": 53  //总数据条数
    }
}
```
***

#### 十四、修改单个跟单信号数据
#### 1、请求接口： /v1.order/update_order

| 字段        |   名称       |  类型   |备注|
| :-----------:| :-------------: | :--:|:--:
| device      | 设备号 |  string |
| data      | 跟单的集合（包含下列所有字段） |  json |
| bn      | 主信号编号 |  string |
| name      | 主信号名称 |  string |
| weight      | 权重(手数) |  int | 跟单方向 （weight正数：正， 负数：反）|
| maxloss      | 最大浮亏 |  int |
| maxhold      | 最大持仓 |  int |
| mt4id      | 被跟单者mt4id |  string |



***
**返回结果**

```
{
    "code": 200,
    "msg": "数据添加成功"
}
```
***

#### 十五、主信号用户详情页
#### 1、请求接口： /v1.signal/detail

| 字段        |   名称       |  类型   |备注|
| :-----------:| :-------------: | :--:|:--:
| mt4id      | mt4id |  int |



***
**返回结果**

```
{
    "code": 200,
    "msg": "查询成功",
    "result": {
        "account": {
            "mt4id": "11951691",
            "img": "http://static.v.znforex.com/upload/image/20171024/62da5079a6fd4765839db1f6b5e78a42.png",
            "country": "俄罗斯",
            "name": "FrankoScalp nano test",
            "bn": "000919",
            "mt4server": "艾福瑞",
            "money": "1066",
            "follow": 86,  订阅者
            "trade_drawdown": "16%",   //最大回撤
            "trade_week": "85",   //周（交易时长）
            "trade_month": 20,
            "avg_mprofit": "13.64%",   //每月增长
            "profit_margin": "1029.01%",   //利润率
            "profit": "10,969.25",   //利润
            "week_trade_num": "17次",  //每周交易
            "holding_time": "1.06小时",  //平均持有时间
            "max_time": "2017-10-25 10:57",  //最近交易时间
            "country_img": "http://static.v.znforex.com/image/countryflag/6.png",
            "service_img": "http://static.v.znforex.com/image/broker/9.png",
            "trade_total": 1509,   // 总交易
            "profit_trade": "1183(78.4%)",   //盈利交易
            "loss_trade": "326(21.6%)",  //亏损交易
            "profit_factor": "2.28",   //利润因子
            "trade_best": "414.31",  //最好交易
            "trade_worst": "-387.91",  //最差交易
            "trade_return": "7.27",  //预期回报
            "sharpe_ratio": "0.13",  //夏普比率
            "sprofit_max": "2403.95 USD 13",  //最大连续赢利
            "sprofit_max_num": "73(1236.00) USD",  //最大连续盈利
            "sloss_max": "-1086.29 USD (13)",   //最大连续亏损
            "mistakes_max": "-244.40 (15 USD)",   //最大连续失误
            "gross_profit": "19917.20",  //毛利money
            "gross_profit_num": 5320,  //毛利点数
            "gross_loss": "-8717.99",   //毛利亏损
            "gross_loss_num": 2946,  //毛利亏损点数
            "avg_profit": "16.84",  //平均利润
            "avg_profit_num": 4,  //平均利润点数
            "avg_loss": "-26.74",   //平均损失
            "avg_loss_num": 9, //平均损失点数
            "avg_mprofit_year": "163.68%" //年度预测
        },
        "trade": {
            "2016": {    //2016为年份，  1-12为月份
                "1": "--",
                "2": "--",
                "3": "--",
                "4": "53.73%",
                "5": "16.96%",
                "6": "30.34%",
                "7": "11.41%",
                "8": "-3.71%",
                "9": "2.32%",
                "10": "22.54%",
                "11": "-2.30%",
                "12": "-2.22%",
                "year_total_profit": "129.07%"  //年总利润
            },
            "2017": {   //2017为年份，  1-12为月份
                "1": "3.35%",
                "2": "-10.77%",
                "3": "11.28%",
                "4": "24.85%",
                "5": "14.11%",
                "6": "-5.06%",
                "7": "2.89%",
                "8": "12.37%",
                "9": "-2.28%",
                "10": "1.72%",
                "11": "1.34%",
                "12": "--",
                "year_total_profit": "53.8%"  //年总利润
            },
            "total_profit": "182.87%"  //总利润
        },
        "count" : 11  //评论总条数
    }
}
```
***

#### 十六、组合跟单存储用户图片地址
#### 1、请求接口： /v1.order/order_car

| 字段        |   名称       |  类型   |
| :-----------:| :-------------: | :--:|
| device     | 设备号 | string |
| mt4id      | mt4id |  string |
| picture    | 图片地址 |  string |
| flag       | 用户国旗 |  string |
| service_img    | 服务商图片 |  string |
| bn   | 主信号编号 |  string |
| name  | 主信号名称 |  string |



***
**返回结果**

```
{
    "code": 200,
    "msg": "添加成功",
}
```
***

#### 十七、交易历史图表
#### 1、请求接口： /v1.signal/history

| 字段        |   名称       |  类型   |
| :-----------:| :-------------: | :--:|
| account     | mt4id | string |



***
**返回结果**

```
{
    "code": 200,
    "msg": "查询成功",
    "result": [
        {   //点数：|(close_price-open_price)*(10^dlen)| （close_price-open_price的值乘以10的dlen次方，然后取其绝对值）
            //利润：当前数据(profit+commission+swap)与上一条数据(profit+commission+swap)累加
            "id": 14156,
            "account": "11931350",
            "ticket": null,
            "symbol": "AUDUSD",  //交易品种
            "lots": "0.01", //手数（交易量）
            "op": "0",   //类型op(1:'sell'  其他：'buy'   )
            "dlen": "5",   //计算点数时候使用：10的dlen次方
            "open_price": "0.75619",  //建仓价格
            "close_price": "0.75311",  //平仓价格
            "sell_price": null,
            "buy_price": null,
            "profit": "-3.08",  //获利
            "commission": "-0.08",  //手续费
            "comment": null,
            "swap": "0",  //库存费
            "takeprofit": "0",  //止损
            "stoploss": "0.75311",  //止盈
            "open_time": 1460311267,
            "close_time": 1460319424,  //平仓时间（前端显示的时候close_time-8*3600+2）
            "operator": "import-api",
            "add_time": 1508825256,
            "modify_time": 1508825256
        }
    ]
```
***

#### 十八、主信号历史、持仓的交易数据
#### 1、请求接口： /v1.signal/iorders

| 字段        |   名称       |  类型   | 备注 |
| :-----------:| :-------------: | :--:|:--:|
| account     | mt4id | string | |
| tab     | 展示数据类别 | string | 默认history（历史数据）， hold （持仓） |
| page     | 页数 | int | |




***
**返回结果**

```
{
    "code": 200,
    "msg": "查询成功",
    "result": {
        "total": 3,
        "per_page": 1,
        "current_page": 1,
        "data": [
            {
                //点数：|(close_price-open_price)*(10^dlen)| （close_price-open_price的值乘以10的dlen次方，然后取其绝对值）
                "id": 14156,
                "account": "11931350",
                "ticket": null,
                "symbol": "AUDUSD",  //交易品种
                "lots": "0.01", //手数（交易量）
                "op": "0",   //类型op(1:'sell'  其他：'buy'   )
                "dlen": "5",   //计算点数时候使用：10的dlen次方
                "open_price": "0.75619",  //建仓价格
                "close_price": "0.75311",  //平仓价格
                "sell_price": null,
                "buy_price": null,
                "profit": "-3.08",  //获利
                "commission": "-0.08",  //手续费
                "comment": null,
                "swap": "0",  //库存费
                "takeprofit": "0",  //止损
                "stoploss": "0.75311",  //止盈
                "open_time": 1460311267,
                "close_time": 1460319424,  //平仓时间（前端显示的时候close_time-8*3600+2）
                "operator": "import-api",
                "add_time": 1508825256,
                "modify_time": 1508825256
            }
        ],
        "commission": 0,   //持仓的总手续费
        "swap": -0.08,  //持仓的总库存费
        "profit": -1.05  //持仓的总获利
    }
}

```
***

#### 十九、信号详情页评论列表数据
#### 1、请求接口： /v1.signal/icomment

| 字段        |   名称       |  类型   | 备注 |
| :-----------:| :-------------: | :--:|:--:|
| mt4id     | mt4id | string | |
| page     | 页数 | int | |


***
**返回结果**

```
{
    "code": 200,
    "msg": "查询成功",
    "result": {
        "total": 8,
        "per_page": 1,
        "current_page": 1,
        "data": [
            {
                "id": 284,
                "uid": 8015,
                "login": "18602707377",
                "username": "",
                "comment": "400-500pips移动止损，轻仓长线，非常好，安全，适合不同点差的平台！",
                "modify_time": 1508742602,
                "u_img": "20171101/a9e3107a6c037857db014b5829f06c1b.jpg"
            }
        ]
    }
}

```
***

#### 二十、信号详情页用户评论
#### 1、请求接口： /v1.signal/comment

| 字段        |   名称       |  类型   | 备注 |
| :-----------:| :-------------: | :--:|:--:|
| mt4id     | mt4id | string | |
| content     | 评论内容 | string | |




***
**返回结果**

```
{
    "code": 200,
    "msg": "评论成功，请等待管理员审核评论！"
}

```
***

#### 二十一、信号详情页图标（业绩）
#### 1、请求接口： /v1.signal/history_profit

| 字段        |   名称       |  类型   | 备注 |
| :-----------:| :-------------: | :--:|:--:|
| account     | mt4id | string | |
| type     | 展示数据类型 | string | 默认day（日），其他有：week（周）、month（月）、year（年） |




***
**返回结果**

```
{
    "code": 200,
    "msg": "查询成功",
    "result": [
        {
            "date": "2016年4月",
            "profit": 107.46   //利润
        },
        {
            "date": "2016年5月",
            "profit": 52.16000000000001
        }
    ]
}

```
***

#### 二十二、信号详情页图标（净值、浮盈/浮亏、仓位）
#### 1、请求接口： /v1.signal/history_total

| 字段        |   名称       |  类型   | 备注 |
| :-----------:| :-------------: | :--:|:--:|
| account     | mt4id | string | |




***
**返回结果**

```
{
    "code": 200,
    "msg": "",
    "data": [
        {   浮盈浮亏=获利（profit）/余额(balance)*100%
            仓位=已用预付款（margin）/净值(equity)*100%
            "account": "11931350",
            "balance": "1763.23",  //结余
            "equity": "1764.10",  //净值
            "margin": "7.28",  //已用预付款
            "free_margin": "1756.82",
            "profit": "0.87",
            "date": 1509423122  //日期
        },
        {
            "account": "11931350",
            "balance": "1760.13",
            "equity": "1773.56",
            "margin": "5.28",
            "free_margin": "1768.28",
            "profit": "13.43",
            "date": 1509464706
        }
    ]
}

```
***

#### 二十三、信号详情页图标（品种）
#### 1、请求接口： /v1.signal/history_symbol

| 字段        |   名称       |  类型   | 备注 |
| :-----------:| :-------------: | :--:|:--:|
| account     | mt4id | string | |


***
**返回结果**

```
{
    "code": 200,
    "msg": "查询成功",
    "data": [
        {
            "symbol": "EURJPY",  //品种
            "buy": 110,  //买
            "sell": 70,   //卖
            "num": 180, //总数
            "profit": 90.82
        },
        {
            "symbol": "USDCHF",
            "buy": 68,
            "sell": 46,
            "num": 114,
            "profit": 185.61999999999998
        }
    ]
}

```
***

#### 二十四、信号详情页中跟随者
#### 1、请求接口： /v1.signal/follow

| 字段        |   名称       |  类型   | 备注 |
| :-----------:| :-------------: | :--:|:--:|
| account     | mt4id | string | |
| page     | 页数 | int | |


***
**返回结果**

```
{
    "code": 200,
    "msg": "查询成功",
    "result": {
        "total": 165,
        "per_page": 10,
        "current_page": 1,
        "data": [
            {   //nickname为空，则名称用login，否则，用nickname
                "weight": "0.01", //权重（手数）：带有%的为比利跟单，否则为固定手数，如果带有'-'，则跟单方向为反向，没有则为正向
                "maxhold": 8,  //最大持仓
                "u_img": "20171101/2dba41aae833d6ae437cf91c2194103b.jpg",  //用户图像，如果为空，则取默认图片
                "login": "13862958091",  //用户
                "zhmt4server": "InfinoxCapitalLtd-InfinoxUK3",  //经纪商名称取'-'，前面字符串，如果没有'-',直接输出
                "zhmt4uid": "686325",
                "nickname": "",   // 用户昵称
                "balance": "0.00"  // 跟单结余
            }
        ],
        "follow": 165,  //总跟随人数
        "tBalance": "407616.80"  //跟随资金
    }
}

```
***

#### 二十五、删除redis中暂存的单个跟单信号数据
#### 1、请求接口： /v1.order/del

| 字段        |   名称       |  类型   | 备注 |
| :-----------:| :-------------: | :--:|:--:|
| device     | 设备号 | string | |
| mt4id     | mt4id | string | |


***
**返回结果**

```
{
    "code": 200,
    "msg": "删除成功"
}

```
***

#### 二十六、获取redis中暂存的组合信号数据
#### 1、请求接口： /v1.order/get_history

| 字段        |   名称       |  类型   | 备注 |
| :-----------:| :-------------: | :--:|:--:|
| device     | 设备号 | string | |



***
**返回结果**

```
{
    "code": 200,
    "msg": "查询成功",
    "result": [
        {
            "u_img":"http://static.v.znforex.com/upload/image/20171101/6e5bf4ac002268fd754fdbc49ab1a3e6.jpg", //用户图像
            "mt4id":"mt4id1"  //mt4id
            "country_img":"http://static.v.znforex.com/image/countryflag/6.png"  //国旗
            "service_img":"http://static.v.znforex.com/app/broker/6.png"  //经济商图片
            "name":"111"  //主信号名称
            "bn":"111"  //主信号编号
            "weight":0.01,  //权重(手数)
            "maxloss":100,    //最大浮亏
            "maxhold":1,    //最大持仓
            "status":0,
            "status_u":1,  //待修改数据
        }
    ]
}

```
***
#### 二十七、单个跟单信号删除
#### 1、请求接口： /v1.order/del_order

| 字段        |   名称       |  类型   | 备注 |
| :-----------:| :-------------: | :--:|:--:|
| mt4id     | mt4id | int | |




***
**返回结果**

```
{
    "code": 200,
    "msg": "删除成功"
}

```
***

#### 二十八、app支付宝支付
#### 1、请求接口： http://api.znforex.com/pay.malipay/create_order.html

| 字段        |   名称       |  类型   | 备注 |
| :-----------:| :-------------: | :--:|:--:|
| uid     | uid | int | |
| server     | vip等级 | string | |
| amount     | 价格 | int | |



***
**返回结果**

```


```
***

#### 二十九、app微信支付
#### 1、请求接口： http://api.znforex.com/pay.mwxpay/create_order

| 字段        |   名称       |  类型   | 备注 |
| :-----------:| :-------------: | :--:|:--:|
| uid     | uid | int | |
| server     | vip等级 | string | |
| amount     | 价格 | int | |



***
**返回结果**

```
暂时无法支付

```
***

#### 三十、分佣关系统计
#### 1、请求接口： /v1.mobile/get_invite_data



***
**返回结果**

```
{
    "code": 200,
    "msg": "查询成功",
    "result": {
        //一级
        "arr_sub": 0, //注册人数
        "arr_sub_mt4": 0, //已绑mt4
        "arr_sub_buy": 0, //购买用户
        //二级
        "arr_sub2": 0, //
        "arr_sub2_mt4": 0,
        "arr_sub2_buy": 0
    }
}

```
***

#### 三十一、安卓app更新接口
#### 1、请求接口： /v1.mobile/appUpdate



***
**返回结果**

```
{
    "code": 200,
    "msg": "获取成功",
    "result": {
        "versionCode": 8,
        "versionName": "3.0.2",
        "updateInfo": "1.修复已知BUG;|2.界面UI优化更新",
        "updateUrl": "http://srefx.com/download/app/sharefx_3.0.apk "
    }
}

```
***

#### 三十二、主信号详情页（盈利能力、风险评级）
#### 1、请求接口： /v1.signal/account_score
| 字段        |   名称       |  类型   | 备注 |
| :-----------:| :-------------: | :--:|:--:|
| mt4id     | mt4id | int | |



***
**返回结果**

```
{
    "code": 200,
    "msg": "查询成功",
    "data": {
        "year_proft": "108.48",  //年度预测
        "trade_win": "41.11",  //交易胜率
        "equity_increase": "0.85",  //最大净值涨幅
        "profit_offset": "65.71",  //盈利偏移占比
        "profit_factor": "1.47",  //利润因子
        "profit_score": "75",  //盈利系数
        "balance_drawdown": "20.00",  //最大结余回撤
        "equity_drawdown": "0.23",  //最大净值回撤
        "max_money": "1.20",  //最大入金加载
        "loss_max": "25.71",  //亏损偏移占比
        "loss_day_max": "2",  //最大单日亏损
        "loss_score": "7" //风险系数
    }
}

```
***

#### 三十三、主信号列表获取多mt4账号
#### 1、请求接口： /v1.signal/get


***
**返回结果**

```
{
    "code": 200,
    "msg": "查询成功",
    "result": [
        {
            "id": 1,
            "uid": 8873,
            "mt4id": "1",
            "mt4server": "ICMarkets-Live",
            "mt4pwd": "1",
            "sh": 1,  //mt4账号审核状态（0：未审核 1:审核通过 2：审核未通过）
            "status": 1,   //用户该mt4id跟单状态（1：在线 2：离线）
        }
    ]
}

```
***

#### 三十四、用户在线留言
#### 1、请求接口： /v1.signal/user_content
| 字段        |   名称       |  类型   | 备注 |
| :-----------:| :-------------: | :--:|:--:|
| name     | 名称 | string | |
| email     | 邮箱 | string | |
| message     | 内容 | string | |
| mobile     | 手机号 | string | |



***
**返回结果**

```
{
    "code": 200,
    "msg": "提交成功"
}

```
***