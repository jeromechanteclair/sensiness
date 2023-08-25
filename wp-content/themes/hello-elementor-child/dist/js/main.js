"use strict";
(self["webpackChunksensiness_theme"] = self["webpackChunksensiness_theme"] || []).push([["/js/main"],{

/***/ "./assets/js/ajax.js":
/*!***************************!*\
  !*** ./assets/js/ajax.js ***!
  \***************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   ajax_call: () => (/* binding */ ajax_call)
/* harmony export */ });
function ajax_call($action) {
  var $formdata = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : '';
  var $submitbutton = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : '';
  var $replaceEl = arguments.length > 3 ? arguments[3] : undefined;
  return new Promise(function (resolve, reject) {
    var xhr = new XMLHttpRequest();
    var submit_button = $submitbutton;
    if (submit_button) {
      submit_button.classList.add('loading');
    }
    xhr.open('POST', wc_add_to_cart_params.ajax_url);
    // set the request header
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    // send the request
    var params = new URLSearchParams($formdata).toString();
    var $string = "action=".concat($action, "&") + params;
    xhr.send($string);

    // listen for the response
    xhr.onload = function () {
      // get the response
      var response = JSON.parse(xhr.responseText);
      var notices = response.notices;
      // console.log($replaceEl)
      // console.log(response)
      if (notices.success) {
        // replace form with success message
        if ($replaceEl) {
          $replaceEl.innerHTML = response.html;
        }
      }
      if (submit_button) {
        submit_button.classList.remove('loading');
      }
      if (xhr.status >= 200 && xhr.status < 300) {
        resolve(xhr.response);
      } else {
        reject(xhr.statusText);
      }
    };
  });
}


/***/ }),

/***/ "./assets/js/cart.js":
/*!***************************!*\
  !*** ./assets/js/cart.js ***!
  \***************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   cart: () => (/* binding */ cart)
/* harmony export */ });
/* harmony import */ var _ajax__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./ajax */ "./assets/js/ajax.js");
/* provided dependency */ var $ = __webpack_require__(/*! jquery */ "./node_modules/jquery/dist/jquery.js");

function cart() {
  var buttons = $(document).find('.single_add_to_cart_button');
  var forms = $(document).find('.ajax-cart');
  var minicart = document.querySelector('.mini-cart');
  $(document).on('submit', '.ajax-cart', function (e) {
    e.preventDefault();
    var data = new FormData(this);
    data.append("loaded", false);
    // console.log(data)
    // const el= document.querySelector('#booking-list');
    var promise = (0,_ajax__WEBPACK_IMPORTED_MODULE_0__.ajax_call)('ajax_add_to_cart', data, '', minicart);
    promise.then(function (val) {
      var values = JSON.parse(val);
      console.log(values);

      // refresh list
    });
  });

  $(document).on('click', '.remove_from_cart_button', function (e) {
    e.preventDefault();
    var product_id = $(this).attr("data-product_id"),
      cart_item_key = $(this).attr("data-cart_item_key"),
      product_container = $(this).parents('.mini_cart_item');
    $.ajax({
      type: 'POST',
      dataType: 'json',
      url: wc_add_to_cart_params.ajax_url,
      data: {
        action: "ajax_remove_from_cart",
        product_id: product_id,
        cart_item_key: cart_item_key
      },
      success: function success(response) {
        if (!response || response.error) return;
        if (response.counter < 1) {
          var _bodyoverlay = document.querySelector('.cart-overlay');
          var _body = document.querySelector('body');
          _bodyoverlay.classList.toggle('show');
          _body.classList.toggle('lock');
        }
        $('.mini-cart').html(response.html);
        // refresh list
        var booking_start_date = document.getElementById('booking_start_date');
        if (booking_start_date) {
          var _product_id = booking_start_date.dataset.product;
          var is_array = JSON.parse(_product_id).length > 0;
          if (is_array) {
            var product_ids = JSON.parse(_product_id);
            update_booking_list(product_ids, false, false, 'ajax_get_bookings_by_products_range');
          } else {
            update_booking_list(_product_id, false, false, 'ajax_get_bookings_by_product_range');
          }
        }
      }
    });
  });
  var bodyoverlay = document.querySelector('.cart-overlay');
  var body = document.querySelector('body');
  $(document).on('click', '.toggle-cart', function () {
    $(document).find('.cart-overlay').toggleClass('show');
    $(document).find('.minicart-aside').toggleClass('hide');
    $(document).find('body').addClass('lock');
    $(document).find('.toggle-menu').removeClass('open');
    $(document).find('.scroll-menus').removeClass('show');
    $(document).find('.scroll-menus .menu').first().removeClass('active');
    $(document).find('.scroll-menus .menu').first().find('li').first().removeClass('active');
  });
  $(document).on('click', '.toggle-close', function () {
    $(document).find('body').toggleClass('lock');
    $(document).find('.cart-overlay').toggleClass('show');
    $(document).find('.minicart-aside').toggleClass('hide');
  });
  $(document).on('click', '.cart-overlay', function () {
    $(document).find('body').toggleClass('lock');
    $(document).find('.cart-overlay').toggleClass('show');
    $(document).find('.minicart-aside').toggleClass('hide');
  });
  // document.addEventListener("click", function (e) {
  //     const target = e.target.closest(".toggle-close"); // Or any other selector.

  //     if (target) {

  //         const minicart = target.closest(".minicart-aside")
  //         minicart.classList.toggle('hide');

  //         body.classList.toggle('lock');
  //     }
  // });

  $(document).on('click', '.woocommerce-delete-coupon', function (e) {
    e.preventDefault();
    var coupon = $(this).attr('data-coupon');
    var $form = $(this).parents('form');
    var data = new FormData($form[0]);
    data.append("coupon", coupon);
    // console.log(data)
    // const el= document.querySelector('#booking-list');
    var promise = (0,_ajax__WEBPACK_IMPORTED_MODULE_0__.ajax_call)('ajax_delete_coupon_code', data, '', minicart);
    promise.then(function (val) {
      var values = JSON.parse(val);
      // console.log(values.fragments['.woocommerce-checkout-review-order-table']);
      if (values.fragments) {
        $(document).find('.woocommerce-checkout-review-order-table').replaceWith(values.fragments['.woocommerce-checkout-review-order-table']);
        $(document).find('.woocommerce-checkout-payment').replaceWith(values.fragments['.woocommerce-checkout-payment']);
      }
    });
  });
  $(document).on('click', '.ajax_coupon', function (e) {
    e.preventDefault();
    var $form = $(this).parents('form');
    var data = new FormData($form[0]);
    data.append("loaded", false);
    // console.log(data)
    // const el= document.querySelector('#booking-list');
    var promise = (0,_ajax__WEBPACK_IMPORTED_MODULE_0__.ajax_call)('ajax_apply_coupon_code', data, '', minicart);
    promise.then(function (val) {
      var values = JSON.parse(val);
      // console.log(values.fragments['.woocommerce-checkout-review-order-table']);
      if (values.fragments) {
        $(document).find('.woocommerce-checkout-review-order-table').replaceWith(values.fragments['.woocommerce-checkout-review-order-table']);
        $(document).find('.woocommerce-checkout-payment').replaceWith(values.fragments['.woocommerce-checkout-payment']);
      }
    });
  });
}


/***/ }),

/***/ "./assets/js/file.js":
/*!***************************!*\
  !*** ./assets/js/file.js ***!
  \***************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   file: () => (/* binding */ file)
/* harmony export */ });
/* provided dependency */ var $ = __webpack_require__(/*! jquery */ "./node_modules/jquery/dist/jquery.js");
function file() {
  var newFileList;
  $(document).on('dragenter', 'label[for="comment_file"] ', function (e) {
    $(this).addClass('drag');
  });
  $(document).on('dragend drop dragleave', 'label[for="comment_file"]', function (e) {
    $(this).removeClass('drag');
  });
  $(document).on('change', '#comment_file', function () {
    var names = [];
    for (var i = 0; i < $(this).get(0).files.length; ++i) {
      var $placeholder = '<div class="file-drop__file" data-index="' + i + '"><i class="remove-file"></i><span class="file-drop__file__filename">' + $(this).get(0).files[i].name + '</span></div>';
      names.push();
      $($placeholder).insertAfter($('.file-drop'));
    }
  });
  $(document).on('click', '.remove-file', function (e) {
    var index = $(this).parent().attr('data-index');
    var input = document.getElementById('comment_file');
    newFileList = Array.from(input.files);
    newFileList.splice(index, 1);
    function FileListItems(files) {
      var b = new ClipboardEvent("").clipboardData || new DataTransfer();
      for (var i = 0, len = files.length; i < len; i++) b.items.add(files[i]);
      return b.files;
    }
    var files = new FileListItems(newFileList);
    input.files = files;
    $(this).parent().remove();
  });
}


/***/ }),

/***/ "./assets/js/main.js":
/*!***************************!*\
  !*** ./assets/js/main.js ***!
  \***************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _variation__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./variation */ "./assets/js/variation.js");
/* harmony import */ var _select__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./select */ "./assets/js/select.js");
/* harmony import */ var _slider__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./slider */ "./assets/js/slider.js");
/* harmony import */ var _file__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./file */ "./assets/js/file.js");
/* harmony import */ var _scroll__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./scroll */ "./assets/js/scroll.js");
/* harmony import */ var _scrollbar__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./scrollbar */ "./assets/js/scrollbar.js");
/* harmony import */ var _cart__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ./cart */ "./assets/js/cart.js");
/* harmony import */ var _video__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! ./video */ "./assets/js/video.js");
/* provided dependency */ var $ = __webpack_require__(/*! jquery */ "./node_modules/jquery/dist/jquery.js");








if ($('#commentform').length > 0) {
  $('#commentform')[0].encoding = 'multipart/form-data';
}
$(document).on('click', '.toggle-review-form', function () {
  $('#review_form_wrapper').toggleClass('hide');
});
$(document).find('.body-overlay').addClass('fade');
setTimeout(function () {
  $(document).find('.body-overlay').addClass('hide');
}, 300);
(0,_video__WEBPACK_IMPORTED_MODULE_7__.video)();
(0,_cart__WEBPACK_IMPORTED_MODULE_6__.cart)();
(0,_scrollbar__WEBPACK_IMPORTED_MODULE_5__.scrollbar)();
(0,_variation__WEBPACK_IMPORTED_MODULE_0__.variation)();
(0,_select__WEBPACK_IMPORTED_MODULE_1__.select)();
(0,_slider__WEBPACK_IMPORTED_MODULE_2__.slider)();
(0,_file__WEBPACK_IMPORTED_MODULE_3__.file)();
(0,_scroll__WEBPACK_IMPORTED_MODULE_4__.scroll)();
var vh = window.innerHeight * 0.01;
// Then we set the value in the --vh custom property to the root of the document
document.documentElement.style.setProperty('--vh', "".concat(vh, "px"));
window.addEventListener('resize', function () {
  // We execute the same script as before
  var vh = window.innerHeight * 0.01;
  document.documentElement.style.setProperty('--vh', "".concat(vh, "px"));
});

/***/ }),

/***/ "./assets/js/scroll.js":
/*!*****************************!*\
  !*** ./assets/js/scroll.js ***!
  \*****************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   scroll: () => (/* binding */ scroll)
/* harmony export */ });
/* provided dependency */ var $ = __webpack_require__(/*! jquery */ "./node_modules/jquery/dist/jquery.js");
function scroll() {
  var isMobile = false; //initiate as false
  // device detection
  if (/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|ipad|iris|kindle|Android|Silk|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i.test(navigator.userAgent) || /1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(navigator.userAgent.substr(0, 4))) {
    isMobile = true;
  }
  if ($(document).find('.single-product .cart').length > 0) {
    var formtop = $('.single-product .cart').offset().top;
    var formtrigger = formtop + $('.single-product .cart').outerHeight();
    var formtrigger = $('.summary').outerHeight(true);
    if (isMobile) {
      var formtrigger = $('.summary').outerHeight() + $('.summary').offset().top;
    }
    //  formtrigger = $('.summary').outerHeight(true);
    var stickyheight = $('.form-sticky-wrapper').outerHeight(true);
    // if(isMobile){
    //  formtrigger = $('.bandeau-marquee').offset().top;

    // }
    $('.form-sticky-wrapper').css('min-height', stickyheight + 'px');
  }
  function isInViewport($element) {
    var elementTop = $($element).offset().top;
    var elementBottom = elementTop + $($element).outerHeight();
    var viewportTop = $(window).scrollTop();
    var viewportBottom = viewportTop + $(window).height();
    return elementBottom > viewportTop && elementTop < viewportBottom;
  }
  ;
  $(window).scroll(function () {
    // console.log($(document).find('.video-container'));
    if ($(document).find('.video-container').length > 0) {
      if (isInViewport($('.video-container'))) {
        var video = $('.video-container').find('video');
        video[0].muted = true;
        video[0].autoplay = true;
        video[0].play();
      } else {
        var _video = $('.video-container').find('video');
        _video[0].autoplay = false;
        _video[0].pause();
      }
    }
    if (formtrigger + 50 < $(window).scrollTop()) {
      var reworked = $('.single-product .cart').outerHeight();
      if (!$('.single-product .cart').hasClass('sticky')) {
        // setTimeout(() => {

        $('.single-product .cart').addClass('sticky');
        // }, 500);
      }
    } else {
      // $('.single-product .cart').removeClass('sticky');
      if ($('.single-product .cart').hasClass('sticky')) {
        // $('.single-product .cart').removeClass('sticky')
        // setTimeout(() => {

        $('.single-product .cart').removeClass('sticky');
        // }, 500);
      }
    }
  });
}



/***/ }),

/***/ "./assets/js/scrollbar.js":
/*!********************************!*\
  !*** ./assets/js/scrollbar.js ***!
  \********************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   scrollbar: () => (/* binding */ scrollbar)
/* harmony export */ });
/* harmony import */ var simplebar__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! simplebar */ "./node_modules/simplebar/dist/index.mjs");
/* harmony import */ var simplebar_dist_simplebar_css__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! simplebar/dist/simplebar.css */ "./node_modules/simplebar/dist/simplebar.css");
/* provided dependency */ var $ = __webpack_require__(/*! jquery */ "./node_modules/jquery/dist/jquery.js");
 // or "import SimpleBar from 'simplebar';" if you want to use it manually.

function detectmob() {
  return window.innerWidth < 1025;
}
var simplebars;
var menubar;
var categorybar;
function scrollbar() {
  var lastScrollTop = 0;
  $(window).scroll(function () {
    var scrollTop = $(this).scrollTop();
    if (scrollTop > lastScrollTop) {
      $('.categories-menu').addClass('scroll');
    } else {
      console.log("Scrolling up");
      $('.categories-menu').removeClass('scroll');
    }
    lastScrollTop = scrollTop;
  });
  if (detectmob()) {
    new simplebar__WEBPACK_IMPORTED_MODULE_0__["default"]($('.scroll-menus')[0]);
    $(document).find('.sub-menu').each(function (i, el) {
      new simplebar__WEBPACK_IMPORTED_MODULE_0__["default"]($(el)[0]);
    });
    $(document).find('.categories-menu .menu').each(function (i, el) {
      new simplebar__WEBPACK_IMPORTED_MODULE_0__["default"]($(el)[0]);
    });
  }
  $(document).on('click', '.toggle-menu', function () {
    $(this).toggleClass('open');
    $(document).find('body').toggleClass('lock');
    $(document).find('.scroll-menus').toggleClass('show');
    $(document).find('.scroll-menus .menu').first().toggleClass('active');
    $(document).find('.scroll-menus .menu').first().find('li').first().toggleClass('active');
  });
  $(document).on('click ', '.has-child >a', function (e) {
    e.preventDefault();
    $(document).find('.has-child').removeClass('active');
    $(document).find('.menu').removeClass('active');
    $(this).parent().addClass('active');
    $(this).parent().parent().addClass('active');
  });
}


/***/ }),

/***/ "./assets/js/select.js":
/*!*****************************!*\
  !*** ./assets/js/select.js ***!
  \*****************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   select: () => (/* binding */ select)
/* harmony export */ });
/* harmony import */ var tom_select__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! tom-select */ "./node_modules/tom-select/dist/js/tom-select.complete.js");
/* harmony import */ var tom_select__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(tom_select__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var tom_select_dist_css_tom_select_min_css__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! tom-select/dist/css/tom-select.min.css */ "./node_modules/tom-select/dist/css/tom-select.min.css");
/* provided dependency */ var $ = __webpack_require__(/*! jquery */ "./node_modules/jquery/dist/jquery.js");


function select() {
  var $selects = $(document).find('.custom-select');
  var $svg = '<svg width="12" height="7" viewBox="0 0 12 7" fill="none" xmlns="http://www.w3.org/2000/svg">\
    <path d="M5.99999 6.67689C5.88589 6.67689 5.77275 6.6551 5.66056 6.61151C5.5484 6.56791 5.45065 6.50445 5.36731 6.42111L0.873087 1.92689C0.728204 1.78202 0.655762 1.60639 0.655762 1.39999C0.655762 1.19359 0.728204 1.01795 0.873087 0.873087C1.01795 0.728204 1.19359 0.655762 1.39999 0.655762C1.60639 0.655762 1.78202 0.728204 1.92689 0.873087L5.99999 4.94616L10.0731 0.873087C10.218 0.728204 10.3936 0.655762 10.6 0.655762C10.8064 0.655762 10.982 0.728204 11.1269 0.873087C11.2718 1.01795 11.3442 1.19359 11.3442 1.39999C11.3442 1.60639 11.2718 1.78202 11.1269 1.92689L6.63266 6.42111C6.53908 6.51471 6.44036 6.58074 6.33651 6.61919C6.23268 6.65765 6.1205 6.67689 5.99999 6.67689Z" fill="#364321"/>\
    </svg>\
    ';
  var tom = false;
  $selects.each(function (index, select) {
    var settings = {
      controlInput: null,
      render: {
        option: function option(data, escape) {
          if (data.pricePromo) {
            if (data.pricePromo.length > 0) {
              return "<div><p>".concat(data.text, "</p><div class=\"select-prices\"><span class=\"promo\">").concat(JSON.parse(data.priceReg), "</span><span class=\"reg\">").concat(JSON.parse(data.pricePromo), "</span></div></div>");
            } else {
              return "<div><p>".concat(data.text, "</p><div class=\"select-prices\"><span class=\"reg\">").concat(JSON.parse(data.priceReg), "</span></div></div>");
            }
          } else {
            return "<div><p>".concat(data.text, "</p><div class=\"select-prices\"><span class=\"reg\"></span></div></div>");
          }
        },
        item: function item(_item, escape) {
          if (_item.pricePromo) {
            if (_item.pricePromo.length > 0) {
              return "<div><p>".concat(_item.text, "</p><div class=\"select-prices\"><span class=\"promo\">").concat(JSON.parse(_item.priceReg), "</span><span class=\"reg\">").concat(JSON.parse(_item.pricePromo), "</span>").concat($svg, "</div></div>");
            } else {
              return "<div><p>".concat(_item.text, "</p><div class=\"select-prices\"><span class=\"reg\">").concat(JSON.parse(_item.priceReg), "</span>").concat($svg, "</div></div>");
            }
          } else {
            return "<div><p>".concat(_item.text, "</p><div class=\"select-prices\"><span class=\"reg\"></span>").concat($svg, "</div></div>");
          }
        }
      }
    };
    tom = new (tom_select__WEBPACK_IMPORTED_MODULE_0___default())(select, settings);
    tom.settings.placeholder = "New placeholder";
    tom.inputState();
  });
  var $form;
  $(document).on('click', '[data-select]', function (e) {
    // resetAttributes($(this));

    allowattributes($(this));
  });
  if (tom) {
    tom.on('change', function (el) {
      var trigger = $(document).find('.variation-item[data-value=' + tom.getValue() + ']');
      allowattributes(trigger, true);
    });
  }
  function allowattributes(trigger) {
    var recurtion = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;
    // $('form.cart').trigger('reload_product_variations');
    var select = $(trigger).attr('data-select');
    var value = $(trigger).attr('data-value');
    var datalink = $(trigger).attr('data-link');
    var dataprice = $(trigger).attr('data-price');
    var datavariation = $(trigger).attr('data-variation_id');
    var $price = $(document).find('.custom-price__right ');
    var $variation_id = $(document).find('.variation_id');
    $variation_id.val(datavariation);
    $price.html(JSON.parse(dataprice));

    // split data-link by white spaces
    var datalinkarray = datalink.split(' ');
    // remove empty value from datalinkarray
    datalinkarray = datalinkarray.filter(function (el) {
      return el.length > 0;
    });
    var lists = $(document).find('.variation-wrapper li');

    // find sibligns
    var siblings = $(document).find('[data-select="' + select + '"]');
    // remove active class from siblings
    siblings.removeClass('selected');
    lists.each(function (i, el) {
      if (!datalinkarray.includes($(el).attr('data-value')) && $(el).attr('data-select') !== select) {
        // $(el).addClass('disabled').removeClass('selected');
      } else {
        if ($(el).attr('data-select') !== select) {}
        // $(el).removeClass('disabled');
      }

      if ($(el).attr('data-select') !== select) {}
    });

    // add active class to clicked button
    $(trigger).addClass('selected');
    var $selected = $('form.cart').find('.selected');
    $selected.each(function (i, el) {
      var currentselect = $(el).attr('data-select');
      var currentval = $(el).attr('data-value');
      if (currentselect == 'attribute_pa_sizes') {
        $('.default.product__item-cart').find('.size').html(currentval);
      }
      if (currentselect == 'attribute_pa_colors') {
        $('.default.product__item-cart').find('.color').html(currentval);
      }
      if (currentselect == 'attribute_contenance') {
        $('.default.product__item-cart').find('.contenance').html(currentval.replace('-', ','));
      }
      if (currentselect == 'attribute_pa_diametres') {
        $('.default.product__item-cart').find('.diametre').html(currentval.replace('-', ','));
      }
    });
    var $select = $('form.cart').find('[name="' + select + '"]');
    $select.val(value).trigger('change');
    if (!recurtion) {
      if (tom) {
        tom.setValue(value);
      }
    }
    $select.trigger('click');
  }
}


/***/ }),

/***/ "./assets/js/slider.js":
/*!*****************************!*\
  !*** ./assets/js/slider.js ***!
  \*****************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   slider: () => (/* binding */ slider)
/* harmony export */ });
/* harmony import */ var swiper_bundle__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! swiper/bundle */ "./node_modules/swiper/swiper-bundle.esm.js");
/* harmony import */ var swiper_swiper_min_css__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! swiper/swiper.min.css */ "./node_modules/swiper/swiper.min.css");
/* provided dependency */ var $ = __webpack_require__(/*! jquery */ "./node_modules/jquery/dist/jquery.js");

// import Swiper styles

function slider() {
  var swiper = document.querySelectorAll(".reassurance-slider");
  if (swiper) {
    swiper.forEach(function (slider) {
      new swiper_bundle__WEBPACK_IMPORTED_MODULE_0__["default"](slider, {
        // Optional parameters
        direction: "horizontal",
        loop: true,
        slidesPerView: 1,
        allowTouchMove: true,
        speed: 1000,
        centeredSlides: true,
        autoplay: {
          delay: 2500,
          disableOnInteraction: false
        },
        breakpoints: {
          // when window width is >= 320px
          320: {
            direction: "horizontal",
            allowTouchMove: true
          },
          640: {
            direction: "horizontal",
            allowTouchMove: true
          }
        },
        // If we need pagination
        pagination: {
          el: ".swiper-pagination",
          type: "bullets",
          clickable: true
        },
        observer: true,
        observeParents: true
      });
    });
  }
  if ($('.gallery-top ').find('.swiper-slide').length > 1) {
    var galleryTop = new swiper_bundle__WEBPACK_IMPORTED_MODULE_0__["default"]('.gallery-top', {
      navigation: {
        nextEl: '.control-next',
        prevEl: '.control-prev'
      },
      on: {
        slideChange: function slideChange() {
          var activeIndex = this.activeIndex + 1;
          // get current slide
          if ($('.gallery-top .autoplay video').length > 0) {
            $('.gallery-top .autoplay video').get(0).pause();
            $('.gallery-top .autoplay video').get(0).currentTime = 0;
            var currentSlide = $('.gallery-top .swiper-slide').eq(this.activeIndex);
            if (currentSlide.hasClass('autoplay')) {
              currentSlide.find('video').get(0).play();
            }
          }
          $('.thumb--active').removeClass('thumb--active');
          $('.thumb').eq(activeIndex - 1).addClass('thumb--active');
        }
      }
    });
  }
  $(document).on('click', '.thumb', function () {
    var index = $('.thumb').index(this);
    // if galleryTOp is defined
    if (galleryTop) {
      galleryTop.slideTo(index);
    }
    $('.thumb--active').removeClass('thumb--active');
    $(this).addClass('thumb--active');
  });
}


/***/ }),

/***/ "./assets/js/variation.js":
/*!********************************!*\
  !*** ./assets/js/variation.js ***!
  \********************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   variation: () => (/* binding */ variation)
/* harmony export */ });
function variation() {}


/***/ }),

/***/ "./assets/js/video.js":
/*!****************************!*\
  !*** ./assets/js/video.js ***!
  \****************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   video: () => (/* binding */ video)
/* harmony export */ });
/* provided dependency */ var $ = __webpack_require__(/*! jquery */ "./node_modules/jquery/dist/jquery.js");
function video() {
  var video = $(document).find('#video-home');
  if (video.length > 0) {
    if (video[0].readyState === 4) {
      // it's loaded
      video.prev().addClass('hide');
      video[0].play();
    }
    video.on('click', function () {
      video[0].play();
    });
  }
}


/***/ }),

/***/ "./assets/scss/style.scss":
/*!********************************!*\
  !*** ./assets/scss/style.scss ***!
  \********************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ })

},
/******/ __webpack_require__ => { // webpackRuntimeModules
/******/ var __webpack_exec__ = (moduleId) => (__webpack_require__(__webpack_require__.s = moduleId))
/******/ __webpack_require__.O(0, ["css/style","/js/vendor"], () => (__webpack_exec__("./assets/js/main.js"), __webpack_exec__("./assets/scss/style.scss")));
/******/ var __webpack_exports__ = __webpack_require__.O();
/******/ }
]);