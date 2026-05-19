<?php
/**
 * Uninstall script for ArvanCloud Smart CDN Replacer
 * 
 * @package ArvanCloudSmartCDN
 */

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Delete custom mappings option
delete_option('arvan_custom_mappings');

// Delete all search transients
global $wpdb;
$wpdb->query(
    $wpdb->prepare(
        "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
        $wpdb->esc_like('_transient_arvan_search_cache_') . '%'
    )
);
$wpdb->query(
    $wpdb->prepare(
        "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
        $wpdb->esc_like('_transient_timeout_arvan_search_cache_') . '%'
    )
);
$wpdb->query(
    $wpdb->prepare(
        "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
        $wpdb->esc_like('_transient_arvan_path_cache_') . '%'
    )
);
$wpdb->query(
    $wpdb->prepare(
        "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
        $wpdb->esc_like('_transient_timeout_arvan_path_cache_') . '%'
    )
);
