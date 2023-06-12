import TomSelect from 'tom-select'
import styles from 'tom-select/dist/css/tom-select.min.css'

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
                option: function (data, escape) {
                    if (data.pricePromo) {


                        if (data.pricePromo.length > 0) {
                            return `<div><p>${data.text}</p><div class="select-prices"><span class="promo">${JSON.parse(data.pricePromo)}</span><span class="reg">${JSON.parse(data.priceReg)}</span></div></div>`;
                        } else {
                            return `<div><p>${data.text}</p><div class="select-prices"><span class="reg">${JSON.parse(data.priceReg)}</span></div></div>`;

                        }
                    }
                    else{
                       return `<div><p>${data.text}</p><div class="select-prices"><span class="reg"></span></div></div>`;

                    }

                },
                item: function (item, escape) {
                    if (item.pricePromo) {
                        if (item.pricePromo.length > 0) {

                            return `<div><p>${item.text}</p><div class="select-prices"><span class="promo">${JSON.parse(item.pricePromo)}</span><span class="reg">${JSON.parse(item.priceReg)}</span>${$svg}</div></div>`;
                        } else {
                            return `<div><p>${item.text}</p><div class="select-prices"><span class="reg">${JSON.parse(item.priceReg)}</span>${$svg}</div></div>`;

                        }
                    }
                    else{
                        return `<div><p>${item.text}</p><div class="select-prices"><span class="reg"></span>${$svg}</div></div>`;

                    }
                }
            }
        };
        tom = new TomSelect(select, settings);
        tom.settings.placeholder = "New placeholder";
        tom.inputState();
    })

    var $form;
    $(document).on('click', '[data-select]', function (e) {
        // resetAttributes($(this));


        allowattributes($(this));


    })
    if (tom) {

        tom.on('change', function (el) {

            let trigger = $(document).find('.variation-item[data-value=' + tom.getValue() + ']');
            allowattributes(trigger, true)
        })
    }




    function allowattributes(trigger, recurtion = false) {

        // $('form.cart').trigger('reload_product_variations');
        let select = $(trigger).attr('data-select');
        let value = $(trigger).attr('data-value');
        let datalink = $(trigger).attr('data-link');
        let dataprice = $(trigger).attr('data-price');
        let datavariation = $(trigger).attr('data-variation_id');
        let $price = $(document).find('.custom-price__right ');
        let $variation_id = $(document).find('.variation_id');
        $variation_id.val(datavariation)
        $price.html(JSON.parse(dataprice));


        // split data-link by white spaces
        let datalinkarray = datalink.split(' ');
        // remove empty value from datalinkarray
        datalinkarray = datalinkarray.filter(function (el) {
            return el.length > 0;
        });
        let lists = $(document).find('.variation-wrapper li');

        // find sibligns
        let siblings = $(document).find('[data-select="' + select + '"]');
        // remove active class from siblings
        siblings.removeClass('selected');
        lists.each(function (i, el) {
            if (!datalinkarray.includes($(el).attr('data-value')) && $(el).attr('data-select') !== select) {
                // $(el).addClass('disabled').removeClass('selected');
            } else {
                if ($(el).attr('data-select') !== select) {}
                // $(el).removeClass('disabled');


            }
            if ($(el).attr('data-select') !== select) {

            }
        })





        // add active class to clicked button
        $(trigger).addClass('selected');
        let $selected = $('form.cart').find('.selected')
        $selected.each(function (i, el) {
            let currentselect = $(el).attr('data-select');
            let currentval = $(el).attr('data-value');
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
        })

        let $select = $('form.cart').find('[name="' + select + '"]');
        $select.val(value).trigger('change')
        if (!recurtion) {
            if (tom) {
                tom.setValue(value);
            }
        }
        $select.trigger('click')


    }

}
export {
    select
}