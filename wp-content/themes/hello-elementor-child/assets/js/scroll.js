function scroll() {
    var formtop = $('.variations_form').offset().top;
    var formtrigger = formtop+$('.variations_form').outerHeight();
    $.fn.isInViewport = function () {
        let elementTop = $(this).offset().top;
        let elementBottom = elementTop + $(this).outerHeight();

        let viewportTop = $(window).scrollTop();
        let viewportBottom = viewportTop + $(window).height();

        return elementBottom > viewportTop && elementTop < viewportBottom;
    };
 
    $(window).scroll(function () {

        if ($('.video-container ').isInViewport()) {

            let video = $('.video-container ').find('video');
            video[0].autoplay = true;
        } else {

            let video = $('.video-container ').find('video');
            video[0].autoplay = false;
        }
        if(formtrigger< $(window).scrollTop()){
               let bodyheight = $('body').outerHeight();
    $('body').css('height',bodyheight+'px')

            let reworked = $('.variations_form').outerHeight() ;
            if( !$('.variations_form').hasClass('sticky')){
                setTimeout(() => {
                    
                    $('.variations_form').addClass('sticky')
                }, 500);

            }
           
        } else {

            // $('.variations_form').removeClass('sticky');
            if( $('.variations_form').hasClass('sticky')){
                // $('.variations_form').removeClass('sticky')
   setTimeout(() => {
                    
                    $('.variations_form').removeClass('sticky')
                }, 500);
            }
         
        }
    });
}
export {
    scroll
}