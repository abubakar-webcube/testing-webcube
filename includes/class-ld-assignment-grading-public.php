<?php
/**
 * The public-facing functionality of the plugin.
 */
class LD_Assignment_Grading_Public {

    /**
     * Initialize the class and set its properties.
     */
    public function __construct() {
        // Enqueue frontend scripts and styles
        add_action('wp_enqueue_scripts', array($this, 'enqueue_public_assets'));

        // Register shortcodes
        add_shortcode('ld_assignment_grading', array($this, 'render_shortcode'));
    }

    /**
     * Enqueue frontend-specific scripts and styles.
     */
    public function enqueue_public_assets() {
        wp_enqueue_style(
            'ld-assignment-grading-public',
            LD_ASSIGNMENT_GRADING_URL . 'assets/css/public.css',
            array(),
            LD_ASSIGNMENT_GRADING_VERSION
        );

        wp_enqueue_script(
            'ld-assignment-grading-public',
            LD_ASSIGNMENT_GRADING_URL . 'assets/js/public.js',
            array('jquery'),
            LD_ASSIGNMENT_GRADING_VERSION,
            true
        );
    }

    /**
     * Render the frontend shortcode.
     */
    public function render_shortcode($atts) {
        // Shortcode attributes
        $atts = shortcode_atts(array(
            'param' => 'default'
        ), $atts, 'ld_assignment_grading');

        // Sanitize input
        $param = sanitize_text_field($atts['param']);

        // Return escaped output
        return esc_html__('Shortcode output: ', 'learndash-assignment-grading') . $param;
    }
}