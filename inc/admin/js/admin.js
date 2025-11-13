/**
 * Feedier plugin Saving process
 */
jQuery( document ).ready( function () {
    jQuery("#tahlil-admin-form").append('<input type="hidden" name="action" value="store_admin_data" />');
    jQuery("#tahlil-admin-form").append('<input type="hidden" name="security" value="'+ tahlil_exchanger._nonce +'" />');
	
    jQuery( document ).on( 'submit', '#tahlil-admin-form', function ( e ) {
        e.preventDefault();
 
        // We make our call
        jQuery.ajax( {
            url: tahlil_exchanger.ajax_url,
            type: 'post',
            data: jQuery(this).serialize(),
            success: function ( response ) {
                alert(response);
            }
        } );
 
    } );
 
} );