import {
    ajax_call,

} from "./ajax";

function cart() {
    const buttons = $(document).find('.single_add_to_cart_button')
    const forms = $(document).find('.ajax-cart');
    const minicart = document.querySelector('.mini-cart')
    $(document).on('submit', '.ajax-cart', function (e) {
        e.preventDefault();

        let data = new FormData(this);
        data.append("loaded", false);
        // console.log(data)
        // const el= document.querySelector('#booking-list');
        let promise = ajax_call('ajax_add_to_cart', data, '', minicart)
        promise.then(function (val) {
            let values = JSON.parse(val);
            console.log(values);

            // refresh list


        });
    })

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
            success: function (response) {
                if (!response || response.error)
                    return;
                if (response.counter < 1) {
                    const bodyoverlay = document.querySelector('.cart-overlay');
                    const body = document.querySelector('body');
                    bodyoverlay.classList.toggle('show');
                    body.classList.toggle('lock');


                }
                $('.mini-cart').html(response.html);
                // refresh list
                const booking_start_date = document.getElementById('booking_start_date');
                if (booking_start_date) {
                    let product_id = booking_start_date.dataset.product;
                    const is_array = JSON.parse(product_id).length > 0
                    if (is_array) {
                        let product_ids = JSON.parse(product_id);
                        update_booking_list(product_ids, false, false, 'ajax_get_bookings_by_products_range');

                    } else {

                        update_booking_list(product_id, false, false, 'ajax_get_bookings_by_product_range');
                    }
                }
            }
        });
    })

    const bodyoverlay = document.querySelector('.cart-overlay');
    const body = document.querySelector('body');


    $(document).on('click', '.toggle-cart', function () {
        console.log($(this).hasClass('no-touch'))
        if(!$(this).hasClass('no-touch')){

        
                    $(document).find('.cart-overlay').toggleClass('show')
                    $(document).find('.minicart-aside').toggleClass('hide')
                    $(document).find('body').addClass('lock')
                    $(document).find('.toggle-menu').removeClass('open')

                    $(document).find('.scroll-menus').removeClass('show')
                    $(document).find('.scroll-menus .menu').first().removeClass('active');
                    $(document).find('.scroll-menus .menu').first().find('li').first().removeClass('active');
            }


    })
    $(document).on('click', '.toggle-close', function () {
        $(document).find('body').toggleClass('lock')

        $(document).find('.cart-overlay').toggleClass('show')
        $(document).find('.minicart-aside').toggleClass('hide')

    })
    $(document).on('click', '.cart-overlay', function () {
        $(document).find('body').toggleClass('lock')

        $(document).find('.cart-overlay').toggleClass('show')
        $(document).find('.minicart-aside').toggleClass('hide')

    })
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
        let coupon = $(this).attr('data-coupon');
        let $form = $(this).parents('form')

        let data = new FormData($form[0]);
        data.append("coupon", coupon);
        // console.log(data)
        // const el= document.querySelector('#booking-list');
        let promise = ajax_call('ajax_delete_coupon_code', data, '', minicart)
        promise.then(function (val) {
            let values = JSON.parse(val);
            // console.log(values.fragments['.woocommerce-checkout-review-order-table']);
            if (values.fragments) {

                $(document).find('.woocommerce-checkout-review-order-table').replaceWith(values.fragments['.woocommerce-checkout-review-order-table'])
                $(document).find('.woocommerce-checkout-payment').replaceWith(values.fragments['.woocommerce-checkout-payment'])
            }



        })
    })

    $(document).on('click', '.ajax_coupon', function (e) {
        e.preventDefault();
        let $form = $(this).parents('form')

        let data = new FormData($form[0]);
        data.append("loaded", false);
        // console.log(data)
        // const el= document.querySelector('#booking-list');
        let promise = ajax_call('ajax_apply_coupon_code', data, '', minicart)
        promise.then(function (val) {
            let values = JSON.parse(val);
            // console.log(values.fragments['.woocommerce-checkout-review-order-table']);
            if (values.fragments) {

                $(document).find('.woocommerce-checkout-review-order-table').replaceWith(values.fragments['.woocommerce-checkout-review-order-table'])
                $(document).find('.woocommerce-checkout-payment').replaceWith(values.fragments['.woocommerce-checkout-payment'])
            }



        })
    })
}
export {
    cart
}