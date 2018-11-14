jQuery.noConflict();

jQuery(document).ready(function ($) {

    $('#light_a_candle').click(function (e) {
        e.preventDefault();
        var that = this;
        var isDisabled = $(this).attr('disabled');
        var postID = $(this).attr('data-id');

        if (typeof isDisabled !== typeof undefined && isDisabled !== false) {
            return false;
        }

        $(this).attr('disabled', true);
        $(this).html('Loading ...');

        $(document.body).css({'cursor': 'wait'});

        $.ajax({
            type: 'POST',
            url: LIGHT_A_CANDLE.ajaxurl,
            data: {
                action: 'light_a_candle',
                post_id: postID,
                nonce: LIGHT_A_CANDLE.nonce
            },
            dataType: 'json',
            success: function (response) {
                let data = response.data
                $('#light_a_candle_response_count').html(data.string)
                $('#light_a_candle_response').html(data.thankyou)
                $(that).html('Success');
                $(that).removeAttr('disabled');
                $(that).fadeOut();
                $(document.body).css({'cursor': 'default'});
            }
        }).fail(function (error) {
            $(document.body).css({'cursor': 'default'});
            $(that).html('Try again');
            $(that).removeAttr('disabled');
            console.log(error)
        });

    });

});