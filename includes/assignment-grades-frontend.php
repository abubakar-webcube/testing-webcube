<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class LearnDashAssignmentColumns {
    /**
     * @var object $assignment Stores the assignment object.
     * @var number $lesson_id Stores the assignment lesson id.
     */
    private $assignment;
    private $lesson_id;

    /**
     * Constructor to initialize hooks.
     */
    public function __construct() {
        // Initialize the assignment variable.
        $this->assignment = null;
        $this->assignment = 0;

        // Hook to modify the columns.
        add_filter('learndash-assignment-list-columns', [ $this, 'modify_columns' ]);

        // Example usage for learndash-assignment-row-before action.
        add_action('learndash-assignment-row-before', [ $this, 'assignment_row_before' ], 10, 4);

        // Hook to add content to custom columns.
        add_filter('learndash-assignment-list-columns-content', [ $this, 'add_column_content' ], 10, 5);

        // Hook to modify the alert message.
        add_filter('learndash_alert_message', array($this, 'ldags_alert_message'), 10, 3);

        // Example usage for learndash-assignment-list-before action (commented out for now).
        // add_action('learndash-assignment-list-before', [ $this, 'assignment_list_before' ], 10, 3);

        // Add custom css to the assignment list page.
        add_action('wp_head', array($this, 'ldags_assigments_report') );
        
    }

    /**
     * Modify columns in the LearnDash assignment list.
     *
     * @param array $columns The current list of columns.
     * @return array The modified list of columns.
     */
    public function modify_columns( $columns ) {
        // Debugging: Log the modified columns.
        // error_log('Modified columns: ' . var_export($columns, true));
        // Remove the 'ld-assignment-column-approved' key if it exists.
        if ( isset( $columns['ld-assignment-column-approved'] ) ) {
            if($columns['ld-assignment-column-approved'] != '0/0 Approved'){
                // Add custom columns.
                $columns['comments'] = __('Comments', 'learndash');
                $columns['Grade'] = __('Grade', 'learndash');
                $columns['status'] = $columns['ld-assignment-column-approved'];
                unset( $columns['ld-assignment-column-approved'] );
            }
        }

        return $columns;
    }

    /**
     * Example usage for learndash-assignment-row-before action.
     *
     * @param object $assignment The assignment object.
     * @param int    $post_id    The post ID.
     * @param int    $course_id  The course ID.
     * @param int    $user_id    The user ID.
     */
    public function assignment_row_before( $assignment, $post_id, $course_id, $user_id ) {
        $this->assignment = $assignment;
        $this->lesson_id = $post_id;
    }

    /**
     * Add content to assignment columns in the assignment list.
     *
     * @param array $row_columns The current row columns data.
     * @return array The modified row columns data.
     */
    public function add_column_content( $row_columns ) {
        // error_log('Row columns before modification: ' . var_export($row_columns, true));
        $approval_status = get_post_meta( $this->assignment->ID, 'approval_status', true );
        $check_reopen_status = get_post_meta($this->assignment->ID, 'ldags_assignment_reopen');
        $grading_scale = get_post_meta( $this->lesson_id, 'assignment_grading_type', true );
        
        // Retrieve the grading scale and type from the lesson or topic.
        $grading_type = get_post_meta( $this->lesson_id, 'assignment_grading_type', true );

        // Default grading scale if none is defined in meta.
        $grading_scale = [
            ['min' => 95, 'max' => 100, 'grade' => 'A+', 'points' => 4.00],
            ['min' => 90, 'max' => 94, 'grade' => 'A',  'points' => 3.75],
            ['min' => 85, 'max' => 89, 'grade' => 'B+', 'points' => 3.50],
            ['min' => 80, 'max' => 84, 'grade' => 'B',  'points' => 3.00],
            ['min' => 75, 'max' => 79, 'grade' => 'C+', 'points' => 2.50],
            ['min' => 70, 'max' => 74, 'grade' => 'C',  'points' => 2.00],
            ['min' => 65, 'max' => 69, 'grade' => 'D+', 'points' => 1.50],
            ['min' => 60, 'max' => 64, 'grade' => 'D',  'points' => 1.00],
            ['min' => 0,  'max' => 59, 'grade' => 'F',  'points' => 0.00],
        ];

        // Fetch the assignment points.
        $assignment_points = (float) get_post_meta( $this->assignment->ID, 'points', true );

        // Retrieve the lesson type and lesson total points.
        $lesson_type = get_post_type( $this->lesson_id );
        $lesson_meta = get_post_meta( $this->lesson_id, '_' . $lesson_type, true );
        $lesson_total_points = isset( $lesson_meta[$lesson_type . '_lesson_assignment_points_amount'] ) 
                            ? (float) $lesson_meta[$lesson_type . '_lesson_assignment_points_amount'] 
                            : 100; // Default to 100 if not defined.

        // Normalize the points to a 0-100 range.
        $normalized_points = ($assignment_points / $lesson_total_points) * 100;

        // Determine the grade based on the grade type.
        $grade_display = '';
        switch ( $grading_type ) {
            case 'AF':
                $grade_display = 'N/A'; // Default value.
                foreach ( $grading_scale as $scale ) {
                    if ( $normalized_points >= $scale['min'] && $normalized_points <= $scale['max'] ) {
                        $grade_display = 'Grade: <b>' . $scale['grade'] . '</b>';
                        break;
                    }
                }
                break;

            case 'GPA':
                $grade_display = 'N/A'; // Default value.
                foreach ( $grading_scale as $scale ) {
                    if ( $normalized_points >= $scale['min'] && $normalized_points <= $scale['max'] ) {
                        $grade_display = 'GPA: <b>' . number_format( $scale['points'], 2 ) . '</b>';
                        break;
                    }
                }
                break;

            case 'points':
            default:
                $grade_display = sprintf( __('<b>%d/%d</b> Points', 'learndash'), $assignment_points, $lesson_total_points );
                break;
        }
        $new_columns = [];
        // Add custom content for specific columns.
        foreach ( $row_columns as $key => $column ) {
            if ( $key === 'status') {
                if($approval_status == 1){
                    // Add content for the 'grade' column.
                    $new_columns['grade'] = sprintf(
                        __('%s', 'learndash'),
                        $grade_display
                    );
                    // Update the 'status' column content based on the approval status.
                    $new_columns[$key] = '<span class="ld-status ld-status-complete"><span class="ld-icon ld-icon-checkmark"></span> '.__('Approved', 'learndash').'</span>';
                } else {
                    // Add content for the 'grade' column.
                    $new_columns['grade'] = sprintf(
                        __('%s', 'learndash'),
                        '--'
                    );
                    if (isset($check_reopen_status) && is_array($check_reopen_status) && !empty($check_reopen_status) && $check_reopen_status[0] != '') {
                        if (intval($check_reopen_status[0]) != 1) {
                            $new_columns[$key] = '<span class="ld-status ld-status-waiting"><span class="ld-icon ld-icon-alert"></span> '.__('Rejected', 'learndash').'</span>';
                        } else {
                            $new_columns[$key] = '<span class="ld-status ld-status-alert"><span class="ld-icon ld-icon-clock"></span> '.__('Re-opened', 'learndash').'</span>';
                        }
                    } else {
                        $new_columns[$key] = $column;
                    }
                }
            } else {
                $new_columns[$key] = $column;
            }
        }

        // error_log('Row columns after modification: ' . var_export($new_columns, true));

        return $new_columns;
    }

    /**
     * Example usage for learndash-assignment-list-before action (optional).
     *
     * @param int $step_id   The step ID.
     * @param int $course_id The course ID.
     * @param int $user_id   The user ID.
     */
    public function assignment_list_before( $step_id, $course_id, $user_id ) {
        echo "<p>Assignments for this step ($step_id) in course ($course_id) for user ($user_id)</p>";
    }

    /**
     * Change alert message     
     *
     * @param [string] $message
     * @param [string] $type
     * @param [string] $icon
     * @return $message
     */
    public function ldags_alert_message($message, $type, $icon){
        if ($type !== 'success') {
            $current_post   = get_post();
            $current_user   = get_current_user_id();
            $assignments    = learndash_get_user_assignments($current_post->ID, $current_user);

            foreach ($assignments as $assignment) {
                $reopen_status = get_post_meta($assignment->ID, 'ldags_assignment_reopen', false); // Retrieve as an array
                
                if (!empty($reopen_status) && isset($reopen_status[0])) {
                    if (intval($reopen_status[0]) === 1) {
                        // Assignment Reopened
                        $message = __(
                            'Your assignment has been reopened. Please review the feedback and resubmit your assignment.', 
                            'learndash'
                        );
                    } elseif (intval($reopen_status[0]) === 0) {
                        // Assignment Rejected
                        $message = __(
                            'Your assignment has been rejected. Please check the feedback and make necessary corrections before resubmitting.', 
                            'learndash'
                        );
                    }
                    return $message;
                }
            }
        }
        return $message;
    }

    /**
     * Add custom css to the assignment list page.
     */
    public function ldags_assigments_report(){
        $assignments_hide_history = get_option( 'learndash_settings_assignments_cpt', [] )['assignments_hide_history'] ?? '';
        // error_log('assignments_hide_history: ' . var_export($assignments_hide_history, true));
        if ( $assignments_hide_history === '1' ) {
            echo '<style>:is(.ld-assignment-list) .ld-table-list-item:nth-child(n+2){display:none;}</style>';
        }
    }
}

// Initialize the class.
new LearnDashAssignmentColumns();


// /**
    //  * learndash-assignment-list-columns filter.
    //  */
    // add_filter('learndash-assignment-list-columns', function( $columns ) {
    //     // Remove the 'ld-assignment-column-approved' key
    //     if ( isset( $columns['ld-assignment-column-approved'] ) ) {
    //         unset( $columns['ld-assignment-column-approved'] );
    //     }

    //     // Add custom columns
    //     $columns['comments'] = __('Comments', 'learndash');
    //     $columns['status'] = __('Status', 'learndash');

    //     // Log the updated columns for debugging
    //     error_log('columns: ' . var_export($columns, true));

    //     return $columns;
    // });

    // /**
    //  * learndash-assignment-list-before action.
    //  */
    // // add_action( 'learndash-assignment-list-before', function( $step_id, $course_id, $user_id ) {
    // //     echo "<p>Assignments for this step ($step_id) in course ($course_id) for user ($user_id)</p>";
    // // }, 10, 3 );

    // /**
    //  * Example usage for learndash-assignment-row-before action.
    //  */
    // add_action(
    //     'learndash-assignment-row-before',
    //     function( $assignment, $post_id, $course_id, $user_id ) {
    //         echo '<pre>';print_r(get_post_meta( $assignment->ID, 'approval_status', true ));echo '</pre>';
    //         echo "<p>Assignment row for post ($post_id) in course ($course_id) for user ($user_id)</p>";
    //     },
    //     10,
    //     4
    // );

    // // Add content to the custom columns
    // add_filter( 'learndash-assignment-list-columns-content', function ( $row_columns ) {
    //     error_log('row_columns: ' . var_export($row_columns,true));
    //     foreach($row_columns as $key => $column) {
    //         // error_log('key: ' . var_export($key,true));
    //         // error_log('column: ' . var_export($column,true));

    //         // Add custom content for the 'status' column.
    //         if ( $key === 'status' ) {
    //             // $is_approved = get_post_meta( $post->ID, 'approval_status', true );
    //             //$row_columns[$key] = $is_approved === 'approved' ? __('Approved', 'learndash') : __('Pending', 'learndash');
    //         }
    //     }

    //     // // Add custom content for the 'grades' column.
    //     $row_columns['grades'] = 'Grades';

    //     return $row_columns;
// }, 10, 5 );


