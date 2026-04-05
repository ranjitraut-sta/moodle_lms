<?php
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/theme/mytheme/lib.php');

$templatecontext = array_merge(
    theme_mytheme_get_frontpage_context(),
    theme_mytheme_get_base_context()
);

echo $OUTPUT->doctype();
?>
<html <?php echo $OUTPUT->htmlattributes(); ?>>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $PAGE->title; ?></title>

    <?php echo $OUTPUT->standard_head_html(); ?>

    <link href="<?php echo $CFG->wwwroot; ?>/theme/mytheme/styles/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@2.5.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500&family=Playfair+Display:ital,wght@0,400..900;1,400..900&display=swap" rel="stylesheet">
    <link href="<?php echo $CFG->wwwroot; ?>/theme/mytheme/styles/main.css" rel="stylesheet">
</head>

<body <?php echo $OUTPUT->body_attributes(); ?>>

<?php echo $OUTPUT->standard_top_of_body_html(); ?>

<!-- Header -->
<?php echo $OUTPUT->render_from_template('theme_mytheme/header', $templatecontext); ?>

<!-- Main Content -->
<?php echo $OUTPUT->render_from_template('theme_mytheme/frontpage_content', $templatecontext); ?>

<!-- Moodle Main Content (Required) -->
<div style="display:none;">
    <?php echo $OUTPUT->main_content(); ?>
</div>

<!-- Footer -->
<?php echo $OUTPUT->render_from_template('theme_mytheme/footer', $templatecontext); ?>

<?php echo $OUTPUT->standard_end_of_body_html(); ?>

<!-- Landing JS -->
<script defer src="<?php echo $CFG->wwwroot; ?>/theme/mytheme/amd/src/landing.js"></script>

</body>
</html>