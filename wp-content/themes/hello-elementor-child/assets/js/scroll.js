function scroll() {
    if($(document).find('.single-product .cart').length>0){

    
    var formtop = $('.single-product .cart').offset().top;
    var formtrigger = formtop + $('.single-product .cart').outerHeight();
    var formtrigger =  $('.summary').outerHeight();
            let summaryheigth = $('.summary').outerHeight(true);

               $('.summary').css('min-height',summaryheigth+'px')
}
    function isInViewport($element) {
        let elementTop = $($element).offset().top;
        let elementBottom = elementTop + $($element).outerHeight();

        let viewportTop = $(window).scrollTop();
        let viewportBottom = viewportTop + $(window).height();

        return elementBottom > viewportTop && elementTop < viewportBottom;
    };

    $(window).scroll(function () {
        // console.log($(document).find('.video-container '));
        if ($(document).find('.video-container ').length>0) {


            if (isInViewport($('.video-container '))) {

                let video = $('.video-container ').find('video');
                video[0].autoplay = true;
            } else {

                let video = $('.video-container ').find('video');
                video[0].autoplay = false;
            }
        }
        if (formtrigger < $(window).scrollTop()) {
                


            let reworked = $('.single-product .cart').outerHeight();
            if (!$('.single-product .cart').hasClass('sticky')) {
                // setTimeout(() => {

                    $('.single-product .cart').addClass('sticky')
                // }, 500);

            }

        } else {

            // $('.single-product .cart').removeClass('sticky');
            if ($('.single-product .cart').hasClass('sticky')) {
                // $('.single-product .cart').removeClass('sticky')
                // setTimeout(() => {

                    $('.single-product .cart').removeClass('sticky')
                // }, 500);
            }

        }
    });
}
export {
    scroll
}