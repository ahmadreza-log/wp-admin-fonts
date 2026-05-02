<?php

/**
 * Plugin Name:       Admin Panel Fonts
 * Plugin URI:        https://ahmadreza.me/plugins/wp-admin-fonts
 * Description:       Change WordPress admin panel fonts easily.
 * Version:           1.2.0
 * Author:            Ahmadreza Ebrahimi
 * Author URI:        https://ahmadreza.me/
 * Text Domain:       admin-panel-fonts
 * Domain Path:       /languages
 * Requires PHP:      8.1
 * Requires at least: 6.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Main plugin class (Singleton pattern)
if (!class_exists('WordpressAdminFonts')) {
    class WordpressAdminFonts
    {
        /**
         * Singleton instance holder
         * @var self|null
         */
        private static ?self $instance = null;

        /**
         * Plugin directory path (trailing slash)
         * @var string
         */
        private string $dir = '';

        /**
         * Plugin directory URL (trailing slash)
         * @var string
         */
        private string $url = '';

        /**
         * Plugin version for cache busting
         * @var string
         */
        private string $version = '1.2.0';

        /**
         * Get the single instance of the class
         * @return self
         */
        public static function instance(): self
        {
            if (is_null(self::$instance)) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        /**
         * Private constructor – sets up hooks and loads translations
         */
        private function __construct()
        {
            $this->dir = plugin_dir_path(__FILE__);
            $this->url = plugin_dir_url(__FILE__);

            // Load text domain for internationalization
            add_action('init', [$this, 'loadTextdomain']);

            // Add font selector menu to the admin bar
            add_action('admin_bar_menu', [$this, 'menu'], 500);

            // Load styles and scripts
            add_action('admin_enqueue_scripts', [$this, 'styles'], 100);
            add_action('admin_enqueue_scripts', [$this, 'scripts'], 100);

            // Register AJAX action
            add_action('wp_ajax_wp-admin-fonts-change', [$this, 'ajax']);

            // Cleanup on uninstall
            register_uninstall_hook(__FILE__, [self::class, 'uninstall']);
        }

        /**
         * Load plugin text domain for translations
         */
        public function loadTextdomain(): void
        {
            load_plugin_textdomain('admin-panel-fonts', false, dirname(plugin_basename(__FILE__)) . '/languages');
        }

        /**
         * Add top‑level menu node to the WordPress admin bar
         * @param WP_Admin_Bar $menu Admin bar object
         */
        public function menu(WP_Admin_Bar $menu): void
        {
            // Parent node
            $menu->add_node([
                'id'    => 'wp-admin-fonts',
                'title' => '<span class="dashicons-admin-appearance ab-icon"></span> ' . __('Admin Font', 'admin-panel-fonts'),
                'href'  => false
            ]);

            // Child node with custom HTML dropdown
            $menu->add_node([
                'id'     => 'wp-admin-fonts-callback',
                'parent' => 'wp-admin-fonts',
                'title'  => '',
                'href'   => false,
                'meta'   => [
                    'html'  => $this->callback(),
                    'class' => 'wp-admin-fonts-select'
                ]
            ]);
        }

        /**
         * Generate the HTML dropdown with available fonts
         * @return string HTML markup
         */
        public function callback(): string
        {
            $fonts = $this->fonts();
            $current = get_option('wp-admin-chosen-font', 'anjoman');

            $out = '<div class="change-panel-font-callback">';
            $out .= '<label>' . __('Select Font', 'admin-panel-fonts') . '</label>';
            $out .= '<select id="change-panel-font">';

            foreach ($fonts as $font => $path) {
                $selected = selected($current, $font, false);
                $display = str_replace(['-', '_'], ' ', ucwords($font));
                $out .= sprintf(
                    '<option value="%s" %s>%s</option>',
                    esc_attr($font),
                    $selected,
                    esc_html($display)
                );
            }

            $out .= '</select></div>';
            return $out;
        }

        /**
         * Get all available font files from assets/fonts directory.
         * Uses transient caching for performance (24 hours).
         * @return array Associative array [font_name => full_file_path]
         */
        private function fonts(): array
        {
            $cache = 'wp-admin-fonts-list';
            $fonts = get_transient($cache);

            if (false === $fonts) {
                $fonts = [];
                $files = array_merge(
                    glob($this->dir . 'assets/fonts/*.woff2'),
                    glob($this->dir . 'assets/fonts/*.woff')
                );

                foreach ($files as $file) {
                    $name = pathinfo(basename($file), PATHINFO_FILENAME);
                    $fonts[$name] = $file;
                }
                set_transient($cache, $fonts, DAY_IN_SECONDS);
            }

            return $fonts;
        }

        /**
         * Enqueue admin styles and inject dynamic @font-face CSS
         */
        public function styles(): void
        {
            // Main stylesheet for dropdown appearance
            wp_enqueue_style('wp-admin-fonts-style', $this->url . 'assets/css/style.css', [], $this->version);

            $font = get_option('wp-admin-chosen-font', 'anjoman');
            $fonts = $this->fonts();

            // Fallback if selected font doesn't exist
            if (!isset($fonts[$font])) {
                $font = 'anjoman';
                if (!isset($fonts[$font])) {
                    $first = array_key_first($fonts);
                    $font = $first ?: '';
                }
                if (!empty($font)) {
                    update_option('wp-admin-chosen-font', $font);
                }
            }

            // Generate dynamic CSS and attach inline
            $css = $this->generate($font, $fonts[$font] ?? '');
            if (!empty($css)) {
                wp_add_inline_style('wp-admin-fonts-style', $css);
            }
        }

        /**
         * Generate the complete @font-face CSS and applied selectors
         * @param string $name Font name (without extension)
         * @param string $path Full server path to the font file
         * @return string CSS block
         */
        private function generate(string $name, string $path): string
        {
            if (empty($name) || empty($path) || !file_exists($path)) {
                return '';
            }

            $ext = pathinfo($path, PATHINFO_EXTENSION);
            $format = ($ext === 'woff2') ? 'woff2' : 'woff';
            $url = $this->url . 'assets/fonts/' . $name . '.' . $ext;
            $family = ucwords(str_replace(['-', '_'], ' ', $name));

            return "@font-face {
                font-family: '{$family}';
                src: url('{$url}') format('{$format}');
                font-display: swap;
                font-weight: 400;
                font-style: normal;
            }
            /* Apply font to all admin elements (RTL and LTR) */
            body.rtl, body.wp-admin,
            .rtl #wpadminbar *, .rtl h1, .rtl h2, .rtl h3, .rtl h4, .rtl h5, .rtl h6,
            .rtl .media-frame, .rtl .media-frame .search,
            .rtl .media-frame input, .rtl .media-frame select,
            .rtl .media-frame textarea, .rtl .media-modal,
            .rtl .quicktags-toolbar input, .rtl .wp-switch-editor,
            .components-notice {
                font-family: '{$family}', '{$family} Fallback', sans-serif;
            }";
        }

        /**
         * Enqueue JavaScript for AJAX font switching
         */
        public function scripts(): void
        {
            wp_enqueue_script('wp-admin-fonts-script', $this->url . 'assets/js/script.js', ['jquery'], $this->version, true);

            // Pass data to JavaScript (including translation strings)
            wp_localize_script('wp-admin-fonts-script', 'WordpressAdminFonts', [
                'ajax'    => admin_url('admin-ajax.php'),
                'nonce'   => wp_create_nonce('wp-admin-fonts-change'),
                'current' => get_option('wp-admin-chosen-font', 'anjoman'),
                'words' => [  // Single‑word translation keys
                    'same'   => __('This font is already active', 'admin-panel-fonts'),
                    'wait'   => __('Changing font...', 'admin-panel-fonts'),
                    'done'   => __('Font changed successfully', 'admin-panel-fonts'),
                    'fail'   => __('Server error', 'admin-panel-fonts'),
                    'reload' => __('Font saved. Reloading...', 'admin-panel-fonts'),
                ]
            ]);
        }

        /**
         * Handle AJAX request to change font
         */
        public function ajax(): void
        {
            check_ajax_referer('wp-admin-fonts-change', 'nonce');

            $font = sanitize_text_field($_POST['font'] ?? '');
            if (empty($font)) {
                wp_send_json_error(__('Font name is invalid.', 'admin-panel-fonts'));
            }

            $fonts = $this->fonts();
            if (!isset($fonts[$font])) {
                wp_send_json_error(__('Selected font does not exist.', 'admin-panel-fonts'));
            }

            update_option('wp-admin-chosen-font', $font);

            // Generate new CSS for the selected font
            $new_css = $this->generate($font, $fonts[$font]);
            wp_send_json_success([
                'message' => __('Font changed successfully', 'admin-panel-fonts'),
                'css'     => $new_css
            ]);
        }

        /**
         * Cleanup when plugin is uninstalled
         */
        public static function uninstall(): void
        {
            delete_option('wp-admin-chosen-font');
            delete_transient('wp-admin-fonts-list');
        }
    }

    WordpressAdminFonts::instance();
}
