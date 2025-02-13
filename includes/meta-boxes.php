<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * LearnDash assignment meta boxes
 */
function add_assignment_meta_box() {
    add_meta_box(
        'assignment_meta_box', // ID of the meta box
        'LranDash Assignment Grading Settings', // Title of the meta box
        'render_assignment_meta_box', // Callback function to render the meta box
        'sfwd-assignment', // Post type
        'normal', // Context: where the box appears (normal, side, advanced)
        'default' // Priority: high, low
    );
}
add_action( 'add_meta_boxes', 'add_assignment_meta_box' );

function render_assignment_meta_box( $post ) {
    $approval_status = get_post_meta( $post->ID, 'approval_status', true );
    $assignment_reopen = get_post_meta( $post->ID, 'ldags_assignment_reopen', true );
    $course_id = get_post_meta($post->ID, 'course_id', true);
    $lesson_id = get_post_meta($post->ID, 'lesson_id', true);
    // $lesson_type = get_post_type( $lesson_id );
    // $lesson_meta = get_post_meta( $lesson_id, '_' . $lesson_type, true );
    // $lesson_upload_limit = $lesson_meta[$lesson_type . '_assignment_upload_limit_count'];
    // learndash_get_setting( $lesson_id, 'assignment_upload_limit_count' );
    // $post_metas = get_post_meta($post->ID);
    // foreach($post_metas as $key=>$val){
    //     // if(is_serialized( $val[0] )){
    //     //     // echo $key . '</br>';
    //     //     echo '<pre>';print_r(unserialize($val[0]));echo '</pre>';
    //     // }
    //     echo $key . ' : ' . $val[0] . '<br/>';
    // }
    // Fetch existing values from the database
    $grading_type = get_post_meta( $post->ID, 'assignment_grading_type', true ) ?? get_post_meta( $lesson_id, 'assignment_grading_type', true );
    $show_manual_grades = get_post_meta( $post->ID, 'show_manual_grades', true ) ?? get_post_meta( $lesson_id, 'show_manual_grades', true );
    $assignment_comment = get_post_meta( $post->ID, 'assignment_comment', true ) ?? get_post_meta( $lesson_id, 'automated_comments', true );

    // Nonce field for security
    wp_nonce_field( 'save_assignment_meta_box', 'assignment_meta_box_nonce' );

    $grading = [
        ['min' => 95, 'max' => 100, 'grade' => 'A+', 'gpa' => 4.00],
        ['min' => 90, 'max' => 94, 'grade' => 'A',  'gpa' => 3.75],
        ['min' => 85, 'max' => 89, 'grade' => 'B+', 'gpa' => 3.50],
        ['min' => 80, 'max' => 84, 'grade' => 'B',  'gpa' => 3.00],
        ['min' => 75, 'max' => 79, 'grade' => 'C+', 'gpa' => 2.50],
        ['min' => 70, 'max' => 74, 'grade' => 'C',  'gpa' => 2.00],
        ['min' => 65, 'max' => 69, 'grade' => 'D+', 'gpa' => 1.50],
        ['min' => 60, 'max' => 64, 'grade' => 'D',  'gpa' => 1.00],
        ['min' => 0,  'max' => 59, 'grade' => 'F',  'gpa' => 0.00],
    ];

    // Fetch the assignment points.
    $assignment_points = (float) get_post_meta( $post->ID, 'points', true );

    // Retrieve the lesson type and lesson total points.
    $lesson_type = get_post_type( $lesson_id );
    $lesson_meta = get_post_meta( $lesson_id, '_' . $lesson_type, true );
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
            foreach ( $grading as $grade ) {
                if ( $normalized_points >= $grade['min'] && $normalized_points <= $grade['max'] ) {
                    $grade_display = 'Grade: <b>' . $grade['grade'] . '</b>';
                    break;
                }
            }
            break;

        case 'GPA':
            $grade_display = 'N/A'; // Default value.
            foreach ( $grading as $grade ) {
                if ( $normalized_points >= $grade['min'] && $normalized_points <= $grade['max'] ) {
                    $grade_display = 'GPA: <b>' . number_format( $grade['points'], 2 ) . '</b>';
                    break;
                }
            }
            break;

        case 'points':
        default:
            $grade_display = sprintf( __('<b>%d/%d</b> Points', 'learndash'), $assignment_points, $lesson_total_points );
            break;
    }

    // Render the fields
    ?>
    <input type="hidden" name="assignment_id" value="<?php echo $post->ID; ?>">
    <input type="hidden" name="assignment_meta_box_nonce" value="<?php echo wp_create_nonce('save_assignment_meta_box'); ?>">
    <input type="hidden" name="lesson_id" value="<?php echo $lesson_id; ?>">
    <?php if($approval_status == 1): ?>
    <!-- Assignment Status -->
    <div class="sfwd sfwd_options sfwd-assignment_settings">
		<div class="sfwd_input " id="sfwd-assignment_status">
			<span class="sfwd_option_label" style="text-align:right;vertical-align:top;">
                <a class="sfwd_help_text_link" style="cursor:pointer;" title="<?php esc_html_e( 'Click for Help!', 'learndash' ); ?>" onclick="toggleVisibility('sfwd-assignment_status_tip');">
                    <img src="<?php echo esc_url( LEARNDASH_LMS_PLUGIN_URL . '/assets/images/question.png' ); ?>" />
                    <label class="sfwd_label textinput"><?php esc_html_e( 'Status', 'learndash' ); ?></label>
                </a>
            </span>
			<span class="sfwd_option_input">
                <div class="sfwd_option_div">
				<?php
				$approval_status_flag = learndash_is_assignment_approved_by_meta( $post->ID );
				if ( 1 == $approval_status_flag ) {
					$approval_status_label = esc_html__( 'Approved', 'learndash' );
					echo '<p>' . esc_html( $approval_status_label ) . '</p>';
				} else {
					if ( ( learndash_get_setting( $lesson_id, 'lesson_assignment_points_enabled' ) === 'on' ) && ( intval( learndash_get_setting( $lesson_id, 'lesson_assignment_points_amount' ) ) > 0 ) ) {
						$approval_status_label = esc_html__( 'Not Approved', 'learndash' );
						echo '<p>' . esc_html( $approval_status_label ) . '</p>';
					} else {
                        $approve_text = esc_html__( 'Approve', 'learndash' );
                        echo '<p><input name="assignment-status" type="submit" class="button button-primary button-large" id="publish" value="' . esc_attr( $approve_text ) . '"></p>';
                    }
				}
				?>
				</div>
                <div class="sfwd_help_text_div" style="display:none" id="sfwd-assignment_status_tip">
                    <label class="sfwd_help_text"><?php esc_html_e( 'Assignment status.', 'learndash' ); ?></label>
                </div>
            </span>
            <p style="clear:left"></p>
        </div>
	</div>
    <?php endif; ?>
    <!-- Grading type -->
    <div class="sfwd sfwd_options sfwd-assignment_settings">
        <div class="sfwd_input" id="sfwd-grading_type">
            <span class="sfwd_option_label" style="text-align:right;vertical-align:top;">
                <a class="sfwd_help_text_link" style="cursor:pointer;" title="Click for Help!" onclick="toggleVisibility('sfwd-assignment_grading_type_tip');">
                    <img src="<?php echo get_site_url(); ?>/wp-content/plugins/sfwd-lms//assets/images/question.png" />
                    <label class="sfwd_label textinput">Grading Type</label>
                </a>
            </span>
            <span class="sfwd_option_input">
                <div class="sfwd_option_div">
                    <p>
                        <label for="assignment_grading_type">Grading Type:</label><br>
                        <select id="assignment_grading_type" name="assignment_grading_type">
                            <option value="AF" <?php selected($grading_type, 'AF', false); ?>>A-F</option>
                            <option value="GPA" <?php selected($grading_type, 'GPA', false); ?>>GPA</option>
                            <option value="points" <?php selected($grading_type, 'points', false); ?>>Points</option>
                        </select>
                    </p>
                </div>
                <div class="sfwd_help_text_div" style="display:none" id="sfwd-assignment_grading_type_tip">
                    <label class="sfwd_help_text"><?php esc_html_e( 'Grading type.', 'learndash' ); ?></label>
                </div>
            </span>
            <p style="clear:left"></p>
        </div>
    </div>
    <?php if($approval_status == 1): ?>
    <!-- Points -->
    <div class="sfwd sfwd_options sfwd-assignment_settings">
		<div class="sfwd_input " id="sfwd-assignment_points">
			<span class="sfwd_option_label" style="text-align:right;vertical-align:top;">
                <a class="sfwd_help_text_link" style="cursor:pointer;" title="<?php esc_html_e( 'Click for Help!', 'learndash' ); ?>" onclick="toggleVisibility('sfwd-assignment_points_tip');">
                    <img src="<?php echo esc_url( LEARNDASH_LMS_PLUGIN_URL . '/assets/images/question.png' ); ?>" />
                    <label class="sfwd_label textinput"><?php esc_html_e( 'Points', 'learndash' ); ?></label>
                </a>
            </span>
			<span class="sfwd_option_input">
                <div class="sfwd_option_div">
				<?php
				if ( ( ! empty( $course_id ) ) && ( ! empty( $lesson_id ) ) ) {
                    if ( ( learndash_get_setting( $lesson_id, 'lesson_assignment_points_enabled' ) === 'on' ) && ( intval( learndash_get_setting( $lesson_id, 'lesson_assignment_points_amount' ) ) > 0 ) ) {
                        $max_points     = intval( learndash_get_setting( $lesson_id, 'lesson_assignment_points_amount' ) );
                        $current_points = intval( get_post_meta( $post->ID, 'points', true ) );
                        $update_text    = learndash_is_assignment_approved_by_meta( $post->ID ) ? esc_html__( 'Update', 'learndash' ) : esc_html__( 'Update & Approve', 'learndash' );

                        echo '<p>';
                        echo "<label for='assignment-points'>" .
                        // translators: placeholder: max points.
                        sprintf( esc_html_x( 'Awarded Points (Out of %d):', 'placeholder: max points', 'learndash' ), esc_attr( $max_points ) ) . '</label><br />';
                        echo '<input name="assignment-points" type="number" min="0" max="' . absint( $max_points ) . '" value="' . absint( $current_points ) . '" /><br />';
                        echo '<p id="assignment-grade-display">'.$grade_display.'</p>';
                        echo '<p><label for="assignment_reopen"><input name="assignment_reopen" type="checkbox" id="assignment_reopen" value="1" '.checked($assignment_reopen, 1, false).'>' . __( ' Reopen assignment', 'learndash' ) . '</label></p>';
                        echo '<p><input name="save" type="submit" class="button button-primary button-large" id="publish" value="' . esc_attr( $update_text ) . '"></p>';
                        echo '</p>';
                    } else {
                        echo '<p>' . esc_html__( 'Points not enabled', 'learndash' ) . '</p>';
                    }
                }
				?>
				</div>
                <div class="sfwd_help_text_div" style="display:none" id="sfwd-assignment_points_tip">
                    <label class="sfwd_help_text"><?php esc_html_e( 'Assignment Points.', 'learndash' ); ?></label>
                </div>
            </span>
            <p style="clear:left"></p>
        </div>
	</div>
    <?php endif; ?>
    <!-- Manual Grade -->
    <div class="sfwd sfwd_options sfwd-assignment_settings">
        <div class="sfwd_input" id="sfwd-show_manual_grades">
            <span class="sfwd_option_label" style="text-align:right;vertical-align:top;">
                <a class="sfwd_help_text_link" style="cursor:pointer;" title="Click for Help!" onclick="toggleVisibility('sfwd-show_manual_grades_tip');">
                    <img src="<?php echo get_site_url(); ?>/wp-content/plugins/sfwd-lms//assets/images/question.png" />
                    <label class="sfwd_label textinput">Manual Grade</label>
                </a>
            </span>
            <span class="sfwd_option_input">
                <div class="sfwd_option_div">
                    <p>
                        <label for="show_manual_grades">Input Manual Grade With Grade Type:</label><br>
                        <input type="text" id="show_manual_grades" name="show_manual_grades" value="<?php esc_attr_e($show_manual_grades); ?>" placeholder="<?php esc_attr_e('Grade: A'); ?>" />
                    </p>
                </div>
                <div class="sfwd_help_text_div" style="display:none" id="sfwd-show_manual_grades_tip">
                    <label class="sfwd_help_text"><?php esc_html_e( 'Add manual grade with grade type e.g(Grade: A) to override default automated calculations.', 'learndash' ); ?></label>
                </div>
            </span>
            <p style="clear:left"></p>
        </div>
    </div>
    <!-- Automated Comments (Hidden if Manual Comments Disabled) -->
    <div class="sfwd sfwd_options sfwd-assignment_settings" style="<?php echo ($assignments_hide_history == 1) ? 'display:none;' : ''; ?>">
        <div class="sfwd_input" id="sfwd-assignment_comment">
            <span class="sfwd_option_label" style="text-align:right;vertical-align:top;">
                <a class="sfwd_help_text_link" style="cursor:pointer;" title="Click for Help!" onclick="toggleVisibility('sfwd-assignment_comment_tip');">
                    <img src="<?php echo get_site_url(); ?>/wp-content/plugins/sfwd-lms//assets/images/question.png" />
                    <label class="sfwd_label textinput">Comment</label>
                </a>
            </span>
            <span class="sfwd_option_input">
                <div class="sfwd_option_div">
                    <p>
                        <label for="assignment_comment">Comment Reason:</label><br>
                        <textarea id="assignment_comment" name="assignment_comment" rows="4" style="width:100%;"><?php echo esc_textarea($assignment_comment); ?></textarea>
                    </p>
                </div>
                <div class="sfwd_help_text_div" style="display:none" id="sfwd-assignment_comment_tip">
                    <label class="sfwd_help_text"><?php esc_html_e( 'Add reason to update or reopen this assignment.', 'learndash' ); ?></label>
                </div>
            </span>
            <p style="clear:left"></p>
        </div>
    </div>

    <?php
}

add_action( 'wp_ajax_LDAGS_assignment_grade', 'LDAGS_assignment_grade' );
function LDAGS_assignment_grade()
{
    // Ensure necessary data is received
    if (!isset($_POST['lesson_id'], $_POST['nonce'])) {
        wp_send_json_error(['message' => __('Invalid request.', 'learndash')]);
    }
    $lesson_id          = intval($_POST['lesson_id']);
    $nonce              = sanitize_text_field($_POST['nonce']);
    $grading_type       = sanitize_text_field( $_POST['grading_type'] );
    $assignment_points  = intval($_POST['assignment_points']);

    // Verify nonce
    if (!wp_verify_nonce($nonce, 'save_assignment_meta_box')) {
        wp_send_json_error(['message' => __('Security check failed.', 'learndash')]);
    }

    // Check if user has permission to reopen
    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => __('You must be logged in.', 'learndash')]);
    }

    $grading = [
        ['min' => 95, 'max' => 100, 'grade' => 'A+', 'gpa' => 4.00],
        ['min' => 90, 'max' => 94, 'grade' => 'A',  'gpa' => 3.75],
        ['min' => 85, 'max' => 89, 'grade' => 'B+', 'gpa' => 3.50],
        ['min' => 80, 'max' => 84, 'grade' => 'B',  'gpa' => 3.00],
        ['min' => 75, 'max' => 79, 'grade' => 'C+', 'gpa' => 2.50],
        ['min' => 70, 'max' => 74, 'grade' => 'C',  'gpa' => 2.00],
        ['min' => 65, 'max' => 69, 'grade' => 'D+', 'gpa' => 1.50],
        ['min' => 60, 'max' => 64, 'grade' => 'D',  'gpa' => 1.00],
        ['min' => 0,  'max' => 59, 'grade' => 'F',  'gpa' => 0.00],
    ];
    // Retrieve the lesson type and lesson total points.
    $lesson_type = get_post_type( $lesson_id );
    $lesson_meta = get_post_meta( $lesson_id, '_' . $lesson_type, true );
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
            foreach ( $grading as $grade ) {
                if ( $normalized_points >= $grade['min'] && $normalized_points <= $grade['max'] ) {
                    $grade_display = 'Grade: <b>' . $grade['grade'] . '</b>';
                    break;
                }
            }
            break;

        case 'GPA':
            $grade_display = 'N/A'; // Default value.
            foreach ( $grading as $grade ) {
                if ( $normalized_points >= $grade['min'] && $normalized_points <= $grade['max'] ) {
                    $grade_display = 'GPA: <b>' . number_format( $grade['gpa'], 2 ) . '</b>';
                    break;
                }
            }
            break;

        case 'points':
        default:
            $grade_display = sprintf( __('<b>%d/%d</b> Points', 'learndash'), $assignment_points, $lesson_total_points );
            break;
    }
    
    wp_send_json_success(['grade' => $grade_display]);
}

function save_assignment_meta_box( $post_id ) {
    // Check nonce for security
    if ( ! isset( $_POST['assignment_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['assignment_meta_box_nonce'], 'save_assignment_meta_box' ) ) {
        return;
    }

    // Prevent auto-saves from overwriting data
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }

    // Check user permissions
    if ( 'sfwd-assignment' !== $_POST['post_type'] || ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }

    // Save Grading type
    if ( isset( $_POST['assignment_grading_type'] ) ) {
        update_post_meta( $post_id, 'assignment_grading_type', sanitize_text_field( $_POST['assignment_grading_type'] ) );
    }

    // Save Disable Manual Comments
    $show_manual_grades = isset( $_POST['show_manual_grades'] ) ? 1 : 0;
    update_post_meta( $post_id, 'show_manual_grades', $show_manual_grades );

    // Save Automated Comments
    if ( isset( $_POST['automated_comments'] ) ) {
        update_post_meta( $post_id, 'automated_comments', sanitize_textarea_field( $_POST['automated_comments'] ) );
    }
}
add_action( 'save_post', 'save_assignment_meta_box' );

// Enqueue admin JavaScript for LearnDash settings
function ldags_enqueue_admin_metabox_scripts($hook) {
    global $post;
    if ($post && get_post_type($post) === 'sfwd-assignment') {
        wp_enqueue_script(
            'ldags-metabox-script',
            LD_ASSIGNMENT_GRADING_URL . 'assets/js/metaboxe.js',
            array('jquery'),
            '1.0',
            true
        );
    }
}
add_action('admin_enqueue_scripts', 'ldags_enqueue_admin_metabox_scripts');