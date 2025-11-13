/**
 * Condoleance Register - Frontend JavaScript
 *
 * @package CondoleanceRegister
 * @since 2.0.0
 */

(function($) {
    'use strict';

    const CondoleanceRegister = {
        init() {
            this.bindEvents();
        },

        bindEvents() {
            $(document).on('click', '.condoleance-light-candle', this.lightCandle.bind(this));
        },

        lightCandle(e) {
            e.preventDefault();

            const $button = $(e.currentTarget);
            const postId = $button.data('post-id');
            const name = $button.data('name') || '';

            if ($button.hasClass('loading')) {
                return;
            }

            $button.addClass('loading').prop('disabled', true);

            $.ajax({
                url: condoleanceRegister.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'light_candle',
                    nonce: condoleanceRegister.nonce,
                    post_id: postId,
                    name: name
                },
                success: (response) => {
                    if (response.success) {
                        this.updateCandleCount(postId, response.data.count);
                        this.showNotification(response.data.message, 'success');
                    } else {
                        this.showNotification(response.data.message, 'error');
                    }
                },
                error: () => {
                    this.showNotification(condoleanceRegister.strings.error, 'error');
                },
                complete: () => {
                    $button.removeClass('loading').prop('disabled', false);
                }
            });
        },

        updateCandleCount(postId, count) {
            $(`.condoleance-candle-count[data-post-id="${postId}"]`).text(count);
        },

        showNotification(message, type) {
            // Simple notification - can be enhanced with a proper notification library
            alert(message);
        }
    };

    $(document).ready(() => CondoleanceRegister.init());

})(jQuery);
