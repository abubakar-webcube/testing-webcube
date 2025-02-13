<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * LearnDash assignment settings fields
 */

// Add assignment sub-setting under "Upload Assignment"
function learndash_add_assignment_topic_settings( $setting_option_fields = array(), $settings_metabox_key = '' ) {
    if ( 'learndash-lesson-display-content-settings' === $settings_metabox_key || 'learndash-topic-display-content-settings' === $settings_metabox_key ) {
        $array_temp = array();
        $child_section = '';

        foreach ( $setting_option_fields as $index => $item ) {
            $array_temp[ $index ] = $item;

            if ( $item['name'] == 'lesson_assignment_upload' ) {
                $grading_type_value = get_post_meta( get_the_ID(), 'assignment_grading_type', true );

                if ( empty( $grading_type_value ) ) {
                    $grading_type_value = '';
                } else {
                    $child_section = 'open';
                }

                $array_temp['assignment_grading_type'] = array(
                    "name"                => "assignment_grading_type",
                    "label"               => "Grading type",
                    "type"                => "select",
                    "class"               => "-small",
                    "parent_setting"      => "lesson_assignment_upload",
                    "value"               => $grading_type_value,
                    "help_text"           => "Select a grading type for this assignment.",
                    "child_section_state" => $child_section,
                    "options"             => array(
                        "AF" => "A-F",
                        "GPA" => "GPA",
                        "points" => "Points",
                    ),
                );
            }
        }

        return $array_temp;
    }

    return $setting_option_fields;
}
add_filter('learndash_settings_fields', 'learndash_add_assignment_topic_settings', 30, 2);

function learndash_save_assignment_topic_metadata( $post_id ) {
    if ( isset( $_POST['post_type'] ) && ($_POST['post_type'] === 'sfwd-lessons' || $_POST['post_type'] === 'sfwd-topic') ) {
        switch ( $_POST['post_type'] ) {
            case 'sfwd-lessons':
                $post_type = 'lesson';
                break;
            case 'sfwd-topic':
                $post_type = 'topic';
                break;
        }

        if ( isset( $_POST[ 'learndash-' . $post_type . '-display-content-settings' ]['assignment_grading_type'] ) ) {
            update_post_meta( $post_id, 'assignment_grading_type', sanitize_text_field( $_POST[ 'learndash-' . $post_type . '-display-content-settings' ]['assignment_grading_type'] ) );
        }
    }
}
add_action('save_post', 'learndash_save_assignment_topic_metadata');