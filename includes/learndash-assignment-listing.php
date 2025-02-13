<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

add_filter('learndash_listing_columns', 'ldags_add_reopen_column', 10, 2);

function ldags_add_reopen_column($columns, $post_type)
{
    if ($post_type == 'sfwd-assignment') {
        $columns['approval_status'] = array(
            'label'   => esc_html__('Status / Points', 'learndash'),
            'after'   => 'author',
            'display' => 'change_status_after_reopen',
        );
        $columns['reopen_assignmen'] = array(
            'label'   => esc_html__('Assignment Action', 'learndash'),
            'after'   => 'approval_status',
            'display' => 'show_column_essay_quiz',
        );
        return $columns;
    } else {
        return $columns;
    }
}

function show_column_essay_quiz($post_id = 0, $column_meta = array()) {
    // Get necessary metadata
    $check_reopen_status = get_post_meta($post_id, 'ldags_assignment_reopen');
    $lesson_id = get_post_meta($post_id, 'lesson_id', true);
    $approval_status_flag = learndash_is_assignment_approved_by_meta($post_id);

    // Determine rejection based on $check_reopen_status logic
    $is_rejected = (isset($check_reopen_status) && is_array($check_reopen_status) && !empty($check_reopen_status) && $check_reopen_status[0] != '' && intval($check_reopen_status[0]) != 1);
    $is_reopened = (isset($check_reopen_status) && is_array($check_reopen_status) && !empty($check_reopen_status) && $check_reopen_status[0] == 1);

    // If the assignment is approved, reopened, or rejected, do not show buttons
    if ($approval_status_flag || $is_reopened || $is_rejected) {
        return;
    }

    // Generate a nonce for security
    $nonce = wp_create_nonce('ldags_reopen_assignment_' . $post_id);

    echo '<div class="ld-approval-action">';
    echo '<button id="assignment_approve_' . absint($post_id) . '" class="small assignment_approve_single">' . __('Approve', 'learndash') . '</button>';
    echo ' <button class="small reopen_assignment_btn" id="' . $post_id . '" data-assignment-id="' . $post_id . '" data-lesson-id="' . $lesson_id . '" data-nonce="' . $nonce . '">' . __('Re-open', 'learndash') . '</button>';
    echo ' <button class="small reject_assignment_btn" id="reject" data-assignment-id="' . $post_id . '" data-lesson-id="' . $lesson_id . '" data-nonce="' . $nonce . '">' . __('Reject', 'learndash') . '</button>';
    echo '</div>';
}


// function show_column_essay_quiz($post_id = 0, $column_meta = array())
    // {
    //     $check_reopen_status = get_post_meta($post_id, 'ldags_assignment_reopen', true);
    //     $lesson_id = get_post_meta($post_id, 'lesson_id', true);
    //     $approval_status_flag  = learndash_is_assignment_approved_by_meta($post_id);
    //     // Generate a nonce for security
    //     $nonce = wp_create_nonce('ldags_reopen_assignment_' . $post_id);
    //     if ($approval_status_flag == 1) {
    //         if ($check_reopen_status) {
    //             echo '<div class="ld-approval-action"><button id="assignment_approve_'. absint($post_id).'" class="small assignment_approve_single">'. __('Approve', 'learndash').'</button>';
    //             echo ' <button class="small reject_assignment_btn" id="reject" data-assignment-id="'.$post_id.'" data-lesson-id="'.$lesson_id.'" data-nonce="'.$nonce.'">' . __('Reject', 'learndash') . '</button></div>';
    //         } else {
    //             echo '<div class="ld-approval-action"><button class="small reopen_assignment_btn" id="' . $post_id . '" data-assignment-id="'.$post_id.'" data-lesson-id="'.$lesson_id.'" data-nonce="'.$nonce.'">' . __('Re-open', 'learndash') . ' </button>';
    //             echo ' <button class="small reject_assignment_btn" id="reject" data-assignment-id="'.$post_id.'" data-lesson-id="'.$lesson_id.'" data-nonce="'.$nonce.'">' . __('Reject', 'learndash') . '</button></div>';
    //         }
    //     } else {
    //         echo '<div class="ld-approval-action"><button id="assignment_approve_'. absint($post_id).'" class="small assignment_approve_single">'. __('Approve', 'learndash').'</button>';
    //         echo ' <button class="small reject_assignment_btn" id="reject" data-assignment-id="'.$post_id.'" data-lesson-id="'.$lesson_id.'" data-nonce="'.$nonce.'">' . __('Reject', 'learndash') . '</button></div>';
    //     }
// }


/**
 * change status
 * hide points 
 * change approve button 
 * 
 * @param integer $post_id
 * @param array $column_meta
 * @return void
 */
function change_status_after_reopen($post_id = 0, $column_meta = array())
{
    $post_id = absint($post_id);
    if (!empty($post_id)) {
        $check_reopened_status = get_post_meta($post_id, 'ldags_assignment_reopen');
        // error_log('test: ' . var_export($check_reopened_status, true));
        $lesson_id = intval(get_post_meta($post_id, 'lesson_id', true));
        if (!empty($lesson_id)) {
            $approval_status_flag = intval(learndash_is_assignment_approved_by_meta($post_id));
            // error_log('test1: ' . var_export($approval_status_flag, true));
            if (1 == $approval_status_flag) {
                $approval_status_slug  = 'approved';
                $approval_status_label = _x('Approved', 'Assignment approval status', 'learndash');
            } else {
                if (isset($check_reopen_status) && is_array($check_reopen_status) && !empty($check_reopen_status) && $check_reopened_status[0] != '') {
                    if (intval($check_reopened_status[0]) != 1) {
                        $approval_status_slug  = 'not_approved';
                        $approval_status_label = _x('Rejected', 'Assignment approval status', 'learndash');
                    } else {
                        $approval_status_slug  = 'not_approved';
                        $approval_status_label = _x('Re-opened', 'Assignment approval status', 'learndash');
                    }
                } else {
                    $approval_status_slug  = 'not_approved';
                    $approval_status_label = _x('Not Approved', 'Assignment approval status', 'learndash');
                }
            }

            echo '<div class="ld-approval-status">' . sprintf(
                // translators: placeholder: Status label, Status value.
                esc_html_x('%1$s: %2$s', 'placeholder: Status label, Status value', 'learndash'),
                '<span class="learndash-listing-row-field-label">' . esc_html__('Status', 'learndash') . '</span>',
                esc_html($approval_status_label)
            ) . '</div>';

            echo '<div class="ld-approval-points">';
            if (learndash_assignment_is_points_enabled($post_id)) {
                $max_points = 0;
                $max_points = learndash_get_setting($lesson_id, 'lesson_assignment_points_amount');

                $current_points = get_post_meta($post_id, 'points', true);
                if (1 != $approval_status_flag) {

                    $points_label = '<label class="learndash-listing-row-field-label" for="assignment_points_' . absint($post_id) . '">' . esc_html__('Points', 'learndash') . '</label>';
                    $points_input = '<input id="assignment_points_' . absint($post_id) . '" class="small-text learndash-award-points" type="number" value="' . absint($current_points) . '" max="' . absint($max_points) . '" min="0" step="1" name="assignment_points[' . absint($post_id) . ']" />';
                    echo sprintf(
                        // translators: placeholders: Points label, points input, maximum points.
                        esc_html_x('%1$s: %2$s / %3$d', 'placeholders: Points label, points input, maximum points', 'learndash'),
                        $points_label, //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                        $points_input, //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                        absint($max_points)
                    );
                } else {
                    // if( $check_reopened_status[0] != 1 ){

                    $points_field = '<span class="learndash-listing-row-field-label">' . esc_html__('Points', 'learndash') . '</span>';
                    echo sprintf(
                        // translators: placeholders: Points label, current points, maximum points.
                        esc_html_x('%1$s: %2$d / %3$d', 'placeholders: Points label, points input, maximum points', 'learndash'),
                        $points_field, //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                        absint($current_points),
                        absint($max_points)
                    );
                    // }
                }
            } else {
                echo sprintf(
                    // translators: placeholder: Points label.
                    esc_html_x('%s: Not Enabled', 'placeholder: Points label', 'learndash'),
                    '<span class="learndash-listing-row-field-label">' . esc_html__('Points', 'learndash') . '</span>'
                );
            }
            echo '</div>';

            if (1 != $approval_status_flag) {

            /*
            ?>
                <div class="ld-approval-action">
                    <button id="assignment_approve_<?php echo absint($post_id); ?>" class="small assignment_approve_single"><?php esc_html_e('Approve', 'learndash'); ?></button>
                </div>

            <?php
            */

            }
        }
    }
}

add_action('wp_ajax_ldags_approve_assignment', 'ldags_approve_assignment_handler');
function ldags_approve_assignment_handler() {
    // Ensure necessary data is received
    if (!isset($_POST['assignment_id'], $_POST['nonce'])) {
        wp_send_json_error(['message' => __('Invalid request.', 'learndash')]);
    }

    $assignment_id = intval($_POST['assignment_id']);
    $lesson_id = intval($_POST['lesson_id']);
    $nonce = sanitize_text_field($_POST['nonce']);

    // Verify nonce
    if (!wp_verify_nonce($nonce, 'ldags_reopen_assignment_' . $assignment_id)) {
        wp_send_json_error(['message' => __('Security check failed.', 'learndash')]);
    }

    // Check if user has permission to reopen
    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => __('You must be logged in.', 'learndash')]);
    }
    
    // Approve the assignment
    update_post_meta($assignment_id, 'approval_status', 1);

    /**
     * Send notification to the user and admin
     * 
     * @param integer $assignment_id
     * @param integer $lesson_id
     * @param integer $user_id
     * @param string $action
     */
    send_assignment_notification($assignment_id, null, get_current_user_id(), 'approve');

    // Respond with success
    wp_send_json_success(['message' => __('Assignment approved successfully!', 'learndash')]);
}

add_action('wp_ajax_ldags_reopen_assignment', 'ldags_reopen_assignment_handler');
function ldags_reopen_assignment_handler() {
    // Ensure necessary data is received
    if (!isset($_POST['assignment_id'], $_POST['nonce'])) {
        wp_send_json_error(['message' => __('Invalid request.', 'learndash')]);
    }

    $assignment_id = intval($_POST['assignment_id']);
    $lesson_id = intval($_POST['lesson_id']);
    $nonce = sanitize_text_field($_POST['nonce']);

    // Verify nonce
    if (!wp_verify_nonce($nonce, 'ldags_reopen_assignment_' . $assignment_id)) {
        wp_send_json_error(['message' => __('Security check failed.', 'learndash')]);
    }

    // Check if user has permission to reopen
    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => __('You must be logged in.', 'learndash')]);
    }

    /**
     * Get the current user ID
     * 
     * @var integer
     */
    $user_id = get_post_meta($assignment_id, 'user_id', true);
    
    /**
     * Check to see if the user has uploaded the maximium number of assignments
     *
     * @var null
     */
    $assignment_upload_limit = learndash_get_setting( $lesson_id, 'assignment_upload_limit_count' );
    if ( isset( $assignment_upload_limit ) ) {
        $assignment_upload_limit_count = intval( $assignment_upload_limit );
        if ( $assignment_upload_limit_count > 0 ) {
            $assignments = learndash_get_user_assignments( $lesson_id, $user_id );
            if ( ! empty( $assignments ) && count( $assignments ) >= $assignment_upload_limit_count ) {
                // Update setting
                $assignment_upload_limit_count = $assignment_upload_limit_count + 1;
                learndash_update_setting($lesson_id, 'assignment_upload_limit_count', $assignment_upload_limit_count);
            }
        }
    }

    $comment_content = get_option( 'learndash_settings_assignments_cpt', [] )['assignment_reopen_comment'] ?? '';

    // Insert a comment
    $comment_data = array(
        'comment_post_ID' => $assignment_id,
        'comment_content' => $comment_content,
        'user_id'         => $user_id,
        'comment_approved' => 1, // Auto-approve the comment
    );
    wp_insert_comment($comment_data);

    // Process resubmission (e.g., update metadata)
    update_post_meta($assignment_id, 'approval_status', 0);
    update_post_meta($assignment_id, 'ldags_assignment_reopen', 1);

    /**
     * Send notification to the user and admin
     * 
     * @param integer $assignment_id
     * @param integer $lesson_id
     * @param integer $user_id
     * @param string $action
     */
    send_assignment_notification($assignment_id, $lesson_id, $user_id, 'reopen');

    // Respond with success
    wp_send_json_success(['message' => __('Assignment reopen successfully!.', 'learndash')]);
}

add_action('wp_ajax_ldags_reject_assignment', 'ldags_reject_assignment_handler');
function ldags_reject_assignment_handler() {
    // Ensure necessary data is received
    if (!isset($_POST['assignment_id'], $_POST['nonce'])) {
        wp_send_json_error(['message' => __('Invalid request.', 'learndash')]);
    }

    $assignment_id = intval($_POST['assignment_id']);
    $lesson_id = intval($_POST['lesson_id']);
    $nonce = sanitize_text_field($_POST['nonce']);

    // Verify nonce
    if (!wp_verify_nonce($nonce, 'ldags_reopen_assignment_' . $assignment_id)) {
        wp_send_json_error(['message' => __('Security check failed.', 'learndash')]);
    }

    // Check if user has permission to reopen
    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => __('You must be logged in.', 'learndash')]);
    }

    /**
     * Get the current user ID
     * 
     * @var integer
     */
    $user_id = get_post_meta($assignment_id, 'user_id', true);
    
    /**
     * Check to see if the user has uploaded the maximium number of assignments
     *
     * @var null
     */
    $assignment_upload_limit = learndash_get_setting( $lesson_id, 'assignment_upload_limit_count' );
    if ( isset( $assignment_upload_limit ) ) {
        $assignment_upload_limit_count = intval( $assignment_upload_limit );
        if ( $assignment_upload_limit_count > 0 ) {
            $assignments = learndash_get_user_assignments( $lesson_id, $user_id );
            if ( ! empty( $assignments ) && count( $assignments ) >= $assignment_upload_limit_count ) {
                // Update setting
                $assignment_upload_limit_count = $assignment_upload_limit_count + 1;
                learndash_update_setting($lesson_id, 'assignment_upload_limit_count', $assignment_upload_limit_count);
            }
        }
    }

    $comment_content = get_option( 'learndash_settings_assignments_cpt', [] )['assignment_reject_comment'] ?? '';

    // Insert a comment
    $comment_data = array(
        'comment_post_ID' => $assignment_id,
        'comment_content' => $comment_content,
        'user_id'         => $user_id,
        'comment_approved' => 1, // Auto-approve the comment
    );
    wp_insert_comment($comment_data);

    // Process resubmission (e.g., update metadata)
    update_post_meta($assignment_id, 'approval_status', 0);
    update_post_meta($assignment_id, 'ldags_assignment_reopen', 0);

    /**
     * Send notification to the user and admin
     * 
     * @param integer $assignment_id
     * @param integer $lesson_id
     * @param integer $user_id
     * @param string $action
     */
    send_assignment_notification($assignment_id, $lesson_id, $user_id, 'reject');

    // Respond with success
    wp_send_json_success(['message' => __('Assignment reject successfully!.', 'learndash')]);
}

/**
 * Send notification to the user and admin
 * 
 * @param integer $assignment_id
 * @param integer $lesson_id
 * @param integer $user_id
 * @param string $action
 */
function send_assignment_notification($assignment_id, $lesson_id, $user_id, $action) {
    // Get Assignment Author (User who submitted it)
    $assignment_author_id = get_post_field('post_author', $assignment_id);
    $assignment_author = get_userdata($assignment_author_id);
    $assignment_author_email = $assignment_author->user_email;

    // Get the admin email
    $admin_email_current_user = get_userdata($user_id)->user_email;
    $admin_email = get_option('admin_email');

    // Get the reason (assuming it's stored in post meta)
    $reason = get_option( 'learndash_settings_assignments_cpt', [] )['assignment_'.$action.'_comment'];
    if (empty($reason)) {
        $reason = __('No specific reason provided.', 'learndash');
    }

    // Email Subject & Message
    switch ($action){
        case 'approve':
            $subject = __('Assignment Approved', 'learndash');
            $message = sprintf(
                __("Hello %s,\n\nCongratulations! Your assignment \"%s\" has been approved.\n\nThank you for your hard work!", 'learndash'),
                $assignment_author->display_name,
                get_the_title($assignment_id)
            );
            $admin_message = sprintf(
                __("Hello,\n\nThe assignment \"%s\" by user %s (%s) has been approved successfully.\n\nThank you!", 'learndash'),
                get_the_title($assignment_id),
                $assignment_author->display_name,
                $assignment_author_email
            );
            break;
        case 'reopen':
            $subject = __('Assignment Reopened', 'learndash');
            $message = sprintf(
                __("Hello %s,\n\nYour assignment %s has been re-opened for resubmission due to the following reason:\n\n%s\n\nPlease resubmit it at your earliest convenience.\n\nThank you!", 'learndash'),
                $assignment_author->display_name,
                get_the_title($assignment_id),
                $reason
            );
            $admin_message = sprintf(
                __("Hello,\n\nThe assignment %s by user %s (%s) has been re-opened for resubmission.\n\nReason:\n%s\n\nPlease review accordingly.\n\nThank you!", 'learndash'),
                get_the_title($assignment_id),
                $assignment_author->display_name,
                $assignment_author_email,
                $reason
            );
            break;
        case 'reject':
            $subject = __('Assignment Rejected', 'learndash');
            $message = sprintf(
                __("Hello %s,\n\nYour assignment %s has been rejected due to the following reason:\n\n%s\n\nPlease review and resubmit it at your earliest convenience.\n\nThank you!", 'learndash'),
                $assignment_author->display_name,
                get_the_title($assignment_id),
                $reason
            );
            $admin_message = sprintf(
                __("Hello,\n\nThe assignment %s by user %s (%s) has been rejected due to the following reason:\n\nReason:\n%s\n\nPlease review accordingly.\n\nThank you!", 'learndash'),
                get_the_title($assignment_id),
                $assignment_author->display_name,
                $assignment_author_email,
                $reason
            );
            break;
    }

    // Send Email to Assignment Author
    wp_mail($assignment_author_email, $subject, $message);

    // Send Email to Admin
    wp_mail($admin_email_current_user, $subject, $admin_message);
    if (strtolower($admin_email_current_user) !== strtolower($admin_email)){
        wp_mail($admin_email, $subject, $admin_message);
    }
}

// Enqueue JavaScript for the Assignment Listing Page and Localize Data
function ldags_enqueue_admin_listing_scripts($hook) {
    if ($hook === 'edit.php' && isset($_GET['post_type']) && $_GET['post_type'] === 'sfwd-assignment') {
        wp_enqueue_script(
            'ldags-listing-script',
            LD_ASSIGNMENT_GRADING_URL . 'assets/js/assignment-listing.js',
            array('jquery'),
            '1.0',
            true
        );

        // Localize script to pass AJAX URL and nonce
        wp_localize_script('ldags-listing-script', 'ldags_assignment_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php')
        ));
    }
}
add_action('admin_enqueue_scripts', 'ldags_enqueue_admin_listing_scripts');
