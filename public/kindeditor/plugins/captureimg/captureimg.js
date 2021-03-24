KindEditor.plugin('captureimg', function(K) {
    var editor = this, name = 'captureimg';
    // 点击图标时执行
    editor.clickToolbar(name, function() {
        K("#loading").show();
        var str = editor.html();
        var imgReg = /<img.*?(?:>|\/>)/gi; //匹配出img整个链接
        var srcReg = /src=[\'\"](((?!sgamer\.com)[^\'\"])*)[\'\"]/i; //匹配出SRC标签
        var arr = str.match(imgReg);//匹配文本里的img图片
        if(arr == null){
            alert("无图片需要抓取");
            K("#loading").hide();
            return;
        }//判断文本里是否有图片
        
        var url = '../index.php?m=Index&a=getImgHost&randid=Math.random()';
        var oldsrc = Array();
        var newsrc = Array();
        var arr_length = arr.length;
        for (var i = 0; i < arr_length; i++) {
            var src = arr[i].match(srcReg); // src[1]图片路径;
            if(src!=null){
                oldsrc[i] = src[1];
            }else{
                oldsrc[i]="";
            }
        }
        var newstr = str;
        var oldsrc_length = oldsrc.length;
        K("#count").html(oldsrc_length);
        for (var j = 0; j < oldsrc_length; j++) {
            var option = {
                imgurl : oldsrc[j]
            }
            $.ajax({
                type : "POST",
                url : url,
                async : false,
                data : option,
                success : function (data) {
                    if (data.error == 0) {
                        K("#num").html(parseInt(j) + 1);
                        newstr = newstr.replace(oldsrc[j], data.msg);
                    }
                }
            });
        }//ajax后台处理图片路径
        editor.html(newstr);
        alert("抓取完成");
        K("#loading").hide();
    });
});