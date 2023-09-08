import {
    variation
} from "./variation";
import {
    select
} from "./select";
import {
    slider
} from "./slider";
import {
    file
} from "./file";
import {
    scroll
} from "./scroll";
import {
    scrollbar
} from "./scrollbar";
import {
    cart
} from "./cart";
import {
    video
} from "./video";

if ($('#commentform').length > 0) {
    $('#commentform')[0].encoding = 'multipart/form-data';
}
$(document).on('click', '.toggle-review-form', function () {
    $('#review_form_wrapper').toggleClass('hide');
})

$(document).find('.body-overlay').addClass('fade');
setTimeout(() => {
    $(document).find('.body-overlay').addClass('hide');
}, 300);
video();
cart();
scrollbar();
variation();
select();
slider();
file();
scroll();
let vh = window.innerHeight * 0.01;
// Then we set the value in the --vh custom property to the root of the document
document.documentElement.style.setProperty('--vh', `${vh}px`);

window.addEventListener('resize', () => {
    // We execute the same script as before
    let vh = window.innerHeight * 0.01;
    document.documentElement.style.setProperty('--vh', `${vh}px`);
});
$(document).on('click', '.toggle-sublist', function () {
    let parent = $(this).parent('li');
    let siblings = $(this).parents('.menu ').find('li');
    // parent.toggleClass('open')
    siblings.toggleClass('open')
})