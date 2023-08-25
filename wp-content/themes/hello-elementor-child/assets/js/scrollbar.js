import SimpleBar from 'simplebar'; // or "import SimpleBar from 'simplebar';" if you want to use it manually.
import 'simplebar/dist/simplebar.css';

function detectmob() {
    return window.innerWidth < 1025;
}

var simplebars;
var menubar;
var categorybar;

function scrollbar() {
    let lastScrollTop = 0;
      
      $(window).scroll(function() {
        const scrollTop = $(this).scrollTop();
        
        if (scrollTop > lastScrollTop) {
              $('.categories-menu').addClass('scroll')
        } else {
          console.log("Scrolling up");
          $('.categories-menu').removeClass('scroll')
        }
        
        lastScrollTop = scrollTop;
      });
    if ( detectmob()) {

      new SimpleBar($('.scroll-menus')[0]);
        $(document).find('.sub-menu').each(function (i, el) {
              new SimpleBar($(el)[0]);
        })
        $(document).find('.categories-menu .menu').each(function (i, el) {
            new SimpleBar($(el)[0]);
        })
    }
    $(document).on('click', '.toggle-menu', function () {
        $(this).toggleClass('open')
        $(document).find('body').toggleClass('lock')
        $(document).find('.scroll-menus').toggleClass('show')
        $(document).find('.scroll-menus .menu').first().toggleClass('active');
        $(document).find('.scroll-menus .menu').first().find('li').first().toggleClass('active');

    })
    $(document).on('click ', '.has-child >a', function (e) {
        e.preventDefault()
        $(document).find('.has-child').removeClass('active');
        $(document).find('.menu').removeClass('active');
        $(this).parent().addClass('active');
        $(this).parent().parent().addClass('active');
    })
}
export {
    scrollbar
}