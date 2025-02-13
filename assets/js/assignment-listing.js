jQuery(document).ready(function ($) {
    function handleAssignmentAction(button, action) {
        var $this = $(button),
            assignmentId = $this.data('assignment-id'),
            lessonId = $this.data('lesson-id'),
            nonce = $this.data('nonce'),
            $row = $this.closest('tr'); // Get the row

        // Prevent multiple clicks
        if ($this.prop('disabled')) return;
        $this.prop('disabled', true);

        // Show WordPress spinner
        var $spinner = $('<span class="spinner is-active"></span>');
        $this.after($spinner);

        var data = {
            action: action,
            assignment_id: assignmentId,
            lesson_id: lessonId,
            nonce: nonce
        };

        $.post(ldags_assignment_ajax.ajax_url, data, function (response) {
            // Check if any existing notice is present
            var lastNotice = $('.wrap>.notice').last();
            var noticeType = response.success ? 'updated' : 'error'; // Use valid WordPress classes
            var noticeHtml = '<div class="notice ' + noticeType + ' is-dismissible"><p>' + response.data.message + '</p></div>';
            if (lastNotice.length) {
                // Append after the last existing notice
                // lastNotice.after(noticeHtml);
                lastNotice.after($(noticeHtml));
            } else {
                // No notice found, insert after <h1> or <hr> inside .wrap
                var target = $('.wrap h1, .wrap hr').first();
                if (target.length) {
                    target.after(noticeHtml);
                } else {
                    // If no <h1> or <hr> exists, prepend inside .wrap
                    $('.wrap').prepend(noticeHtml);
                }
            }
            setTimeout(function () {
                window.location.reload();
            }, 20000);
        }).always(function () {
            $this.prop('disabled', false); // Re-enable button
            $spinner.remove(); // Remove WordPress spinner
        });
    }

    $('.reopen_assignment_btn').on('click', function (e) {
        e.preventDefault();
        handleAssignmentAction(this, 'ldags_reopen_assignment');
    });

    $('.reject_assignment_btn').on('click', function (e) {
        e.preventDefault();
        handleAssignmentAction(this, 'ldags_reject_assignment');
    });

    // Approve Assignment Button
    $('.approve_assignment_btn').on('click', function (e) {
        e.preventDefault();
        handleAssignmentAction(this, 'ldags_approve_assignment'); // Call the approve action
    });
});

// jQuery(document).ready(function ($) {
//     $('.reopen_assignment_btn').on('click', function (e) {
//         e.preventDefault();
//         var $this = $(this),
//             assignmentId = $this.data('assignment-id'),
//             lessonId = $this.data('lesson-id'),
//             nonce = $this.data('nonce');
//         var data = {
//             action: 'ldags_reopen_assignment',
//             assignment_id: assignmentId,
//             lesson_id: lessonId,
//             nonce: nonce
//         };
//         $.post(ldags_assignment_ajax.ajax_url, data, function (response) {
//             if (response.success) {
//                 alert(response.data.message);
//                 location.reload();
//             } else {
//                 alert(response.data.message);
//             }
//         });
//     });
//     $('.reject_assignment_btn').on('click', function (e) {
//         e.preventDefault();
//         var $this = $(this),
//             assignmentId = $this.data('assignment-id'),
//             lessonId = $this.data('lesson-id'),
//             nonce = $this.data('nonce');
//         var data = {
//             action: 'ldags_reject_assignment',
//             assignment_id: assignmentId,
//             lesson_id: lessonId,
//             nonce: nonce
//         };
//         $.post(ldags_assignment_ajax.ajax_url, data, function (response) {
//             if (response.success) {
//                 alert(response.data.message);
//                 location.reload();
//             } else {
//                 alert(response.data.message);
//             }
//         });
//     });
// });
