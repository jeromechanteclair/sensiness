/* global wc_admin_bogof_metabox_params */
;( function( $ ) {

	if ( 'undefined' === typeof wc_admin_bogof_metabox_params ) {
		return;
	}

	// True/False switch
	$.fn.true_false_switch = function() {
		function toogleClass( $toggleEl ) {
			var target        = $toggleEl.attr('href');
			var toggleclass   = 'yes' === $(target).val() ? 'enabled' : 'disabled';
			$toggleEl.find('span.woocommerce-input-toggle').removeClass('woocommerce-input-toggle--enabled').removeClass('woocommerce-input-toggle--disabled').addClass('woocommerce-input-toggle--' + toggleclass);
		};

		this.each( function(){
			$(this).on('click', function(e) {
				e.preventDefault();
				var target = $(this).attr('href');
				value      = 'yes' == $(target).val() ? 'no' : 'yes';
				$(target).val(value);
				toogleClass($(this));
				// Change envent.
				$(target).trigger('change');
			});

			toogleClass($(this));
		});

		return this;
	};

	// Hide/Show fields by conditions.
	$.fn.show_hide_fields = function()  {
		//Evaluate a condition.
		function conditionEval(field, operator, value) {
			var eval      = false;
			var $field    = $('[name="' + field + '"]').length ? $('[name="' + field + '"]') : $('#' + field);
			var field_val = $field.val();
			if ('=' == operator) {
				eval = (field_val == value);
			} else {
				// operator !=
				eval = (field_val != value);
			}
			return eval;
		}

		// On field change.
		$(this).on('wc_bogo_field_change', function() {
			var conditions = $(this).data('show-if');
			if ( conditions instanceof Array ) {
				var showHide = true;
				conditions.forEach(function(condition){
					showHide = showHide && conditionEval( condition.field, condition.operator, condition.value);
				});
				$(this).toggle(showHide);
			}
		});

		// Listen to the change event.
		this.each(function(){
			var $that = $(this);
			var conditions = $that.data('show-if');
			if ( conditions instanceof Array ) {
				conditions.forEach(function(condition){
					var $field = $('[name="' + condition.field + '"]').length ? $('[name="' + condition.field + '"]') : $('#' + condition.field);
					$field.on('change', function(){
						$that.triggerHandler('wc_bogo_field_change');
					});
				});
			}
		});

		// Show/Hide the element.
		this.each(function(){
			$(this).triggerHandler('wc_bogo_field_change');
		});

		return this;
	};

	// Table input.
	$.fn.table_multirow = function() {
		this.each( function(){
			var $that    = $(this);
			var $table   = $that.find('table.wc-bogo-table>tbody').first();

			$that.on('click', 'a.add-row', function(e){
				e.preventDefault();
				var templateId = $(this).attr('href')
				var trTemplate = wp.template( templateId );
				var data       = $(this).data();
				var lastRowId  = $table.find('>tr.row-input').last().data('rowId') + 1;
				var row        = trTemplate($.extend(data, {rowId: lastRowId }));
				var $row       = $table.append( row )

				$row.find('[data-show-if]').show_hide_fields();	// Show-hide fields.
				$( document.body ).trigger('wc-enhanced-select-init'); // init enhanced-select.
			});

			$that.on('click', 'a.remove-row', function(e){
				e.preventDefault();
				// Remove the row.
				$(this).closest('tr').remove();
			});
		});

		return this;
	};

	// Display errors.
	$.fn.wc_bogo_display_error = function( error_message, hide_on_focus ) {
		if (this.parent().find( '.wc-bogo-error-tip' ).length > 0) {
			this.wc_bogo_hide_errors();
		}

		// Add the wrapper.
		if (! this.parent().hasClass('wc-bogo-error-tip-wrap')) {
			this.parent().addClass('wc-bogo-error-tip-wrap');
		}

		// Get dimensions.
		var $el = this.hasClass('select2-hidden-accessible') ? this.parent().find('span.select2.select2-container') : this;
		var position = $.extend( $el.position(), {
			width: $el.width(),
			height: $el.height()
		});

		// Display the error tip.
		var $tip = $( '<div class="wc-bogo-error-tip" style="display:none;">' + error_message + '</div>' );
		this.parent().append($tip);
		$tip.css( 'left', position.left + position.width - ( position.width / 2 ) - ( $( '.wc-bogo-error-tip' ).width() / 2 ) )
			.css( 'top', position.top + position.height + 4 )
			.fadeIn( '100' );

		if ( true === hide_on_focus ) {
			$el.addClass('-hide-error-on-focus');
		}
		return this;
	};

	// Hide errors.
	$.fn.wc_bogo_hide_errors = function() {
		var $parent = this.parent();
		$parent.find('.wc-bogo-error-tip').fadeOut( '100', function() {
			$(this).remove();
		} );
		this.removeClass('-hide-error-on-focus');
		return this;
	};

	// Hide all errors.
	wc_bogo_hide_all_errors = function() {
		$('.wc-bogo-error-tip').remove();
	};

	// Quantities.
	$.fn.wc_bogo_quantities = function() {
		var $quantities = this;

		// Quantity error tip.
		$quantities.on( 'wc_bogo_add_quantity_error', '[name^="_free_quantity"]', function(){
			$(this).wc_bogo_display_error(wc_admin_bogof_metabox_params.i18n.free_less_than_min_error);
		});

		// Remove error tip.
		$quantities.on( 'wc_bogo_remove_quantity_error', '[name^="_free_quantity"]', function(){
			$(this).wc_bogo_hide_errors();
		});

		/**
		 * Check the min quantity is greater than the free quantitie.
		 *
		 * @param {*} $row The row contains the input texts.
		 */
		function checkQuantities($row) {
			var $free_qty_input = $row.find('[name^="_free_quantity"]');
			var free_qty        = parseInt($free_qty_input.val(), 10);
			var min_qty         = parseInt($row.find('[name^="_min_quantity"]').val(), 10);
			if ( isNaN(free_qty) || min_qty > free_qty ) {
				$free_qty_input.trigger('wc_bogo_remove_quantity_error');
			} else {
				$free_qty_input.trigger('wc_bogo_add_quantity_error');
			}
		};

		/**
		 * On quantity change.
		 */
		function onQuantityChange(){
			if ( 'checking' == $quantities.data('quantities_status') ) {
				var $row = $(this).closest('tr');
				checkQuantities($row);
			}
		};

		/**
		 * Remove all error tips.
		 */
		function removeAllErrors() {
			$quantities.find('.wc-bogo-error-tip').remove();
		}

		// Custom event to change the quantity checking status.
		$quantities.on('wc_bogo_checking_quantities', function(event, check){
			$quantities.data('quantities_status', (check ? 'checking' : 'off'));
			if ( check ) {
				$quantities.find('[name^="_min_quantity"]').each(onQuantityChange);
			} else {
				removeAllErrors();
			}
		});

		// Check the quantities on changes.
		$quantities.on('keyup mouseup', '[name^="_free_quantity"]', onQuantityChange);
		$quantities.on('change', '[name^="_min_quantity"]', onQuantityChange);

		return this;
	};

	// Post Box.
	$.fn.wc_bogo_postbox = function() {

		var $postbox = this;

		// Return the promotion type.
		function getType () {
			return $postbox.find('#_type').val();
		};

		// Check type of promotion.
		function isType( typeToCheck ) {
			return typeToCheck === getType();
		}

		// Is the promotion enabled?
		function isEnabled() {
			return 'yes' === $('#_enabled').val();
		}

		// True/False switchs.
		$postbox.find('.wc-bogo-true-false').true_false_switch();

		// Table input.
		$postbox.find('.wc-bogo-table-input').table_multirow();

		// Quantities error tips
		$postbox.find('.wc-bogo-field.-quantity_rules')
			.wc_bogo_quantities()
			.trigger('wc_bogo_checking_quantities', [isType('cheapest_free') && isEnabled()]);

		// On type change.
		$postbox.on('change', '#_type', function(){
			// Check quantities.
			$postbox.find('.wc-bogo-field.-quantity_rules').trigger('wc_bogo_checking_quantities', [isType('cheapest_free') && isEnabled()] );
		});

		// On enable change.
		$postbox.on('change', '#_enabled', function(){
			// Check quantities.
			$postbox.find('.wc-bogo-field.-quantity_rules').trigger('wc_bogo_checking_quantities', [isType('cheapest_free') && isEnabled()] );
			// Remove all errors after disable the promiton.
			if ( ! isEnabled() ) {
				wc_bogo_hide_all_errors();
			}
		});

		// Init date picker.
		$postbox.find('.date-picker').datepicker({
			dateFormat: 'yy-mm-dd',
			numberOfMonths: 1,
			showButtonPanel: true
		});

		// Conditional Logic.
		$postbox.find('[data-show-if]').show_hide_fields();

		// Remove placeholder.
		$postbox.find('.wc-bogo-fields').removeClass('-loading');
		$postbox.find('.wc-bogo-field.-placeholder').remove();

		return $postbox;
	}

	// Post Box.
	$.fn.wc_bogo_submitpost = function() {
		var $submitpost = this;
		var xhr         = false;
		var validForm   = false;

		// Show Spinner.
		function showSpinner($spinner) {
			$spinner.addClass('is-active'); // add class (WP > 4.2)
			$spinner.css('display', 'inline-block'); // css (WP < 4.2)
			return $spinner;
		};

		// Hide Spinner.
		function hideSpinner($spinner) {
			$spinner.removeClass('is-active'); // add class (WP > 4.2)
			$spinner.css('display', 'none'); // css (WP < 4.2)
			return $spinner;
		};

		// Lock Form.
		function lockForm() {
			var $wrap = $submitpost.find('#submitpost');
			var $submit = $wrap.find('.button, [type="submit"]');
			var $spinner = $wrap.find('.spinner'); // hide all spinners (hides the preview spinner)

			// lock
			$submit.addClass('disabled');
			showSpinner($spinner.last());

			// Hide previous errors.
			wc_bogo_hide_all_errors();
			hideNotice();
		  };

		// UnLock Form.
		function unlockForm() {
			var $wrap = $submitpost.find('#submitpost');
			var $submit = $wrap.find('.button, [type="submit"]');
			var $spinner = $wrap.find('.spinner, .acf-spinner'); // unlock

			$submit.removeClass('disabled');
			hideSpinner($spinner);
		};

		// Hide errors on focus.
		$submitpost.on('change focus', '.-hide-error-on-focus', function(){
			$(this).wc_bogo_hide_errors();
		});

		// Retrun AJAX data.
		function getAjaxData() {
			var actionName = 'wc_bogof_validate_save'
			var actionSet    = false;
			var ajaxData     = $submitpost.serializeArray();

			ajaxData.forEach(function(value, index){
				if ('action' === value.name ) {
					ajaxData[index].value = actionName;
					actionSet = true;
				}
			});
			if ( ! actionSet ) {
				ajaxData.push({
					name: 'action',
					value: actionName
				});
			}
			return ajaxData;
		};

		// Add or update a notice.
		function notice( message ) {
			var $notice;

			if ( $('#wc-bogo-validation-notice').length ) {
				$notice = $('#wc-bogo-validation-notice');
				$notice.hide();
			} else {
				$notice = $('<div id="wc-bogo-validation-notice" class="notice notice-error is-dismissible" style="display:none;"><p><strong></strong></p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>');
				$notice.on('click', 'button.notice-dismiss', function(){
					$(this).closest('.notice').fadeOut();
				});
				$notice.insertBefore($submitpost);
			}
			$notice.find('p>strong').text(message);
			$notice.fadeIn();
		}

		// Hide the notice.
		function hideNotice() {
			return $('#wc-bogo-validation-notice').hide();
		}

		// Display errors.
		function showErrors(errors) {
      		var $scrollTo = false;

			errors.forEach(function(error){
				var $input = $('#_'+error.field);
				$input.wc_bogo_display_error(error.message, true);
				if ( !$scrollTo ) {
					$scrollTo = $input;
				}
			});

			var errorCount = $('.-hide-error-on-focus').length;
			if ( errorCount>1 ) {
				notice(wc_admin_bogof_metabox_params.i18n.fields_requires_attention.replace('%s', errorCount));
			} else {
				notice(wc_admin_bogof_metabox_params.i18n.field_requires_attention);
			}

			if ( $scrollTo && $scrollTo.length ) {
				setTimeout(function () {
					$('html, body').animate({
						scrollTop: $scrollTo.offset().top - $(window).height() / 2
					}, 500);
				}, 10);
			}
		};

		// Validate the form via AJAX.
		$submitpost.find( ':submit' ).on( 'click.edit-post', function( event ) {
			if ( false !== xhr || event.isDefaultPrevented() || validForm ) {
				return;
			}
			lockForm();
			validForm = false;
			var ajaxData = getAjaxData();

			xhr = $.ajax({
				url: wc_admin_bogof_metabox_params.ajaxurl,
				dataType: 'json',
				type: 'post',
				data: $.param(ajaxData),
				success: function( response ){
					if ( Array.isArray( response ) && response.length ) {
						// Errors.
						showErrors(response);
						unlockForm();
					} else {
						// No errors. Submit post.
						validForm = true;
					}
				},
				complete: function() {
					xhr = false;
					if ( validForm ) {
						$submitpost.submit();
					}
				},
				error: function() {
					// Delegate validation.
					$submitpost.submit();
				}
			});

			return false;

		});
	};

	// Init PostBox.
	$(document).ready(function(){
		$('.postbox.wc-bogo-postbox').wc_bogo_postbox();
		$('form#post').wc_bogo_submitpost();
	});

})( jQuery );