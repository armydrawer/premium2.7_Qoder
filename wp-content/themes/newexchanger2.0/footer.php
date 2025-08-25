<?php if( !defined( 'ABSPATH')){ exit(); }

$f_change = get_theme_option('f_change', array('ctext','timetable','tm_soc','vk','fb','tw','ins','tm_name','tm','email','jabber','phone','icq','skype','viber','whatsapp'));
?>
		<?php if(!is_front_page()){ ?>
				<!--end inner content -->
				</div>

				<?php if (!is_page_template('pn-notsidebar.php')) { ?>
					<aside class="aside">
						<?php get_sidebar(); ?>
					</aside>
				<?php } ?>

		<!--end inner content and sidebar -->
		</div>
		<?php } ?>

		<!--end container -->
		</div>
	</main>

	<footer class="footer">
		<div class="container footer__grid">
			<div class="footer__col-1">
				<div class="footer__logo"></div>
				<div class="footer__logo-mobile"></div>
				<?php if($f_change['tm_soc'] || $f_change['vk'] || $f_change['ins'] || $f_change['fb'] || $f_change['tw']){ ?>
                    <div class="social_links">
                        <?php
                            $self_soc_link = lang_self_link();
                            $self_soc_link = urlencode($self_soc_link);
                        ?>
                        <?php if($f_change['tm_soc']){
                            $tm_soc = "tg://resolve?domain=".pn_strip_input(str_replace('@','', $f_change['tm_soc']));
                            if(strstr($f_change['tm_soc'],'[soc_link]')){
                                $tm_soc = 'https://telegram.me/share/url?url='.$self_soc_link;
                            }
                        ?>
                            <a href="<?php echo $tm_soc ?>" class="footer__social contacts-tg" target="_blank"></a>
                        <?php } ?>
                        <?php if($f_change['vk']){ ?>
                            <a href="<?php echo $f_change['vk'] ?>" class="contacts-vk" target="_blank"></a>
                        <?php } ?>
                        <?php if($f_change['ins']){ ?>
                            <a href="<?php echo $f_change['ins'] ?>" class="contacts-ins" target="_blank"></a>
                        <?php } ?>
                        <?php if($f_change['fb']){ ?>
                            <a href="<?php echo $f_change['fb'] ?>" class="contacts-fb" target="_blank"></a>
                        <?php } ?>
                        <?php if($f_change['tw']){ ?>
                            <a href="<?php echo $f_change['tw'] ?>" class="contacts-tw" target="_blank"></a>
                        <?php } ?>
                    </div>
                <?php } ?>
                <div class="footer__info footer__mobile-info"><?php echo $f_change['timetable'] ?></div>
				<?php if($f_change['ctext']){ ?>
                    <div class="cop"><?php echo apply_filters('comment_text', $f_change['ctext']); ?></div>
                <?php } else { ?>
                    <div class="cop">&copy; <?php echo get_copy_date('2015'); ?> PremiumExchanger.com — <?php _e('electronic currency exchange service.','pntheme'); ?></div>
                <?php } ?>
			</div>
			<div class="footer__col-2">
				<?php
				wp_nav_menu(array(
					'sort_column' => 'menu_order',
					'container' => 'div',
					'container_class' => 'menu',
					'menu_class' => 'footer__menu',
					'menu_id' => '',
					'depth' => '1',
					'fallback_cb' => 'no_menu',
					'theme_location' => 'the_bottom_menu'
				));
				?>
			</div>
			<div class="footer__col-3">

				<?php
				    $self_link = lang_self_link();
				    $self_link = urlencode($self_link);
				?>

				<?php if($f_change['tm_name']){
					$tm_link = "tg://resolve?domain=".pn_strip_input(str_replace('@','', $f_change['tm']));
					if(strstr($f_change['tm'],'[soc_link]')){
						$tm_link = 'https://telegram.me/share/url?url='.$self_link;
					}
				?>
				    <a href="<?php echo $tm_link ?>" class="contacts-tg"  target="_blank"><span><?php echo $f_change['tm_name'] ?></span></a>
				<?php } ?>
				<?php if($f_change['email']){ ?>
				    <a href="mailto:<?php echo $f_change['email'] ?>" class="contacts-mail" target="_blank"><span><?php echo $f_change['email'] ?></span></a>
				<?php } ?>

				<?php if($f_change['jabber']){ ?>
					<a href="xmpp:<?php echo $f_change['jabber'] ?>" class="contacts-jabber" target="_blank"><span><?php echo $f_change['jabber'] ?></span></a>
				<?php } ?>
				<?php if($f_change['phone']){ ?>
					<a href="tel:<?php echo $f_change['phone'] ?>" class="contacts-phone" target="_blank"><span><?php echo $f_change['phone'] ?></span></a>
				<?php } ?>
				<?php if($f_change['icq']){ ?>
					<a class="contacts-icq">
					    <span class="contacts-icq" target="_blank"><?php echo $f_change['icq'] ?></span>
                    </a>
				<?php } ?>
				<?php if($f_change['skype']){ ?>
					<a href="skype:<?php echo $f_change['skype'] ?>" class="contacts-skype" target="_blank"><span><?php echo $f_change['skype'] ?></span></a>
				<?php } ?>
				<?php if($f_change['viber']){ ?>
					<a href="viber://chat?number=<?php echo $f_change['viber'] ?>" class="contacts-viber" target="_blank"><span><?php echo $f_change['viber'] ?></span></a>
				<?php } ?>
				<?php if($f_change['whatsapp']){ ?>
					<a href="https://wa.me/<?php echo $f_change['whatsapp'] ?>" class="contacts-whatsapp" target="_blank"><span><?php echo $f_change['whatsapp'] ?></span></a>
				<?php } ?>
				<div class="footer__info"><?php echo $f_change['timetable'] ?></div>

			</div>
			<div class="footer__col-4">
			    <a href="#" class="app-link googleplay"></a>
			    <a href="#" class="app-link appstore"></a>
			</div>
		</div>
	</footer>

    <div class="lang_constants">
        <div class="lang_constant_choose_file"><?php _e('Choose file','pntheme'); ?></div>
        <div class="timer_days"><?php _e('days','pntheme'); ?></div>
        <div class="timer_hours"><?php _e('hours','pntheme'); ?></div>
    </div>

<script src="<?php echo PN_TEMPLATEURL ?>/js/marquee3k.js"></script>
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
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
</script>

<?php wp_footer(); ?>
<script>
	document.addEventListener("DOMContentLoaded", function() {
		const observer = new MutationObserver(handleMutation);
		const targetDiv = document.querySelector('.walletsverify_box');
		// const targetDiv = document.querySelector('.userwalletsform_box');
		const config = { childList: true };


		observer.observe(targetDiv, config);

		function handleMutation(mutations) {
			mutations.forEach(mutation => {
				if (mutation.addedNodes.length) {

					const chooseFileBtnText = $('.lang_constant_choose_file').text() || "";
					const choseFileBtnVerify = $(`<div class="chose_file_btn obs">${chooseFileBtnText}</div>`)
					choseFileBtnVerify.click((e)=>{
						$(e.target).prev('input').click()
					})

					if(!$('.verify_acc_file').find('.chose_file_btn').length){
						$('.verify_acc_file').append(choseFileBtnVerify)
					}

				}
			});
		}
	})
</script>

</body>
</html>
