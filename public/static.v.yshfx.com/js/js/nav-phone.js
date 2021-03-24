

/**PC导航**/
$(function(){

	$('.language').mouseenter(function(){
		var $this = $(this);
		$this
			.find('.nf-ol')
			.css('display', 'block')
			.addClass('animated-fast fadeInUpMenu')

	}).mouseleave(function(){

		var $this = $(this);
		$this
			.find('.nf-ol')
			.css('display', 'none')
			.removeClass('animated-fast fadeInUpMenu');
	});


});

$(function(){

	$('.pc-nav li').mouseenter(function(){
		var $this = $(this);
		$this
			.find('.dropdown')
			.css('display', 'block')
			.addClass('animated-fast fadeInUpMenu')

	}).mouseleave(function(){

		var $this = $(this);
		$this
			.find('.dropdown')
			.css('display', 'none')
			.removeClass('animated-fast fadeInUpMenu');
	});


});

/*******手机端点击弹出导航********/
$(function(){
	$('.menubtn').click(function(){//给d1绑定一个点击事件;
	        /*这个判断的意义是,如果d2是隐藏的,那么让它显示出来,并将d1的文本内容替换成收起,
	        如果是显示的,那么就隐藏它并将d1的文本内容替换为展开;*/
	        if($('.menu').is(':hidden'))
	        {
	          $('.menu').slideDown('slow');
	          $(this).addClass("o");
	        }else{
	          $('.menu').slideUp('slow');
	          $(this).removeClass("o");
	            }

	});
});

/*下拉*/
$(function() {
    var Accordion = function(el, multiple) {
        this.el = el || {};
        this.multiple = multiple || false;

        // Variables privadas
        var links = this.el.find('.menu-list li a');
        // Evento
        links.on('click', {el: this.el, multiple: this.multiple}, this.dropdown)
    }
    Accordion.prototype.dropdown = function(e) {
        var $el = e.data.el;
        $this = $(this),
            $next = $this.next();

        $next.slideToggle();
        $this.parent().toggleClass('open');

        if (!e.data.multiple) {
            $el.find('.dropdown').not($next).slideUp().parent().removeClass('open');
        };
    }
    var accordion = new Accordion($('#menu'), false);
});






