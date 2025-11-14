<?php

// if $data is not null
if (!empty($data)) {
	// extract $data as variables
	if (isset($data->show_email)) {
		$show_email = $data->show_email;
	} else {
		$show_email = true;
	}
} else {
	$show_email = true;
}
// check post status
if (get_post_status() == 'expired') {
	return;
}
$contacts = false;
$phone = get_post_meta(get_the_ID(), '_phone', true);
$mail = get_post_meta(get_the_ID(), '_email', true);
if (!$show_email) {
	$mail = false;
}
$website = get_post_meta(get_the_ID(), '_website', true);
if ($phone || $mail || $website) {
	$contacts = true;
}

// get social media links
$defined_socials = get_option('listeo_contact_tab_fields');

// get only keys

if(empty($defined_socials)) {
    $defined_socials = array('_facebook', '_twitter', '_instagram', '_youtube', '_skype','_whatsapp');
} else {
	$defined_socials = array_keys($defined_socials);
}

$socials = false;
if(in_array('_facebook', $defined_socials)) {
    $facebook = get_post_meta(get_the_ID(), '_facebook', true);
} else {
    $facebook = false;
}
if(in_array('_youtube', $defined_socials)) {
    $youtube = get_post_meta(get_the_ID(), '_youtube', true);
} else {
    $youtube = false;
}
if(in_array('_twitter', $defined_socials)) {
    $twitter = get_post_meta(get_the_ID(), '_twitter', true);
} else {
    $twitter = false;
}
if(in_array('_instagram', $defined_socials)) {
    $instagram = get_post_meta(get_the_ID(), '_instagram', true);
} else {
    $instagram = false;
}
if(in_array('_skype', $defined_socials)) {
    $skype = get_post_meta(get_the_ID(), '_skype', true);
} else {
    $skype = false;
}
if(in_array('_whatsapp', $defined_socials)) {
    $whatsapp = get_post_meta(get_the_ID(), '_whatsapp', true);
} else {
    $whatsapp = false;
}
if(in_array('_linkedin', $defined_socials)) {
    $linkedin = get_post_meta(get_the_ID(), '_linkedin', true);
} else {
    $linkedin = false;
}
if(in_array('_soundcloud', $defined_socials)) {
    $soundcloud = get_post_meta(get_the_ID(), '_soundcloud', true);
} else {
    $soundcloud = false;
}
if(in_array('_pinterest', $defined_socials)) {
    $pinterest = get_post_meta(get_the_ID(), '_pinterest', true);
} else {
    $pinterest = false;
}
if(in_array('_viber', $defined_socials)) {
    $viber = get_post_meta(get_the_ID(), '_viber', true);
} else {
    $viber = false;
}
if(in_array('_tiktok', $defined_socials)) {
    $tiktok = get_post_meta(get_the_ID(), '_tiktok', true);
} else {
    $tiktok = false;
}
if(in_array('_snapchat', $defined_socials)) {
    $snapchat = get_post_meta(get_the_ID(), '_snapchat', true);
} else {
    $snapchat = false;
}
if(in_array('_telegram', $defined_socials)) {
    $telegram = get_post_meta(get_the_ID(), '_telegram', true);
} else {
    $telegram = false;
}
if(in_array('_tumblr', $defined_socials)) {
    $tumblr = get_post_meta(get_the_ID(), '_tumblr', true);
} else {
    $tumblr = false;
}
if(in_array('_reddit', $defined_socials)) {
    $reddit = get_post_meta(get_the_ID(), '_reddit', true);
} else {
    $reddit = false;
}
if(in_array('_medium', $defined_socials)) {
    $medium = get_post_meta(get_the_ID(), '_medium', true);
} else {
    $medium = false;
}
if(in_array('_twitch', $defined_socials)) {
    $twitch = get_post_meta(get_the_ID(), '_twitch', true);
} else {
    $twitch = false;
}
if(in_array('_mixcloud', $defined_socials)) {
    $mixcloud = get_post_meta(get_the_ID(), '_mixcloud', true);
} else {
    $mixcloud = false;
}
if(in_array('_tripadvisor', $defined_socials)) {
    $tripadvisor = get_post_meta(get_the_ID(), '_tripadvisor', true);
} else {
    $tripadvisor = false;
}
if(in_array('_yelp', $defined_socials)) {
    $yelp = get_post_meta(get_the_ID(), '_yelp', true);
} else {
    $yelp = false;
}
if(in_array('_foursquare', $defined_socials)) {
    $foursquare = get_post_meta(get_the_ID(), '_foursquare', true);
} else {
    $foursquare = false;
}
if(in_array('_line', $defined_socials)) {
    $line = get_post_meta(get_the_ID(), '_line', true);
} else {
    $line = false;
}


if ($facebook || $line || $youtube || $twitter || $instagram || $skype || $whatsapp || $soundcloud || $pinterest || $viber || $tiktok || $snapchat || $telegram || $tumblr || $reddit || $medium || $twitch || $mixcloud || $tripadvisor || $yelp || $foursquare) {
	$socials = true;
}

if ($socials || $contacts) :
?>

	<div class="listing-links-container">
		<?php
		$visibility_setting = get_option('listeo_user_contact_details_visibility'); // hide_all, show_all, show_logged, show_booked,  
		if ($visibility_setting == 'hide_all') {
			$show_details = false;
		} elseif ($visibility_setting == 'show_all') {
			$show_details = true;
		} else {
			if (is_user_logged_in()) {
				if ($visibility_setting == 'show_logged') {
					$show_details = true;
				} else {
					$show_details = false;
				}
			} else {
				$show_details = false;
			}
		}


		if ($contacts) :

			if ($show_details) { ?>

				<ul class="listing-links contact-links">
					<?php if (isset($phone) && !empty($phone)) : ?>
						<li><a href="tel:<?php echo esc_attr($phone); ?>" class="listing-links"><i class="fa fa-phone"></i> <?php echo esc_html($phone); ?></a></li>
					<?php endif; ?>
					<?php if (isset($mail) && !empty($mail)) : ?>
						<li><a href="mailto:<?php echo esc_attr($mail); ?>" class="listing-links"><i class="fa fa-envelope-o"></i> <?php echo esc_html($mail); ?></a>
						</li>
					<?php endif; ?>
					<?php if (isset($website) && !empty($website)) :
						$url =  wp_parse_url($website); ?>
						<li><a rel=nofollow href="<?php echo esc_url($website) ?>" target="_blank" class="listing-links"><i class="fa fa-link"></i> <?php
																																					if (isset($url['host'])) {
																																						echo esc_html($url['host']);
																																					} else {
																																						esc_html_e('Visit website', 'listeo_core');
																																					} ?></a></li>
					<?php endif; ?>
				</ul>
				<div class="clearfix"></div>
				<?php
			} else {
				if ($visibility_setting != 'hide_all') { ?>
					<p><?php if (get_option('listeo_popup_login', true) != 'ajax') {
							printf(
								esc_html__('Please %s sign %s in to see contact details.', 'listeo_core'),
								sprintf('<a href="%s" class="sign-in">', wp_login_url(apply_filters('the_permalink', get_permalink(get_the_ID()), get_the_ID()))),
								'</a>'
							);
						} else {
							printf(esc_html__('Please %s sign %s in to see contact details.', 'listeo_core'), '<a href="#sign-in-dialog" class="sign-in popup-with-zoom-anim">', '</a>');
						}
						?></p>
				<?php } ?>
		<?php }
		endif; ?>

		​<?php if ($show_details && $socials) : ?>
		<ul class="listing-links">
			<?php if (isset($facebook) && !empty($facebook)) : ?>
				<li><a href="<?php echo esc_url($facebook); ?>" target="_blank" class="listing-links-fb"><i class="fa fa-facebook-square"></i> Facebook</a></li>
			<?php endif; ?>
			<?php if (isset($youtube) && !empty($youtube)) : ?>
				<li><a href="<?php echo esc_url($youtube); ?>" target="_blank" class="listing-links-yt"><i class="fa fa-youtube-play"></i> YouTube</a></li>
			<?php endif; ?>
			<?php if (isset($instagram) && !empty($instagram)) : ?>
				<li><a href="<?php echo esc_url($instagram); ?>" target="_blank" class="listing-links-ig"><i class="fa fa-instagram"></i> Instagram</a></li>
			<?php endif; ?>
			<?php if (isset($twitter) && !empty($twitter)) : ?>
				<li><a href="<?php echo esc_url($twitter); ?>" target="_blank" class="listing-links-tt"><i class="fa-brands fa-x-twitter"></i> Share</a></li>
			<?php endif; ?>
			<?php if (isset($linkedin) && !empty($linkedin)) : ?>
				<li><a href="<?php echo esc_url($linkedin); ?>" target="_blank" class="listing-links-linkedit"><i class="fa fa-linkedin"></i> LinkedIn</a></li>
			<?php endif; ?>
			<?php if (isset($viber) && !empty($viber)) : ?>
				<li><a href="<?php echo esc_url($viber); ?>" target="_blank" class="listing-links-viber"><i class="fab fa-viber"></i> Viber</a></li>
			<?php endif; ?>
			<?php if (isset($skype) && !empty($skype)) : ?>
				<li><a href="<?php if (strpos($skype, 'http') === 0) {
									echo esc_url($skype);
								} else {
									echo "skype:+" . $skype . "?call";
								} ?>" target="_blank" class="listing-links-skype"><i class="fa fa-skype"></i> Skype</a></li>
			<?php endif; ?>
			<?php if (isset($whatsapp) && !empty($whatsapp)) : ?>
				<li><a href="<?php if (strpos($whatsapp, 'http') === 0) {
									echo esc_url($whatsapp);
								} else {
									echo "https://wa.me/" . $whatsapp;
								} ?>" target="_blank" class="listing-links-whatsapp"><i class="fa fa-whatsapp"></i> WhatsApp</a></li>
			<?php endif; ?>
			<?php if (isset($soundcloud) && !empty($soundcloud)) : ?>
				<li><a href="<?php if (strpos($soundcloud, 'http') === 0) {
									echo esc_url($soundcloud);
								} else {
									echo "https://soundcloud.com/" . $soundcloud;
								} ?>" target="_blank" class="listing-links-soundcloud"><i class="fa fa-soundcloud"></i> Soundcloud</a></li>
			<?php endif; ?>
			<?php if (isset($pinterest) && !empty($pinterest)) : ?>
				<li><a href="<?php if (strpos($pinterest, 'http') === 0) {
									echo esc_url($pinterest);
								} else {
									echo "https://pinterest.com/" . $pinterest;
								} ?>" target="_blank" class="listing-links-pinterest"><i class="fa fa-pinterest"></i> Pinterest</a></li>
			<?php endif; ?>
			<?php if (isset($tiktok) && !empty($tiktok)) : ?>
				<li><a href="<?php if (strpos($tiktok, 'http') === 0) {
									echo esc_url($tiktok);
								} else {
									echo "https://tiktok.com/@" . $_tiktok;
								} ?>" target="_blank" class="listing-links-tiktok"><i class="fab fa-tiktok"></i> TikTok</a></li>
			<?php endif; ?>
			<?php if (isset($snapchat) && !empty($snapchat)) : ?>
				<li><a href="<?php if (strpos($snapchat, 'http') === 0) {
									echo esc_url($snapchat);
								} else {
									echo "https://snapchat.com/add/" . $snapchat;
								} ?>" target="_blank" class="listing-links-snapchat"><i class="fab fa-snapchat"></i> Snapchat</a></li>
			<?php endif; ?>
			<?php if (isset($telegram) && !empty($telegram)) : ?>
				<li><a href="<?php if (strpos($telegram, 'http') === 0) {
									echo esc_url($telegram);
								} else {
									echo "https://telegram.me/" . $telegram;
								} ?>" target="_blank" class="listing-links-telegram"><i class="fab fa-telegram"></i> Telegram</a></li>
			<?php endif; ?>
			<?php if (isset($tumblr) && !empty($tumblr)) : ?>
				<li><a href="<?php if (strpos($tumblr, 'http') === 0) {
									echo esc_url($tumblr);
								} else {
									echo "https://tumblr.com/" . $tumblr;
								} ?>" target="_blank" class="listing-links-tumblr"><i class="fab fa-tumblr"></i> Tumblr</a></li>
			<?php endif; ?>
			<?php if (isset($reddit) && !empty($reddit)) : ?>
				<li><a href="<?php if (strpos($reddit, 'http') === 0) {
									echo esc_url($reddit);
								} else {
									echo "https://reddit.com/u/" . $reddit;
								} ?>" target="_blank" class="listing-links-reddit"><i class="fab fa-reddit"></i> Reddit</a></li>
			<?php endif; ?>
			<?php if (isset($medium) && !empty($medium)) : ?>
				<li><a href="<?php if (strpos($medium, 'http') === 0) {
									echo esc_url($medium);
								} else {
									echo "https://medium.com/@" . $medium;
								} ?>" target="_blank" class="listing-links-medium"><i class="fab fa-medium"></i> Medium</a></li>
			<?php endif; ?>
			<?php if (isset($twitch) && !empty($twitch)) : ?>
				<li><a href="<?php if (strpos($twitch, 'http') === 0) {
									echo esc_url($twitch);
								} else {
									echo "https://twitch.tv/" . $twitch;
								} ?>" target="_blank" class="listing-links-twitch"><i class="fab fa-twitch"></i> Twitch</a></li>
			<?php endif; ?>
			<?php if (isset($mixcloud) && !empty($mixcloud)) : ?>
				<li><a href="<?php if (strpos($mixcloud, 'http') === 0) {
									echo esc_url($mixcloud);
								} else {
									echo "https://mixcloud.com/" . $mixcloud;
								} ?>" target="_blank" class="listing-links-mixcloud"><i class="fab fa-mixcloud"></i> Mixcloud</a></li>
			<?php endif; ?>
			<?php if (isset($tripadvisor) && !empty($tripadvisor)) : ?>
				<li><a href="<?php if (strpos($tripadvisor, 'http') === 0) {
									echo esc_url($tripadvisor);
								} else {
									echo "https://tripadvisor.com/" . $tripadvisor;
								} ?>" target="_blank" class="listing-links-tripadvisor"><i class="fab fa-tripadvisor"></i> TripAdvisor</a></li>
			<?php endif; ?>
			<?php if (isset($yelp) && !empty($yelp)) : ?>
				<li><a href="<?php if (strpos($yelp, 'http') === 0) {
									echo esc_url($yelp);
								} else {
									echo "https://yelp.com/" . $yelp;
								} ?>" target="_blank" class="listing-links-yelp"><i class="fab fa-yelp"></i> Yelp</a></li>
			<?php endif; ?>
			<?php if (isset($foursquare) && !empty($foursquare)) : ?>
				<li><a href="<?php if (strpos($foursquare, 'http') === 0) {
									echo esc_url($foursquare);
								} else {
									echo "https://foursquare.com/" . $foursquare;
								} ?>" target="_blank" class="listing-links-foursquare"><i class="fab fa-foursquare"></i> Foursquare</a></li>
			<?php endif; ?>

			<?php if (isset($line) && !empty($line)) : ?>
				<li><a href="<?php if (strpos($line, 'http') === 0) {
									echo esc_url($line);
								} else {
									echo "https://line.me/" . $line;
								} ?>" target="_blank" class="listing-links-line"><i class="fab fa-line"></i> Line</a></li>
			<?php endif; ?>


		</ul>
		<div class="clearfix"></div>
	<?php endif; ?>

	</div>
	<div class="clearfix"></div>
<?php endif; ?>