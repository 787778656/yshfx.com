var d = [];
var u ;
var API = "http://api.yshfx.com/";
$(function () {
  $(".confirm_close").click(function () {
    $(".confirmAlert").hide();
    $(".confirmBox").hide();
  });
  var account = $("input[name='account']").val();
  u = API+'v1.web/history?account=' + account;
  transaction.chart(u, "chart");
  //var n = 1;
  //  导航
  $("#jy").click(function () {
    $(".o_main").removeClass("o_main_active");
    $(".orh_normal").removeClass("orh_active");
    $(".orh_normal_p").removeClass("orh_active_p");
    $(this).addClass("orh_active");
    $(this).find(".orh_normal_p").addClass("orh_active_p");
    $(".or_bg2").hide();
    $(".or_bg1").show();
    $(this).find(".or_bg2").show();
    $(this).find(".or_bg1").hide();
    $(".or_body").hide();
    $(".transaction").fadeIn();
    $(".follow_mon").hide();
    transaction.setOption(d, "chart");
  });
  $("#ls").click(function () {
    $(".o_main").removeClass("o_main_active");
    $(".orh_normal").removeClass("orh_active");
    $(".orh_normal_p").removeClass("orh_active_p");
    $(this).addClass("orh_active");
    $(this).find(".orh_normal_p").addClass("orh_active_p");
    $(".or_bg2").hide();
    $(".or_bg1").show();
    $(this).find(".or_bg2").show();
    $(this).find(".or_bg1").hide();
    $(".or_body").hide();
    $(".historycontent").fadeIn();
    $(".follow_mon").hide();
  });
});


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
  }
};
