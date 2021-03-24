
var n=0;
$.fn.dataStatistics = function(options){
  n++;
    options = $.extend({  
        min  : 100,       //初始数值
        max  : 150, //最大数字  
        time : 60000,  //时长
        len:6 //数字是几位数
    },options || {}); 
    
    var ths = this;//解决this指向问题
    
    //初始化---------------------------------------start
      
      var el = ths.find('.set_last');
      var html = '<div class="digit">' +
                          '  <div class="digit_top">' +
                          '    <span class="digit_wrap"></span>' +
                          '  </div>' +
                          '  <div class="shadow_top"></div>' +
                          '  <div class="digit_bottom">' +
                          '    <span class="digit_wrap"></span>' +
                          '  </div>' +
                          '  <div class="shadow_bottom"></div>' +
                          '</div>'
      //初始化值
      var nowNums=zfill(options.min, options.len).toString().split("");
      
      //补0
      function zfill(num, size) {
              var s = "000000000" + num;
              return s.substr(s.length-size);
      }
      //ths.find('.digit_set').html(html);
      if(n==1){
          ths.find('.digit_set').each(function() {
           
              for(i=0; i<=9; i++) {
                //$(this).find('.digit')[i].remove();
                $(this).append(html);
                currentDigit = $(this).find('.digit')[i];
                $(currentDigit).find('.digit_wrap').html(i);
              }
            
        });
      }
  //初始化数值填入
  $(".digit").removeClass("active");
  $(".digit").removeClass("previous");
  $.each(nowNums, function(index,val) {
       var set =ths.find('.digit_set').eq(index);
       var i =parseInt(val)
       set.find('.digit').eq(i).addClass('active');
       set.find('.digit').eq(i+1).addClass('previous');
       set.find(".digit_wrap").html(val);
  });
  
};



