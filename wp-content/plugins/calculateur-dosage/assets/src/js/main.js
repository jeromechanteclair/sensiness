const forms = document.querySelectorAll('.cbd-calculator-form');

if (forms) {
    forms.forEach(form => {
        const product = form.querySelector('#product');
        const traitement = form.querySelector('#traitement');
        const human_weight = form.querySelector('#human_weight');
        const posologie__value = document.querySelector('.posologie__value');
        const product_selector = form.querySelector('.product_selector');
        const radios = form.querySelectorAll('input[type="radio"]');
        const traitement_selector = form.querySelector('.traitement_selector');
        const human_weight_selector = form.querySelector('.human_weight_selector');
        const calculator__posologie = form.querySelector('.cbd-calculator__posologie');

        let radiosvalue = get_radio_value(radios);
        let product_value = product.value
        let traitement_value = traitement.value
        let option = traitement.options[traitement.selectedIndex];
        let posologies = JSON.parse(option.getAttribute("data-posologie"))
        let human_weight_value = human_weight.value
        let output = form.querySelector(".range_value span");
        let posologieValue = get_posologie(posologies, human_weight_value);

        posologie__value.innerHTML = posologieValue;
        output.innerHTML = human_weight.value;

        if (product_selector) {
            if (product_selector.classList.contains('hide')) {
                product_value = false;
            }
            if (product) {
                product.addEventListener("change", function (e) {

                    product_value = e.currentTarget.value

                    let displayedoptions = traitement_selector.querySelectorAll('option');
                    let product_values = JSON.parse(product_value);
                    for (const [i, option] of Object.entries(displayedoptions)) {


                        if (!product_values.includes(parseInt(option.value))) {
                            option.hidden = true

                        } else {
                            option.hidden = false;
                            traitement.value = option.value;
                        }
                    }
                    let event = new Event('change', {
                        'bubbles': true
                    })

                    traitement.dispatchEvent(event);
                    if (product_value !== '*') {

                        traitement_selector.classList.remove('hide')
                    } else {
                        traitement_selector.classList.add('hide')

                    }


                }, false);
            }
        }
        if (radios) {
            radios.forEach(radio => {
                radio.addEventListener('click', function () {
                    radiosvalue = radio.value;
                    let results = form.nextElementSibling;
                    if (product_selector.classList.contains('hide')) {
                        results.classList.add('hide');
                        traitement_selector.classList.add('hide');
                        human_weight_selector.classList.add('hide');
                        calculator__posologie.classList.add('hide');
                        product_selector.classList.remove('hide')
                        product_value = product.value
                    } else {
                        results.classList.remove('hide');
                        product_selector.classList.add('hide')
                        traitement_selector.classList.remove('hide');
                        human_weight_selector.classList.remove('hide');
                        calculator__posologie.classList.remove('hide');
                        product_value = false;
                        let displayedoptions = traitement_selector.querySelectorAll('option');

                        for (const [i, option] of Object.entries(displayedoptions)) {
                            option.hidden = false;
                        }
                        let event = new Event('change', {
                            'bubbles': true
                        })

                        product.dispatchEvent(event);
                        product.value = '*';

                    }
                    ajaxcall(form, product_value, traitement_value, human_weight_value);

                });
            });
        }

        if (traitement) {
            traitement.addEventListener("change", function (e) {

                traitement_value = e.currentTarget.value
                option = traitement.options[traitement.selectedIndex];
                posologies = JSON.parse(option.getAttribute("data-posologie"))
                ajaxcall(form, product_value, traitement_value, human_weight_value);
                posologie__value.innerHTML = get_posologie(posologies, human_weight_value);
                human_weight_selector.classList.remove('hide');
                calculator__posologie.classList.remove('hide');
            }, false);
        }
        if (human_weight) {
            human_weight.addEventListener("change", function (e) {
                human_weight_value = e.currentTarget.value
                ajaxcall(form, product_value, traitement_value, human_weight_value);;
                posologie__value.innerHTML = get_posologie(posologies, human_weight_value);
            }, false);
            human_weight.addEventListener("input", function (e) {

                output.innerHTML = e.currentTarget.value;

                let value = (e.currentTarget.value - e.currentTarget.min) / (e.currentTarget.max - e.currentTarget.min) * 100;

                this.style.background = 'linear-gradient(to right, #FFD372 0%, #FFD372 ' + value + '%, #ffff ' + value + '%, #ffff 100%)'
            }, false);
            
        }
        ajaxcall(form, product_value, traitement_value, human_weight_value);


    });
}

function get_radio_value(radios) {
    for (let i = 0; i < radios.length; i++) {
        if (radios[i].checked) {
            return radios[i].value;
        }
    }
}

function get_posologie(posologies, human_weight_value) {


    for (const [key, value] of Object.entries(posologies)) {

        if (parseInt(key) >= parseInt(human_weight_value)) {

            result = value;
            break;
        }
    }

    return result;

}

function ajaxcall(form, product_value, traitement_value, human_weight_value) {
    const xhr = new XMLHttpRequest();

    // open the request
    // add class loading to the button
    // get submit button
    // const submit_button = form.querySelector('button[type="submit"]');

    // submit_button.classList.add('loading');
    xhr.open('POST', wc_add_to_cart_params.ajax_url);
    // set the request header
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    // send the request
    // whr set action
    if (!product_value) {
        $string = "action=caculateur_ajax_results&traitement_value=" + traitement_value + "&human_weight_value=" + human_weight_value;

    } else {

        $string = "action=caculateur_ajax_results&product_value=" + product_value + "&traitement_value=" + traitement_value + "&human_weight_value=" + human_weight_value;
    }


    xhr.send($string);

    // listen for the response
    xhr.onload = function () {
        // get the response
        const response = JSON.parse(xhr.responseText);

        const msg = response.data.msg;
        const status = response.data.status;
        if (status === 'success') {
            // replace form with success message

         let    innerresult = form.nextElementSibling;
            innerresult.innerHTML = '<p>' + msg + '</p>';


        } else {

        }



    }
}