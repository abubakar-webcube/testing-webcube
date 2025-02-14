jQuery(document).ready(function ($) {
    // Admin JS functionality
    console.log('LearnDash Assignment Grading admin script loaded.');

    // Example: Handle form submissions
    $('#ld-assignment-grading-form').on('submit', function (e) {
        e.preventDefault();
        alert('Form submitted!');
    });
});