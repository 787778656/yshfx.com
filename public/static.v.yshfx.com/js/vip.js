var API = "http://api.yshfx.com/";
var uid =$("#uid").attr("data-uid");
var amount1=999;
var amount2=1599;
var amount3=1999;
$(function(){
  if(uid == 0){
      $("#tovip1,#tovip2,#tovip3").html('服务收费').css({"background":"#611987","color":"#fff"});
      //$(".vipwindow1").css({"height":"380px","margin-top":"-190px"});
      $(".paymoney").hide();
      $(".vip1_login").show();
      $(".vip1_login").on("click", function () {
        $(".vip_window").hide();
        $("#vipcontent1").hide();
        $(".no_login").hide();
        $(".vip_login").fadeIn();
      });
      /*$("#button").click(function(){
        location.reload();
      });*/
  }
  $("#tovip1").click(function(){
    submit(uid,'vip1',amount1,"#wechatvip1");
    windowalert("#vipcontent1");
  });
  $("#tovip2").click(function(){
    submit(uid,'vip2',amount2,"#wechatvip2");
    windowalert("#vipcontent2-1");
  });
  $("#tovip3").click(function(){
    submit(uid,'vip3',amount3,"#wechatvip3");
    windowalert("#vipcontent3")
  });
  $("#freepay").click(function(){
    windowalert("#vipcontent2")
  });
  $(".window_close").click(function(){
    $(".vip_window").hide();
    $(".vipwindow1").hide();
    if(uid == 0){

    }else{
      location.reload();
    }
  });
  /*价格*/
  $(".vip_price_li").click(function(){
    $(".vip_price_li").removeClass("vip_price_active");
    $(".vipwindow_tip").removeClass("vipwindow_tip2");
    $(this).addClass("vip_price_active").find(".vipwindow_tip").addClass("vipwindow_tip2");
    $(".vip_money1").html($(this).find(".vip_price1 span").html());
    amount1=$(this).find(".vip_price1 span").html();
    submit(uid,'vip1',amount1,"#wechatvip1");
  });
  $(".vip_price_li2").click(function(){
    $(".vip_price_li2").removeClass("vip_price_active");
    $(".vipwindow_tip22").removeClass("vipwindow_tip2");
    $(this).addClass("vip_price_active").find(".vipwindow_tip22").addClass("vipwindow_tip2");
    $(".vip_money2").html($(this).find(".vip_price2 span").html());
    amount2=$(this).find(".vip_price2 span").html();
    submit(uid,'vip2',amount2,"#wechatvip2");
  });
  $(".vip_price_li3").click(function(){
    $(".vip_price_li3").removeClass("vip_price_active");
    $(".vipwindow_tip33").removeClass("vipwindow_tip2");
    $(this).addClass("vip_price_active").find(".vipwindow_tip33").addClass("vipwindow_tip2");
    $(".vip_money3").html($(this).find(".vip_price3 span").html());
    amount3=$(this).find(".vip_price3 span").html();
    submit(uid,'vip3',amount3,"#wechatvip3");
  });
  /*vip2*/
  $("#tobuyvip2").click(function(){
    windowalert("#vipcontent2-1");
  });
  /*还未购买*/
  $("#notbuy").click(function(){
    windowalert("#vipcontent1");
  })
});

function windowalert(obj){
  $(".vip_window").show();
  $(".vipwindow1").hide();
  $(obj).show();
}
function submit(uid,server,amount,obj){
  if(uid == 0){
    
  }else{
    var src=API+'pay.wxpay/qrcode.html?uid='+uid+'&server='+server+'&amount='+amount;
    $(obj).attr("src",src);
  }
}
