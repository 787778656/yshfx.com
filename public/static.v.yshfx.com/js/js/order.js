var API = "http://api.yshfx.com/";
 var d = [];
var chart1_data = [];
var chart2_data = [];
var chart22_data = [];
var chart3_data = [];
var chart4_data = [];
var chart5_data = [];
var myData = [];
var databeast = [];
var databeauty = [];
var u = API+'v1.web/history?account=' + account;
chart1_url = u;
chart2_url = API+"v1.web/history_profit?time=day&account=" + account;
chart22_url = API+"v1.web/history_lots?time=day&account=" + account;
chart3_url = API+"v1.web/history_total?account=" + account;
chart4_url = chart3_url;
chart5_url = chart3_url;
chart6_url = API+"v1.web/history_symbol?account=" + account;
$(function () {
  $(".confirm_close").click(function () {
    $(".confirmAlert").hide();
    $(".confirmBox").hide();
  });

  transaction.chart(u, "chart");
  var n = 1;
  //  导航
  $("#jy").click(function () {
    $(".o_main").removeClass("o_main_active");
    $(".orh_normal").removeClass("orh_active");
    $(".orh_normal_p").removeClass("orh_active_p");
    $(this).addClass("orh_active");
    $(this).find(".orh_normal_p").addClass("orh_active_p");
    $(".or_body").hide();
    $(".transaction").fadeIn();
    transaction.setOption(d, "chart");
  });
  $("#tb").click(function () {
    if (n == 1) {
      n++;
      allchart1.chart(chart1_url, "allchart1");
      allchart2.chart(chart2_url, "allchart2");
      allchart22.chart(chart22_url, "allchart22");
      allchart3.chart(chart3_url, "allchart3");
      allchart6.chart(chart6_url, "allchart6");
    }
    $(".o_main").addClass("o_main_active");
    $(".orh_normal").removeClass("orh_active");
    $(".orh_normal_p").removeClass("orh_active_p");
    $(this).addClass("orh_active");
    $(this).find(".orh_normal_p").addClass("orh_active_p");
    $(".or_body").hide();
    $(".allchart").fadeIn();
    allchart1.setOption(chart1_data, "allchart1");
    allchart2.setOption(chart2_data, "allchart2");
    allchart22.setOption(chart22_data, "allchart22");
    allchart3.setOption(chart3_data, "allchart3");
    allchart4.setOption(chart4_data, "allchart4");
    allchart5.setOption(chart5_data, "allchart5");
    allchart6.setOption(myData, databeast, databeauty, "allchart6");
  });
  $("#ls").click(function () {
    $(".o_main").removeClass("o_main_active");
    $(".orh_normal").removeClass("orh_active");
    $(".orh_normal_p").removeClass("orh_active_p");
    $(this).addClass("orh_active");
    $(this).find(".orh_normal_p").addClass("orh_active_p");
    $(".or_body").hide();
    $(".historycontent").fadeIn();
  });
  $("#cc").click(function () {
    $(".o_main").removeClass("o_main_active");
    $(".orh_normal").removeClass("orh_active");
    $(".orh_normal_p").removeClass("orh_active_p");
    $(this).addClass("orh_active");
    $(this).find(".orh_normal_p").addClass("orh_active_p");
    $(".or_body").hide();
    $(".positionContent").fadeIn();
  });
  $("#pl").click(function () {
    $(".o_main").removeClass("o_main_active");
    $(".orh_normal").removeClass("orh_active");
    $(".orh_normal_p").removeClass("orh_active_p");
    $(this).addClass("orh_active");
    $(this).find(".orh_normal_p").addClass("orh_active_p");
    $(".or_body").hide();
    $(".comment").fadeIn();
  });
  $("#gsz").click(function () {
    $(".o_main").removeClass("o_main_active");
    $(".orh_normal").removeClass("orh_active");
    $(".orh_normal_p").removeClass("orh_active_p");
    $(this).addClass("orh_active");
    $(this).find(".orh_normal_p").addClass("orh_active_p");
    $(".or_body").hide();
    $(".follower").fadeIn();
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
                color: '#ffffff'
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
        }
      },
      complete: function () {
        $("#" + obj).parent().find(".chartload").hide();
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
            chart2_data[0] = [0, 0];
          } else {
            for (var i = 0; i < data.length; i++) {
              chart2_data.push([data[i].date, parseFloat(data[i].profit).toFixed(2)]);
            }
          }  
          allchart2.setOption(chart2_data, obj);
        }
      },
      complete: function () {
        $("#" + obj).parent().find(".chartload").hide();
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
            chart22_data[0] = [0, 0];
          } else {
            for (var i = 0; i < data.length; i++) {
              chart22_data.push([data[i].date, parseFloat(data[i].lots).toFixed(2)]);
            }
          }  
          allchart22.setOption(chart22_data, obj);
        }
      },
      complete: function () {
        $("#" + obj).parent().find(".chartload").hide();
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
            chart3_data[0] = [0, 0, 0];
            chart4_data[0] = [0, 0];
            chart5_data[0] = [0, 0];
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
        allchart3.setOption(chart3_data, obj);
        allchart4.chart(chart4_url, "allchart4");
        allchart5.chart(chart5_url, "allchart5");
        $("#" + obj).parent().find(".chartload").hide();
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
};

var allchart4 = {
  chart: function (geturl, obj) {
    allchart4.setOption(chart4_data, obj);
    $("#" + obj).parent().find(".chartload").hide();
    $("#" + obj).css("visibility", "visible");
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
};

var allchart5 = {
  chart: function (geturl, obj) {
    allchart5.setOption(chart5_data, obj);
    $("#" + obj).parent().find(".chartload").hide();
    $("#" + obj).css("visibility", "visible");
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
            myData[0] = [0];
            databeast[0] = 0;
            databeauty[0] = -0;
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
};