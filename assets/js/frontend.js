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

        // ------------------------------------------------------------------
        // Session token (persistent cookie)
        // ------------------------------------------------------------------

        /**
         * Return the session token, creating and storing it in a cookie if absent.
         */
        getSessionToken() {
            let token = this.getCookie(condoleanceRegister.cookieName);

            if (!token || !/^[a-f0-9]{64}$/.test(token)) {
                token = this.generateToken();
                this.setCookie(condoleanceRegister.cookieName, token, condoleanceRegister.cookieExpiry);
            }

            return token;
        },

        generateToken() {
            const array = new Uint8Array(32);
            crypto.getRandomValues(array);
            return Array.from(array).map(b => b.toString(16).padStart(2, '0')).join('');
        },

        getCookie(name) {
            const match = document.cookie.match(new RegExp('(?:^|; )' + name.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + '=([^;]*)'));
            return match ? decodeURIComponent(match[1]) : null;
        },

        setCookie(name, value, seconds) {
            const expires = new Date(Date.now() + seconds * 1000).toUTCString();
            document.cookie = `${name}=${encodeURIComponent(value)}; expires=${expires}; path=/; SameSite=Lax`;
        },

        // ------------------------------------------------------------------
        // Form submit
        // ------------------------------------------------------------------

        bindEvents() {
            $(document).on('submit', '.condoleance-light-candle-form', this.handleFormSubmit.bind(this));
            $(document).on('change', '#anonymous', function() {
                const $name = $('#name');
                $('#anonymous-notice').toggle(this.checked);
                $name.prop('disabled', this.checked).prop('required', !this.checked);
                if (this.checked) {
                    $name.val('');
                }
            });
        },

        handleFormSubmit(e) {
            e.preventDefault();

            const $form   = $(e.currentTarget);
            const $button = $form.find('button[type="submit"]');
            const postId  = $form.find('input[name="post_id"]').val();

            if ($button.hasClass('loading')) {
                return;
            }

            $button.addClass('loading').prop('disabled', true);

            const name        = $form.find('input[name="name"]').val();
            const isAnonymous = $form.find('input[name="anonymous"]').is(':checked');
            const token       = this.getSessionToken();

            fetch(`${condoleanceRegister.restUrl}/candles/${postId}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'same-origin',
                body: JSON.stringify({ name, anonymous: isAnonymous, session_token: token }),
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.updateCandleCount(postId, data.count);
                    this.updateCandleUsersList(data.users);
                    this.showCandleUsersLink();
                    this.showNotification(data.message, 'success');

                    // Only lock the button for named candles; anonymous users can light again.
                    if (data.already_lit) {
                        this.markAlreadyLit(postId);
                    }

                    // Persist token returned by server (named candles only — anonymous use one-off tokens).
                    if (data.session_token) {
                        this.setCookie(condoleanceRegister.cookieName, data.session_token, condoleanceRegister.cookieExpiry);
                    }

                    const modal = bootstrap.Modal.getInstance(document.getElementById('candleModal'));
                    if (modal) modal.hide();
                    $form[0].reset();

                } else if (data.already_lit) {
                    this.markAlreadyLit(postId);
                    this.showNotification(condoleanceRegister.strings.alreadyLit, 'info');

                    const modal = bootstrap.Modal.getInstance(document.getElementById('candleModal'));
                    if (modal) modal.hide();

                } else {
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

        // ------------------------------------------------------------------
        // UI helpers
        // ------------------------------------------------------------------

        /**
         * Disable the "light a candle" button and update its label.
         */
        markAlreadyLit(postId) {
            const btn = document.querySelector(`.condoleance-light-candle[data-post-id="${postId}"]`);
            if (!btn) return;
            btn.classList.add('already-lit');
            btn.setAttribute('disabled', 'disabled');
            btn.removeAttribute('data-bs-toggle');
            btn.removeAttribute('data-bs-target');
            btn.textContent = btn.dataset.litLabel || 'Kaarsje al aangestoken';
        },

        updateCandleCount(postId, count) {
            $(`.condoleance-candle-count[data-post-id="${postId}"]`).text(count);
        },

        showCandleUsersLink() {
            const $link = $('a[data-bs-target="#candleUsersModal"]').parent();
            if ($link.length && $link.hasClass('d-none')) {
                $link.removeClass('d-none');
            }
        },

        updateCandleUsersList(users) {
            if (!users || users.length === 0) return;

            const $modalBody = $('#candleUsersModal .modal-body ul');
            if (!$modalBody.length) return;

            const usersHtml = users.map(user => {
                const date          = new Date(user.date);
                const formattedDate = date.toLocaleDateString('nl-NL', {
                    day: '2-digit', month: '2-digit', year: 'numeric',
                    hour: '2-digit', minute: '2-digit',
                });
                const displayName = user.anonymous ? 'Anoniem' : user.name;

                return `<li class="candle-user-item mb-3 pb-3 border-bottom">
                    <small class="text-muted d-block mb-1">${formattedDate}</small>
                    <span class="candle-user-name fw-bold">${displayName}</span>
                </li>`;
            }).join('');

            $modalBody.html(usersHtml);
        },

        showNotification(message, type) {
            const $notification = $('.condoleance-notification');
            const classMap      = { success: 'alert-success', error: 'alert-danger', info: 'alert-info' };
            const alertClass    = classMap[type] || 'alert-info';

            $notification
                .removeClass('d-none alert-success alert-danger alert-info')
                .addClass(alertClass)
                .text(message)
                .slideDown();

            setTimeout(() => {
                $notification.slideUp(400, function() {
                    $(this).addClass('d-none').removeClass('alert-success alert-danger alert-info').text('');
                });
            }, 4000);
        },
    };

    $(document).ready(() => CondoleanceRegister.init());

})(jQuery);
