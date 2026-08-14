<?php
// Minimal bootstrap & WordPress stubs for plugin unit tests.

if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/../../');
}

if (!defined('HOUR_IN_SECONDS')) {
    define('HOUR_IN_SECONDS', 3600);
}

// Global in-memory stores used by the option/transient stubs below.
$GLOBALS['aa_options'] = array();
$GLOBALS['aa_transients'] = array();

// Minimal WP_Error stand-in.
if (!class_exists('WP_Error')) {
    class WP_Error
    {
        private $code;
        private $message;
        private $data;

        public function __construct($code = '', $message = '', $data = '')
        {
            $this->code = $code;
            $this->message = $message;
            $this->data = $data;
        }

        public function get_error_code()
        {
            return $this->code;
        }

        public function get_error_message()
        {
            return $this->message;
        }

        public function get_error_data()
        {
            return $this->data;
        }
    }
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

if (!function_exists('register_activation_hook')) {
    function register_activation_hook($file, $callback) {}
}

if (!function_exists('register_deactivation_hook')) {
    function register_deactivation_hook($file, $callback) {}
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
    function get_option($option, $default = false) {
        return array_key_exists($option, $GLOBALS['aa_options']) ? $GLOBALS['aa_options'][$option] : $default;
    }
}

if (!function_exists('update_option')) {
    function update_option($option, $value) {
        $GLOBALS['aa_options'][$option] = $value;
        return true;
    }
}

if (!function_exists('get_transient')) {
    function get_transient($transient) {
        if (!isset($GLOBALS['aa_transients'][$transient])) {
            return false;
        }
        $entry = $GLOBALS['aa_transients'][$transient];
        if ($entry['expires'] !== 0 && time() >= $entry['expires']) {
            unset($GLOBALS['aa_transients'][$transient]);
            return false;
        }
        return $entry['value'];
    }
}

if (!function_exists('set_transient')) {
    function set_transient($transient, $value, $expiration = 0) {
        $GLOBALS['aa_transients'][$transient] = array(
            'value'   => $value,
            'expires' => $expiration > 0 ? time() + $expiration : 0,
        );
        return true;
    }
}

if (!function_exists('delete_transient')) {
    function delete_transient($transient) {
        unset($GLOBALS['aa_transients'][$transient]);
        return true;
    }
}

if (!function_exists('add_query_arg')) {
    function add_query_arg($key, $value, $url) {
        return $url . (strpos($url, '?') !== false ? '&' : '?') . urlencode($key) . '=' . urlencode($value);
    }
}

if (!function_exists('is_wp_error')) {
    function is_wp_error($thing) {
        return $thing instanceof WP_Error;
    }
}

if (!function_exists('current_time')) {
    function current_time($type) { return date('Y-m-d H:i:s'); }
}

if (!function_exists('wp_json_encode')) {
    function wp_json_encode($data) { return json_encode($data); }
}

if (!function_exists('wp_remote_post')) {
    function wp_remote_post($url, $args = array()) {
        if (isset($GLOBALS['aa_http_handler']) && is_callable($GLOBALS['aa_http_handler'])) {
            return call_user_func($GLOBALS['aa_http_handler'], $url, $args);
        }
        return new WP_Error('no_http_handler', 'No HTTP handler configured in tests.');
    }
}

if (!function_exists('wp_remote_retrieve_body')) {
    function wp_remote_retrieve_body($response) {
        if (is_array($response) && isset($response['body'])) {
            return $response['body'];
        }
        return '';
    }
}

if (!function_exists('wp_remote_retrieve_response_code')) {
    function wp_remote_retrieve_response_code($response) {
        if (is_array($response) && isset($response['response']['code'])) {
            return (int) $response['response']['code'];
        }
        return 200;
    }
}

if (!function_exists('wp_remote_retrieve_response_message')) {
    function wp_remote_retrieve_response_message($response) {
        if (is_array($response) && isset($response['response']['message'])) {
            return $response['response']['message'];
        }
        return '';
    }
}

// Test helpers for resetting the in-memory stores between tests.
if (!function_exists('aa_test_reset')) {
    function aa_test_reset() {
        $GLOBALS['aa_options'] = array();
        $GLOBALS['aa_transients'] = array();
        unset($GLOBALS['aa_http_handler']);
    }
}

if (!function_exists('aa_test_set_option')) {
    function aa_test_set_option($key, $value) {
        $GLOBALS['aa_options'][$key] = $value;
    }
}

require_once __DIR__ . '/../amazon-associates-snippets/amazon-associates-snippets.php';
aa_snippets_init();

require_once __DIR__ . '/TestDoubles.php';
