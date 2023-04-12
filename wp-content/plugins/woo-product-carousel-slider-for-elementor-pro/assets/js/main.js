'use strict';
(function ($) {
	jQuery(window).on('elementor/frontend/init', function(){
		elementorFrontend.hooks.addAction('frontend/element_ready/wpce-slider.default', function ($scope, $) {
			var elem = $scope.find('.wpce_slider_wrapper');
			
			var display_dots = $scope.find('.wpce_slider_wrapper').data('display-dots');
			if( display_dots == 'yes' ){
				display_dots = true;
			}else{
				display_dots = false;
			}

			var autoplay = $scope.find('.wpce_slider_wrapper').data('autoplay');
			if( autoplay == 'yes' ){
				autoplay = true;
			}else{
				autoplay = false;
			}

			var infinite_loop = $scope.find('.wpce_slider_wrapper').data('infinite_loop');
			if( infinite_loop == 'yes' ){
				infinite_loop = true;
			}else{
				infinite_loop = false;
			}

			var autoplaySpeed = 3000;
			if( autoplay == true ){
				autoplaySpeed = $scope.find('.wpce_slider_wrapper').data('autoplay-speed');
			}

			var slideSpeed = $scope.find('.wpce_slider_wrapper').data('slide-speed');
			if( slideSpeed <= 0 ){
				slideSpeed = 1000;
			}

			var slides_to_show = $scope.find('.wpce_slider_wrapper').data('slide-to-show');
			if( slides_to_show > 0 ){
				slides_to_show  = $scope.find('.wpce_slider_wrapper').data('slide-to-show');
			}else{
				slides_to_show = 3
			}

			var slides_to_scroll = $scope.find('.wpce_slider_wrapper').data('slides-to-scroll');
			if( slides_to_scroll > 0 ){
				slides_to_scroll  = $scope.find('.wpce_slider_wrapper').data('slides-to-scroll');
			}else{
				slides_to_scroll = 3
			}

			/*var pauseOnFocus = $scope.find('.wpce_slider_wrapper').data('pause-on-focus');
			if( pauseOnFocus == 'yes' ){
				pauseOnFocus = true;
			}else{
				pauseOnFocus = false;
			}*/

			var pauseOnHover = $scope.find('.wpce_slider_wrapper').data('pause-on-hover');
			if( pauseOnHover == 'yes' ){
				pauseOnHover = true;
			}else{
				pauseOnHover = false;
			}

			var pauseOnDotsHover = $scope.find('.wpce_slider_wrapper').data('pause-on-dots-hover');
			if( pauseOnDotsHover == 'yes' ){
				pauseOnDotsHover = true;
			}else{
				pauseOnDotsHover = false;
			}

			var prev_arrow = $scope.find('.wb-arrow-prev');
			var next_arrow = $scope.find('.wb-arrow-next');
			elem.slick({
				infinite: infinite_loop,
				slidesToShow: slides_to_show,
				slidesToScroll: slides_to_scroll,
				autoplay: autoplay,
				arrows: true,
				prevArrow: prev_arrow,
				nextArrow: next_arrow,
				dots: display_dots,
				draggable: true,
				focusOnSelect: false,
				swipe: true,
				adaptiveHeight: true,
				speed: slideSpeed,
				autoplaySpeed: autoplaySpeed,
				// pauseOnFocus : pauseOnFocus,
				pauseOnHover : pauseOnHover,
				pauseOnDotsHover : pauseOnDotsHover,
				 /*responsive: [
				    {
				      breakpoint: 768,
				      settings: {
				        slidesToShow: 2,
				        slidesToScroll: 2,
				      }
				    },
				    {
				      breakpoint: 480,
				      settings: {
				        slidesToShow: 1,
				        slidesToScroll: 1,
				      }
				    },
				]*/
			});
		});
	});
})(jQuery);