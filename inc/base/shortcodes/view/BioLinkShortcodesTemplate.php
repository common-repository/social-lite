<?php
    use ChadwickMarketing\SocialLite\base\shortcodes\BioLinkShortcodes;
?>
<!DOCTYPE html>
<html style="margin-top: 0px!important;">
<head>
    <?php wp_head(); ?>
</head>
<body>
    <div id="<?php echo SOCIAL_LITE_SLUG . "-shortcode-root" ?>">
        <?php echo BioLinkShortcodes::instance()->executeShortcode(sanitize_text_field($_GET['shortcode']), is_home() || is_front_page() ? BioLinkData::instance()->getHomepageBioLink()['id'] : get_the_ID()); ?>
    </div>
    <?php wp_footer(); ?>
</body>
</html>