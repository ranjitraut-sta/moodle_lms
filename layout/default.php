<?php
defined('MOODLE_INTERNAL') || die();

/**
 * Clean mytheme default layout (Boost compatible)
 * Ensures filemanager, editors, and all mod pages work
 */

// Get base context
$templatecontext = theme_mytheme_get_base_context();

// Blocks and region menu
$hasblocks = $PAGE->blocks->region_has_content('side-pre', $OUTPUT);
$regionmainsettingsmenu = $OUTPUT->region_main_settings_menu();
$hasregionmainsettingsmenu = !empty($regionmainsettingsmenu);

// Output starts
echo $OUTPUT->doctype();
?>
<html <?php echo $OUTPUT->htmlattributes(); ?>>
<head>
    <title><?php echo $OUTPUT->page_title(); ?></title>
    <link rel="shortcut icon" href="<?php echo $OUTPUT->favicon(); ?>" />
    <?php echo $OUTPUT->standard_head_html(); ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?php echo new moodle_url('/theme/mytheme/styles/main.css'); ?>">
</head>
<body <?php echo $OUTPUT->body_attributes(); ?>>
<?php echo $OUTPUT->standard_top_of_body_html(); ?>

<?php echo $OUTPUT->render_from_template('theme_mytheme/header', $templatecontext); ?>

<div id="page-wrapper">
    <div id="page" class="container-fluid">
        <div id="page-content" class="row">
            <div id="region-main-box" class="col-12">
                
                <?php if ($hasblocks) { ?>
                    <div data-region="drawer-toggle" class="d-print-none drawer drawer-left-toggle">
                        <button class="btn icon-no-margin" aria-expanded="true" data-action="toggle-drawer" data-side="left" data-preference="drawer-open-block" title="<?php echo get_string('sidepanel'); ?>">
                            <span class="dir-rtl-hide"><?php echo $OUTPUT->pix_icon('t/leftpanel', get_string('sidepanel')); ?></span>
                            <span class="dir-ltr-hide"><?php echo $OUTPUT->pix_icon('t/rightpanel', get_string('sidepanel')); ?></span>
                            <span class="sr-only"><?php echo get_string('sidepanel'); ?></span>
                        </button>
                    </div>
                <?php } ?>

                <section id="region-main" class="container" aria-label="<?php echo get_string('content'); ?>">
                    <?php echo $OUTPUT->full_header(); ?>
                    <?php echo $OUTPUT->course_content_header(); ?>
                    
                    <?php if ($hasregionmainsettingsmenu) { ?>
                        <div class="region_main_settings_menu_proxy d-print-none">
                            <?php echo $regionmainsettingsmenu; ?>
                        </div>
                    <?php } ?>

                    <?php echo $OUTPUT->main_content(); ?>

                    <?php echo $OUTPUT->course_content_footer(); ?>
                </section>

                <?php echo $OUTPUT->standard_after_main_region_html(); ?>
            </div>

            <?php if ($hasblocks) { ?>
                <div id="theme_boost-drawers-blocks" class="block-region drawer drawer-left d-print-none" data-region="fixed-drawer" data-drawer="open" data-side="left" data-preference="drawer-open-block" aria-hidden="false">
                    <?php echo $OUTPUT->blocks('side-pre'); ?>
                </div>
            <?php } ?>
        </div>
    </div>
</div>

<?php echo $OUTPUT->render_from_template('theme_mytheme/footer', $templatecontext['footer']); ?>

<?php echo $OUTPUT->standard_end_of_body_html(); ?>
</body>
</html>