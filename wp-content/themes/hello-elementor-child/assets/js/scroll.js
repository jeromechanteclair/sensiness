function scroll() {
    var formtop = $('.variations_form').offset().top;
    var formtrigger = formtop+$('.variations_form').outerHeight()+100;
    $.fn.isInViewport = function () {
        let elementTop = $(this).offset().top;
        let elementBottom = elementTop + $(this).outerHeight();

        let viewportTop = $(window).scrollTop();
        let viewportBottom = viewportTop + $(window).height();

        return elementBottom > viewportTop && elementTop < viewportBottom;
    };
    $(window).scroll(function () {
        console.log(formtrigger);
        if ($('.video-container ').isInViewport()) {

            let video = $('.video-container ').find('video');
            video[0].autoplay = true;
        } else {

            let video = $('.video-container ').find('video');
            video[0].autoplay = false;
        }
        if(formtrigger< $(window).scrollTop()){
            let summaryheigth = $('.summary').outerHeight();
            let reworked = $('.variations_form').outerHeight() ;
            $('.summary').css('height',summaryheigth+'px')
            $('.variations_form').addClass('sticky')
           
        } else {
            $('.variations_form').removeClass('sticky');
           
         
        }
    });
}
export {
    scroll
}