<?php
    use ChadwickMarketing\SocialLite\base\data\BioLinkData;
?>
<!DOCTYPE html>
<html>
<?php
    include_once SOCIAL_LITE_INC . 'view/bio/BioLinkHead.php';
?>
<body>
    <div id="<?php echo SOCIAL_LITE_SLUG . '-root'; ?>"
        data-bio-link-id="<?php echo esc_attr(is_home() || is_front_page() ? BioLinkData::instance()->getHomepageBioLink()['id'] : get_the_ID()); ?>">
    </div>
    <?php wp_footer(); ?>
</body>
</html>
