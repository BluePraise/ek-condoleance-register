(function( $ ) {
	'use strict';
	$('#attachmentForm').validate({
		rules: {
			author: {
				required: true,
				minlength: 2
			},

			email: {
				required: true,
				email: true
			},

			comment: {
				required: true,
				minlength: 10
			},

			pmg_comment_title: {
				required: true,
				minlength: 2
			},

			attachment: {
				required: true
			}
		},

		messages: {
			author: "Please fill the required field",
			email: "Vul alstublieft een geldig e-mailadres in.",
			comment: "Vul alstublieft uw bericht in",
			pmg_comment_title: "Vul alstublieft uw naam in.",
			attachment: "Het bestand dat u uploadt moet een geldig bestandstype zijn (jpg, jpeg, gif, png) en minder dan 2 MB!"
		},

		errorElement: "div",
		errorPlacement: function(error, element) {
			element.after(error);
		}
	});
})( jQuery );
