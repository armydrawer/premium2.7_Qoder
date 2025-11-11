<?php if (!defined('ABSPATH')) {
	exit();
}

$f_change = get_theme_option('f_change', array(
	'ctext',
	'timetable',
	'in',
	'vk',
	'tg',
	'telegram',
	'yt',
	'fb',
	'tw',
	'ins',
	'tm',
	'tm2',
	'tm3',
	'tm_link',
	'tm2_link',
	'tm3_link',
	'email',
	'jabber',
	'phone',
	'icq',
	'skype',
	'viber',
	'whatsapp',
	'footer_partners_link',
	'footer_partners_img',

	'dzen',
	'dzen_link',

    'f_logo',


    ));
    $h_change = get_theme_option('h_change', array('linkhead',));
?>
<?php if (!is_front_page()) { ?>
	<!--end inner content -->
	</div>

	<?php if (!is_page_template('pn-notsidebar.php')) { ?>
		<aside class="aside">
			<?php get_sidebar(); ?>
		</aside>
	<?php } ?>

	<!--end inner content and sidebar -->
	</div>
	</div>
<?php } ?>

<!--end container -->
</div>
</main>

<footer class="footer">

	<div class="container footer__grid">
        <div class="footer__col-1">
            <?php if($h_change['linkhead'] == 1 and !is_front_page() or $h_change['linkhead'] != 1){ ?>
                <a class="footer__logo" href="<?php echo get_site_url_ml(); ?>">
            <?php } ?>

            <?php
                $logo = get_logotype();
                $textlogo = get_textlogo();
                if($logo){
            ?>
                <img src="<?php echo $logo; ?>" alt="" />
            <?php } elseif($textlogo){ ?>
                <?php echo $textlogo; ?>
            <?php } else {
                $textlogo = str_replace(array('http://','https://','www.'),'', PN_SITE_URL);
                $textlogo = rtrim($textlogo,'/');
            ?>
                <img src="<?php echo PN_TEMPLATEURL; ?>/images/logo.svg" alt="logo">
            <?php } ?>

            <?php if($h_change['linkhead'] == 1 and !is_front_page() or $h_change['linkhead'] != 1){ ?>
                </a>
            <?php } ?>

            <?php if ($f_change['tg'] || $f_change['vk'] || $f_change['fb'] || $f_change['tw'] || $f_change['ins']) { ?>
                <div class="social_links">
                    <?php if ($f_change['tg']) { ?>
                        <a href="tg://resolve?domain=<?php echo pn_strip_input(str_replace('@', '', $f_change['tg'])); ?>" class="footer__social contacts-tg" target="_blank"></a>
                    <?php } ?>
                    <?php if ($f_change['vk']) { ?>
                        <a href="<?php echo $f_change['vk'] ?>" class="footer__social contacts-vk" target="_blank"></a>
                    <?php } ?>
                    <?php if ($f_change['fb']) { ?>
                        <a href="<?php echo $f_change['fb'] ?>" class="footer__social contacts-fb" target="_blank"></a>
                    <?php } ?>
                    <?php if ($f_change['tw']) { ?>
                        <a href="<?php echo $f_change['tw'] ?>" class="footer__social contacts-tw" target="_blank"></a>
                    <?php } ?>
                    <?php if ($f_change['ins']) { ?>
                        <a href="<?php echo $f_change['ins'] ?>" class="footer__social contacts-ins" target="_blank"></a>
                    <?php } ?>
                </div>
            <?php } ?>

            <?php if ($f_change['ctext']) { ?>
                <div class="cop"><?php echo apply_filters('comment_text', $f_change['ctext']); ?></div>
            <?php } ?>


        </div>

        <div class="footer__col-2">
            <div class="footer__nav">
                <?php
                    wp_nav_menu(array(
                        'sort_column' => 'menu_order',
                        'container' => 'div',
                        'container_class' => 'menu',
                        'menu_class' => 'footer__menu',
                        'menu_id' => '',
                        'depth' => '2',
                        'fallback_cb' => 'no_menu',
                        'theme_location' => 'the_bottom_menu'
                    ));
                ?>
            </div>
        </div>

        <div class="footer__col-3">
            <?php if ($f_change['tm'] || $f_change['email']) { ?>
                <div class="footer__contacts">
                    <?php if ($f_change['tm']) {
                        $tm_link = "tg://resolve?domain=" . pn_strip_input(str_replace('@', '', $f_change['tm_link']));
                    ?>
                        <a href="<?php echo $tm_link ?>" class="footer__link contacts-tg" target="_blank">
                            <span class="contacts-icon tg-icon"></span>
                            <span><?php echo $f_change['tm'] ?></span>
                        </a>
                    <?php } ?>
                    <?php if ($f_change['tm2']) {
                        $tm2_link = "tg://resolve?domain=" . pn_strip_input(str_replace('@', '', $f_change['tm2_link']));
                    ?>
                        <a href="<?php echo $tm2_link ?>" class="footer__link contacts-tg" target="_blank">
                            <span class="contacts-icon tg-icon"></span>
                            <span><?php echo $f_change['tm2'] ?></span>
                        </a>
                    <?php } ?>
                    <?php if ($f_change['email']) { ?>
                        <a href="mailto:<?php echo $f_change['email'] ?>" class="footer__link contacts-email" target="_blank">
                            <span class="contacts-icon email-icon"></span>
                            <span><?php echo $f_change['email'] ?></span>
                        </a>
                    <?php } ?>
                    <?php if ($f_change['skype']) { ?>
                        <a href="skype:<?php echo $f_change['skype'] ?>" class="footer__link contacts-skype" target="_blank">
                            <span class="contacts-icon skype-icon"></span>
                            <span><?php echo $f_change['skype'] ?></span>
                        </a>
                    <?php } ?>
                </div>
            <?php } ?>
            <?php if ($f_change['timetable']) { ?>
                <div class="footer__info"><?php echo apply_filters('comment_text', $f_change['timetable']); ?></div>
            <?php } ?>

        </div>
	</div>
</footer>

<div id="topped" class="js_to_top js_show_top"><span></span></div>

<div class="lang_constants">
	<div class="lang_constant_choose_file"><?php _e('Choose file', 'pntheme'); ?></div>
	<div class="lang_constant_title_give"><?php _e('Give', 'pntheme'); ?></div>
	<div class="lang_constant_title_get"><?php _e('Get', 'pntheme'); ?></div>
	<div class="lang_constant_title_exchange"><?php _e('Exchange', 'pntheme'); ?></div>
	<div class="timer_days"><?php _e('days', 'pntheme'); ?></div>
	<div class="timer_hours"><?php _e('hours', 'pntheme'); ?></div>

	<div class="widget-buttons section-buttons">
        <div class="reviews-button-navigation">
            <div class="swiper-button-next reviews-button-nav reviews-button-prev"></div>
            <div class="swiper-button-prev reviews-button-nav reviews-button-next"></div>
        </div>
    </div>
</div>


<script src="<?php echo PN_TEMPLATEURL ?>/js/marquee3k.js"></script>
<script src="<?php echo PN_TEMPLATEURL ?>/js/swiper.js"></script>


<?php wp_footer(); ?>
<script>
	document.addEventListener("DOMContentLoaded", function() {
		const observer = new MutationObserver(handleMutation);
		const targetDiv = document.querySelector('.walletsverify_box');
		// const targetDiv = document.querySelector('.userwalletsform_box');
		const config = {
			childList: true
		};


		observer.observe(targetDiv, config);

		function handleMutation(mutations) {
			mutations.forEach(mutation => {
				if (mutation.addedNodes.length) {

					const chooseFileBtnText = $('.lang_constant_choose_file').text() || "";
					const choseFileBtnVerify = $(`<div class="chose_file_btn obs">${chooseFileBtnText}</div>`)
					choseFileBtnVerify.click((e) => {
						$(e.target).prev('input').click()
					})

					if (!$('.verify_acc_file').find('.chose_file_btn').length) {
						$('.verify_acc_file').append(choseFileBtnVerify)
					}

				}
			});
		}
	})
</script>
<script>

	// swiper
	const swiper = new Swiper(".mySwiper", {
		spaceBetween: 30,
		pagination: {
			el: ".swiper-pagination",
			clickable: true
		},
		autoplay: {
			delay: 15000,
		},
		speed: 1500,
	});

	const swiper2 = new Swiper(".reviewsSwiper", {
		navigation: {
			nextEl: '.reviews-button-next',
			prevEl: '.reviews-button-prev',
		},
		speed: 1500,
		breakpoints: {
			0: {
				slidesPerView: 1,
				spaceBetween: 8,
				slidesPerGroup: 1,
			},
			641: {
				slidesPerView: 3,
				spaceBetween: 8,
				slidesPerGroup: 3,
			},
		}
	});

	const swiper3 = new Swiper(".newsSwiper", {
		navigation: {
			nextEl: '.reviews-button-next',
			prevEl: '.reviews-button-prev',
		},
		speed: 1500,
		breakpoints: {
			0: {
				slidesPerView: 1,
				spaceBetween: 8,
				slidesPerGroup: 1,
				enabled: true,
			},
			641: {
				slidesPerView: 3,
				spaceBetween: 8,
				slidesPerGroup: 3,
			},
		}
	});

	const swiper4 = new Swiper(".lastExchanges", {
		spaceBetween: 30,
		speed: 1500,
		navigation: {
			nextEl: '.reviews-button-next',
			prevEl: '.reviews-button-prev',
		},
		breakpoints: {
			0: {
				slidesPerView: 1,
				spaceBetween: 8,
				slidesPerGroup: 1,
				enabled: true,
			},
			641: {
				spaceBetween: 8,
				slidesPerGroup: 3,
				enabled: false,
			},
		}
	});
	const swiper5 = new Swiper(".advantagesSwiper", {
        spaceBetween: 30,
        speed: 1500,
        navigation: {
            nextEl: '.reviews-button-next',
            prevEl: '.reviews-button-prev',
        },
        breakpoints: {
            0: {
                slidesPerView: 1,
                spaceBetween: 8,
                slidesPerGroup: 1,
                enabled: true,
            },
            641: {
                slidesPerView: 3,
                spaceBetween: 8,
                slidesPerGroup: 3,
                enabled: false,
            },
        }
    });

</script>
</body>

</html>

