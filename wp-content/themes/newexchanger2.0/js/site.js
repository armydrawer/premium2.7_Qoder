jQuery(function($){

	//promo parse review data
	function promoParseReviewData() {
		$('.winner__name').text($('.constant_winner-name').text())
		$('.winner__time').text($('.constant_winner-time').text())
	}
	promoParseReviewData();

	//menu button
	function menuOpen() {
		const $menuBtn = $('.main-menu-btn');
		const $body = $('body');
		const $btnClose = $('.main-menu-btn-close');
		$menuBtn.on('click', function ()  {
			$body.toggleClass('menu-open');
		})
		$btnClose.on('click', function () {
			$body.removeClass('menu-open');
		})
	}
	menuOpen();

	//body switch theme
	function bodyLoad() {
		const $body = $('body');
		const $darkMode = $('.header__dark-mode');
		const $lightMode = $('.dark-mode__mob');
		const $darkModeDesc = $('.header__dark-mode');

		$body.css('visibility','visible');
		$body.addClass('light');

		if (localStorage.getItem('theme') === 'dark'){
			$darkMode.removeClass('light');
			$lightMode.removeClass('light');
			$body.removeClass('light');
		} else {
			$darkMode.addClass('light');
			$lightMode.addClass('light');
			$body.addClass('light');
		}
		$darkMode.on('click', function () {
			if (!$body.hasClass('light')) {
				$darkMode.addClass('light');
				$darkModeDesc.addClass('light')
				$body.addClass('light');
				localStorage.setItem('theme', 'light');
			} else {
				$darkMode.removeClass('light');
				$darkModeDesc.removeClass('light')
				$body.removeClass('light');
				localStorage.setItem('theme', 'dark');
			}
		})

	}
	bodyLoad();


	//show more home reserve
	function showMore() {

		const $showMoreReserve = $('.show-more-reserve');
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
	});

	$(document).on('click', function(event) {
		if ($(event.target).closest(".langlist_title, .langlist_ul").length) return;
		$('.langlist_ul').hide();
	});
	/* end lang */
	/* WRAP ITEMS FOR SCROLLBAR COLUMNS (TABLE 1, 4, 5) */
	$('.tbl1 .js_item_left, .tbl4 .js_item_left, .tbl5 .js_item_left').wrapAll('<div class="scroll-wrapper"></div>')

	/* 2nd table MOVING TAGS TO [TITLE & CURRENCIES SECTION] */
	const moveTags = () => {
		$('.xtp_left_col_icon').prependTo($('.xtp_left_col_table .xtp_table_ins'))
		$('.xtp_right_col_icon').prependTo($('.xtp_right_col_table .xtp_table_ins'))
	}
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

	function promoWrapTransparent() {
		if($('.promo_wrap').length) {
			$('.page_wrap').addClass('active')
		}
	}
	promoWrapTransparent();

	function hideSwiper() {
		if(!$('.swiper-slide')[0]) {
			$('.banner-swiper').addClass('hidden')
		}
	}
	hideSwiper();

	function addNewWrapper5() {
		if($('.xchange_type_table.tbl5').length) {
			$(`<div class="xtt5_wrapper"></div>`).insertBefore($('.xchange_type_table.tbl5 .xtt_table_body_wrap .xtt_left_col_table'));
			$('.xchange_type_table.tbl5 .xtt_table_body_wrap .xtt_left_col_table').appendTo($('.xchange_type_table.tbl5 .xtt5_wrapper'));
			$('.xchange_type_table.tbl5 .xtt_table_body_wrap .xtt_right_col_table').appendTo($('.xchange_type_table.tbl5 .xtt5_wrapper'));
		}
	}
	addNewWrapper5();

	//userwallets container
	function userwalletsContainer() {
		if($('.userwallets_form').length) {
			$(`<div class="userwallets__container"></div>`).insertBefore($('.userwallets_form'));

			$('.userwallets_form').appendTo($('.userwallets__container'));
			$('.userwallets.pntable_wrap').appendTo($('.userwallets__container'));
		}
	}
	userwalletsContainer();

	function gridAdvantages() {
		if(window.innerWidth > 641 && $('.icon-card').length === 1) {
			$('.grid-advantages').removeClass('col--lg-3');
		}
		if(window.innerWidth > 641 && $('.icon-card').length === 2) {
			$('.grid-advantages').removeClass('col--lg-3');
			$('.grid-advantages').addClass('col--lg-2');
		}
		if(window.innerWidth > 641 && $('.icon-card').length === 4) {
			$('.grid-advantages').removeClass('col--lg-3');
			$('.grid-advantages').addClass('col--lg-2');
		}
		if(window.innerWidth > 641 && $('.icon-card').length === 5) {
			$('.grid-advantages').removeClass('col--lg-3');
			$('.grid-advantages').addClass('grid5');
			$('.icon-card')[0].classList.add('active1');
			$('.icon-card')[1].classList.add('active2');
			$('.icon-card')[2].classList.add('active3');
			$('.icon-card')[3].classList.add('active4');
			$('.icon-card')[4].classList.add('active5');
		}
	}
	gridAdvantages();

	function dropdownItemsCount() {
		if($('.xtl_left_col .select_js_ulli ').length <= 4) {
			$('.select_js_ul_ins').addClass('active');
		} else {
			$('.select_js_ul_ins').removeClass('active');
		}
		if($('.xtl_right_col .select_js_ulli ').length <= 4) {
			$('.select_js_ul_ins').addClass('active');
		} else {
			$('.select_js_ul_ins').removeClass('active');
		}
		if($('.xchange_div_col_give .select_js_ulli ').length <= 4) {
			$('.select_js_ul_ins').addClass('active');
		} else {
			$('.select_js_ul_ins').removeClass('active');
		}
		if($('.xchange_div_col_get .select_js_ulli ').length <= 4) {
			$('.select_js_ul_ins').addClass('active');
		} else {
			$('.select_js_ul_ins').removeClass('active');
		}
	}
	// dropdownItemsCount()

	$(window).on('load', () => {
		dropdownItemsCount();
	})

	/* AJAX EVENTS */
	$(document).ajaxStart(()=>{

	})

	$(document).ajaxStop(()=>{

		$(window).on('load', () => {
			dropdownItemsCount();
		})
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
		verifyChooseBtn();
	})

	$('li.menu-item-has-children').click((e)=>{
		e.stopPropagation()
		if ($(e.target).hasClass('menu-item-has-children')) {
			$(e.target).toggleClass('closed')
		}
	})
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
			$('.user-bar.sticky').addClass('active');
			return
		} else {
			$('.user-bar.sticky').removeClass('active');
		}
	}

	$(window).on('scroll', function(){
		top_menu();
		contacts_menu();
		recolorBar();

	});
	$(window).on('resize', function(){
		top_menu();
		contacts_menu();
		top_menu();
	});
	$(document).ready(function(){
		top_menu();
		contacts_menu();
		recolorBar();
	});
	contacts_menu();

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

	$('a.home_reserv_more').on('click', function(){
		var title_no = $(this).attr('data-no');
		var title_yes = $(this).attr('data-yes');
		if($(this).hasClass('active')){
			$('.one_home_reserv.hide_item').hide();
			$(this).html(title_no);
		} else {
			$('.one_home_reserv.hide_item').show();
			$(this).html(title_yes);
		}
		$(this).toggleClass('active');

		return false;
	});

	function newsImg() {
		if($('.single_news_wrap').length && $('.news-block__image img').attr('src').length > 1) {
			$('.news-block__image').addClass('news-img--active');
		}
	}
	newsImg();

	function promoTimer() {
		const $timer = $('.js_timer');
		const days = $('.timer_days').text()
		const hours = $('.timer_hours').text()

		if($('.promo_wrap').length) {
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
