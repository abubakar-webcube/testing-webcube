<?php
/**
 * Fired when the plugin is uninstalled.
 */

// If uninstall not called from WordPress, exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Delete plugin options
delete_option('ld_assignment_grading_options');

// Remove custom database tables (if any)
global $wpdb;
$table_name = $wpdb->prefix . 'ld_assignment_grading';
$wpdb->query("DROP TABLE IF EXISTS $table_name");