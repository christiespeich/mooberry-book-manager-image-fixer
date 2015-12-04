jQuery( document ).ready(function() {
	disable();
	jQuery('.cmb2-id-mbdbif-images-to-fix .button').css('display', 'none');
	jQuery('input[name="mbdbif_images_to_fix[]"').on('change', toggle_restore);
	toggle_restore();

});


function toggle_restore() {
	disable();
	jQuery.each(jQuery('input[name="mbdbif_images_to_fix[]"]:checked'), function(){ 
		if (jQuery(this).val() == 'retailers' || jQuery(this).val() == 'formats') {
			jQuery('#mbdbif_include_deleted').prop('disabled',  false);
			jQuery('.cmb2-id-mbdbif-include-deleted label').css('color', '#222');
			return false;
		}
	});
}

function disable() {
	jQuery('#mbdbif_include_deleted').prop('disabled',  true);
	jQuery('.cmb2-id-mbdbif-include-deleted label').css('color', 'lightgray');
}
	
	
