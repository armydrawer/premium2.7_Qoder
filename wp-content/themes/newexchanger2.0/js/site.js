jQuery(function($){

	// Marquee3k.init();

	function bodyLoad() {
		const $html  = $('html');
		const $body  = $('body');
		const $both  = $html.add($body);

		const $darkMode     = $('.header__dark-mode');
		const $lightMode    = $('.dark-mode_mob');
		const $darkModeDesc = $('.header__dark-mode');

		const mode = Number($body.data('theme-mode')) || 0;

		// $body.css('visibility', 'visible');

		function setTheme(t) {
			$both.removeClass('light dark').addClass(t);
		}

		if (mode === 0) { setTheme('light');  localStorage.removeItem('theme'); return; }
		if (mode === 1) { setTheme('dark');   localStorage.removeItem('theme'); return; }
		if (mode === 2) {
			$body.addClass('switcher');
		} else {
			$body.removeClass('switcher');
		}

		if (localStorage.getItem('theme') === 'dark') {
			$darkMode.removeClass('light');
			$lightMode.removeClass('light');
			setTheme('dark');
		} else {
			$darkMode.addClass('light');
			$lightMode.addClass('light');
			setTheme('light');
		}

		$darkMode.on('click', function () {
			const nowLight = $html.hasClass('light');
			if (nowLight) {
				$darkMode.removeClass('light');
				$darkModeDesc.removeClass('light');
				setTheme('dark');
				localStorage.setItem('theme', 'dark');
			} else {
				$darkMode.addClass('light');
				$lightMode.addClass('light');
				$darkModeDesc.addClass('light');
				setTheme('light');
				localStorage.setItem('theme', 'light');
			}
		});
	}
	bodyLoad();

	function oncetoggle() {
		$('.oncetoggle').on('click', function () {
			$(this).toggleClass('active');
		})
	}
	oncetoggle();

	//menu
	function menuOpen() {
		const $menuBtn = $('.main-menu-btn');
		const $body = $('body');
		const $btnClose = $('.menu-close-btn');
		$menuBtn.on('click', function ()  {
			$body.toggleClass('menu-open');
		})
		$btnClose.on('click', function () {
			$body.removeClass('menu-open');
		})
	}
	menuOpen();

	//exchange 0 move minmax
	function moveXchangeMinMax () {
		if($('.xchange_div .span_give_max').length) {
			$('.xchange_div .js_course_html').parent().parent().addClass('xchange_data_course');
			$('.xchange_div .span_give_max').parent().parent().addClass('xchange_data_give_max');
			$('.xchange_div_col_give .xchange_select').parent().addClass('xchange_data_give_select');
			$('.xchange_data_give_max').insertAfter($('.xchange_div_col_give .xchange_select'));
			// $('.xchange_data_give_max').insertAfter($('.xchange_div_col_give .sumcommiss_wrapper'));
		}
		// if($('.xchange_data_course .js_course_html').length && window.innerWidth > 640) {
		// 	$('.xchange_data_course').appendTo($('.xchange_data_title.give'));
		// } else {
		// 	$('.xchange_data_course').prependTo($('.xchange_div_col_give .xchange_data_ins'));
		// }
		if($('.xchange_div .span_get_max').length) {
			$('.xchange_div .span_skidka').parent().parent().parent().addClass('xchange_data_discount');
			$('.xchange_div .span_get_max').parent().parent().addClass('xchange_data_get_max');
			$('.xchange_div_col_get .xchange_select').parent().addClass('xchange_data_get_select');
			$('.xchange_data_get_max').insertAfter($('.xchange_div_col_get .xchange_select'));
			// $('.xchange_data_get_max').insertAfter($('.xchange_div_col_get .sumcommiss_wrapper'));
		}
		// if($('.xchange_div .xchange_data_discount .span_skidka').length && window.innerWidth > 640) {
		// 	$('.xchange_div .xchange_data_discount').appendTo($('.xchange_data_title.get'));
		// } else {
		// 	$('.xchange_div .xchange_data_discount').prependTo($('.xchange_div_col_get .xchange_data_ins'));
		// }
	}
	moveXchangeMinMax();


	//show more home reserve
	function showMore() {
		const $showMoreReserve = $('.btn-reserve');
		const $homeReserveItems = $('.home-reserve .reserve-item.to-hide');

		$showMoreReserve.on('click', function (event) {
			event.preventDefault();
			if (event.target.text === event.target.dataset.hideText) {
				$showMoreReserve.text(event.target.dataset.showText)

			} else {
				$showMoreReserve.text(event.target.dataset.hideText)
			}
			$homeReserveItems.each( function (item) {
				$(this).toggleClass('hidden')
			})
		})

	}
	showMore();

	/* lang */
	$('.langlist_title').on('click', function(){
		$('.langlist_ul').toggle();
		$('.langlist_title').toggleClass('active');
	});

	$(document).on('click', function(event) {
		if ($(event.target).closest(".langlist_title, .langlist_ul").length) return;
		$('.langlist_ul').hide();
		$('.langlist_title').removeClass('active');
	});
	/* end lang */
	/* WRAP ITEMS FOR SCROLLBAR COLUMNS (TABLE 1, 4, 5) */
	$('.tbl1 .js_item_left, .tbl4 .js_item_left, .tbl5 .js_item_left').wrapAll('<div class="scroll-wrapper"></div>')

	/* 2nd table MOVING TAGS TO [TITLE & CURRENCIES SECTION] */
	const moveTags = () => {
		$('.xtp_left_col_icon').prependTo($('.xtp_left_col_table .xtp_table_ins'))
		$('.xtp_right_col_icon').prependTo($('.xtp_right_col_table .xtp_table_ins'))
	}

	const moveTagsIcons2Bottom = () => {
		return

		//clear containers (for refresh DOM after ajax)
		$('.left_tags_icons_wrapper').remove()
		$('.right_tags_icons_wrapper').remove()

		// take tags, take_icons, wrapAll, appendTo(xtp_icon_wrap)
		// -left side
		$('.xtp_left_col_icon').wrapAll('<div class="left_tags_icons_wrapper"></div>')
		// -right side
		$('.xtp_right_col_icon').wrapAll('<div class="right_tags_icons_wrapper"></div>')
		// -move them to top
		$('.left_tags_icons_wrapper').prependTo($('.xtp_left_col_table'))
		$('.right_tags_icons_wrapper').prependTo($('.xtp_right_col_table'))
	}
	//moveTagsIcons2Bottom()

	/* STAGES */
	// /* SHOW H1 */
	// if ($('#exchange_status_html').length) {
	// 	$('.main-title').show()
	// }

	/* HIDE H1 AT MAIN EXCHANGE PAGE */
	if ($('.xchange_div').length && !$('body.home').length) {
		$('.main-title').hide();
	}
	/* HIDE H1 AT STEP2 EXCHANGE PAGE */
	if ($('.block_xchangedata').length && !$('body.home').length) {
		$('.main-title').hide();
	}
	/* STAGES. MOVE PAYINFO TO TOP */
	$('.block_payinfo').prependTo('#exchange_status_html');
	$('.notice_message').prependTo('#exchange_status_html');

	/* MOVE UPDATE BUTTON TO BOTTOM */
	$('.block_paybutton_merch').insertAfter('.block_statusbids');

	/* MOVE STATUS TEXT TO TOP */
	const moveStatusText = () => {
		$('.block_status').prependTo('.block_statusbids_ins');
	}
	moveStatusText();

	/* SWITCHER */
	const createSwitcher = () => {
		const updatesLink = $('.block_paybutton_merch_ins a');
		const switcher = $('<div class="switcher_wrapper"><div class="switcher"><div class="bullet"></div></div><div class="switcher_text"></div></div>');
		switcher.find('.switcher_text').text(updatesLink.text());
		if (updatesLink.hasClass('refresh_button_disable')) {
			switcher.find('.switcher').addClass('active');
		}

		switcher.click(()=>{
			switcher.find('.switcher').toggleClass('active')
			switcher.find('.switcher_text').text(updatesLink.text());

			window.location.href = updatesLink.attr('href')
		})

		if (!$('.block_paybutton_merch_ins').find('.switcher_wrapper').length) {
			$('.block_paybutton_merch_ins').append(switcher)
		}
	}
	createSwitcher();

	/* STYLING INPUT FILE */
	function verifyChooseBtn() {
		const chooseFileBtnText = $('.lang_constant_choose_file').text() || "";
		const choseFileBtn = $(`<div class="chose_file_btn">${chooseFileBtnText}</div>`)
		choseFileBtn.click((e)=>{
			$(e.target).prev('input').click()
		})
		if(!$('.chose_file_btn').length) {
			$('.ustbl_file').append(choseFileBtn);
			$('.verify_acc_file').append(choseFileBtn);
		}
	}
	verifyChooseBtn();

	//userwallets container
	function userwalletsContainer() {
		if($('.userwallets_form').length) {
			$(`<div class="userwallets__container"></div>`).insertBefore($('.userwallets_form'));

			$('.userwallets_form').appendTo($('.userwallets__container'));
			$('.userwallets.pntable_wrap').appendTo($('.userwallets__container'));
		}
	}
	userwalletsContainer();

	//register page split block
	function regDivSplit() {
		if($('.reg_div_wrap').length || $('.resultfalse').length) {
			$('.page_wrap .text').addClass('text--active')
		}
	}
	regDivSplit();

	//table 3 custom
	function table3Custom() {
		if($('.xchange_type_list').length) {
			$('.xchange_table_wrap').addClass('tbl3');
		}
	}
	table3Custom();

	function tablesWrapper() {
		if(!$('.xchange_type_list').length) {
			$('.xchange_table_wrap').addClass('tables');
		}
	}
	tablesWrapper();

	function moveSum1() {

		const $sum1 = $('.xchange_type_list .js_sum1').parent();
		const $sum2 = $('.xchange_type_list .js_sum2').parent();

		$sum1.prependTo($('.xtl_left_col .xtl_select_wrap'));
		$sum2.prependTo($('.xtl_right_col .xtl_select_wrap'));

		// const $title1 = $('.xtl_left_col .xtl_table_title');
		// const $title2 = $('.xtl_right_col .xtl_table_title');
		//
		// $title1.prependTo($('.xtl_left_col .xtl_selico_wrap'));
		// $title2.prependTo($('.xtl_right_col .xtl_selico_wrap'));


		if($('.xtl_table_wrap_ins') && $sum1.length < 1 || $sum2.length < 1) {
			$('.xtl_select_wrap .select_js_title').addClass('sum_empty')
		} else {
			$('.xtl_select_wrap .select_js_title').removeClass('sum_empty')
		}
	}
	// moveSum1();


	function moveSum1Error() {
		if($('.xchange_type_list .js_sum1_error').length || $('.xchange_type_list .js_sum2_error').length) {
			$('.js_sum1_error').insertAfter($('.xtl_select_wrap').eq(0));
			$('.js_sum2_error').insertAfter($('.xtl_select_wrap').eq(1));
		}

	}
	// moveSum1Error();

	function moveXchangeSumError() {
		if($('.xchange_div .js_sum1_error').length && window.innerWidth < 641 || $('.xchange_div .js_sum2_error').length && window.innerWidth < 641) {
			$('.xchange_select_parent_wrapper').addClass('error');
			$('.xchange_div .js_sum1_error').insertAfter($('.xchange_input_dropdown_wrapper').eq(0));
			$('.xchange_div .js_sum2_error').insertAfter($('.xchange_input_dropdown_wrapper').eq(1));
		}
		else {
			$('.xchange_select_parent_wrapper').removeClass('error');
			$('.xchange_div .js_sum1_error').appendTo($('.xchange_input_dropdown_wrapper').eq(0));
			$('.xchange_div .js_sum2_error').appendTo($('.xchange_input_dropdown_wrapper').eq(1));
		}
	}
	// moveXchangeSumError();

	function selectTbl3() {
		if($('.xtl_input_wrap').length < 1) {
			$('.xtl_left_col .select_js').addClass('disable');
			$('.xtl_right_col .select_js').addClass('disable');
		}
	}
	// selectTbl3();

	function tbl3RateReserve() {
		if(!($('.tbl3-rateblock').length)) {
			$(`<div class="tbl3-rateblock"></div>`).appendTo($('.xtl_table_body'));
		}
		if(!($('.tbl3-rateblock .xtl_exchange_rate').length)) {
			$('.xtl_exchange_rate').appendTo($('.xtl_left_col'));
		}
		if(!($('.tbl3-rateblock .xtl_exchange_reserve').length)) {
			$('.xtl_exchange_reserve').appendTo($('.tbl3-rateblock'));
		}
	}
	// tbl3RateReserve();

	function moveCourseTbl3() {
		if(!($('.tbl3-rateblock .xtl_exchange_rate').length)) {
			$('.xtl_exchange_rate').prependTo($('.xtl_center_col'));
		}
	}
	// moveCourseTbl3();

	function hideSidebar() {
		if($('.promo_wrap').length || $('.aside').length || $('.many_news_wrap').length || $('.single_news_wrap').length) {
			$('.aside').addClass('hidden');
			$('.inner-content').removeClass('span--lg-3');
			$('.inner-content').addClass('span--lg-4');
		}
	}
	// hideSidebar();

	function moveBanners() {
		if($('.home').length) {
			$('.banners').insertAfter($('.main-form'))
		}
	}
	// moveBanners();

	function moveErrorInputsTbl3() {
		if($('.xtl_input_wrap').hasClass('error')) {
			$('.xtl_selico_wrap').addClass('error');
		} else {
			$('.xtl_selico_wrap').removeClass('error');
		}
		// console.log($('.xtl_left_col .xtl_selico_wrap').hasClass('error'))
		if ($('.xtl_left_col .xtl_selico_wrap').hasClass('error')) {
			$('.js_sum1_error').addClass('active');
		} else {
			$('.js_sum1_error').removeClass('active');
		}
		if ($('.xtl_right_col .xtl_selico_wrap').hasClass('error')) {
			$('.js_sum2_error').addClass('active');
		} else {
			$('.js_sum2_error').removeClass('active');
		}
		// 	$('.js_sum1_error').addClass('active')
		// $('.js_sum1_error').insertAfter($('.xtl_selico_wrap')[0])

		// }
	}
	// moveErrorInputsTbl3();

	//exchange 0 custom
	function moveSumXchange() {
		const $sumX1 = $('.xchange_div .js_sum1').parent().parent().parent();
		const $selectX1 = $('.xchange_div_col_give .xchange_select');
		const $parentSelectX1 = $('.xchange_div .js_sum1').parent().parent();

		const $sumX2 = $('.xchange_div .js_sum2').parent().parent().parent();
		const $selectX2 = $('.xchange_div_col_get .xchange_select');
		const $parentSelectX2 = $('.xchange_div .js_sum2').parent().parent();

		$parentSelectX1.addClass('xchange_select_wrapper');
		$parentSelectX2.addClass('xchange_select_wrapper');

		$parentSelectX1.parent().addClass('xchange_dropdown');
		$parentSelectX2.parent().addClass('xchange_dropdown');


		if(window.innerWidth > 640 && $('.xchange_data_give_select') || window.innerWidth > 640 && !$('.xchange_data_get_select')) {
			$sumX1.prependTo($('.xchange_div_col_give .xchange_data_ins'));
			$sumX2.prependTo($('.xchange_div_col_get .xchange_data_ins'));

			$selectX1.insertAfter($('.xchange_div .js_sum1'));
			$selectX2.insertAfter($('.xchange_div .js_sum2'));

			// $('.xchange_div_col_give .xchange_data_title').prependTo($('.xchange_div .js_sum1').parent().parent());
			// $('.xchange_div_col_get .xchange_data_title').prependTo($('.xchange_div .js_sum2').parent().parent());
		} else {
			$selectX1.prependTo($('.xchange_data_give_select'));
			$selectX2.prependTo($('.xchange_data_get_select'));

			$sumX1.insertAfter($('.xchange_data_give_select'));
			$sumX2.insertAfter($('.xchange_data_get_select'));
		}


		$('.xchange_div .js_sum1').parent().addClass('xchange_input_dropdown_wrapper');
		$('.xchange_div .js_sum2').parent().addClass('xchange_input_dropdown_wrapper');
		// $('.span_give_max').parent().parent().addClass('minmax');
		// $('.span_get_max').parent().parent().addClass('minmax');

		// if(!$('.xchange_course_wrapper')[0]) {
		// 	$(`<div class="xchange_course_wrapper"></div>`).insertBefore($('.xchange_select_wrapper')[0]);
		// 	$('.xchange_div .js_course_html').parent().parent().appendTo($('.xchange_course_wrapper'));
		// 	$('.xchange_div .span_give_max').parent().parent().appendTo($('.xchange_course_wrapper'));
		// }
		// if(!$('.xchange_discount_wrapper')[0]) {
		// 	$(`<div class="xchange_discount_wrapper"></div>`).insertBefore($('.xchange_select_wrapper')[1]);
		// 	$('.xchange_div .span_skidka').parent().parent().parent().appendTo($('.xchange_discount_wrapper'));
		// 	$('.xchange_div .span_get_max').parent().parent().appendTo($('.xchange_discount_wrapper'));
		// }

		// if(!$('#exch_html .warning_message')[0]) {
		// 	$('#exch_html .xchange_div').addClass('active');
		// 	$('#exch_html .notice_message').addClass('active');
		// }
		//  else {
		// 	$('#exch_html .xchange_div').removeClass('active');
		// 	$('#exch_html .notice_message').removeClass('active');
		// }
	}
	// moveSumXchange();

	function captchaWrapper() {
		if(!$('.captcha_wrapper')[0]) {
			$(`<div class="captcha_wrapper"></div>`).appendTo($('.xchange_div_ins'));
			$(`<div class="captcha_left"></div>`).appendTo($('.captcha_wrapper'));
			$('.xchange_div_ins .captcha_div').appendTo($('.captcha_left'));
			$('.xchange_div_ins .captcha_sci_div').appendTo($('.captcha_left'));
			$('.xchange_div_ins .exchange_checkpersdata').appendTo($('.captcha_left'));
			$('.xchange_div_ins .xchange_checkdata_div').appendTo($('.captcha_left'));
			$('.xchange_div_ins .xchange_submit_div').appendTo($('.captcha_wrapper'));
		}
	}
	captchaWrapper();

	function hideFooterTitle() {
		if(!$('.footer__menu-sections').length) {
			$('.footer__nav-sections').addClass('hidden');
			$('.footer__col-1').addClass('hidden');
		}
		if(!$('.footer__menu-exchange').length) {
			$('.footer__nav-exchange').addClass('hidden');
		}
		if(!$('.footer__menu-pairs').length) {
			$('.footer__nav-pairs').addClass('hidden');
		}
		if(!$('.footer__menu-exchange').length && !$('.footer__menu-pairs').length) {
			$('.footer__col-2').addClass('hidden');
		}
	}
	// hideFooterTitle();

	function wrapperCalcCheckbox() {
		if($('.js_changecalc.jcheckbox')[0]) {
			$('.js_changecalc.jcheckbox').parent().addClass('js_changecalc-label');
			$('.js_changecalc.jcheckbox').parent().parent().parent() .addClass('js_changecalc-wrapper');
			$('js_changecalc.jcheckbox').insertBefore($('.js_changecalc-label'));
		}
	}
	// wrapperCalcCheckbox();

	function xchangeComissHide() {
		if($('.js_comis_text1').text().length <= 1) {
			$('.js_comis_text1').parent().addClass('hidden');
		}
		else {
			$('.js_comis_text1').parent().removeClass('hidden');
		}
		if($('.js_comis_text2').text().length <= 1) {
			$('.js_comis_text2').parent().addClass('hidden');
		}
		else {
			$('.js_comis_text2').parent().removeClass('hidden');
		}
	}
	xchangeComissHide();

	/* AJAX EVENTS */
	$(document).ajaxStart(()=>{

	})

	$(document).ajaxStop(()=>{
		// tbl5MinMax();
		// xchangeSumCommiss();
		moveXchangeMinMax();
		xchangeComissHide();
		// moveTagsIcons2Bottom()
		// moveTags()

		/* STAGES */
		/* STAGES. MOVE PAYINFO TO TOP */
		$('.block_payinfo').prependTo('#exchange_status_html')
		$('.notice_message').prependTo('#exchange_status_html')

		/* MOVE UPDATE BUTTON TO BOTTOM */
		$('.block_paybutton_merch').insertAfter('.block_statusbids')


		/* MOVE STATUS TEXT TO TOP */
		moveStatusText();

		/* SWITCHER */
		createSwitcher();

		// moveSum1();
		// moveSum1Error();
		// selectTbl3();
		// moveErrorInputsTbl3();
		// moveXchangeSumError();
		// moveSumXchange();
		// reserveMove();
		// tbl3RateReserve();
		// moveCourseTbl3();

		captchaWrapper();
		// wrapperCalcCheckbox();
		verifyChooseBtn();

	})


	// $('li.menu-item-has-children').click((e)=>{
	// 	e.stopPropagation()
	// 	if ($(e.target).hasClass('menu-item-has-children')) {
	// 		$(e.target).toggleClass('closed')
	// 	}
	// })
	/* END OF THEME CUSTOM INTERACTIONS */

	/* social link */
	$('.social_link').on('click', function(){
		var link_url = $(this).attr('href');
		window.open(link_url,'','menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600');

		return false;
	});
	/* end social link */

	/* c menu */
	$('.topbar_icon_tab').on('click',function(){
		$('.topbar_icon_tabul').addClass('open');
	});

	$(document).on('click', function(event) {
		if($(event.target).closest(".topbar_icon_tab, .topbar_icon_tabul").length) return;
		$('.topbar_icon_tabul').removeClass('open');
	});
	/* end c menu */

	function top_menu(){
		var hei = 0;
		if($('#wpadminbar').length > 0){
			if($('#wpadminbar').css('position') == 'fixed'){
				hei = parseInt($('#wpadminbar').height());
			}
		}
		if($('#fix_div').length > 0){
			var npos = $(window).scrollTop();
			var one = parseInt($('#fix_div').offset().top) - hei;
			var wid = $(window).width();
			if(wid >= 310){
				if(npos > one){
					$('#fix_elem').css({'position': 'fixed', 'top': hei}).addClass('fix_div');
				} else {
					$('#fix_elem').css({'position':'absolute', 'top': '0px'}).removeClass('fix_div');
				}
			} else {
				$('#fix_elem').css({'position':'absolute', 'top': '0px'}).removeClass('fix_div');
			}
		}
	}

	function contacts_menu(){
		$('.topbar_icon_wrap').removeClass('adaptive');
		var hei_start = $('.topbar').height();
		$('.topbar_icon').show();
		var hei_now = $('.topbar_contain').height();
		if(hei_now > hei_start){
			$('.topbar_icon_wrap').addClass('adaptive');
		}
	}

	function recolorBar() {
		if($(window).scrollTop() > 0) {
			$('.navigation.sticky').addClass('active');
			$('.user-bar.sticky').addClass('active');
			return
		} else {
			$('.navigation.sticky').removeClass('active');
			$('.user-bar.sticky').removeClass('active');
		}
	}
	recolorBar();

	$(window).on('scroll', function(){
		top_menu();
		// contacts_menu();
		recolorBar();
	});
	$(window).on('resize', function(){
		// xchangeSumCommiss();
		top_menu();
		// contacts_menu();
		// gridAdvantages();
		// moveSumXchange();
		// moveXchangeMinMax();
	});
	$(document).ready(function(){
		top_menu();
		// contacts_menu();
	});
	// contacts_menu();

	$('.js_menu li').hover(function(){
		$(this).find('ul:first').show('drop');
	}, function(){
		$(this).find('ul:first').stop(true,true).hide();
	});

	$('.js_menu li a').on('click', function(){
		var href = $(this).attr('href');
		if(href == '#'){
			return false;
		}
	});
	$('.sub-menu').append('<div class="ugmenu"></div>');

	var content_menu = $('.js_menu').html();
	$('.mobile_menu_ins').html(content_menu);

	$('.topmenu_ico').on('click', function(){
		$('.mobile_menu_abs, .mobile_menu').show();
	});
	$('.mobile_menu_close').on('click', function(){
		$('.mobile_menu_abs, .mobile_menu').hide();
	});

	$('table').each(function(){
		$(this).find('th:first').addClass('th1');
		$(this).find('th:last').addClass('th2');
		$(this).find('tr:last').find('td:first').addClass('td1');
		$(this).find('tr:last').find('td:last').addClass('td2');
	});

	function addNewWrapper5() {
		if($('.xchange_type_table.tbl5').length) {
			$(`<div class="xtt5_wrapper"></div>`).insertBefore($('.xchange_type_table.tbl5 .xtt_table_body_wrap .xtt_left_col_table'));
			$('.xchange_type_table.tbl5 .xtt_table_body_wrap .xtt_left_col_table').appendTo($('.xchange_type_table.tbl5 .xtt5_wrapper'));
			$('.xchange_type_table.tbl5 .xtt_table_body_wrap .xtt_right_col_table').appendTo($('.xchange_type_table.tbl5 .xtt5_wrapper'));
		}
	}
	addNewWrapper5();

	function newsImg() {
		if($('.single_news_wrap').length && $('.news-block__image img').attr('src').length > 1) {
			$('.news-block__image').addClass('news-img--active');
		}
	}
	newsImg();

	function promoTimer() {
		if($('.promo .js_timer').length) {
			const $timer = $('.js_timer');
			const days = $('.timer_days').text();
			const hours = $('.timer_hours').text();

			$timer.attr('data-d', days);
			$timer.attr('data-h', hours);
		}
	}
	promoTimer();

	$(document).JcheckboxInit();
	$(document).Jcheckbox();

	$(document).Jselect('init', {trigger: '.js_my_sel', class_ico: 'currency_logo'});

	$(document).AdaptiveTable();


});
