function ajax_call($action, $formdata = '', $submitbutton = '', $replaceEl) {
    return new Promise((resolve, reject) => {
        const xhr = new XMLHttpRequest();
        const submit_button = $submitbutton
        if (submit_button) {
            submit_button.classList.add('loading');
        }
        xhr.open('POST', wc_add_to_cart_params.ajax_url);
        // set the request header
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        // send the request
        const params = new URLSearchParams($formdata).toString();
        const $string = `action=${$action}&` + params;

        xhr.send($string);

        // listen for the response
        xhr.onload = function () {
            // get the response
            const response = JSON.parse(xhr.responseText);
            const notices = response.notices
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

        }
    })
}

   
export {
    ajax_call,

}