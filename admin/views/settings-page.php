<div class="wrap">
    <h1><?php esc_html_e('LearnDash Assignment Grading Settings', 'learndash-assignment-grading'); ?></h1>
    <form method="post" action="options.php">
        <?php
        // Output security fields
        settings_fields('ld_assignment_grading_options_group');

        // Output setting sections
        do_settings_sections('ld-assignment-grading');

        // Submit button
        submit_button();
        ?>
    </form>
</div>