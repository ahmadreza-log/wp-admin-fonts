/**
 * Admin Panel Fonts - Single Word Identifiers
 * Changes admin font via AJAX without reload.
 */

(function ($) {
    'use strict';

    // Data object passed from PHP via wp_localize_script
    const data = window.WordpressAdminFonts;
    if (!data) return;

    // Translation strings (single‑word keys)
    const words = data.words || {
        same: 'This font is already active',
        wait: 'Changing font...',
        done: 'Font changed successfully',
        fail: 'Server error',
        reload: 'Font saved. Reloading...'
    };

    /**
     * Display a floating toast notification.
     * @param {string} text - Message content.
     * @param {string} kind - Type: 'success', 'error', or 'info'.
     */
    function toast(text, kind = 'success') {
        // Choose background color based on notification type
        let color = '#2ecc71'; // green for success
        if (kind === 'error') color = '#e74c3c'; // red
        if (kind === 'info') color = '#3498db'; // blue

        // Create the toast element and apply styles
        const box = $('<div>')
            .text(text)
            .css({
                position: 'fixed',
                bottom: '20px',
                left: '20px',
                zIndex: 999999,
                backgroundColor: color,
                color: '#fff',
                padding: '12px 20px',
                borderRadius: '8px',
                fontSize: '14px',
                fontFamily: 'sans-serif',
                boxShadow: '0 4px 12px rgba(0,0,0,0.15)',
                opacity: 0,
                transition: 'opacity 0.3s ease',
                pointerEvents: 'none'
            })
            .appendTo('body');

        // Fade in
        setTimeout(() => box.css('opacity', 1), 10);

        // Fade out and remove after 3 seconds
        setTimeout(() => {
            box.css('opacity', 0);
            setTimeout(() => box.remove(), 300);
        }, 3000);
    }

    /**
     * Main class for switching fonts.
     * All properties and methods use single‑word names.
     */
    class Switcher {
        /**
         * Constructor – stores config and initializes.
         * @param {Object} config - Contains ajax URL, nonce, etc.
         */
        constructor(config) {
            this.url = config.ajax;   // AJAX endpoint
            this.nonce = config.nonce; // Security nonce
            this.select = $('#change-panel-font'); // Dropdown element
            this.current = this.select.val();      // Currently active font

            this.change = this.change.bind(this); // Bind event handler
            this.start(); // Attach event listeners
        }

        /**
         * Attach change event listener to the select dropdown.
         */
        start() {
            if (this.select.length) {
                this.select.on('change', this.change);
            }
        }

        /**
         * Get or create the inline style element that holds dynamic font CSS.
         * @returns {jQuery} The style tag.
         */
        style() {
            let tag = $('#wp-admin-fonts-style-inline-css');
            if (!tag.length) {
                tag = $('<style id="wp-admin-fonts-style-inline-css"></style>');
                $('head').append(tag);
            }
            return tag;
        }

        /**
         * Inject new CSS into the style tag.
         * @param {string} css - Complete @font-face CSS block.
         * @returns {boolean} True if successful.
         */
        inject(css) {
            if (!css) return false;
            this.style().html(css);
            return true;
        }

        /**
         * Handle select change event.
         * @param {Event} event - The change event.
         */
        change(event) {
            const target = $(event.target);
            const fresh = target.val(); // Newly selected font name

            // Do nothing if the same font is selected
            if (fresh === this.current) {
                toast(words.same, 'info');
                return;
            }

            // Disable dropdown and show loading message
            target.prop('disabled', true);
            toast(words.wait, 'info');

            // Send AJAX request to save the new font
            $.ajax({
                url: this.url,
                type: 'POST',
                data: {
                    action: 'wp-admin-fonts-change',
                    font: fresh,
                    nonce: this.nonce
                },
                success: (reply) => {
                    // If response contains CSS, inject it
                    if (reply.success && reply.data && reply.data.css) {
                        if (this.inject(reply.data.css)) {
                            toast(reply.data.message || words.done, 'success');
                            this.current = fresh;
                            // Update selected option in dropdown
                            this.select.find('option').removeAttr('selected');
                            this.select.find(`option[value="${fresh}"]`).attr('selected', 'selected');
                            return;
                        }
                    }
                    // Fallback: reload page if CSS injection fails
                    toast(words.reload, 'info');
                    setTimeout(() => location.reload(), 1000);
                },
                error: () => {
                    // On error, show message and restore previous selection
                    toast(words.fail, 'error');
                    target.val(this.current);
                },
                complete: () => {
                    // Re-enable dropdown after request finishes
                    target.prop('disabled', false);
                }
            });
        }
    }

    // Instantiate the Switcher class when DOM is ready
    $(() => new Switcher(data));
})(jQuery);