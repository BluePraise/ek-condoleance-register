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
            $(document).on('submit', '.condoleance-light-candle-form', this.handleFormSubmit.bind(this));
        },

        handleFormSubmit(e) {
            e.preventDefault();

            const $form = $(e.currentTarget);
            const $button = $form.find('button[type="submit"]');
            const postId = $form.find('input[name="post_id"]').val();

            if ($button.hasClass('loading')) {
                return;
            }

            // Disable submit button
            $button.addClass('loading').prop('disabled', true);

            const name = $form.find('input[name="name"]').val();
            const isAnonymous = $form.find('input[name="anonymous"]').is(':checked');

            const payload = {
                name: name,
                anonymous: isAnonymous
            };

            fetch(`${condoleanceRegister.restUrl}/candles/${postId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                credentials: 'same-origin',
                body: JSON.stringify(payload)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.updateCandleCount(postId, data.count);
                    this.showNotification(data.message, 'success');

                    // Close modal and reset form
                    const modal = bootstrap.Modal.getInstance(document.getElementById('candleModal'));
                    if (modal) {
                        modal.hide();
                    }
                    $form[0].reset();
                } else {
                    console.error('Error response:', data);
                    this.showNotification(data.message || condoleanceRegister.strings.error, 'error');
                }
            })
            .catch(error => {
                console.error('Error lighting candle:', error);
                this.showNotification(condoleanceRegister.strings.error, 'error');
            })
            .finally(() => {
                $button.removeClass('loading').prop('disabled', false);
            });
        },
        updateCandleCount(postId, count) {
            $(`.condoleance-candle-count[data-post-id="${postId}"]`).text(count);
        },

        showNotification(message, type) {
            const $notification = $('.condoleance-notification');
            const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';

            $notification
                .removeClass('d-none alert-success alert-danger')
                .addClass(alertClass)
                .text(message)
                .slideDown();

            setTimeout(() => {
                $notification.slideUp(400, function() {
                    $(this).addClass('d-none').removeClass(alertClass).text('');
                });
            }, 4000);
        }
    };

    $(document).ready(() => CondoleanceRegister.init());

})(jQuery);
