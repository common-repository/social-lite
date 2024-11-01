<?php 
    use ChadwickMarketing\SocialLite\base\data\BioLinkData;
    use ChadwickMarketing\SocialLite\base\seo\BioLinkSEO;
    $is_seo_plugin_active = BioLinkSEO::instance()->isSEOPluginActive();

    $bio_link_data = (is_home() || is_front_page()) ? BioLinkData::instance()->getHomepageBioLink() : BioLinkData::instance()->getBioLinkDataById(get_the_ID());
?>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<?php if (!$is_seo_plugin_active) : ?>
<?php if (isset($bio_link_data['data']['meta']['title']) && !empty($bio_link_data['data']['meta']['title'])) : ?>
    <title><?php esc_attr_e($bio_link_data['data']['meta']['title']); ?></title>
    <meta property="og:title" content="<?php esc_attr_e($bio_link_data['data']['meta']['title']); ?>">
    <meta name="twitter:title" content="<?php esc_attr_e($bio_link_data['data']['meta']['title']); ?>">
<?php endif; ?>
<?php if (isset($bio_link_data['data']['meta']['description']) && !empty($bio_link_data['data']['meta']['description'])) : ?>
    <meta name="description" content="<?php esc_attr_e($bio_link_data['data']['meta']['description']); ?>">
    <meta property="og:description" content="<?php esc_attr_e($bio_link_data['data']['meta']['description']); ?>">
    <meta name="twitter:description" content="<?php esc_attr_e($bio_link_data['data']['meta']['description']); ?>">
<?php endif; ?>
<?php if (isset($bio_link_data['data']['meta']['image']) && !empty($bio_link_data['data']['meta']['image'])) : ?>
    <meta property="og:image" content="<?php esc_attr_e($bio_link_data['data']['meta']['image']); ?>">
    <meta name="twitter:image" content="<?php esc_attr_e($bio_link_data['data']['meta']['image']); ?>">
<?php endif; ?>
<?php endif; ?>
    <?php wp_head(); ?>
</head>

   