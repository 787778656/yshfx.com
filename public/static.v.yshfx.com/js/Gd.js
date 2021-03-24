$(function () {
    

    //交易动态数据滚动
    var lists = $("#lists1");
    var count = lists.children().length;
    if (count > 5) {
        setInterval(function () {
            var listAll = lists.children();
            var last = $(listAll[listAll.length - 1]);
            var first = $(listAll[0]);
            first.slideDown("slow");
            first.addClass('b_b_c');
            first.next().removeClass('b_b_c');
            setTimeout(function () {
                last.remove();
                last.css({ display: 'none' });
                last.prependTo(lists);
            }, 1000);
        }, 6000);
    }
   

});