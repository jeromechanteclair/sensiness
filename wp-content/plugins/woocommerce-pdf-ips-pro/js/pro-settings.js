jQuery( function( $ ) {
	// show warning when filename does not contain unique identifier
	$('#wpo-wcpdf-settings #filename').change(function(){
		var filename = $(this).val();
		if ( filename.length > 0 && ( filename.indexOf("{{") == -1 || filename.indexOf("}}") == -1 ) ) {
			$(this).closest('td').addClass('setting-warning');
		} else {
			$(this).closest('td').removeClass('setting-warning');
		}
	}).change();

	// Disable 'Always use most current settings' checkbox when 'Keep PDF' is active
	// Check if keep PDF is active on page load
	if($('input#archive_pdf').prop("checked") == true){
		$( 'input#use_latest_settings' ).prop( 'disabled', true );
	}
	// Disable/enable 'Always use most current settings' checkbox when 'Keep PDF' checkbox changes
	$('input#archive_pdf').click(function(){
		if($(this).prop("checked") == true){
			$( 'input#use_latest_settings' ).prop( 'disabled', true );
		}
		else if($(this).prop("checked") == false){
			$( 'input#use_latest_settings' ).prop( 'disabled', false );
		}
	});
	
});