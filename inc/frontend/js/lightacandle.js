jQuery.noConflict();

jQuery(document).ready(function($) {
    $("#candle_anonym").change(function() {
        if ($(this).is(":checked")) {
            $("#candle_name")
                .val("")
                .attr({
                    placeholder: "Anonymous"
                });
        } else {
            $("#candle_name")
                .val("")
                .attr("placeholder", "Jouw naam");
        }
    });
    $("#candle_name").on("input", function() {
        if ($(this).val()) {
            $("#candle_anonym").attr("checked", false);
        } else {
            $("#candle_anonym").attr("checked", true);
        }
    });

    $("#candle-show-modal").click(function() {
        $("body")
            .css({ position: "relative", overflow: "hidden" })
            .append('<div class="modal-backdrop"></div>');
        $(".candle-modal")
            .show()
            .addClass("modal-open");
    });
    $("#light_a_candle").click(function() {
        $(".candle-form__in").fadeToggle();
    });

    $(".candle-modal-close").click(function() {
        $("body").css({ position: "static", overflow: "visible" });

        $(".modal-backdrop").remove();
        $(".candle-modal")
            .hide()
            .removeClass("modal-open");
    });
    $("body").on("click", ".modal-open", function(e) {
        if (
            !$(e.target).is(
                ".candle-modal-dialog, .candle-modal-content, .candle-modal h2, .candle-authors, .candle-authors div, .candle-authors time, .candle-authors p"
            )
        ) {
            $(".candle-modal-close").click();
        }
    });

    $(".candle-form").submit(function(e) {
        e.preventDefault();
        var $form = $(this);
        var $candleButton = $("#light_a_candle");
        var isDisabled = $candleButton.attr("disabled");
        var postID = $candleButton.attr("data-id");
        var newDate = new Date();
        var minutes = newDate.getMinutes() || "00";
        var candleDate =
            new Date().toLocaleDateString() +
            " at " +
            newDate.getHours() +
            ":" +
            minutes;

        if (typeof isDisabled !== typeof undefined && isDisabled !== false) {
            return false;
        }

        $(document.body).css({ cursor: "wait" });

        $.ajax({
            type: "POST",
            url: LIGHT_A_CANDLE.ajaxurl,
            data: {
                action: "light_a_candle",
                post_id: postID,
                candle_name: $("#candle_name").val(),
                candle_date: candleDate,
                nonce: LIGHT_A_CANDLE.nonce
            },
            dataType: "json",
            beforeSend: function() {
                $candleButton.attr("disabled", true);
                $candleButton.html("Loading ...");
            },
            success: function(response) {
                let data = response.data;
                console.log(JSON.parse(data.authors));
                $("#light_a_candle_response_count").html(data.string);
                $("#light_a_candle_response").html(data.thankyou);
                $candleButton.html("Success");
                $candleButton.removeAttr("disabled");
                $form.fadeOut();
                $(document.body).css({ cursor: "default" });
                $("#light_a_candle_response")
                    .delay(6000)
                    .fadeOut("slow");
                var candles = $("<div>");

                $.each(JSON.parse(data.authors), function(i, val) {
                    candles.append(
                        "<div><time>" +
                            val.candle_date +
                            "</time><p>" +
                            val.candle_name +
                            "</p></div>"
                    );
                });
                $(".candle-authors").html(candles);
            }
        }).fail(function(error) {
            $(document.body).css({ cursor: "default" });
            $candleButton.html("Try again");
            $candleButton.removeAttr("disabled");
            console.log(error);
        });
    });
});
