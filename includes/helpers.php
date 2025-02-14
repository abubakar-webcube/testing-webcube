<?php
/**
 * Helper functions for the plugin.
 */

if (!function_exists('ld_assignment_grading_log')) {
    /**
     * Log messages for debugging.
     *
     * @param mixed $message The message to log.
     */
    function ld_assignment_grading_log($message) {
        if (WP_DEBUG === true) {
            if (is_array($message) || is_object($message)) {
                error_log(print_r($message, true));
            } else {
                error_log($message);
            }
        }
    }
}