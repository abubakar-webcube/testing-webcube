<?php
/**
 * The admin-specific functionality of the plugin.
 */
class LD_Assignment_Grading_Admin {

    /**
     * Initialize the class and set its properties.
     */
    public function __construct() {
        // Hook into the admin menu
        add_action('admin_menu', array($this, 'add_admin_menu'));

        // Enqueue admin scripts and styles
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
    }

    /**
     * Add an admin menu page for the plugin.
     */
    public function add_admin_menu() {
        add_menu_page(
            __('LearnDash Assignment Grading', 'learndash-assignment-grading'), // Page title
            __('Assignment Grading', 'learndash-assignment-grading'),          // Menu title
            'manage_options',                                                // Capability
            'ld-assignment-grading',                                         // Menu slug
            array($this, 'render_admin_page'),                               // Callback function
            'dashicons-clipboard',                                           // Icon URL
            6                                                               // Position
        );
    }

    /**
     * Render the admin settings page.
     */
    public function render_admin_page() {
        // Check user capabilities
        if (!current_user_can('manage_options')) {
            return;
        }

        // Include the admin view template
        include LD_ASSIGNMENT_GRADING_PATH . 'admin/views/settings-page.php';
    }

    /**
     * Enqueue admin-specific scripts and styles.
     */
    public function enqueue_admin_assets($hook) {
        // Load assets only on the plugin's admin page
        if ($hook === 'toplevel_page_ld-assignment-grading') {
            wp_enqueue_style(
                'ld-assignment-grading-admin',
                LD_ASSIGNMENT_GRADING_URL . 'assets/css/admin.css',
                array(),
                LD_ASSIGNMENT_GRADING_VERSION
            );

            wp_enqueue_script(
                'ld-assignment-grading-admin',
                LD_ASSIGNMENT_GRADING_URL . 'assets/js/admin.js',
                array('jquery'),
                LD_ASSIGNMENT_GRADING_VERSION,
                true
            );
        }
    }
}