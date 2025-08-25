jQuery(function($) {	

/* to top */
	$('.js_to_top').on('click', function() {
		
		$('body, html').animate({scrollTop : 0},500);
		
		return false;	
	});
/* end to top */	
		
	$(document).on('click', '.js_slide_menu', function() {
		
		var id = $(this).attr('href').replace('#', '');
		var act_id = $('#' + id);
		
		if ($(this).hasClass('active')) {
			$('.js_slide_menu, .slide_window').removeClass('active');
			var hei = '0px';
		} else {
			$('.js_slide_menu, .slide_window').removeClass('active');
			$(this).addClass('active');
			act_id.addClass('active');
			var hei = act_id.height();
		}
		
		$('.slide_window.toleft:not(.active)').animate({'left': '-1000px'}, 500);
		$('.slide_window.toright:not(.active)').animate({'right': '-1000px'}, 500);
		$('.slide_window.toleft.active').animate({'left': '0px'}, 500);
		$('.slide_window.toright.active').animate({'right': '0px'}, 500);
		$('#content_wrap').animate({'min-height': hei}, 500);
		
		return false;
	});

    $(document).click(function(event) {
        if ($(event.target).closest(".js_slide_menu, .slide_window").length) { return; }

		$('.js_slide_menu, .slide_window').removeClass('active');
		$('.slide_window.toleft').animate({'left': '-1000px'}, 500);
		$('.slide_window.toright').animate({'right': '-1000px'}, 500);
		$('#content_wrap').animate({'min-height': '0px'}, 500);
		
        event.stopPropagation();
    });	

	$('table').each(function() {
	    $(this).find('th:first').addClass('th1');
		$(this).find('th:last').addClass('th2');
	    $(this).find('tr:last').find('td:first').addClass('td1');
		$(this).find('tr:last').find('td:last').addClass('td2');	
	});		
	
	$(document).JcheckboxInit();
	$(document).Jcheckbox();
		
	$(document).AdaptiveTable({trigger : '.pntable table'});
});