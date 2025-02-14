<?php
/**
 * The assignment grading functionality of the plugin.
 */
class LD_Assignment_Grading_Grading {

    /**
     * Initialize the class and set its properties.
     */
    public function __construct() {
        // Hook into LearnDash assignment submission
        add_action('learndash_assignment_uploaded', array($this, 'handle_assignment_submission'), 10, 2);

        // Hook into LearnDash assignment grading
        add_action('learndash_assignment_grading', array($this, 'handle_assignment_grading'), 10, 2);
    }

    /**
     * Handle assignment submission.
     *
     * @param int $assignment_id The assignment ID.
     * @param array $assignment_meta The assignment metadata.
     */
    public function handle_assignment_submission($assignment_id, $assignment_meta) {
        // Fire before submission hook
        do_action('ld_assignment_grading_before_submission', $assignment_id, $assignment_meta);

        // Log the assignment submission
        error_log('Assignment submitted: ' . $assignment_id);

        // Example: Notify the instructor
        $instructor_email = get_option('admin_email');
        $subject = __('New Assignment Submitted', 'learndash-assignment-grading');
        $message = sprintf(__('A new assignment has been submitted. Assignment ID: %d', 'learndash-assignment-grading'), $assignment_id);
        wp_mail($instructor_email, $subject, $message);

        // Fire after submission hook
        do_action('ld_assignment_grading_after_submission', $assignment_id, $assignment_meta);
    }

    /**
     * Handle assignment grading.
     *
     * @param int $assignment_id The assignment ID.
     * @param array $grade_data The grading data.
     */
    public function handle_assignment_grading($assignment_id, $grade_data) {
        // Fire before grading hook
        do_action('ld_assignment_grading_before_grading', $assignment_id, $grade_data);

        // Log the assignment grading
        error_log('Assignment graded: ' . $assignment_id);

        // Filter grading data
        $grade_data = apply_filters('ld_assignment_grading_grade_data', $grade_data, $assignment_id);

        // Example: Save the grade to the database
        $grade = isset($grade_data['grade']) ? floatval($grade_data['grade']) : 0;
        update_post_meta($assignment_id, '_ld_assignment_grade', $grade);

        // Example: Notify the student
        $student_id = get_post_meta($assignment_id, 'user_id', true);
        $student_email = get_userdata($student_id)->user_email;
        $subject = __('Your Assignment Has Been Graded', 'learndash-assignment-grading');
        $message = sprintf(__('Your assignment (ID: %d) has been graded. Grade: %.2f', 'learndash-assignment-grading'), $assignment_id, $grade);

        // Filter notification email content
        $message = apply_filters('ld_assignment_grading_notification_email', $message, $assignment_id, $grade_data);

        wp_mail($student_email, $subject, $message);

        // Fire after grading hook
        do_action('ld_assignment_grading_after_grading', $assignment_id, $grade_data);
    }
}