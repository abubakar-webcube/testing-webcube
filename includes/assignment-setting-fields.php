<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * LearnDash assignment setting fields
 */
add_filter( 'learndash_settings_fields', 'add_assignment_setting_fields', 10, 2 );

/**
 * Add fields to LearnDash Assignment Options settings.
 *
 * @param array  $setting_option_fields Existing fields for the settings section.
 * @param string $settings_section_key  The key for the settings section.
 *
 * @return array Modified fields with the custom field added.
 */
function add_assignment_setting_fields( $setting_option_fields, $settings_section_key ) {
    global $pagenow; // Get the current page in the admin area

    // Ensure we are modifying only the Assignment Options page in the admin
    if ( is_admin() && $pagenow === 'admin.php' && isset($_GET['page']) && $_GET['page'] === 'assignments-options' ) {
        if ( $settings_section_key === 'cpt_options' ) {
            $saved_options = get_option( 'learndash_settings_assignments_cpt', [] );
            // error_log('saved_options: ' . var_export($saved_options, true));
            // Define the new custom field
            $setting_option_fields['assignments_hide_history'] = array(
                'name'      => 'assignments_hide_history',
                'name'      => 'assignments_hide_history',
                'type'      => 'checkbox-switch',
                "class"     => "-small setting-audio",
                'label'     => esc_html__('Hide assignment history on frontend', 'LDAG'),
                'help_text' => esc_html__('Choose whether to hide or show the assignment history.', 'LDAG'),
                'value'     => $saved_options['assignments_hide_history'] ?? '',
                'options'   => array(
                    '1'     => esc_html__('Hidden', 'LDAG'),
                    '0'     => '',
                ),
            );
            $setting_option_fields['assignment_reopen_comment'] = array(
                'name'      => 'assignment_reopen_comment',
                'type'      => 'textarea',
                'label'     => esc_html__( 'Assignment Reopen Comment', 'LDAG' ),
                'help_text' => esc_html__( 'This comment will show for all assignments.', 'LDAG' ),
                'value'     => $saved_options['assignment_reopen_comment'] ?? 'Your assignment has been reopened for resubmission. Please carefully review the feedback provided and make the necessary revisions to meet the required standards. We encourage you to use this opportunity to enhance your work before submitting again.',
            );
            $setting_option_fields['assignment_reject_comment'] = array(
                'name'      => 'assignment_reject_comment',
                'type'      => 'textarea',
                'label'     => esc_html__( 'Assignment Reject Comment', 'LDAG' ),
                'help_text' => esc_html__( 'This comment will show for all assignments.', 'LDAG' ),
                'value'     => $saved_options['assignment_reject_comment'] ?? 'Your assignment has been reviewed and does not meet the required criteria for approval. Please refer to the feedback provided and make the necessary improvements before resubmitting. If you need further clarification, do not hesitate to reach out for guidance.',
            );
            // $setting_option_fields['assignment_grades'] = array(
            //     'name'      => 'assignment_grades',
            //     'type'      => 'checkbox-switch',
            //     "class"     => "-small setting-audio",
            //     'label'     => esc_html__('Enable Assignment Grade', 'LDAG'),
            //     'help_text' => esc_html__('Choose whether to enable or disable the assignment grade.', 'LDAG'),
            //     'value'     => $saved_options['assignment_grades'] ?? '',
            //     'options'   => array(
            //         '1'     => esc_html__('Enabled', 'LDAG'),
            //         '0'     => '',
            //     ),
            // );
        }
    }

    return $setting_option_fields;
}

/**
 * Save the custom field value when settings are updated.
 */
add_filter( 'pre_update_option_learndash_settings_assignments_cpt', 'save_assignment_setting_fields', 10, 2 );

function save_assignment_setting_fields( $new_value, $old_value ) {
    // Ensure we are processing the correct settings page
    if ( isset( $_POST['option_page'] ) && $_POST['option_page'] === 'assignments-options' ) {
        if (isset($_POST['learndash_settings_assignments_cpt']['assignments_hide_history'])) {
            $new_value['assignments_hide_history'] = '1';
        }
        if ( isset( $_POST['learndash_settings_assignments_cpt']['assignment_reopen_comment'] ) ) {
            $new_value['assignment_reopen_comment'] = sanitize_text_field( wp_unslash( $_POST['learndash_settings_assignments_cpt']['assignment_reopen_comment'] ) );
        }
        if ( isset( $_POST['learndash_settings_assignments_cpt']['assignment_reject_comment'] ) ) {
            $new_value['assignment_reject_comment'] = sanitize_text_field( wp_unslash( $_POST['learndash_settings_assignments_cpt']['assignment_reject_comment'] ) );
        }
    }

    return $new_value;
}

/**
 * Display the assignment fields lable capitalize on the Assignment Options page.
 */
function assignment_admin_css() {
    $screen = get_current_screen();
    if ($screen && $screen->id === 'sfwd-assignment_page_assignments-options') {
        echo '<style>.sfwd_label{text-transform:capitalize;}</style>';
    }
}
add_action('admin_head', 'assignment_admin_css');
