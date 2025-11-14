<?php
/*
Template Name: Location Schweiz
*/

get_header();
?>
<!-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"> -->
<!-- <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/lightgallery/2.8.3/css/lightgallery.min.css"> -->
<!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lightgallery/2.8.3/css/lg-zoom.min.css"> -->
<!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lightgallery/2.8.3/css/lg-thumbnail.min.css"> -->
<style>
html, body {
	overflow-x: hidden !important;
} 
.wc-timeline-button-show-cart.right {
	right: 15px;
	bottom: 95px !important;
}
#elementor-lightbox-slideshow-single-img {
	display: none !important;
}

.mmenu-trigger {
	background-color: transparent;
	box-shadow: inset 0px 0px 1px 2px rgba(255, 255, 255, 0.05);
	border-radius: 10px;
}
.hamburger-inner, .hamburger-inner::before, .hamburger-inner::after {
	height: 1px;
	background-color: rgba(255, 255, 255, 0.7);
}

body.page-template-template-schweiz-page div#header:first-of-type {
	background: rgba(0, 0, 0, 0.37) !important;
	box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1) !important;
	backdrop-filter: blur(5px) !important;
}
body.page-template-template-schweiz-page div#header:first-of-type #navigation.style-1 .jet-menu-title {
	color: #fff;
}
a.landing-booking-btn {
	font-family: "Kumbh Sans", Sans-serif;
	font-size: 14px;
	font-weight: 600;
	/*text-transform: uppercase;*/
	line-height: 22px;
	fill: #FFFFFF;
	color: #FFFFFF;
	background-color: rgba(255, 255, 255, 0.13);
	border-radius: 10px;
	padding: 9px 15px;
	/*backdrop-filter: blur(2px);*/
	box-shadow: -1px -1px 0px 0px rgba(255, 255, 255, .25) inset;
	transition: 0.5s;
}
a.landing-booking-btn:hover {
	box-shadow: 1px 1px 0px 0px rgba(255, 255, 255, .25) inset;
}


div.tab-dots {
	display: flex;
	align-items: center;
	justify-content: center;
	column-gap: 8px;
	margin-top: 15px;
}
div.tab-dots span {
	width: 10px;
	height: 10px;
	border: 1px solid rgba(42, 67, 48, 0.6);
	border-radius: 50%;
	background-color: #ffffff;
	cursor: pointer;
}
div.tab-dots span.actv {
	border-color: #2A4330;
	background-color: #2A4330;
} 
.btt_btn {
	opacity: 0;
}


@media only screen and (min-width: 1025px) {
	#header-container .left-side {
		width: 62% !important;
	}
	#header-container .right-side {
		width: 38% !important;
	}
}
@media (min-width: 1024px){
	
}

@media only screen and (max-width: 1024px) and (min-width: 768px) {
	#header-container .left-side {
		width: 40% !important;
	}
	#header-container .right-side {
		width: 60% !important;
	}
}
@media (max-width: 767px) {
	a.landing-booking-btn {
		display: none;
	}
	.mob-acc:not(.ac) .elementor-image-box-description {
		display: none;
	}
	.mob-acc .elementor-image-box-title {
		cursor: pointer;
		margin-bottom: 0 !important;
		padding-bottom: 20px;
	}
	.mob-acc .elementor-image-box-title::after {
		content: '';
		position: absolute;
		left: calc(50% - 5px);
		bottom: -7px;
		width: 10px;
		height: 10px;
		cursor: pointer;
		background-image: url(/wp-content/themes/listeo-child/assets/union-icon.svg);
	}
	.mob-acc .elementor-image-box-title.active::after,
	.mob-acc.ac .elementor-image-box-title.active::after {
		opacity: 0;
		z-index: -1;
	}
	.mob-acc.ww .elementor-image-box-title::after {
		background-image: url(/wp-content/themes/listeo-child/assets/union-icon-w.svg);
	}
}

</style>

<?php
while ( have_posts() ) : the_post();

	the_content();

endwhile; // End of the loop.

?>

<!-- single listing slider html code -->


<!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/lightgallery/2.8.3/lightgallery.min.js"></script> -->
<!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/lightgallery/2.8.3/plugins/zoom/lg-zoom.min.js"></script> -->
<!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/lightgallery/2.8.3/plugins/thumbnail/lg-thumbnail.min.js"></script> -->
<script>
	// lightGallery(document.querySelector('.popup-gallery'), {

	// const galleryElement = document.querySelector('.popup-gallery');

	// const gallery = lightGallery(galleryElement, {
	// 	selector: 'a',
  	// 	plugins: [lgThumbnail],
	// 	closable: true,
	// 	controls: true,
	// 	backdropDuration: 300,
	// 	download: false,
	// 	showCloseIcon: true,
	// 	mobileSettings: {
	// 	    controls: true,
	// 	    showCloseIcon: true,
	// 	    download: false,
	// 	},
	// });

</script>
<script>
jQuery(document).ready(function($) {

	$(window).on('scroll', function() {
        if ($(this).scrollTop() > 1000) { // adjust 100 as needed
            $('.btt_btn').stop(true, true).fadeTo(300, 1); // fade in
        } else {
            $('.btt_btn').stop(true, true).fadeTo(300, 0); // fade out
        }
    });




    $('.e-n-tab-title').on('click', function() {
        var index = $(this).index();
        
        $('.tab-dots span').removeClass('actv');
        $('.tab-dots span').eq(index).addClass('actv');
    });
    

    $('.tab-dots span').on('click', function() {
        var index = $(this).index();
        
        // Update tab buttons
        $('.e-n-tab-title').attr('aria-selected', 'false');
        $('.e-n-tab-title').eq(index).attr('aria-selected', 'true');
        
        // Update tab content
        $('.e-n-tabs-content .e-child').removeClass('e-active');
        $('.e-n-tabs-content .e-child').eq(index).addClass('e-active');
        
        // Update dots
        $('.tab-dots span').removeClass('actv');
        $(this).addClass('actv');
        
        // Trigger click on the tab button (in case there's other functionality)
        $('.e-n-tab-title').eq(index).trigger('click');
    });



    $(".mob-acc.ac .elementor-image-box-title").addClass("active");

    $(".mob-acc .elementor-image-box-title").on("click", function() {
	    const $title = $(this);
	    const $desc = $title.next(".elementor-image-box-description");

	    // Toggle "active" class on the title
	    $title.toggleClass("active");

	    // Toggle visibility of the next description
	    $desc.slideToggle(200);
	});
});
</script>




<?php
get_footer();
