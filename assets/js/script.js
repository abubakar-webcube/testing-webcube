document.addEventListener("DOMContentLoaded", function () {
    let assignmentUploadCheckbox = document.querySelector('[name="lesson_assignment_upload"]');
    let gradingScaleRow = document.querySelector('[name="custom_grading_scale"]').closest('.ld-setting-field');
    let disableManualCommentsRow = document.querySelector('[name="disable_manual_comments"]').closest('.ld-setting-field');

    function toggleCustomFields() {
        let display = assignmentUploadCheckbox.checked ? "block" : "none";
        gradingScaleRow.style.display = display;
        disableManualCommentsRow.style.display = display;
    }

    if (assignmentUploadCheckbox) {
        assignmentUploadCheckbox.addEventListener("change", toggleCustomFields);
        toggleCustomFields(); // Set initial state
    }
});
