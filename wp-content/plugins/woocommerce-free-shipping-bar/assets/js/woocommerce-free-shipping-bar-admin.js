jQuery(document).ready(function () {
    /**
     * Created by huyko on 02/06/2017.
     */
    'use strict';
    jQuery('.vi-ui.tabular.menu .item').vi_tab({
        history: true,
        historyType: 'hash'
    });

    /*Setup tab*/
    var tabs,
        tabEvent = false,
        initialTab = 'general',
        navSelector = '.vi-ui.menu',
        navFilter = function (el) {
            return jQuery(el).attr('href').replace(/^#/, '');
        },
        panelSelector = '.vi-ui.tab',
        panelFilter = function () {
            jQuery(panelSelector + ' a').filter(function () {
                return jQuery(navSelector + ' a[title=' + jQuery(this).attr('title') + ']').size() != 0;
            }).each(function (event) {
                jQuery(this).attr('href', '#' + $(this).attr('title').replace(/ /g, '_'));
            });
        };

    // Initializes plugin features
    jQuery.address.strict(false).wrap(true);

    if (jQuery.address.value() == '') {
        jQuery.address.history(false).value(initialTab).history(true);
    }

    // Address handler
    // jQuery.address.init(function (event) {
    //
    //     // Adds the ID in a lazy manner to prevent scrolling
    //     jQuery(panelSelector).attr('id', initialTab);
    //
    //     // Enables the plugin for all the content links
    //     jQuery(panelSelector + ' a').address(function () {
    //         return navFilter(this);
    //     });
    //
    //     panelFilter();
    //
    //     // Tabs setup
    //     tabs = jQuery('.vi-ui.menu')
    //         .vi_tab({
    //             history: true,
    //             historyType: 'hash'
    //         })
    //
    //     // Enables the plugin for all the tabs
    //     jQuery(navSelector + ' a').click(function (event) {
    //         tabEvent = true;
    //         jQuery.address.value(navFilter(event.target));
    //         tabEvent = false;
    //         return false;
    //     });
    //
    // });
    jQuery('.vi-ui.checkbox').checkbox();
    jQuery('.vi-ui.dropdown').dropdown();
    /*Save Submit button*/
    jQuery('.wfsb-submit').one('click', function () {
        jQuery(this).addClass('loading');
    });

    jQuery('.wfspb-sub-settime').dependsOn({
        'input[name="wfspb-param[time-to-disappear]"]': {
            checked: true
        }
    });


    jQuery('.wfspb-progress-percent').dependsOn({
        'input[name="wfspb-param[enable-progress]"]': {
            checked: true
        }
    });

    jQuery('.wfsb-small-bar').dependsOn({
        'input[name="wfspb-param[show_at_order_bottom]"]': {
            checked: true
        }
    });
    jQuery('.wfsb-bar-in-mini-cart').dependsOn({
        'input[name="wfspb-param[position_mini_cart]"]': {
            checked: true
        }
    });
    jQuery('.wfspb-gift-box-option').dependsOn({
        'select[name="wfspb-param[gift_icon]"]': {
            values: ['1']

        }
    });

    jQuery('input[name="wfspb-param[bg-color]"]').colorPicker({
        renderCallback: function ($elm, toggled) {
            var id = $elm.attr('id');
            if ($elm.text) {
                jQuery('#wfspb-top-bar').css('background-color', $elm.text);
            }
        }
    });

    jQuery('input[name="wfspb-param[text-color]"]').colorPicker({
        renderCallback: function ($elm, toggled) {
            var id = $elm.attr('id');
            if ($elm.text) {
                jQuery('#wfspb-top-bar').css('color', $elm.text);
            }
        }
    });

    jQuery('input[name="wfspb-param[link-color]"]').colorPicker({
        renderCallback: function ($elm, toggled) {
            var id = $elm.attr('id');
            if ($elm.text) {
                jQuery('#wfspb-top-bar #wfspb-main-content a').css('color', $elm.text);
            }
        }
    });

    jQuery('input[name="wfspb-param[progress-text-color]"]').colorPicker({
        renderCallback: function ($elm, toggled) {
            var id = $elm.attr('id');
            if ($elm.text) {
                jQuery('#wfspb-label').css('color', $elm.text);
            }
        }
    });

    jQuery('input[name="wfspb-param[bg-color-progress]"]').colorPicker({
        renderCallback: function ($elm, toggled) {
            var id = $elm.attr('id');
            if ($elm.text) {
                jQuery('#wfspb-progress').css('background-color', $elm.text);
            }
        }
    });

    jQuery('input[name="wfspb-param[bg-current-progress]"]').colorPicker({
        renderCallback: function ($elm, toggled) {
            var id = $elm.attr('id');
            if ($elm.text) {
                jQuery('#wfspb-current-progress').css('background-color', $elm.text);
            }
        }
    });

    jQuery('input[name="wfspb-param[position]"]').on('change', function () {
        var data = jQuery(this).val();
        if (data == 0) {
            jQuery('#wfspb-top-bar').removeClass('bottom_bar').addClass('top_bar');
        } else {
            jQuery('#wfspb-top-bar').removeClass('top_bar').addClass('bottom_bar');
        }
    });

    jQuery('.select-textalign').dropdown({
        onChange: function () {
            var text_align = jQuery('.select-textalign').children('.text').text();
            jQuery('#wfspb-top-bar #wfspb-main-content').css('text-align', text_align);
        }
    });

    jQuery('#wfspb-font').fontselect().change(function () {
        var font = jQuery(this).val().replace(/\+/g, ' ');
        jQuery('#wfspb-top-bar').css('font-family', font);

    });

    jQuery('.select-fontsize').dropdown({
        onChange: function () {
            var font_size = jQuery('.select-fontsize').children('.text').text();
            jQuery('#wfspb-top-bar #wfspb-main-content').css('font-size', font_size);
            jQuery('#wfspb-close').css({'font-size': font_size, 'line-height': font_size});
        }
    });

    jQuery('.select-fontsize-progress').dropdown({
        onChange: function () {
            var font_size = jQuery('.select-fontsize-progress').children('.text').text();
            jQuery('#wfspb-label').css('font-size', font_size);
        }
    });

    jQuery('.wfspb-enable-progress').checkbox('setting', 'onChange', function () {
        if (jQuery('.wfspb-enable-progress').hasClass('checked')) {
            jQuery('#wfspb-progress').removeClass('disable_progress_bar').addClass('anable_progress_bar');
        } else {
            jQuery('#wfspb-progress').removeClass('anable_progress_bar').addClass('disable_progress_bar');
        }
    });
    /**
     * Start Get download key
     */
    jQuery('.villatheme-get-key-button').one('click', function (e) {
        let v_button = jQuery(this);
        v_button.addClass('loading');
        let data = v_button.data();
        let item_id = data.id;
        let app_url = data.href;
        let main_domain = window.location.hostname;
        main_domain = main_domain.toLowerCase();
        let popup_frame;
        e.preventDefault();
        let download_url = v_button.attr('data-download');
        popup_frame = window.open(app_url, "myWindow", "width=380,height=600");
        window.addEventListener('message', function (event) {
            /*Callback when data send from child popup*/
            let obj = jQuery.parseJSON(event.data);
            let update_key = '';
            let message = obj.message;
            let support_until = '';
            let check_key = '';
            if (obj['data'].length > 0) {
                for (let i = 0; i < obj['data'].length; i++) {
                    if (obj['data'][i].id == item_id && (obj['data'][i].domain == main_domain || obj['data'][i].domain == '' || obj['data'][i].domain == null)) {
                        if (update_key == '') {
                            update_key = obj['data'][i].download_key;
                            support_until = obj['data'][i].support_until;
                        } else if (support_until < obj['data'][i].support_until) {
                            update_key = obj['data'][i].download_key;
                            support_until = obj['data'][i].support_until;
                        }
                        if (obj['data'][i].domain == main_domain) {
                            update_key = obj['data'][i].download_key;
                            break;
                        }
                    }
                }
                if (update_key) {
                    check_key = 1;
                    jQuery('.villatheme-autoupdate-key-field').val(update_key);
                }
            }
            v_button.removeClass('loading');
            if (check_key) {
                jQuery('<p><strong>' + message + '</strong></p>').insertAfter(".villatheme-autoupdate-key-field");
                jQuery(v_button).closest('form').submit();
            } else {
                jQuery('<p><strong> Your key is not found. Please contact support@villatheme.com </strong></p>').insertAfter(".villatheme-autoupdate-key-field");
            }
        });
    });
    /**
     * End get download key
     */
});

