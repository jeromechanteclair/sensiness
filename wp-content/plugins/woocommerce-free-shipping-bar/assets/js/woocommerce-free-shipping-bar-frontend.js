jQuery(document).ready(function ($) {
    "use strict";

    let shippingBar = $('#wfspb-top-bar');

    // let miniShippingBar = $('.woocommerce-free-shipping-bar-order');

    /**
     * Class Free Shipping Bar
     */

    let woocommerce_free_shipping_bar = {
        init_delay: parseInt(_wfsb_params.initialDelay),
        time_to_disappear: true,
        display_time: parseInt(_wfsb_params.displayTime),
        timeout_display: 0,
        timeout_init: 0,
        hash: _wfsb_params.hash,

        init: function () {
            this.closeBtn();
            this.giftBoxIcon();
            this.initBar();
            this.add_to_cart();
        },

        closeBtn() {
            /*Close button*/
            $('#wfspb-close').on('click', function () {
                woocommerce_free_shipping_bar.bar_hide();
                woocommerce_free_shipping_bar.gift_box_show();

            });
        },

        giftBoxIcon() {
            /*Gift box icon*/
            $('.wfspb-gift-box').on('click', function () {
                woocommerce_free_shipping_bar.gift_box_hide();
                woocommerce_free_shipping_bar.bar_show();
                woocommerce_free_shipping_bar.clear_time_init();
            });
        },

        bar_show: function () {
            let hasMess = $('#wfspb-main-content').children.length;
            if (hasMess) shippingBar.fadeIn(500);

            /*
            wp.wfspbConditionalVariable - Used in some specific conditions if the customer wants to add the condition.
            Eg:
            wp.wfspbConditionalVariable = true
            or
            wp.wfspbConditionalVariable = $('body')hasClass('home')
            */
            if (typeof wp.wfspbConditionalVariable === 'undefined' || wp.wfspbConditionalVariable ){
                if (this.time_to_disappear) {

                    this.timeout_display = setTimeout(function () {
                        woocommerce_free_shipping_bar.bar_hide();
                        woocommerce_free_shipping_bar.gift_box_show();
                    }, this.display_time * 1000);
                }
            }



            $('.woocommerce-free-shipping-bar-order').show();

        },

        bar_hide: function () {
            shippingBar.removeClass('wfsb-fixed');
            shippingBar.fadeOut(500);
            // $('.woocommerce-free-shipping-bar-order').hide();

            let themeHeader;
            if (!themeHeader || themeHeader.length === 0) themeHeader = $(`${_wfsb_params.headerSelector}`);
            if (themeHeader) themeHeader.css('cssText', `top:inherit;`);
        },

        gift_box_hide: function () {
            $('.wfspb-gift-box').addClass('wfsb-hidden');
        },

        gift_box_show: function () {
            $('.wfspb-gift-box').removeClass('wfsb-hidden');
        },

        clear_time_init: function () {
            clearTimeout(this.timeout_init);
        },

        clear_time_display: function () {
            clearTimeout(this.timeout_display);
        },

        showBarHasDelay() {
            this.timeout_init = setTimeout(function () {
                woocommerce_free_shipping_bar.bar_show();
                woocommerce_free_shipping_bar.gift_box_hide();
            }, this.init_delay * 1000);
        },

        initBar() {
            if (parseInt(_wfsb_params.cacheCompa) === 1) {
                let data = sessionStorage.getItem(this.hash);

                if (!data) {
                    this.ajax();
                } else {
                    data = JSON.parse(data);
                    this.handleBar(data);
                }
            } else {
                this.showBarHasDelay();
            }
        },

        add_to_cart: function () {  // update total amount after click add_to_cart

            let _this = this;
            $(document).ajaxComplete(function (event, jqxhr, settings) {

                var ajax_link = settings.url;
                var data_opts = settings.data;

                if (ajax_link && ajax_link != 'undefined' && ajax_link.search(/wc-ajax=add_to_cart/i) >= 0
                    || ajax_link.search(/wc-ajax=remove_from_cart/i) >= 0
                    || ajax_link.search(/wc-ajax=get_refreshed_fragments/i) >= 0
                    || ajax_link.search(/wc-ajax=update_order_review/i) >= 0
                    || ajax_link.search(/admin-ajax\.php/i) >= 0
                    || ajax_link.search(/wc-ajax=xt_woofc_update_cart/i) >= 0
                    || ajax_link.search(/wc-ajax=viwcaio_add_to_cart/i) >= 0
                    || ajax_link.search(/wc-ajax=wpvs_add_to_cart/i) >= 0) {

                    if (ajax_link.search(/admin-ajax\.php/i) >= 0) {
                        let flag = false;
                        if (typeof data_opts === 'string') {
                            if (data_opts && data_opts.search(/action=wmc_get_products_price|action=basel_ajax_add_to_cart|action=basel_update_cart_item|action=kadence_pro_add_to_cart/i) >= 0) {
                                flag = true;
                            }
                        }

                        if (typeof data_opts === 'object') {
                            if (data_opts.action && data_opts.action === 'bodycommerce_ajax_add_to_cart_woo') flag = true;
                        }

                        if (data_opts instanceof FormData) {
                            let action = data_opts.get('action');
                            if ( (action.search(/add_to_cart/i) >= 0) ) {
                                flag = true;
                            }
                        }

                        if (!flag) return;
                    }

                    if (jqxhr.statusText === 'timeout' || !jqxhr.responseText) return;

                    let responseData = JSON.parse(jqxhr.responseText);

                    let wfspbData;
                    if (responseData.hasOwnProperty('fragments')) {
                        let fragment = responseData.fragments;
                        wfspbData = fragment.hasOwnProperty('wfspb') ? fragment.wfspb : '';
                    }

                    // if (!responseData.hasOwnProperty('fragments')) return;
                    // let fragment = responseData.fragments;
                    // let wfspbData = fragment.hasOwnProperty('wfspb') ? fragment.wfspb : '';

                    if (wfspbData) {
                        _this.updateSession(wfspbData);
                        _this.handleBar(wfspbData);
                    } else {
                        _this.ajax();
                    }
                }
            });
        },

        updateSession(data) {
            if (data) sessionStorage.setItem(this.hash, JSON.stringify(data));
        },

        ajax() {
            let _this = this, loadingBar = '<div class="wfspb-loading"><div class="wfspb-progress"></div></div>';

            // shippingBar.attr('data-time-disappear');
            // $('#wfspb-gift-box').attr('data-display');

            $.ajax({
                url: _wfsb_params.ajax_url,
                cache: false,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'wfspb_added_to_cart',
                    lang_code: _wfsb_params.lang_code
                },
                beforeSend() {
                    // $('#wfspb-progress, #wfspb-main-content').css('opacity',0);
                    // $('#wfspb-top-bar').append(loadingBar);
                },
                success(response) {
                    if (response) sessionStorage.setItem(_this.hash, JSON.stringify(response));
                    _this.handleBar(response)
                },
                complete() {
                    // $('#wfspb-progress, #wfspb-main-content').css('opacity',1);
                    // $('#wfspb-top-bar .wfspb-loading').remove();
                }
            });
        },

        handleBar(response) {
            if (response.no_free_shipping && 1 == response.no_free_shipping) {
                woocommerce_free_shipping_bar.bar_hide();
                return;
            }

            // if (parseInt(response.total_percent) == 0) {
            //     shippingBar.addClass('wfspb-hidden');
            // } else {
            //     shippingBar.removeClass('wfspb-hidden');
            // }

            woocommerce_free_shipping_bar.clear_time_display();
            woocommerce_free_shipping_bar.bar_show();

            woocommerce_free_shipping_bar.gift_box_hide();

            $("#wfspb-main-content").html(response.message_bar);
            // $(".woocommerce-free-shipping-bar-order").html(response.small_bar);
            let shippingBarOrder = $(".woocommerce-free-shipping-bar-order");

            shippingBarOrder.each(function (index, element) {
                for (let i in response.shortcode_bar) {
                    if ($(element).hasClass('wfspb-shortcode-' + i)) {
                        $(element).html(response.shortcode_bar[i]);
                    } else if (!$(element).hasClass('wfspb-is-shortcode')) {
                        $(element).html(response.small_bar)
                    }
                }
            });

            $("#wfspb-current-progress, .woocommerce-free-shipping-bar-order-bar-inner").animate({width: response.total_percent + '%'}, 1000);
            $("#wfspb-label").html(parseInt(response.total_percent) + '%');

            parseInt(response.total_percent) >= 100 || parseInt(response.total_percent) == 0 ? $('#wfspb-progress').fadeOut(500) : $('#wfspb-progress').show();
        }
    };


    woocommerce_free_shipping_bar.time_to_disappear = _wfsb_params.time_to_disappear;
    woocommerce_free_shipping_bar.init();

    let themeHeader;
    window.onscroll = () => {
        if (!themeHeader || themeHeader.length === 0) themeHeader = $(`${_wfsb_params.headerSelector}`);
        let topPos = document.documentElement.scrollTop;
        if (topPos > 100) {
            if (shippingBar.css('display') === 'none') {
                shippingBar.removeClass('wfsb-fixed');
                return;
            }
            shippingBar.addClass('wfsb-fixed');
            if (shippingBar.hasClass('top_bar')) {
                let height = shippingBar.outerHeight(true);
                if (themeHeader) themeHeader.css('cssText', `top:${height}px !important;`);
            }
        } else if (topPos === 0) {
            shippingBar.removeClass('wfsb-fixed');
            if (themeHeader) themeHeader.css('top', 'inherit');
        }
    };
});



