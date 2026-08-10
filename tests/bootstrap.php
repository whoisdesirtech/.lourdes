<?php
// Minimal bootstrap & WordPress stubs for plugin unit tests.

if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/../../');
}

// Stub essential WordPress functions if WP core is not loaded
if (!function_exists('plugin_dir_path')) {
    function plugin_dir_path($file) {
        return trailingashit(dirname($file));
    }
}

if (!function_exists('trailingashit')) {
    function trailingashit($string) {
        return rtrim($string, '/\\') . '/';
    }
}

if (!function_exists('plugin_dir_url')) {
    function plugin_dir_url($file) {
        return 'http://example.com/wp-content/plugins/' . basename(dirname($file)) . '/';
    }
}

if (!function_exists('plugin_basename')) {
    function plugin_basename($file) {
        return basename(dirname($file)) . '/' . basename($file);
    }
}

if (!function_exists('add_action')) {
    function add_action($hook, $callback, $priority = 10, $accepted_args = 1) {}
}

if (!function_exists('add_filter')) {
    function add_filter($hook, $callback, $priority = 10, $accepted_args = 1) {}
}

if (!function_exists('add_shortcode')) {
    function add_shortcode($tag, $callback) {
        $GLOBALS['wp_shortcodes'][$tag] = $callback;
    }
}

if (!function_exists('do_shortcode')) {
    function do_shortcode($content) {
        if (preg_match('/\[amazon_box\s+asin="([^"]+)"\]/', $content, $m)) {
            return aa_render_product_box($m[1]);
        }
        return $content;
    }
}

if (!function_exists('shortcode_atts')) {
    function shortcode_atts($pairs, $atts, $shortcode = '') {
        $atts = (array)$atts;
        $out = array();
        foreach ($pairs as $name => $default) {
            if (array_key_exists($name, $atts)) {
                $out[$name] = $atts[$name];
            } else {
                $out[$name] = $default;
            }
        }
        return $out;
    }
}

if (!function_exists('__')) {
    function __($text, $domain = 'default') { return $text; }
}

if (!function_exists('_e')) {
    function _e($text, $domain = 'default') { echo $text; }
}

if (!function_exists('esc_html__')) {
    function esc_html__($text, $domain = 'default') { return $text; }
}

if (!function_exists('esc_html_e')) {
    function esc_html_e($text, $domain = 'default') { echo $text; }
}

if (!function_exists('esc_html')) {
    function esc_html($text) { return htmlspecialchars((string)$text, ENT_QUOTES, 'UTF-8'); }
}

if (!function_exists('esc_attr')) {
    function esc_attr($text) { return htmlspecialchars((string)$text, ENT_QUOTES, 'UTF-8'); }
}

if (!function_exists('esc_url')) {
    function esc_url($url) { return filter_var($url, FILTER_SANITIZE_URL); }
}

if (!function_exists('get_option')) {
    function get_option($option, $default = false) { return $default; }
}

if (!function_exists('get_transient')) {
    function get_transient($transient) { return false; }
}

if (!function_exists('set_transient')) {
    function set_transient($transient, $value, $expiration = 0) { return true; }
}

if (!function_exists('add_query_arg')) {
    function add_query_arg($key, $value, $url) {
        return $url . (strpos($url, '?') !== false ? '&' : '?') . urlencode($key) . '=' . urlencode($value);
    }
}

if (!function_exists('is_wp_error')) {
    function is_wp_error($thing) { return false; }
}

if (!function_exists('current_time')) {
    function current_time($type) { return date('Y-m-d H:i:s'); }
}

require_once __DIR__ . '/../amazon-associates-snippets/amazon-associates-snippets.php';
aa_snippets_init();
