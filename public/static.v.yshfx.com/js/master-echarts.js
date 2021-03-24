   /**
 * Created by Administrator on 2017/10/25 0025.
 */
$(function(){
    setChart(0);
});
function setChart(start){
    var chartbox=document.getElementsByClassName("signal_chart");
    for(var n=start;n<chartbox.length;n++){
        chart(chartbox[n].getAttribute("data-chart"),chartbox[n]);
    }
}
function chart(chart,obj){
    var d=chart.split(",");
    var xy=[];
    for(var i=0;i<d.length;i++){
        xy[i]=[i,d[i]];
    }
    var dom = obj;
    var myChart = echarts.init(dom);
    var option = {
        tooltip: {
            trigger: 'axis',
            triggerOn: 'mousemove',
            formatter: function (params) {
            },
            axisPointer:{
                type:'line',
                lineStyle:{
                    width:0
                }
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
            show:true,
            left:-6,
            right:-5,
            top:0,
            bottom:0,
            borderColor:'#f5f2f2'
        },
        xAxis: {
            type: "category",
            //show:false,
            //scale: true,
            axisLine: {
                show:false
            },
            axisTick:{
                show:false
            },
            data: xy.map(function (item) {
                return item[0]
            }),
            splitLine: {
                show: true,
                interval:1,
                lineStyle: {
                    color: "#f5f2f2",
                    type: "solid"
                }
            }
        },
        yAxis: [
            {
                type: 'value',
                //show:false,
                axisLabel: {
                    formatter: '{value} '
                },
                axisLine: {
                    show:false
                },
                splitLine: {
                    show: true,
                    interval:1,
                    lineStyle: {
                        color: "#f5f2f2",
                        type: "solid"
                    }
                }
            }
        ],
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
                            color: 'rgba(255,144,0,0.3)'
                        },
                            {
                                offset: 1,
                                color: 'rgba(255, 255, 255,0.3)'
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