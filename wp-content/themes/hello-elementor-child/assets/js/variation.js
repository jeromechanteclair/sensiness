function variation() {
        var $form;
    $(document).on('click', '[data-select]', function (e) {
        // resetAttributes($(this));
        
        
        allowattributes($(this));
        
        
    })

  


   function allowattributes(trigger) {

		// $('form.cart').trigger('reload_product_variations');
		let select = $(trigger).attr('data-select');
		let value = $(trigger).attr('data-value');
		let datalink = $(trigger).attr('data-link');
		let dataprice = $(trigger).attr('data-price');
		let datavariation = $(trigger).attr('data-variation_id');
        let $price = $(document).find('.custom-price__right ');
        let $variation_id = $(document).find('.variation_id');
         $variation_id.val(datavariation)
        console.log($price);
        $price.html( JSON.parse(dataprice));
       
		// console.log(datalink);
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
				// console.log($(el).attr('data-value'));
			} else {
				if ($(el).attr('data-select') !== select) {
					// console.log($(el).attr('data-value'));
				}
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
			// $('form.cart').find('[name="' + currentselect + '"]').val(currentval)
			// $('form.cart').find('[name="' + currentselect + '"]').trigger('change')
			// console.log($('form.cart').find('[name="' + currentselect + '"]'), $('form.cart').find('[name="' + currentselect + '"]').val());
		})
		// console.log(value);
        let $select =$('form.cart').find('[name="' + select + '"]');
		$select.val(value).trigger('change')
    
       $select.trigger('click')
		// $('.default.product__item-cart').find('.size').html(v.attributes['attribute_pa_sizes'])
		// $('.default.product__item-cart').find('.color').html(v.attributes['attribute_pa_colors'])

		// $('form.cart').find('[name="' + select + '"]').trigger('change')
		// trigger found_variation event
		//

   }


}
export{
    variation
}