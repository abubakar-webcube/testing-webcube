<?php
/**
 * Plugin Name: LearnDash Assignment Grading
 * Plugin URI: https://example.com/learndash-assignment-grading
 * Description: A plugin for grading LearnDash assignments with enhanced features.
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://example.com
 * Text Domain: learndash-assignment-grading
 * Domain Path: /languages
 */

// Prevent direct access
defined('ABSPATH') || exit;

// Load the main plugin class
require_once plugin_dir_path(__FILE__) . 'includes/class-ld-assignment-grading-core.php';

// Initialize the plugin
LD_Assignment_Grading_Core::get_instance();