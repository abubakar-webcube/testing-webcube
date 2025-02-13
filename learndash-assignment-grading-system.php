<?php
/**
 * Plugin Name: LearnDash Assignment Grading
 * Plugin URI:  https://wooninjas.com/
 * Description: Enhances LearnDash assignments with custom grading scales, comments, and grading history.
 * Version:     1.0.0
 * Author:      Wooninjas
 * Author URI:  https://wooninjas.com/
 * License:     GPL2
 * Text Domain: LDAG
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Prevent direct access
}

// Define Plugin Constants
define('LD_ASSIGNMENT_GRADING_VERSION', '1.0.0');
define('LD_ASSIGNMENT_GRADING_DIR', plugin_dir_path(__FILE__));
define('LD_ASSIGNMENT_GRADING_URL', plugin_dir_url(__FILE__));

// Include necessary files
require_once LD_ASSIGNMENT_GRADING_DIR . 'includes/lesson-topic-settings-fields.php';
require_once LD_ASSIGNMENT_GRADING_DIR . 'includes/learndash-assignment-listing.php';
require_once LD_ASSIGNMENT_GRADING_DIR . 'includes/meta-boxes.php';
require_once LD_ASSIGNMENT_GRADING_DIR . 'includes/assignment-setting-fields.php';
require_once LD_ASSIGNMENT_GRADING_DIR . 'includes/assignment-grades-frontend.php';

// Load plugin files on init
function ld_assignment_grading_init() {
    load_plugin_textdomain('LDAG', false, dirname(plugin_basename(__FILE__)) . '/languages');
}
add_action('plugins_loaded', 'ld_assignment_grading_init');
