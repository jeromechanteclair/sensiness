import SimpleBar from 'simplebar'; // or "import SimpleBar from 'simplebar';" if you want to use it manually.
import 'simplebar/dist/simplebar.css';
function detectmob() {
  return window.innerWidth < 1025;
}
function scrollbar() {
    if(detectmob()){

        new SimpleBar($('.scroll-menus')[0]);
        $(document).find('.sub-menu').each(function(i,el){
               new SimpleBar($(el)[0]);
        })
    }
    $(document).on('click', '.toggle-menu', function () {
$(document).find('body').toggleClass('lock')
        $(document).find('.scroll-menus').toggleClass('show')
        $(document).find('.scroll-menus .menu').first().find('li').first().toggleClass('active');

    })
       $(document).on('click ', '.has-child >a', function (e) {
        e.preventDefault()
       $(document).find('.has-child').removeClass('active');
       $(this).parent().addClass('active');
       })
}
export {
    scrollbar
}