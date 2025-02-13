jQuery(document).ready(function ($) {
    function toggleAutomatedComments() {
        if ($('[name="disable_manual_comments"]').is(':checked')) {
            $('[name="automated_comments"]').closest('.sfwd-assignment_settings').hide();
        } else {
            $('[name="automated_comments"]').closest('.sfwd-assignment_settings').show();
        }
    }
    // Run on page load
    toggleAutomatedComments();
    jQuery(jQuery('.sfwd-assignment_settings #sfwd-assignment_points')[1]).hide();
    jQuery(jQuery('.sfwd-assignment_settings #sfwd-assignment_status')[1]).hide();
    // Bind change event
    $('[name="disable_manual_comments"]').on('change', toggleAutomatedComments);

    function updateAssignmentGrade() {
        let lessonId = $('[name="lesson_id"]').val();
        let points = $('[name="assignment-points"]').val();
        let gradingType = $('[name="assignment_grading_type"]').val();
        let nonce = $('[name="assignment_meta_box_nonce"]').val();

        if (!lessonId || !nonce) {
            console.error("Missing required data.");
            return;
        }

        $.ajax({
            url: ajaxurl, // WordPress AJAX handler
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'LDAGS_assignment_grade',
                lesson_id: lessonId,
                assignment_points: points,
                grading_type: gradingType,
                nonce: nonce
            },
            beforeSend: function () {
                $('#assignment-grade-display').html('Calculating...');
            },
            success: function (response) {
                if (response.success) {
                    $('#assignment-grade-display').html(response.data.grade);
                } else {
                    $('#assignment-grade-display').html('<span style="color: red;">Error: ' + response.data.message + '</span>');
                }
            },
            error: function () {
                $('#assignment-grade-display').html('<span style="color: red;">Failed to fetch grade.</span>');
            }
        });
    }
    $('[name="assignment-points"], [name="assignment_grading_type"]').on('change', function(){
        updateAssignmentGrade();
    });
});
