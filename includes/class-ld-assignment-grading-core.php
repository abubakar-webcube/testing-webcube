<?php
/**
 * The core plugin class.
 */
final class LD_Assignment_Grading_Core {

    /**
     * The single instance of the class.
     */
    private static $instance = null;

    /**
     * Main plugin instance.
     */
    public static function get_instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Class constructor.
     */
    private function __construct() {
        $this->define_constants();
        $this->init_hooks();
    }

    /**
     * Define plugin constants.
     */
    private function define_constants() {
        define('LD_ASSIGNMENT_GRADING_VERSION', '1.0.0');
        define('LD_ASSIGNMENT_GRADING_PATH', plugin_dir_path(dirname(__FILE__))); // Updated
        define('LD_ASSIGNMENT_GRADING_URL', plugin_dir_url(dirname(__FILE__)));  // Updated
    }

    /**
     * Initialize hooks.
     */
    private function init_hooks() {
        // Activation/Deactivation hooks
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));

        // Load text domain for localization
        add_action('plugins_loaded', array($this, 'load_textdomain'));

        // Initialize admin and frontend functionality
        if (is_admin()) {
            require_once LD_ASSIGNMENT_GRADING_PATH . 'includes/class-ld-assignment-grading-admin.php'; // Updated
            new LD_Assignment_Grading_Admin();
        } else {
            require_once LD_ASSIGNMENT_GRADING_PATH . 'includes/class-ld-assignment-grading-public.php'; // Updated
            new LD_Assignment_Grading_Public();
        }
    }

    /**
     * Plugin activation.
     */
    public function activate() {
        // Activation code here
    }

    /**
     * Plugin deactivation.
     */
    public function deactivate() {
        // Deactivation code here
    }

    /**
     * Load plugin text domain for localization.
     */
    public function load_textdomain() {
        load_plugin_textdomain(
            'learndash-assignment-grading',
            false,
            dirname(plugin_basename(__FILE__)) . '/languages/'
        );
    }
}