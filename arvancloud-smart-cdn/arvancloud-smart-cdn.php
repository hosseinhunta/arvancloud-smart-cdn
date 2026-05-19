<?php
/**
 * Plugin Name: ArvanCloud Smart CDN Replacer
 * Plugin URI: https://github.com/hosseinhunta/arvancloud-smart-cdn
 * Description: جایگزینی هوشمند لینک‌های jsDelivr، unpkg و cdnjs با کتابخانه آروان 
 * Version: 2.1.0
 * Author: Hossein Mohmmadian
 * Author URI: https://astel.ir
 * Text Domain: arvancloud-smart-cdn
 * Domain Path: /languages
 * Requires at least: 5.0
 * Requires PHP: 7.4
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

if (!defined('ABSPATH')) {
    exit;
}

final class ArvanCloud_Smart_CDN_Replacer {

    const OPTION_KEY = 'arvan_custom_mappings';
    const SEARCH_CACHE_KEY = 'arvan_search_cache';
    const PATH_CACHE_GROUP = 'arvan_path_cache';
    const CACHE_DURATION = DAY_IN_SECONDS;

    private $default_map = [
        '@fortawesome/fontawesome-free' => 'font-awesome',
        'bootstrap'                     => 'bootstrap',
        'jquery'                        => 'jquery',
        'codemirror'                    => 'codemirror',
        'webfontloader'                 => 'webfont',
        'vue'                           => 'vue',
        'react'                         => 'react',
        'react-dom'                     => 'react-dom',
        'popper.js'                     => 'popper.js',
        'lodash'                        => 'lodash',
        'moment'                        => 'moment',
        'chart.js'                      => 'chart.js',
        'swiper'                        => 'swiper',
        'aos'                           => 'aos',
        'animate.css'                   => 'animate.css',
        'slick-carousel'                => 'slick',
        'fancyapps'                     => 'fancybox',
        'gsap'                          => 'gsap',
        'three'                         => 'three',
        'axios'                         => 'axios',
        'alpinejs'                      => 'alpinejs',
        'leaflet'                       => 'leaflet',
        'plyr'                          => 'plyr',
        'highlight.js'                  => 'highlight.js',
        'prismjs'                       => 'prism',
        'clipboard'                     => 'clipboard',
        'flatpickr'                     => 'flatpickr',
        'select2'                       => 'select2',
        'izitoast'                      => 'izitoast',
        'sweetalert2'                   => 'sweetalert2',
        'datatables'                    => 'datatables',
        'fullcalendar'                  => 'fullcalendar',
        'tinymce'                       => 'tinymce',
        'mathjax'                       => 'mathjax',
        'tailwindcss'                   => 'tailwindcss',
        'flowbite'                      => 'flowbite',
        'htmx.org'                      => 'htmx',
        'sortablejs'                    => 'sortablejs',
        'dropzone'                      => 'dropzone',
        'video.js'                      => 'video.js',
        'd3'                            => 'd3',
        'echarts'                       => 'echarts',
        'marked'                        => 'marked',
        'quill'                         => 'quill',
        'ckeditor5'                     => 'ckeditor5',
    ];

    private $path_fixes = [
        'codemirror' => [
            'remove_prefix' => 'lib/', 
        ],
        'webfontloader' => [
            'rename_min' => true,   
        ],
    ];

    public function __construct() {
        add_action('plugins_loaded', [$this, 'load_textdomain']);
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_post_arvan_search', [$this, 'handle_search_request']);

        add_filter('script_loader_src', [$this, 'replace_url'], 10, 2);
        add_filter('style_loader_src',  [$this, 'replace_url'], 10, 2);
        add_filter('script_module_loader_src', [$this, 'replace_url'], 10, 2);
    }

    public function load_textdomain() {
        load_plugin_textdomain(
            'arvancloud-smart-cdn',
            false,
            dirname(plugin_basename(__FILE__)) . '/languages'
        );
    }

    private function get_all_mappings() {
        $custom = get_option(self::OPTION_KEY, []);
        if (!is_array($custom)) $custom = [];
        return array_merge($this->default_map, $custom);
    }

    public function replace_url($src, $handle) {
        if (empty($src)) return $src;

        $query = '';
        if (false !== strpos($src, '?')) {
            list($src, $query) = explode('?', $src, 2);
            $query = $query ? "?{$query}" : '';
        }

        $npm_package = '';
        $version = '';
        $filepath = '';

        if (preg_match('#^(https?:)?//cdn\.jsdelivr\.net/npm/(.+)@([\d.]+(?:-[^/]+)?)(?:/(.*))?$#', $src, $m)) {
            $npm_package = $m[2];
            $version = $m[3];
            $filepath = isset($m[4]) ? $m[4] : '';
        } elseif (preg_match('#^(https?:)?//unpkg\.com/(.+)@([\d.]+(?:-[^/]+)?)(?:/(.*))?$#', $src, $m)) {
            $npm_package = $m[2];
            $version = $m[3];
            $filepath = isset($m[4]) ? $m[4] : '';
        } elseif (preg_match('#^(https?:)?//cdnjs\.cloudflare\.com/ajax/libs/([^/]+)/([\d.]+(?:-[^/]+)?)(?:/(.*))?$#', $src, $m)) {
            $npm_package = $m[2];
            $version = $m[3];
            $filepath = isset($m[4]) ? $m[4] : '';
        }

        if (empty($npm_package) || empty($filepath)) {
            return $src . $query;
        }

        $all_mappings = $this->get_all_mappings();
        if (isset($all_mappings[$npm_package])) {
            $folder = $all_mappings[$npm_package];
        } else {
            $folder = $this->guess_folder($npm_package);
            if (empty($folder)) return $src . $query;
        }

        $fixed_filepath = $this->apply_manual_fixes($npm_package, $filepath);
        if ($fixed_filepath) {
            $filepath = $fixed_filepath;
        } else {
            $correct_filepath = $this->find_correct_filepath_with_head($folder, $version, $filepath);
            if ($correct_filepath) {
                $filepath = $correct_filepath;
            }
        }

        $new_url = sprintf('https://lib.arvancloud.ir/%s/%s/%s', $folder, $version, $filepath);
        return $new_url . $query;
    }

    private function apply_manual_fixes($npm_package, $filepath) {
        if (!isset($this->path_fixes[$npm_package])) {
            return false;
        }
        $fix = $this->path_fixes[$npm_package];
        $new_path = $filepath;

        if (isset($fix['remove_prefix']) && strpos($new_path, $fix['remove_prefix']) === 0) {
            $new_path = substr($new_path, strlen($fix['remove_prefix']));
        }
        if (isset($fix['rename_min']) && $fix['rename_min'] === true) {
            $new_path = preg_replace('/\.min\.js$/i', '.js', $new_path);
            $new_path = preg_replace('/\.min\.css$/i', '.css', $new_path);
        }

        return ($new_path !== $filepath) ? $new_path : false;
    }

    /**
     * @param string $folder
     * @param string $version
     * @param string $original_path
     * @return string|false
     */
    private function find_correct_filepath_with_head($folder, $version, $original_path) {
        $cache_key = md5($folder . '_' . $version . '_' . $original_path);
        $cached = get_transient(self::PATH_CACHE_GROUP . '_' . $cache_key);
        if ($cached !== false) {
            return $cached === 'null' ? false : $cached;
        }

        $candidates = [];
        $candidates[] = $original_path;
        $candidates[] = preg_replace('/\.min(\.\w+)$/', '$1', $original_path); 
        $candidates[] = basename($original_path);                          
        $candidates[] = preg_replace('/\.min(\.\w+)$/', '$1', basename($original_path)); 
        $candidates[] = preg_replace('#^[^/]+/#', '', $original_path);       

        $candidates = array_unique(array_filter($candidates));

        foreach ($candidates as $cand) {
            $test_url = sprintf('https://lib.arvancloud.ir/%s/%s/%s', $folder, $version, $cand);
            $response = wp_remote_head($test_url, [
                'timeout' => 5,
                'user-agent' => 'WordPress/' . get_bloginfo('version'),
            ]);
            if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
                set_transient(self::PATH_CACHE_GROUP . '_' . $cache_key, $cand, self::CACHE_DURATION);
                return $cand;
            }
        }

        set_transient(self::PATH_CACHE_GROUP . '_' . $cache_key, 'null', self::CACHE_DURATION);
        return false;
    }

    private function guess_folder($npm_package) {
        $scoped_fallback = [
            '@fortawesome/fontawesome-free' => 'font-awesome',
            '@popperjs/core'               => 'popper.js',
            '@splidejs/splide'             => 'splide',
            '@fancyapps/fancybox'          => 'fancybox',
            '@lottiefiles/lottie-player'   => 'lottie',
            '@sweetalert2/theme-bootstrap-4' => 'sweetalert2',
        ];
        if (isset($scoped_fallback[$npm_package])) {
            return $scoped_fallback[$npm_package];
        }
        if (strpos($npm_package, '@') === 0 && strpos($npm_package, '/') !== false) {
            $parts = explode('/', $npm_package);
            $npm_package = end($parts);
        }
        return strtolower($npm_package);
    }

    private function search_arvancloud($package_name) {
        $package_name = sanitize_text_field($package_name);
        $cache_key = md5($package_name);
        $cached = get_transient(self::SEARCH_CACHE_KEY . '_' . $cache_key);
        if ($cached !== false) return $cached;

        $search_url = 'https://lib.arvancloud.ir/search?q=' . urlencode($package_name);
        $response = wp_remote_get($search_url, [
            'timeout' => 15,
            'user-agent' => 'WordPress/' . get_bloginfo('version') . '; ' . get_bloginfo('url'),
        ]);
        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) return false;

        $body = wp_remote_retrieve_body($response);
        $pattern = '#https://lib\.arvancloud\.ir/([a-z0-9._-]+)/#i';
        preg_match_all($pattern, $body, $matches);
        if (!empty($matches[1])) {
            $folders = array_unique($matches[1]);
            $best_match = $folders[0];
            foreach ($folders as $folder) {
                if (stripos($folder, $package_name) !== false) {
                    $best_match = $folder;
                    break;
                }
            }
            set_transient(self::SEARCH_CACHE_KEY . '_' . $cache_key, $best_match, self::CACHE_DURATION);
            return $best_match;
        }
        return false;
    }

    public function handle_search_request() {
        if (!current_user_can('manage_options') || !check_admin_referer('arvan_search_nonce')) {
            wp_die('دسترسی غیرمجاز');
        }
        $package_name = isset($_POST['package_name']) ? sanitize_text_field($_POST['package_name']) : '';
        if (empty($package_name)) {
            wp_redirect(add_query_arg('search_status', 'empty', wp_get_referer()));
            exit;
        }
        $folder = $this->search_arvancloud($package_name);
        if ($folder) {
            $mappings = get_option(self::OPTION_KEY, []);
            $mappings[$package_name] = $folder;
            update_option(self::OPTION_KEY, $mappings);
            wp_redirect(add_query_arg([
                'search_status' => 'success',
                'package' => urlencode($package_name),
                'folder' => urlencode($folder)
            ], wp_get_referer()));
        } else {
            wp_redirect(add_query_arg([
                'search_status' => 'not_found',
                'package' => urlencode($package_name)
            ], wp_get_referer()));
        }
        exit;
    }

    public function add_admin_menu() {
        add_options_page(
            __('ArvanCloud CDN Settings', 'arvancloud-smart-cdn'),
            __('ArvanCloud CDN', 'arvancloud-smart-cdn'),
            'manage_options',
            'arvan-cdn-settings',
            [$this, 'settings_page']
        );
    }

    public function register_settings() {
        register_setting('arvan_cdn_settings', self::OPTION_KEY, [
            'sanitize_callback' => [$this, 'sanitize_mappings'],
            'default' => [],
        ]);
    }

    public function sanitize_mappings($input) {
        if (!is_array($input)) return [];
        $clean = [];
        foreach ($input as $package => $folder) {
            $package = sanitize_text_field($package);
            $folder  = sanitize_text_field($folder);
            if (!empty($package) && !empty($folder)) $clean[$package] = $folder;
        }
        return $clean;
    }

    public function settings_page() {
        if (!current_user_can('manage_options')) return;
        $mappings = get_option(self::OPTION_KEY, []);
        if (!is_array($mappings)) $mappings = [];

        // اضافه کردن دستی
        if (isset($_POST['arvan_action']) && $_POST['arvan_action'] === 'add' && check_admin_referer('arvan_cdn_action', 'arvan_nonce')) {
            if (!empty($_POST['new_package']) && !empty($_POST['new_folder'])) {
                $new_package = sanitize_text_field($_POST['new_package']);
                $new_folder  = sanitize_text_field($_POST['new_folder']);
                $mappings[$new_package] = $new_folder;
                update_option(self::OPTION_KEY, $mappings);
                echo '<div class="notice notice-success is-dismissible"><p>' . __('Mapping added successfully.', 'arvancloud-smart-cdn') . '</p></div>';
            }
        }

        if (isset($_POST['arvan_action']) && $_POST['arvan_action'] === 'delete' && isset($_POST['delete_package']) && check_admin_referer('arvan_cdn_action', 'arvan_nonce')) {
            $delete_package = sanitize_text_field($_POST['delete_package']);
            if (isset($mappings[$delete_package])) {
                unset($mappings[$delete_package]);
                update_option(self::OPTION_KEY, $mappings);
                echo '<div class="notice notice-success is-dismissible"><p>' . __('Mapping deleted.', 'arvancloud-smart-cdn') . '</p></div>';
            }
        }

        $search_status = isset($_GET['search_status']) ? $_GET['search_status'] : '';
        $searched_package = isset($_GET['package']) ? $_GET['package'] : '';
        $found_folder = isset($_GET['folder']) ? $_GET['folder'] : '';
        if ($search_status === 'success') {
            echo '<div class="notice notice-success is-dismissible"><p>';
            printf(__('✅ Library <strong>%s</strong> found and stored as <code>%s</code>.', 'arvancloud-smart-cdn'), esc_html($searched_package), esc_html($found_folder));
            echo '</p></div>';
        } elseif ($search_status === 'not_found') {
            echo '<div class="notice notice-warning is-dismissible"><p>';
            printf(__('⚠️ Library <strong>%s</strong> not found in ArvanCloud. Please add the folder manually.', 'arvancloud-smart-cdn'), esc_html($searched_package));
            echo '</p></div>';
        } elseif ($search_status === 'empty') {
            echo '<div class="notice notice-error is-dismissible"><p>' . __('❌ Please enter a package name.', 'arvancloud-smart-cdn') . '</p></div>';
        }
        ?>
        <div class="wrap">
            <h1><?php _e('ArvanCloud Smart CDN Replacer Settings', 'arvancloud-smart-cdn'); ?></h1>
            <p><?php _e('This plugin automatically replaces links from jsDelivr, unpkg, and cdnjs with ArvanCloud’s CDN.', 'arvancloud-smart-cdn'); ?></p>

            <div class="card" style="max-width:800px; padding:20px; margin:20px 0; background:#fff; border:1px solid #ccd0d4; border-radius:4px;">
                <h2>🔍 <?php _e('Automatic Library Search', 'arvancloud-smart-cdn'); ?></h2>
                <p><?php _e('Enter the npm package or library name to automatically find and save the corresponding folder on ArvanCloud.', 'arvancloud-smart-cdn'); ?></p>
                <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                    <?php wp_nonce_field('arvan_search_nonce'); ?>
                    <input type="hidden" name="action" value="arvan_search">
                    <input type="text" name="package_name" class="regular-text" placeholder="<?php _e('e.g. jquery or swiper', 'arvancloud-smart-cdn'); ?>" required>
                    <button type="submit" class="button button-primary">🔍 <?php _e('Search & Save', 'arvancloud-smart-cdn'); ?></button>
                </form>
            </div>

            <h2><?php _e('Current Custom Mappings', 'arvancloud-smart-cdn'); ?></h2>
            <?php if (!empty($mappings)): ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead><tr><th><?php _e('Package Name', 'arvancloud-smart-cdn'); ?></th><th><?php _e('ArvanCloud Folder', 'arvancloud-smart-cdn'); ?></th><th><?php _e('Action', 'arvancloud-smart-cdn'); ?></th></tr></thead>
                    <tbody>
                        <?php foreach ($mappings as $package => $folder): ?>
                            <tr>
                                <td><code><?php echo esc_html($package); ?></code></td>
                                <td><code><?php echo esc_html($folder); ?></code></td>
                                <td>
                                    <form method="post" style="display:inline;">
                                        <?php wp_nonce_field('arvan_cdn_action', 'arvan_nonce'); ?>
                                        <input type="hidden" name="arvan_action" value="delete">
                                        <input type="hidden" name="delete_package" value="<?php echo esc_attr($package); ?>">
                                        <button type="submit" class="button button-small"><?php _e('Delete', 'arvancloud-smart-cdn'); ?></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p><?php _e('No custom mappings yet.', 'arvancloud-smart-cdn'); ?></p>
            <?php endif; ?>

            <h2><?php _e('Add Manual Mapping', 'arvancloud-smart-cdn'); ?></h2>
            <form method="post" action="">
                <?php wp_nonce_field('arvan_cdn_action', 'arvan_nonce'); ?>
                <table class="form-table">
                    <tr><th><label for="new_package"><?php _e('Package Name', 'arvancloud-smart-cdn'); ?></label></th><td><input type="text" id="new_package" name="new_package" class="regular-text" placeholder="<?php _e('e.g. bootstrap', 'arvancloud-smart-cdn'); ?>"></td></tr>
                    <tr><th><label for="new_folder"><?php _e('ArvanCloud Folder', 'arvancloud-smart-cdn'); ?></label></th><td><input type="text" id="new_folder" name="new_folder" class="regular-text" placeholder="<?php _e('e.g. font-awesome', 'arvancloud-smart-cdn'); ?>"></td></tr>
                </table>
                <p class="submit"><input type="submit" name="arvan_action" value="add" class="button button-primary"></p>
            </form>

            <h3><?php _e('Default Supported Libraries', 'arvancloud-smart-cdn'); ?></h3>
            <ul style="column-count:3; list-style:disc; padding-right:20px;">
                <?php foreach ($this->default_map as $pkg => $folder): ?>
                    <li><code><?php echo esc_html($pkg); ?></code> → <code><?php echo esc_html($folder); ?></code></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php
    }
}

new ArvanCloud_Smart_CDN_Replacer();
