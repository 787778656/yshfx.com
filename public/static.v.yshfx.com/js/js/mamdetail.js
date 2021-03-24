var API = "http://api.yshfx.com/";
var d = [];
    var chart1_data = [];
    var chart2_data = [];
    var chart22_data = [];
    var chart2_data2 = [];
    var chart3_data = [];
    var chart4_data = [];
    var chart5_data = [];
    var myData = [];
    var databeast = [];
    var databeauty = [];
    var account=$("input[name='account']").val();
    var u = API+'v1.web/history?account=' + account;
    chart1_url = u;
    chart2_url = API+"v1.web/history_profit?time=day&account=" + account;
    chart22_url = API+"v1.web/history_lots?time=day&account=" + account;
    chart3_url = API+"v1.web/history_total?account=" + account;
    chart4_url = chart3_url;
    chart5_url = chart3_url;
    chart6_url = API+"v1.web/history_symbol?account=" + account;
    var profit_radar=[0, 0, 0, 0, 0];
    var profit_radar2 = [0, 0, 0, 0, 0];
    var profit_radar_detail = [0, 0, 0, 0, 0];
    var profit_radar2_detail = [0, 0, 0, 0, 0];
    var n = -1;
    var nn = -1;
    var profit_score;
    var loss_score;
    var animatecount = 1;
    var today=new Date();
    function date(today){
        var year=today.getFullYear();
        var month=today.getMonth()+1;
        if(month<10){
            month='0'+month;
        }
        var day=today.getDate();
        if(day<10){
            day='0'+day;
        }
        return year+'-'+month+'-'+day;
    }
    $(function () {
        var mamId=$("input[name='mamid']").val()
        $.ajax({
                url:API+"v1.web/history_mam_profit?mamId="+mamId+"&account="+account,
                type:'get',
                dataType:'jsonp',
                jsonp: 'callback',
                success:function(body){
                    if (body.code == 200) {
                        if (body.data != "") {
                            var reg = new RegExp(",", "g");//g,表示全部替换。
                            var item = body.data;
                            var alltime = item.all.replace(reg, "");
                            var day = item.day.replace(reg, "");
                            var week = item.week.replace(reg, "");
                            var month = item.month.replace(reg, "");
                            var month3 = item.month3.replace(reg, "");
                            var month6 = item.month6.replace(reg, "");
                            var year = item.year.replace(reg, "");
                            $("#alltime").html(alltime + "%")
                            $("#day").html(day + "%")
                            $("#week").html(week + "%")
                            $("#month").html(month + "%")
                            $("#month3").html(month3 + "%")
                            $("#month6").html(month6 + "%")
                            $("#year").html(year + "%")
//                            $("#calc-main").attr("data-percents", '{"month":{"value":' + (month / 12) + ',"localized":"' + (month / 12) + '%"},"quater":{"value":' + (month3 / 12) * 3 + ',"localized":"' + (month3 / 12) * 3 + '%"},"half":{"value":' + (month6 / 12) * 6 + ',"localized":"' + (month6 / 12) * 6 + '%"},"year":{"value":' + year + ',"localized":"' + year + '%"},"allTime":{"value":' + alltime + ',"localized":"' + alltime + '%"}}')
                            $("#calc-main").attr("data-percents", '{"month":{"value":' + month + ',"localized":"' + month + '%"},"quater":{"value":' + month3 + ',"localized":"' + month3 + '%"},"half":{"value":' + month6 + ',"localized":"' + month6 + '%"},"year":{"value":' + year + ',"localized":"' + year + '%"},"allTime":{"value":' + alltime + ',"localized":"' + alltime + '%"}}')
                            setTimeout(function () {
                            var element = document.createElement("script");
                            element.src = static+"js/investInPastCalc111.js";
                            document.body.appendChild(element);
                            },1000)
                        } else {
                            $("#alltime").html("--")
                            $("#day").html("--")
                            $("#week").html("--")
                            $("#month").html("--")
                            $("#month3").html("--")
                            $("#month6").html("--")
                            $("#year").html("--")
                        }
                    }
                }
            })
        $(".today").html(date(today));
        $(".confirm_close").click(function () {
            $(".confirmAlert").hide();
            $(".confirmBox").hide();
        });

        transaction0.chart(chart2_url, "chart");
        var n = 1;
         /*MAM交易详情页导航*/
        $(".main_b_title li").on("click", function () {
            $(this).addClass("active").siblings().removeClass("active");
        });
        $(".main_b_title1").on("click", function () {
            $(".main_b_content1").fadeIn().siblings().hide();
            transaction0.setOption(chart2_data2, "chart");
        });
        $(".main_b_title2").on("click", function () {
            $(".main_b_content2").fadeIn().siblings().hide();
            setTimeout(function () {
                    var mainheight2 = $("#iframe_ifollow").contents().find("body").height() + 100;
                    if (mainheight2 > 400) {
                        $("#iframe_ifollow").height(mainheight2);
                    } else {
                        $("#iframe_ifollow").height(400);
                    }
                    $("#iframe_ifollow").contents().find(".goin").click(function(){
                        var mamid=$("input[name='mamid']").val();
                        if(mamid){  
                            location.href=http+"mam/index.html?id="+mamid
                        }else{
                            $("#vip_login").fadeIn();
                        }
                    })
                }, 600);
        });
        $(".main_b_title3").on("click", function () {
             if (animatecount == 1) {
                    var element = document.createElement("script");
                    element.src = static + "js/jquery.animateNumber.min.js";
                    document.body.appendChild(element);
                    animatecount++;
            }
            $(".main_b_content3").fadeIn().siblings().hide();
            if($(this).data("id")=="1"){
                transaction.chart(u, "chart2");
            }
        });
        $(".main_b_title4").on("click", function () {
            $(".main_b_content4").fadeIn().siblings().hide();
            setTimeout(function () {
                    var mainheight = $("#mainframe").contents().find("body").height();
                    if (mainheight > 200) {
                        $(".commitContent").height(600);
                        $(".icommentframe").height(600);
                    } else {
                        $(".commitContent").height(480);
                        $(".icommentframe").height(480);
                    }
                }, 600);
        });
        $("#iframe_ifollow").load(function() {
            var mainheight = $(this).contents().find("body").height()+100;
            if(mainheight>400){
                $(this).height(mainheight);
            } else {
                $(this).height(400);
            }
            $(this).contents().find(".goin").click(function(){
                var mamid=$("input[name='mamid']").val();
                if(mamid){  
                    location.href=http+"mam/index.html?id="+mamid
                }else{
                    $("#vip_login").fadeIn();
                }
            })
        });
        $("#iframe_ls").load(function() {
            var mainheight = $(this).contents().find("body").height()+64;
            if(mainheight>400){
                $(this).height(mainheight);
            } else {
                $(this).height(400);
            }
        });
        $("#iframe_cc").load(function() {
            var mainheight = $(this).contents().find("body").height()+64;
            if(mainheight>400){
                $(this).height(mainheight);
            }else{
                $(this).height(400);
            }
        });
        /*交易数据里面的导航*/
        $(".main_content3_left li").on("click", function () {
            $(this).addClass("active").siblings().removeClass("active");
            $(".main_b_title3").data("id",$(this).data("id"))
            $(".content3").hide();
            if($(this).data("id")=="1"){
                $(".jy").show()
                transaction.setOption(d, "chart2");
            }else if($(this).data("id")=="2"){
                $(".tb").show()
                if (n == 1) {
                    n++;
                    allchart1.chart(chart1_url, "allchart1");
                    allchart2.chart(chart2_url, "allchart2");
                    allchart22.chart(chart22_url, "allchart22");
                    allchart3.chart(chart3_url, "allchart3");
                    allchart6.chart(chart6_url, "allchart6");
                }
                allchart1.setOption(chart1_data, "allchart1");
                allchart2.setOption(chart2_data, "allchart2");
                allchart22.setOption(chart22_data, "allchart22");
                allchart3.setOption(chart3_data, "allchart3");
                allchart4.setOption(chart4_data, "allchart4");
                allchart5.setOption(chart5_data, "allchart5");
                allchart6.setOption(myData, databeast, databeauty, "allchart6");
            }else if($(this).data("id")=="3"){
                $(".ls").show()
                setTimeout(function () {
                    var mainheight2 = $("#iframe_ls").contents().find("body").height() + 64;
                    if (mainheight2 > 400) {
                        $("#iframe_ls").height(mainheight2);
                    } else {
                        $("#iframe_ls").height(400);
                    }
                }, 600);
            }else if($(this).data("id")=="4"){
                $(".cc").show()
                setTimeout(function () {
                    var mainheight2 = $("#iframe_cc").contents().find("body").height() + 64;
                    if (mainheight2 > 400) {
                        $("#iframe_cc").height(mainheight2);
                    } else {
                        $("#iframe_cc").height(400);
                    }
                }, 600);
            }
        });
        $(".main_content3_right_title li").on("click", function () {
            $(this).addClass("active").siblings().removeClass("active");
            $(".content3_detail").hide()
            if($(this).data("id")=="1"){
                $(".tj").show();
            } else if ($(this).data("id") == "2") {
                $(".yl").show();
                $(".moduluscontain").css("width", "0");
                $(".moduluscontainBox").css("left", "-20px");
                $(".modulusConTips li").css("color","#999999");
                radar1.getdata();
                $(".orh_active2").removeClass("orh2_img_show");
                $(this).addClass("orh2_img_show");
            }else if($(this).data("id")=="3"){
                $(".fx").show();
                $(".moduluscontain2").css("width", "0");
                $(".moduluscontainBox2").css("left", "-20px");
                $(".modulusConTips2 li").css("color", "#999999");
                radar2.getdata();
                $(".orh_active2").removeClass("orh2_img_show");
                $(this).addClass("orh2_img_show");
            }
            $(".main_content3_right_title").data("id",$(this).data("id"))
        });
        $("input[value='month']").click(function () {
            allchart2.chart(API+"v1.web/history_profit?time=month&account=" + account, "allchart2");
        });
        $("input[value='week']").click(function () {
            allchart2.chart(API+"v1.web/history_profit?time=week&account=" + account, "allchart2");
        });
        $("input[value='day']").click(function () {
            allchart2.chart(API+"v1.web/history_profit?time=day&account=" + account, "allchart2");
        });
        $("input[value='month2']").click(function () {
            allchart22.chart(API+"v1.web/history_lots?time=month&account=" + account, "allchart22");
        });
        $("input[value='week2']").click(function () {
            allchart22.chart(API+"v1.web/history_lots?time=week&account=" + account, "allchart22");
        });
        $("input[value='day2']").click(function () {
            allchart22.chart(API+"v1.web/history_lots?time=day&account=" + account, "allchart22");
        });
    });
    $(".signalmode").click(function(e){
        e.stopPropagation();
        $(".signalmode").find("img").hide();
        $(this).find("img").show();
    });
    $(".signalmodeswitch").click(function(){
        if($(this).data("mode")=='1'){
            $(this).attr("src","__STATIC__image/signal_data_i08.png")
            $(this).data("mode","2")
        }else if($(this).data("mode")=='2'){
            $(this).attr("src","__STATIC__image/signal_data_i07.png")
            $(this).data("mode","1")
        }
    });
    $("body").click(function(){
        $(".signalmode").find("img").hide();
    })
    $(".goin").click(function(){
        var mamid=$("input[name='mamid']").val();
        if(mamid){  
            location.href=http+"mam/index.html?id="+mamid
        }else{
            $("#vip_login").fadeIn();
        }
    })
    var radar1={
        getdata: function (){
            $.ajax({
                url: API+'v1.web/account_score?account='+account,
                type: 'get',
                dataType: 'jsonp',
                jsonp: 'callback',
                success: function (body) {
                    if(body.code==200){
                        var data=body.data;
                        profit_score=parseFloat(data.profit_score);
                        var reg = new RegExp(",","g");
                        var year_proft= (data.year_proft.toString()).replace(reg,"");//年度预测
                        var profit_factor= (data.profit_factor.toString()).replace(reg,"");//利润因子
                        var profit_offset= (data.profit_offset.toString()).replace(reg,"");//盈利偏移
                        var equity_increase= (data.equity_increase.toString()).replace(reg,"");//净值涨幅
                        var trade_win= (data.trade_win.toString()).replace(reg,"");//交易胜率

                        year_proft= parseFloat(year_proft).toFixed(2);//年度预测
                        profit_factor= parseFloat(profit_factor).toFixed(2);//利润因子
                        profit_offset= parseFloat(profit_offset).toFixed(2);//盈利偏移
                        equity_increase= parseFloat(equity_increase).toFixed(2);//净值涨幅
                        trade_win= parseFloat(trade_win).toFixed(2);//交易胜率

                        if (year_proft <= 40) {
                            profit_radar[0]=1;
                        }else if(year_proft<=60){
                            profit_radar[0]=2;
                        }else if (year_proft <= 80) {
                            profit_radar[0] = 3;
                        } else if (year_proft <= 100) {
                            profit_radar[0] = 4;
                        } else if (year_proft > 100) {
                            profit_radar[0] = 5;
                        }

                        if (profit_factor <= 1.2) {
                            profit_radar[1]=1;
                        }else if(profit_factor<=1.4){
                            profit_radar[1]=2;
                        }else if (profit_factor <= 1.6) {
                            profit_radar[1] = 3;
                        } else if (profit_factor <= 1.8) {
                            profit_radar[1] = 4;
                        } else if (profit_factor > 1.8) {
                            profit_radar[1] = 5;
                        }

                        if (profit_offset <= 20) {
                            profit_radar[2]=1;
                        }else if(profit_offset<=40){
                            profit_radar[2]=2;
                        }else if (profit_offset <= 60) {
                            profit_radar[2] = 3;
                        } else if (profit_offset <= 80) {
                            profit_radar[2] = 4;
                        } else if (profit_offset > 80) {
                            profit_radar[2] = 5;
                        }

                        if (equity_increase <= 2) {
                            profit_radar[3]=1;
                        }else if(equity_increase<=4){
                            profit_radar[3]=2;
                        }else if (equity_increase <= 6) {
                            profit_radar[3] = 3;
                        } else if (equity_increase <= 8) {
                            profit_radar[3] = 4;
                        } else if (equity_increase > 8) {
                            profit_radar[3] = 5;
                        }

                        if (trade_win <= 40) {
                            profit_radar[4]=1;
                        }else if(trade_win<=50){
                            profit_radar[4]=2;
                        }else if (trade_win <= 60) {
                            profit_radar[4] = 3;
                        } else if (trade_win <= 70) {
                            profit_radar[4] = 4;
                        } else if (trade_win > 70) {
                            profit_radar[4] = 5;
                        }

                        profit_radar_detail=[year_proft, profit_factor,profit_offset,equity_increase,trade_win];

                        n=-1;
                        var dom = document.getElementById("gain_chart");
                        var myChart = echarts.init(dom);
                        var option = {
                            radar: {
                                // shape: 'circle',
                                center: ['50%', '60%'],//位置
                                radius: '80%',//半径
                                name: {
                                    show: true,
                                    formatter: function (value, indicator) {
                                        n++;
                                        if (n == 1) {
                                            return value + "(" + profit_radar_detail[n] + ")";
                                        } else {
                                            return value + "(" + profit_radar_detail[n] + "%)";
                                        }
                                    },
                                    textStyle: {
                                        color: '#333333',
                                        fontSize: 14,
                                        backgroundColor: '#999',
                                        borderRadius: 3,
                                        padding: [3, 5]
                                    }
                                },
                                indicator: [
                                    { name: '年度预测', min: 0, max: 5 },
                                    { name: '利润因子', min: 0, max: 5 },
                                    { name: '盈利偏移占比', min: 0, max: 5 },
                                    { name: '最大净值涨幅', min: 0, max: 5 },
                                    { name: '交易胜率', min: 0, max: 5 }
                                ],
                                axisLine: {
                                    lineStyle: {
                                        color: '#fff',
                                        width: 1,
                                        type: 'solid'
                                    },
                                },
                                splitLine: {
                                    lineStyle: {
                                        color: ['#fff', '#fff', '#fff', '#fff'],
                                        width: 1
                                    }
                                },
                                splitArea: {
                                    areaStyle: {
                                        color: ['#ccf5ff', '#a3edff', '#72e3ff', '#39d7ff', '#00c6ff']
                                    }
                                }
                            },
                            series: [{
                                type: 'radar',
                                itemStyle: {
                                    normal: {
                                        color: '#5035f9',
                                        borderColor: '#5035f9',
                                        borderWidth: 1
                                    },
                                    emphasis: {
                                        lineStyle: {
                                            width: 6
                                        }
                                    }
                                },
                                areaStyle: {
                                    normal: {
                                        opacity: 0
                                    }
                                },
                                lineStyle: {
                                    normal: {
                                        color: '#5035f9',
                                        borderColor: '#5035f9',
                                        width: 2
                                    },
                                    emphasis: {
                                        width: 2.8
                                    }
                                },
                                data: [
                                    {
                                        value: profit_radar,
                                        name: ''
                                    }
                                ]
                            }]
                        };
                        if (option && typeof option === "object") {
                            myChart.setOption(option, true);
                        }

                        if (profit_score < 25) {
                            $(".modulusConTips li").eq(0).css("color", "#00a8ff");
                        }else if(profit_score < 50){
                            $(".modulusConTips li").eq(0).css("color", "#00a8ff");
                            $(".modulusConTips li").eq(1).css("color", "#00a8ff");
                        } else if (profit_score < 75) {
                            $(".modulusConTips li").eq(0).css("color", "#00a8ff");
                            $(".modulusConTips li").eq(1).css("color", "#00a8ff");
                            $(".modulusConTips li").eq(2).css("color", "#00a8ff");
                        } else if (profit_score < 100) {
                            $(".modulusConTips li").eq(0).css("color", "#00a8ff");
                            $(".modulusConTips li").eq(1).css("color", "#00a8ff");
                            $(".modulusConTips li").eq(2).css("color", "#00a8ff");
                            $(".modulusConTips li").eq(3).css("color", "#00a8ff");
                        } else if (profit_score < 125) {
                            $(".modulusConTips li").eq(0).css("color", "#00a8ff");
                            $(".modulusConTips li").eq(1).css("color", "#00a8ff");
                            $(".modulusConTips li").eq(2).css("color", "#00a8ff");
                            $(".modulusConTips li").eq(3).css("color", "#00a8ff");
                            $(".modulusConTips li").eq(4).css("color", "#00a8ff");
                        }else if(profit_score==125){
                            $(".modulusConTips li").eq(0).css("color", "#00a8ff");
                            $(".modulusConTips li").eq(1).css("color", "#00a8ff");
                            $(".modulusConTips li").eq(2).css("color", "#00a8ff");
                            $(".modulusConTips li").eq(3).css("color", "#00a8ff");
                            $(".modulusConTips li").eq(4).css("color", "#00a8ff");
                            $(".modulusConTips li").eq(5).css("color", "#00a8ff");
                        }
                        if(profit_score<41){
                            $(".yl_result").html("低");
                        }else if(profit_score<83){
                            $(".yl_result").html("中");
                        }else{
                            $(".yl_result").html("高");
                        }
                        var ss= (profit_score/125*100)+'%';
                        $(".moduluscontain").animate({"width": ss},600);
                        var left= (profit_score/125)*700-20;
                        $(".moduluscontainBox").animate({ "left": left }, 600);
                        $('#target').animateNumber(
                                {
                                    number: profit_score
                                },
                                600
                        );
                    }
                }
            });
        }
    };
    var radar2 = {
        getdata:function(){
            $.ajax({
                url: API+'v1.web/account_score?account='+account,
                type: 'get',
                dataType: 'jsonp',
                jsonp: 'callback',
                success: function (body) {
                    if(body.code==200){
                        var data=body.data;
                        loss_score=parseFloat(data.loss_score);
                        var reg = new RegExp(",","g");

                        var balance_drawdown= (data.balance_drawdown.toString()).replace(reg,"");//结余跌幅
                        var loss_day_max= (data.loss_day_max.toString()).replace(reg,"");//日亏损
                        var loss_max= (data.loss_max.toString()).replace(reg,"");//亏损偏移
                        var max_money= (data.max_money.toString()).replace(reg,"");//最大入金
                        var equity_drawdown= (data.equity_drawdown.toString()).replace(reg,"");//净值跌幅

                        balance_drawdown= parseFloat(balance_drawdown).toFixed(2);//结余跌幅
                        loss_day_max= parseFloat(loss_day_max).toFixed(2);//日亏损
                        loss_max= parseFloat(loss_max).toFixed(2);//亏损偏移
                        max_money= parseFloat(max_money).toFixed(2);//最大入金
                        equity_drawdown= parseFloat(equity_drawdown).toFixed(2);//净值跌幅


                        if (balance_drawdown <= 10) {
                            profit_radar2[0]=1;
                        }else if(balance_drawdown<=20){
                            profit_radar2[0]=2;
                        }else if (balance_drawdown <= 30) {
                            profit_radar2[0] = 3;
                        } else if (balance_drawdown <= 40) {
                            profit_radar2[0] = 4;
                        } else if (balance_drawdown > 40) {
                            profit_radar2[0] = 5;
                        }

                        if (loss_day_max <= 5) {
                            profit_radar2[1]=1;
                        }else if(loss_day_max<=8){
                            profit_radar2[1]=2;
                        }else if (loss_day_max <= 12) {
                            profit_radar2[1] = 3;
                        } else if (loss_day_max <= 15) {
                            profit_radar2[1] = 4;
                        } else if (loss_day_max > 15) {
                            profit_radar2[1] = 5;
                        }

                        if (loss_max <= 20) {
                            profit_radar2[2]=1;
                        }else if(loss_max<=40){
                            profit_radar2[2]=2;
                        }else if (loss_max <= 60) {
                            profit_radar2[2] = 3;
                        } else if (loss_max <= 80) {
                            profit_radar2[2] = 4;
                        } else if (loss_max > 80) {
                            profit_radar2[2] = 5;
                        }

                        if (max_money <= 8) {
                            profit_radar2[3]=1;
                        }else if(max_money<=12){
                            profit_radar2[3]=2;
                        }else if (max_money <= 16) {
                            profit_radar2[3] = 3;
                        } else if (max_money <= 20) {
                            profit_radar2[3] = 4;
                        } else if (max_money > 20) {
                            profit_radar2[3] = 5;
                        }

                        if (equity_drawdown <= 10) {
                            profit_radar2[4]=1;
                        }else if(equity_drawdown<=15){
                            profit_radar2[4]=2;
                        }else if (equity_drawdown <= 20) {
                            profit_radar2[4] = 3;
                        } else if (equity_drawdown <= 25) {
                            profit_radar2[4] = 4;
                        } else if (equity_drawdown > 25) {
                            profit_radar2[4] = 5;
                        }
                        profit_radar2_detail=[balance_drawdown,loss_day_max,loss_max,max_money,equity_drawdown];
                        nn=-1;
                        var dom = document.getElementById("gain_chart2");
                        var myChart = echarts.init(dom);
                        var option = {
                            radar: {
                                // shape: 'circle',
                                center: ['50%', '60%'],//位置
                                radius: '80%',//半径
                                name: {
                                    show: true,
                                    formatter: function (value, indicator) {
                                        nn++;
                                        return value + "(" + profit_radar2_detail[nn] + "%)";

                                    },
                                    textStyle: {
                                        color: '#333333',
                                        fontSize: 14,
                                        backgroundColor: '#999',
                                        borderRadius: 3,
                                        padding: [3, 5]
                                    }
                                },
                                indicator: [
                                    { name: '最大结余回撤', min: 0, max: 5 },
                                    { name: '最大单日亏损', min: 0, max: 5 },
                                    { name: '亏损偏移占比', min: 0, max: 5 },
                                    { name: '最大仓位占用', min: 0, max: 5 },
                                    { name: '最大净值回撤', min: 0, max: 5 }
                                ],
                                axisLine: {
                                    lineStyle: {
                                        color: '#fff',
                                        width: 1,
                                        type: 'solid'
                                    },
                                },
                                splitLine: {
                                    lineStyle: {
                                        color: ['#fff', '#fff', '#fff', '#fff'],
                                        width: 1
                                    }
                                },
                                splitArea: {
                                    areaStyle: {
                                        color: ['#ffe8cc', '#ffd5a3', '#ffbe72', '#ffa439', '#ff8b00']
                                    }
                                }
                            },
                            series: [{
                                type: 'radar',
                                itemStyle: {
                                    normal: {
                                        color: '#e84e0e',
                                        borderColor: '#e84e0e',
                                        width: 1
                                    },
                                    emphasis: {
                                        lineStyle: {
                                            width: 6
                                        }
                                    }
                                },
                                areaStyle: {
                                    normal: {
                                        opacity: 0
                                    }
                                },
                                lineStyle: {
                                    normal: {
                                        color: '#e84e0e',
                                        borderColor: '#e84e0e',
                                        width: 2
                                    },
                                    emphasis: {
                                        width: 2.8
                                    }
                                },
                                data: [
                                    {
                                        value: profit_radar2,
                                        name: ''
                                    }
                                ]
                            }]
                        };
                        if (option && typeof option === "object") {
                            myChart.setOption(option, true);
                        }

                        if (loss_score < 5) {
                            $(".modulusConTips2 li").eq(0).css("color", "#611987");
                        } else if (loss_score < 10) {
                            $(".modulusConTips2 li").eq(0).css("color", "#611987");
                            $(".modulusConTips2 li").eq(1).css("color", "#611987");
                        } else if (loss_score < 15) {
                            $(".modulusConTips2 li").eq(0).css("color", "#611987");
                            $(".modulusConTips2 li").eq(1).css("color", "#611987");
                            $(".modulusConTips2 li").eq(2).css("color", "#611987");
                        } else if (loss_score < 20) {
                            $(".modulusConTips2 li").eq(0).css("color", "#611987");
                            $(".modulusConTips2 li").eq(1).css("color", "#611987");
                            $(".modulusConTips2 li").eq(2).css("color", "#611987");
                            $(".modulusConTips2 li").eq(3).css("color", "#611987");
                        } else if (loss_score < 25) {
                            $(".modulusConTips2 li").eq(0).css("color", "#611987");
                            $(".modulusConTips2 li").eq(1).css("color", "#611987");
                            $(".modulusConTips2 li").eq(2).css("color", "#611987");
                            $(".modulusConTips2 li").eq(3).css("color", "#611987");
                            $(".modulusConTips2 li").eq(4).css("color", "#611987");
                        } else if (loss_score == 25) {
                            $(".modulusConTips2 li").eq(0).css("color", "#611987");
                            $(".modulusConTips2 li").eq(1).css("color", "#611987");
                            $(".modulusConTips2 li").eq(2).css("color", "#611987");
                            $(".modulusConTips2 li").eq(3).css("color", "#611987");
                            $(".modulusConTips2 li").eq(4).css("color", "#611987");
                            $(".modulusConTips2 li").eq(5).css("color", "#611987");
                        }
                        if(loss_score<8){
                            $(".fx_result").html("低");
                        }else if(loss_score<16){
                            $(".fx_result").html("中");
                        }else{
                            $(".fx_result").html("高");
                        }
                        var ss = (loss_score / 25 * 100) + '%';
                        $(".moduluscontain2").animate({ "width": ss }, 600);
                        var left = (loss_score/25)*700 - 20;
                        $(".moduluscontainBox2").animate({ "left": left }, 600);
                        $('#target2').animateNumber(
                                {
                                    number: loss_score
                                },
                                600
                        );
                    }
                }
            });
        }
    };

    var transaction0 = {
        chart: function (geturl, obj) {
            $.ajax({
                url: geturl,
                type: 'get',
                dataType: 'jsonp',
                jsonp: "callback",
                success: function (body) {
                    if (body.code == 200) {
                        var data = body.data;
                        chart2_data2 = [];
                        if (data.length == 0) {
                         $('#'+obj).html('<img src="__STATIC__image/chart_none.jpg" class="chart_none">')
                        } else {
                            var chartsalldata = 0;
                            for (var i = 0; i < data.length; i++) {
                                chartsalldata+=parseFloat(data[i].profit)
                                chart2_data2.push([data[i].date, chartsalldata.toFixed(2)]);
                            }
                            transaction0.setOption(chart2_data2, obj);
                        }
                    }
                },
                complete: function () {
                    $("#" + obj).parent().find(".chartload").hide();
                    $("#" + obj).css("visibility", "visible");
                }
            });
        },
        setOption: function (d, obj) {
            if(chart2_data2.length>0){
            var xy = d;
            var dom = document.getElementById(obj);
            var myChart = echarts.init(dom);
            var option = {
                tooltip: {
                    trigger: 'axis'
                },
                xAxis: {
                    name: '日期',
                    type: 'category',
                    boundaryGap: true,
                    data: xy.map(function (item) {
                        return item[0]
                    }),
                    splitLine: {
                        show: true,
                        lineStyle: {
                            color: "#dfdfdf",
                            type: "dashed"
                        }
                    }
                },
                yAxis: {
                    name: '利润',
                    type: 'value',
                    splitLine: {
                        lineStyle: {
                            color: "#dfdfdf",
                            type: "dashed"
                        }
                    }
                },
                dataZoom: [{
                    type: 'inside',
                    start: 0,      /*时间调节放在最后面90%-100%*/
                    end: 100
                }, {
                    start: 0,
                    end: 100,
                    show: false,
                    handleIcon: 'M10.7,11.9v-1.3H9.3v1.3c-4.9,0.3-8.8,4.4-8.8,9.4c0,5,3.9,9.1,8.8,9.4v1.3h1.3v-1.3c4.9-0.3,8.8-4.4,8.8-9.4C19.5,16.3,15.6,12.2,10.7,11.9z M13.3,24.4H6.7V23h6.6V24.4z M13.3,19.6H6.7v-1.4h6.6V19.6z',
                    handleSize: '80%',
                    handleStyle: {
                    color: '#fff',
                    shadowBlur: 3,
                    shadowColor: 'rgba(0, 0, 0, 0.6)',
                    shadowOffsetX: 2,
                    shadowOffsetY: 2
                    }
                }],
                grid: {
                    left: 80,
                    right: 40,
                    top: 50,
                    bottom: 50,
                },
                visualMap: {
                    show: false,
                    left: 150,
                    top: 10,
                    inRange: {
                        colorLightness: [0.8, 0.4]
                    },
                    pieces: [{
                        gt: 0,
                        lte: 9999999999,
                        color: '#fa4431'
                    }],
                    outOfRange: {
                        color: '#fa4431'
                    },
                    controller: {
                        inRange: {
                            color: '#fa4431'
                        }
                    }
                },
                series: [
                    {
                        name: '利润',
                        type: 'line',
                        smooth: true,
                        symbol: 'none',
                        sampling: 'false',
                        lineStyle: {
                            normal: {
                            width: 2
                            }
                        },
                        itemStyle: {
                            normal: {
                            color: '#611987'
                            }
                        },
                        data: xy.map(function (item) {
                            return item[1];
                        }),
                        markLine:
                        {
                            silent: true,
                            lineStyle: {
                                normal: {
                                    type: 'solid',
                                    color: "red",
                                    width: 1.3,
                                    opacity: 0.8
                                }
                            }
                        }
                    }
                ]
            };
            if (option && typeof option === "object") {
                myChart.setOption(option, true);

            }
            } else {
                 $('#'+obj).html('<img src="__STATIC__image/chart_none.jpg" class="chart_none">')
            }
        }
    };
    var transaction = {
        chart: function (geturl, obj) {
            $.ajax({
            url: geturl,
            type: 'get',
            dataType: 'jsonp',
            jsonp: 'callback',
            success: function (data) {
                if (data.code == 200) {
                var items = data.data;
                d[0] = [0, 0, 0, 0, 0, 0, 0, 0];
                if(items.length>0){
                    for (var i = 0; i < items.length; i++) {
                        var ds = Math.abs(Math.round((parseFloat(items[i].close_price) - parseFloat(items[i].open_price)) * Math.pow(10, items[i].dlen)));
                        var type = items[i].op == 1 ? 'sell' : 'buy';
                        if (items[i].commission == '') {
                        var sxf = 0;
                        } else {
                        var sxf = items[i].commission;
                        }
                        if (items[i].swap == null) {
                        var kcf = 0;
                        } else {
                        var kcf = items[i].swap;
                        }
                        d[i + 1] = [i + 1, d[i][1] + parseFloat(items[i].profit) + parseFloat(sxf) + parseFloat(kcf), items[i].symbol, type, items[i].lots, items[i].profit, ds];
                    }
                    transaction.setOption(d, obj);
                }else{
                    $('#'+obj).html('<img src="__STATIC__image/chart_none.jpg" class="chart_none">')
                }
                }
            },
            complete: function () {
                $("#" + obj).parent().find(".chartload").hide();
                $("#" + obj).parent().find(".chart_tips").css("visibility", "visible");
                $("#" + obj).css("visibility", "visible");
            }
            });
        },
        setOption: function (d, obj) {
             if(d.length>1){
            var xy = d;
            var dom = document.getElementById(obj);
            var myChart = echarts.init(dom);
            var option = {
            tooltip: {
                trigger: 'axis',
                triggerOn: 'mousemove',
                formatter: function (params) {
                var paramsNum = params[0].value;
                return '<div>品种：' + paramsNum[2] + '<br>类型：' + paramsNum[3] + '<br>手数：' + paramsNum[4] + '<br>获利：' + parseFloat(paramsNum[5]).toFixed(2) + '<br>点数：' + paramsNum[6] + '<br>利润：' + paramsNum[1].toFixed(2) + '</div>';
                },
                backgroundColor: '#fff',
                borderColor: '#611987',
                borderWidth: '1',
                textStyle: {
                fontSize: 12,
                color: '#000'
                }
            },
            grid: {
                left: 90,
                right: 40,
                top: 50,
                bottom: 50,
            },
            xAxis: {
                type: "category",
                name: "交易",
                scale: true,
                axisLine: {
                onZero: true
                },
                data: xy.map(function (item) {
                return item[0]
                }),
                splitLine: {
                show: true,
                lineStyle: {
                    color: "#dfdfdf",
                    type: "dashed"
                }
                }
            },
            yAxis: [
                {
                type: 'value',
                name: '利润',
                axisLabel: {
                    formatter: '{value} '
                },
                splitLine: {
                    lineStyle: {
                    color: "#dfdfdf",
                    type: "dashed"
                    }
                }
                }
            ],
            dataZoom: [{
                type: 'inside',
                start: 0,      /*时间调节放在最后面90%-100%*/
                end: 100
            }, {
                start: 0,
                end: 100,
                show: false,
                handleIcon: 'M10.7,11.9v-1.3H9.3v1.3c-4.9,0.3-8.8,4.4-8.8,9.4c0,5,3.9,9.1,8.8,9.4v1.3h1.3v-1.3c4.9-0.3,8.8-4.4,8.8-9.4C19.5,16.3,15.6,12.2,10.7,11.9z M13.3,24.4H6.7V23h6.6V24.4z M13.3,19.6H6.7v-1.4h6.6V19.6z',
                handleSize: '80%',
                handleStyle: {
                color: '#fff',
                shadowBlur: 3,
                shadowColor: 'rgba(0, 0, 0, 0.6)',
                shadowOffsetX: 2,
                shadowOffsetY: 2
                }
            }],
            visualMap: {
                show: false,
                left: 100,
                top: 10,
                pieces: [{
                gt: 0,
                lte: 100000,
                color: '#611987'
                }],
                outOfRange: {
                color: '#611987'
                }
            },
            series: [
                {
                animation: true,
                animationDuration: 300,
                name: '交易',
                type: 'line',
                smooth: true,
                showSymbol: false,
                lineStyle: {
                    normal: {
                    width: 2
                    }
                },
                itemStyle: {
                    normal: {
                    color: '#611987'
                    }
                },
                areaStyle: {
                    normal: {
                    color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [{
                        offset: 0,
                        color: '#611987'
                    },
                    {
                        offset: 1,
                        color: '#f7bd8e'
                    }
                    ])
                    }
                },
                data: xy.map(function (item) {
                    return item;
                })/*,
                    markLine:
                    {
                    silent: true,
                    lineStyle:{
                    normal:{
                    type:'solid',
                    color:"red",
                    width:1.3,
                    opacity:0.8
                    }
                    },
                    data:[
                    [
                    {
                    // 起点和终点的项会共用一个 name
                    name: '最小值到最大值',
                    type: 'min'
                    },
                    {
                    type: 'max'
                    }
                    ]
                    ]
                    }*/
                }
            ]
            };
            if (option && typeof option === "object") {
            myChart.setOption(option, true);

            }
             }else {
               $('#'+obj).html('<img src="__STATIC__image/chart_none.jpg" class="chart_none">')
            }
        }
    };

    var allchart1 = {
        chart: function (geturl, obj) {
            $.ajax({
            url: geturl,
            type: 'get',
            dataType: 'jsonp',
            jsonp: 'callback',
            success: function (data) {
                if (data.code == 200) {
                var items = data.data;
                chart1_data[0] = [0, 0, 0, 0, 0, 0, 0, 0];
                if(items.length>0){
                    for (var i = 0; i < items.length; i++) {
                        var ds = Math.abs(Math.round((parseFloat(items[i].close_price) - parseFloat(items[i].open_price)) * Math.pow(10, items[i].dlen)));
                        var type = items[i].op == 1 ? 'sell' : 'buy';
                        if (items[i].commission == '') {
                        var sxf = 0;
                        } else {
                        var sxf = items[i].commission;
                        }
                        if (items[i].swap == null) {
                        var kcf = 0;
                        } else {
                        var kcf = items[i].swap;
                        }
                        chart1_data[i + 1] = [i + 1, chart1_data[i][1] + parseFloat(items[i].profit) + parseFloat(sxf) + parseFloat(kcf), items[i].symbol, type, items[i].lots, items[i].profit, ds];
                    }
                    allchart1.setOption(chart1_data, obj);
                } else {
                    $('#'+obj).html('<img src="__STATIC__image/chart_none.jpg" class="chart_none">')
                }
                }
            },
            complete: function () {
                $("#" + obj).parent().find(".chartload").hide();
                $("#" + obj).css("visibility", "visible");
            }
            });
        },
        setOption: function (d, obj) {
            if(chart1_data.length>1){
            var xy = d;
            var dom = document.getElementById(obj);
            var myChart = echarts.init(dom);
            var option = {
            tooltip: {
                trigger: 'axis',
                triggerOn: 'mousemove',
                formatter: function (params) {
                var paramsNum = params[0].value;
                return '<div>品种：' + paramsNum[2] + '<br>类型：' + paramsNum[3] + '<br>手数：' + paramsNum[4] + '<br>获利：' + parseFloat(paramsNum[5]).toFixed(2) + '<br>点数：' + paramsNum[6] + '<br>利润：' + paramsNum[1].toFixed(2) + '</div>';
                },
                backgroundColor: '#fff',
                borderColor: '#008cdd',
                borderWidth: '1',
                textStyle: {
                fontSize: 12,
                color: '#000'
                }
            },
            grid: {
                left: 90,
                right: 40,
                top: 50,
                bottom: 50,
            },
            xAxis: {
                type: "category",
                name: "交易",
                scale: true,
                axisLine: {
                onZero: true
                },
                data: xy.map(function (item) {
                return item[0]
                }),
                splitLine: {
                show: true,
                lineStyle: {
                    color: "#dfdfdf",
                    type: "dashed"
                }
                }
            },
            yAxis: [
                {
                type: 'value',
                name: '利润',
                axisLabel: {
                    formatter: '{value} '
                },
                splitLine: {
                    lineStyle: {
                    color: "#dfdfdf",
                    type: "dashed"
                    }
                }
                }
            ],
            dataZoom: [{
                type: 'inside',
                start: 0,      /*时间调节放在最后面90%-100%*/
                end: 100
            }, {
                start: 0,
                end: 100,
                show: false,
                handleIcon: 'M10.7,11.9v-1.3H9.3v1.3c-4.9,0.3-8.8,4.4-8.8,9.4c0,5,3.9,9.1,8.8,9.4v1.3h1.3v-1.3c4.9-0.3,8.8-4.4,8.8-9.4C19.5,16.3,15.6,12.2,10.7,11.9z M13.3,24.4H6.7V23h6.6V24.4z M13.3,19.6H6.7v-1.4h6.6V19.6z',
                handleSize: '80%',
                handleStyle: {
                color: '#fff',
                shadowBlur: 3,
                shadowColor: 'rgba(0, 0, 0, 0.6)',
                shadowOffsetX: 2,
                shadowOffsetY: 2
                }
            }],
            visualMap: {
                show: false,
                left: 100,
                top: 10,
                pieces: [{
                gt: 0,
                lte: 100000,
                color: '#611987'
                }],
                outOfRange: {
                color: '#611987'
                }
            },
            series: [
                {
                animation: true,
                animationDuration: 300,
                name: '交易',
                type: 'line',
                smooth: true,
                showSymbol: false,
                lineStyle: {
                    normal: {
                    width: 2
                    }
                },
                itemStyle: {
                    normal: {
                    color: '#008cdd'
                    }
                },
                areaStyle: {
                    normal: {
                    color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [{
                        offset: 0,
                        color: '#008cdd'
                    },
                    {
                        offset: 1,
                        color: '#9cd6f7'
                    }
                    ])
                    }
                },
                data: xy.map(function (item) {
                    return item;
                })
                }
            ]
            };
            if (option && typeof option === "object") {
            myChart.setOption(option, true);

            }
            }
        }
    };

    var allchart2 = {
        chart: function (geturl, obj) {
            $.ajax({
            url: geturl,
            type: 'get',
            dataType: 'jsonp',
            jsonp: "callback",
            success: function (body) {
                if (body.code == 200) {
                    var data = body.data;
                    chart2_data = [];
                    if (data.length == 0) {
                        $('#'+obj).html('<img src="__STATIC__image/chart_none.jpg" class="chart_none">')
                    } else {
                        for (var i = 0; i < data.length; i++) {
                        chart2_data.push([data[i].date, parseFloat(data[i].profit).toFixed(2)]);
                        }
                        allchart2.setOption(chart2_data, obj);
                    }  
                }
            },
            complete: function () {
                $("#" + obj).parent().find(".chartload").hide();
                $("#" + obj).css("visibility", "visible");
            }
            });
        },
        setOption: function (d, obj) {
            if(chart2_data.length>0){
            var xy = d;
            var dom = document.getElementById(obj);
            var myChart = echarts.init(dom);
            var option = {
            tooltip: {
                trigger: 'axis'
            },
            xAxis: {
                name: '日期',
                type: 'category',
                boundaryGap: true,
                data: xy.map(function (item) {
                return item[0]
                }),
                splitLine: {
                show: true,
                lineStyle: {
                    color: "#dfdfdf",
                    type: "dashed"
                }
                }
            },
            yAxis: {
                name: '利润',
                type: 'value',
                splitLine: {
                lineStyle: {
                    color: "#dfdfdf",
                    type: "dashed"
                }
                }
            },
            grid: {
                left: 90,
                right: 40,
                top: 50,
                bottom: 50,
            },
            dataZoom: [{
                type: 'inside',
                start: 0,      /*时间调节放在最后面90%-100%*/
                end: 100
            }, {
                start: 0,
                end: 100,
                show: false,
                handleIcon: 'M10.7,11.9v-1.3H9.3v1.3c-4.9,0.3-8.8,4.4-8.8,9.4c0,5,3.9,9.1,8.8,9.4v1.3h1.3v-1.3c4.9-0.3,8.8-4.4,8.8-9.4C19.5,16.3,15.6,12.2,10.7,11.9z M13.3,24.4H6.7V23h6.6V24.4z M13.3,19.6H6.7v-1.4h6.6V19.6z',
                handleSize: '80%',
                handleStyle: {
                color: '#fff',
                shadowBlur: 3,
                shadowColor: 'rgba(0, 0, 0, 0.6)',
                shadowOffsetX: 2,
                shadowOffsetY: 2
                }
            }],
            visualMap: {
                show: false,
                left: 100,
                top: 10,
                inRange: {
                colorLightness: [0.8, 0.4]
                },
                pieces: [{
                gt: 0,
                lte: 9999999999,
                color: '#fa4431'
                }],
                outOfRange: {
                color: '#fa4431'
                },
                controller: {
                inRange: {
                    color: '#fa4431'
                }
                }
            },
            series: [
                {
                name: '利润',
                type: 'bar',
                smooth: true,
                symbol: 'none',
                sampling: 'average',
                itemStyle: {
                    normal: {
                    width: 4,
                    arBorderRadius: 20,
                    color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [{
                        offset: 0,
                        color: '#00a2ff'
                    }, {
                        offset: 1,
                        color: '#00a2ff'
                    }]),
                    // shadowColor: 'rgba(35,149,229,0.8)',
                    // shadowBlur: 20,
                    areaStyle: { type: 'default' }
                    }
                },
                data: xy.map(function (item) {
                    return item[1];
                }),
                markLine:
                {
                    silent: true,
                    lineStyle: {
                    normal: {
                        type: 'solid',
                        color: "red",
                        width: 1.3,
                        opacity: 0.8
                    }
                    }
                }
                }
            ]
            };
            if (option && typeof option === "object") {
            myChart.setOption(option, true);

            }
            }
        }
    };

    var allchart22 = {
        chart: function (geturl, obj) {
            $.ajax({
            url: geturl,
            type: 'get',
            dataType: 'jsonp',
            jsonp: "callback",
            success: function (body) {
                if (body.code == 200) {
                    var data = body.data;
                    chart22_data = [];
                    if (data.length == 0) {
                         $('#'+obj).html('<img src="__STATIC__image/chart_none.jpg" class="chart_none">')
                    } else {
                        for (var i = 0; i < data.length; i++) {
                        chart22_data.push([data[i].date, parseFloat(data[i].lots).toFixed(2)]);
                        }
                        allchart22.setOption(chart22_data, obj);
                    }  
                }
            },
            complete: function () {
                $("#" + obj).parent().find(".chartload").hide();
                $("#" + obj).css("visibility", "visible");
            }
            });
        },
        setOption: function (d, obj) {
            if(chart22_data.length>0){
            var xy = d;
            var dom = document.getElementById(obj);
            var myChart = echarts.init(dom);
            var option = {
            tooltip: {
                trigger: 'axis'
            },
            xAxis: {
                name: '日期',
                type: 'category',
                boundaryGap: true,
                data: xy.map(function (item) {
                return item[0]
                }),
                splitLine: {
                show: true,
                lineStyle: {
                    color: "#dfdfdf",
                    type: "dashed"
                }
                }
            },
            yAxis: {
                name: '手数',
                type: 'value',
                splitLine: {
                lineStyle: {
                    color: "#dfdfdf",
                    type: "dashed"
                }
                }
            },
            grid: {
                left: 90,
                right: 40,
                top: 50,
                bottom: 50,
            },
            dataZoom: [{
                type: 'inside',
                start: 0,      /*时间调节放在最后面90%-100%*/
                end: 100
            }, {
                start: 0,
                end: 100,
                show: false,
                handleIcon: 'M10.7,11.9v-1.3H9.3v1.3c-4.9,0.3-8.8,4.4-8.8,9.4c0,5,3.9,9.1,8.8,9.4v1.3h1.3v-1.3c4.9-0.3,8.8-4.4,8.8-9.4C19.5,16.3,15.6,12.2,10.7,11.9z M13.3,24.4H6.7V23h6.6V24.4z M13.3,19.6H6.7v-1.4h6.6V19.6z',
                handleSize: '80%',
                handleStyle: {
                color: '#fff',
                shadowBlur: 3,
                shadowColor: 'rgba(0, 0, 0, 0.6)',
                shadowOffsetX: 2,
                shadowOffsetY: 2
                }
            }],
            series: [
                {
                name: '手数',
                type: 'bar',
                smooth: true,
                symbol: 'none',
                sampling: 'average',
                itemStyle: {
                normal: {
                    color: new echarts.graphic.LinearGradient(
                        0, 0, 0, 1,
                        [
                            {offset: 0, color: '#64b5ff'},
                            {offset: 1, color: '#7acd7a'}
                        ]
                    )
                },
                emphasis: {
                    color: new echarts.graphic.LinearGradient(
                        0, 0, 0, 1,
                        [
                            {offset: 0, color: '#2378f7'},
                            {offset: 0.7, color: '#2378f7'},
                            {offset: 1, color: '#83bff6'}
                        ]
                    )
                    }
            },
                data: xy.map(function (item) {
                    return item[1];
                }),
                markLine:
                {
                    silent: true,
                    lineStyle: {
                    normal: {
                        type: 'solid',
                        color: "red",
                        width: 1.3,
                        opacity: 0.8
                    }
                    }
                }
                }
            ]
            };
            if (option && typeof option === "object") {
            myChart.setOption(option, true);

            }
            }
        }
    };

    var allchart3 = {
        chart: function (geturl, obj) {
            $.ajax({
            url: geturl,
            type: 'get',
            dataType: 'jsonp',
            jsonp: 'callback',
            success: function (body) {
                if (body.code == 200) {
                    var data = body.data;
                    chart3_data = [];
                    chart4_data = [];
                    chart5_data = [];
                    if (data.length == 0) {
                        $('#'+obj).html('<img src="__STATIC__image/chart_none.jpg" class="chart_none">')
                    } else {
                        for (var i in data) {
                        var time = new Date(parseInt(data[i].date) * 1000);
                        var date = time.getFullYear() + '-' + (time.getMonth() + 1) + '-' + time.getDate();
                        chart3_data.push([date, parseFloat(data[i].balance).toFixed(2), parseFloat(data[i].equity).toFixed(2)]);
                        chart4_data.push([date, ((data[i].profit / data[i].balance) * 100).toFixed(2)]);
                        chart5_data.push([date, ((data[i].margin / data[i].equity) * 100).toFixed(2)]);
                        }
                    }
                }

            },
            complete: function () {
                if(chart3_data.length>0){
                    allchart3.setOption(chart3_data, obj);
                }
                allchart4.chart(chart4_url, "allchart4");
                allchart5.chart(chart5_url, "allchart5");
                $("#" + obj).parent().find(".chartload").hide();
                $("#" + obj).css("visibility", "visible");
            }
            });
        },
        setOption: function (d, obj) {
            if(chart3_data.length>0){
            var xy = d;
            var dom = document.getElementById(obj);
            var myChart = echarts.init(dom);
            var option = {
            tooltip: {
                trigger: 'axis'
            },
            grid: {
                left: 90,
                right: 40,
                top: 50,
                bottom: 50,
            },
            xAxis: {
                type: "category",
                name: "日期",
                scale: true,
                axisLine: {
                onZero: true
                },
                data: xy.map(function (item) {
                return item[0]
                }),
                splitLine: {
                show: true,
                lineStyle: {
                    color: "#dfdfdf",
                    type: "dashed"
                }
                }
            },
            yAxis: [
                {
                type: 'value',
                name: '',
                axisLine: {
                    onZero: true
                },
                axisLabel: {
                    formatter: '{value} '
                },
                splitLine: {
                    lineStyle: {
                    color: "#dfdfdf",
                    type: "dashed"
                    }
                }
                }
            ],
            dataZoom: [{
                type: 'inside',
                start: 0,      /*时间调节放在最后面90%-100%*/
                end: 100
            }, {
                start: 0,
                end: 100,
                show: false,
                handleIcon: 'M10.7,11.9v-1.3H9.3v1.3c-4.9,0.3-8.8,4.4-8.8,9.4c0,5,3.9,9.1,8.8,9.4v1.3h1.3v-1.3c4.9-0.3,8.8-4.4,8.8-9.4C19.5,16.3,15.6,12.2,10.7,11.9z M13.3,24.4H6.7V23h6.6V24.4z M13.3,19.6H6.7v-1.4h6.6V19.6z',
                handleSize: '80%',
                handleStyle: {
                color: '#fff',
                shadowBlur: 3,
                shadowColor: 'rgba(0, 0, 0, 0.6)',
                shadowOffsetX: 2,
                shadowOffsetY: 2
                }
            }],
            series: [
                {
                animation: true,
                animationDuration: 300,
                name: '结余',
                type: 'line',
                smooth: true,
                showSymbol: false,
                lineStyle: {
                    normal: {
                    width: 2
                    }
                },
                itemStyle: {
                    normal: {
                    color: '#0080ff'
                    }
                },
                areaStyle: {
                    normal: {
                    color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [{
                        offset: 0,
                        color: '#0080ff'
                    },
                    {
                        offset: 1,
                        color: '#fff'
                    }
                    ])
                    }
                },
                data: xy.map(function (item) {
                    return item[1];
                })
                },
                {
                animation: true,
                animationDuration: 300,
                name: '净值',
                type: 'line',
                smooth: true,
                showSymbol: false,
                lineStyle: {
                    normal: {
                    width: 2
                    }
                },
                itemStyle: {
                    normal: {
                    color: '#7fbf7f'
                    }
                },
                areaStyle: {
                    normal: {
                    color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [{
                        offset: 0,
                        color: '#7fbf7f'
                    },
                    {
                        offset: 1,
                        color: '#fff'
                    }
                    ])
                    }
                },
                data: xy.map(function (item) {
                    return item[2];
                })
                }
            ]
            };
            if (option && typeof option === "object") {
            myChart.setOption(option, true);

            }
            }
        }
    };

    var allchart4 = {
        chart: function (geturl, obj) {
            if(chart4_data.length>1){
                allchart4.setOption(chart4_data, obj);
            } else {
                $('#'+obj).html('<img src="__STATIC__image/chart_none.jpg" class="chart_none">')
            }
            $("#" + obj).parent().find(".chartload").hide();
            $("#" + obj).css("visibility", "visible");
        },
        setOption: function (d, obj) {
             if(chart4_data.length>1){
            var xy = d;
            var dom = document.getElementById(obj);
            var myChart = echarts.init(dom);
            var option = {
            tooltip: {
                trigger: 'axis',
                triggerOn: 'mousemove',
                formatter: function (params) {
                var paramsNum = params[0].value;
                if (paramsNum > 0) {
                    return '<span style="background:#0098f0;padding:5px;border-radius:4px;">' + paramsNum + '%</span>';
                } else {
                    return '<span style="background:#f25137;padding:5px;border-radius:4px;">' + paramsNum + '%</span>';
                }
                },
                backgroundColor: '',
                borderColor: '',
                borderWidth: '0',
                textStyle: {
                fontSize: 12,
                color: '#fff'
                }
            },
            grid: {
                left: 90,
                right: 40,
                top: 50,
                bottom: 50,
            },
            xAxis: {
                type: "category",
                name: "日期",
                scale: true,
                axisLine: {
                onZero: true
                },
                data: xy.map(function (item) {
                return item[0]
                }),
                splitLine: {
                show: true,
                lineStyle: {
                    color: "#dfdfdf",
                    type: "dashed"
                }
                }
            },
            yAxis: [
                {
                type: 'value',
                name: '',
                axisLabel: {
                    formatter: '{value} '
                },
                splitLine: {
                    lineStyle: {
                    color: "#dfdfdf",
                    type: "dashed"
                    }
                }
                }
            ],
            dataZoom: [{
                type: 'inside',
                start: 0,      /*时间调节放在最后面90%-100%*/
                end: 100
            }, {
                start: 0,
                end: 100,
                show: false,
                handleIcon: 'M10.7,11.9v-1.3H9.3v1.3c-4.9,0.3-8.8,4.4-8.8,9.4c0,5,3.9,9.1,8.8,9.4v1.3h1.3v-1.3c4.9-0.3,8.8-4.4,8.8-9.4C19.5,16.3,15.6,12.2,10.7,11.9z M13.3,24.4H6.7V23h6.6V24.4z M13.3,19.6H6.7v-1.4h6.6V19.6z',
                handleSize: '80%',
                handleStyle: {
                color: '#fff',
                shadowBlur: 3,
                shadowColor: 'rgba(0, 0, 0, 0.6)',
                shadowOffsetX: 2,
                shadowOffsetY: 2
                }
            }],
            visualMap: {
                top: 10,
                right: 10,
                show: false,
                pieces: [
                {
                    gt: -1000,
                    lte: 0,
                    color: '#f25137'
                },
                {
                    gt: 0,
                    lte: 1000,
                    color: '#0098f0'
                }
                ]
            },
            series: [
                {
                animation: true,
                animationDuration: 300,
                name: '浮盈/浮亏',
                type: 'line',
                smooth: true,
                showSymbol: false,
                lineStyle: {
                    normal: {
                    width: 2
                    }
                },
                itemStyle: {
                    normal: {
                    color: '#f25137'
                    }
                },
                // areaStyle: {
                //   normal: {
                //     color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [{
                //       offset: 0,
                //       color: '#008cdd'
                //     },
                //     {
                //       offset: 1,
                //       color: '#9cd6f7'
                //     }
                //     ])
                //   }
                // },
                data: xy.map(function (item) {
                    return item[1];
                })
                }
            ]
            };
            if (option && typeof option === "object") {
            myChart.setOption(option, true);

            }
             }
        }
    };

    var allchart5 = {
        chart: function (geturl, obj) {
            if(chart5_data.length>1){
                allchart5.setOption(chart5_data, obj);
            } else {
                 $('#'+obj).html('<img src="__STATIC__image/chart_none.jpg" class="chart_none">')
            }
            $("#" + obj).parent().find(".chartload").hide();
            $("#" + obj).css("visibility", "visible");
        },
        setOption: function (d, obj) {
            if(chart5_data.length>1){
            var xy = d;
            var dom = document.getElementById(obj);
            var myChart = echarts.init(dom);
            var option = {
            tooltip: {
                trigger: 'axis',
                triggerOn: 'mousemove',
                formatter: function (params) {
                var paramsNum = params[0].value;
                return params[0].value + '%';
                },
                backgroundColor: '#0080ff',
                borderWidth: '0',
                textStyle: {
                fontSize: 12,
                color: '#fff'
                }
            },
            grid: {
                left: 90,
                right: 40,
                top: 50,
                bottom: 50,
            },
            xAxis: {
                type: "category",
                name: "日期",
                scale: true,
                axisLine: {
                onZero: true
                },
                data: xy.map(function (item) {
                return item[0]
                }),
                splitLine: {
                show: true,
                lineStyle: {
                    color: "#dfdfdf",
                    type: "dashed"
                }
                }
            },
            yAxis: [
                {
                type: 'value',
                name: '仓位,%',
                nameTextStyle: {
                    color: '#0080ff'
                },
                axisLabel: {
                    formatter: '{value} '
                },
                splitLine: {
                    lineStyle: {
                    color: "#dfdfdf",
                    type: "dashed"
                    }
                }
                }
            ],
            dataZoom: [{
                type: 'inside',
                start: 0,      /*时间调节放在最后面90%-100%*/
                end: 100
            }, {
                start: 0,
                end: 100,
                show: false,
                handleIcon: 'M10.7,11.9v-1.3H9.3v1.3c-4.9,0.3-8.8,4.4-8.8,9.4c0,5,3.9,9.1,8.8,9.4v1.3h1.3v-1.3c4.9-0.3,8.8-4.4,8.8-9.4C19.5,16.3,15.6,12.2,10.7,11.9z M13.3,24.4H6.7V23h6.6V24.4z M13.3,19.6H6.7v-1.4h6.6V19.6z',
                handleSize: '80%',
                handleStyle: {
                color: '#fff',
                shadowBlur: 3,
                shadowColor: 'rgba(0, 0, 0, 0.6)',
                shadowOffsetX: 2,
                shadowOffsetY: 2
                }
            }],
            visualMap: {
                top: 10,
                right: 10,
                show: false,
                pieces: [{
                gt: 0,
                lte: 50,
                color: '#0080ff'
                }, {
                gt: 50,
                lte: 10000,
                color: '#0080ff'
                }],
                outOfRange: {
                color: '#f25137'
                }
            },
            series: [
                {
                animation: true,
                animationDuration: 300,
                name: '浮盈/浮亏，%',
                type: 'line',
                smooth: true,
                showSymbol: false,
                lineStyle: {
                    normal: {
                    width: 2
                    }
                },
                itemStyle: {
                    normal: {
                    color: '#00a2ff'
                    }
                },
                areaStyle: {
                    normal: {
                    color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [{
                        offset: 0,
                        color: '#8fc7ff'
                    },
                    {
                        offset: 1,
                        color: '#fff'
                    }
                    ])
                    }
                },
                data: xy.map(function (item) {
                    return item[1];
                })
                }
            ]
            };
            if (option && typeof option === "object") {
            myChart.setOption(option, true);

            }
            }
        }
    };

    var allchart6 = {
        chart: function (geturl, obj) {
            $.ajax({
            url: geturl,
            type: 'get',
            dataType: 'jsonp',
            jsonp: 'callback',
            success: function (body) {
                if (body.code == 200) {
                var data = body.data;
                myData = [];
                databeast = [];
                databeauty = [];
                if (data == null) {
                    $('#'+obj).html('<img src="__STATIC__image/chart_none.jpg" class="chart_none">')
                } else {
                    for (var i in data) {
                    myData.push([data[i].symbol + '(' + data[i].num + ')']);
                    databeauty.push(-data[i].buy);
                    databeast.push(data[i].sell);
                    }
                }
                allchart6.setOption(myData, databeast, databeauty, obj);
                }
            },
            complete: function () {
                $("#" + obj).parent().find(".chartload").hide();
                $("#" + obj).css("visibility", "visible");
                $(obj).next().show();
            }
            });
        },
        setOption: function (myData, databeast, databeauty, obj) {
            if(myData.length>0){
            var dom = document.getElementById(obj);
            var myChart = echarts.init(dom);
            var xAxisData = [];
            var itemStyle = {
            normal: {
                color: '#0098f0'
            },
            emphasis: {
                barBorderWidth: 1,
                shadowBlur: 10,
                shadowOffsetX: 0,
                shadowOffsetY: 0,
                shadowColor: 'rgba(0,0,0,0.5)'
            }
            };
            var itemStyle2 = {
            normal: {
                color: '#f25137'
            },
            emphasis: {
                barBorderWidth: 1,
                shadowBlur: 10,
                shadowOffsetX: 0,
                shadowOffsetY: 0,
                shadowColor: 'rgba(0,0,0,0.5)'
            }
            };
            var option = {
            backgroundColor: '#fff',
            legend: {
                y: 'bottom',
                data: ['进', '出']
            },
            tooltip: {
                formatter: function (params) {
                if (params.value < 0) {
                    return 'Buy: ' + -params.value
                } else {
                    return 'Sell: ' + params.value
                }
                }
            },
            dataZoom: [{
                type: 'inside',
                start: 0,      /*时间调节放在最后面90%-100%*/
                end: 100
            }, {
                start: 0,
                end: 100,
                show: false,
                handleIcon: 'M10.7,11.9v-1.3H9.3v1.3c-4.9,0.3-8.8,4.4-8.8,9.4c0,5,3.9,9.1,8.8,9.4v1.3h1.3v-1.3c4.9-0.3,8.8-4.4,8.8-9.4C19.5,16.3,15.6,12.2,10.7,11.9z M13.3,24.4H6.7V23h6.6V24.4z M13.3,19.6H6.7v-1.4h6.6V19.6z',
                handleSize: '80%',
                handleStyle: {
                color: '#fff',
                shadowBlur: 3,
                shadowColor: 'rgba(0, 0, 0, 0.6)',
                shadowOffsetX: 2,
                shadowOffsetY: 2
                }
            }],
            xAxis: {
                data: xAxisData,
                name: '品种',
                silent: false,
                type: 'category',
                data: myData
            },
            yAxis: [{
                inverse: true,
                name: '',
                axisLabel: {
                formatter: function (value) {
                    if (value < 0) {
                    return -value
                    } else {
                    return value
                    }
                }
                },
                splitLine: {
                show: true,
                lineStyle: {
                    color: "#dfdfdf",
                    type: "dashed"
                }
                }

            }, {
                inverse: true,
                name: '数量',
                position: 'left',
                nameLocation: 'start',
                splitArea: {
                show: false
                },
                splitLine: {
                show: true,
                lineStyle: {
                    color: "#dfdfdf",
                    type: "dashed"
                }
                }
            }],
            grid: {
                left: 90,
                right: 40,
                top: 50,
                bottom: 50,
            },
            series: [{
                name: '数量',
                type: 'bar',
                stack: 'one',
                itemStyle: itemStyle2,
                data: databeast
            }, {
                name: '品种',
                type: 'bar',
                stack: 'one',
                itemStyle: itemStyle,
                data: databeauty
            }]
            };
            if (option && typeof option === "object") {
            myChart.setOption(option, true);

            }
            }
        }
    };