<?php
defined('MOODLE_INTERNAL') || die();

echo $OUTPUT->doctype();
?>
<html <?php echo $OUTPUT->htmlattributes(); ?>>
<head>
    <title><?php echo $OUTPUT->page_title(); ?></title>
    <?php echo $OUTPUT->standard_head_html(); ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body <?php echo $OUTPUT->body_attributes(); ?>>
<?php echo $OUTPUT->standard_top_of_body_html(); ?>

<div id="page" class="container-fluid">
    <div id="page-content">
        <div id="region-main-box">
            <div id="region-post-box">
                <div id="region-main-wrap">
                    <div id="region-main">
                        <?php echo $OUTPUT->main_content(); ?>
                    </div>
                </div>
                <div id="region-pre" class="block-region" data-blockregion="side-pre" data-droptarget="1">
                    <?php echo $OUTPUT->blocks('side-pre'); ?>
                </div>
                <div id="region-post" class="block-region" data-blockregion="side-post" data-droptarget="1">
                    <?php echo $OUTPUT->blocks('side-post'); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php echo $OUTPUT->standard_end_of_body_html(); ?>
</body>
</html>
