<?php
if (!defined('__TYPECHO_ADMIN__')) {
    exit;
}

$header = '
<!-- CSS Reset & Grid -->
<link rel="stylesheet" href="' . $options->adminUrl('css/normalize.css',true) . '">
<link rel="stylesheet" href="' . $options->adminUrl('css/grid.css',true) . '">
<!-- Theme Variables -->
<link rel="stylesheet" href="' . $options->adminUrl('css/style.css?v=1.3.1-rc2',true) . '">
<link rel="stylesheet" href="' . $options->adminUrl('css/light.css?v=1.3.1-rc2',true) . '">
<link rel="stylesheet" href="' . $options->adminUrl('css/dark.css?v=1.3.1-rc2',true) . '">
<!-- TailwindCSS -->
<link rel="stylesheet" href="' . $options->adminUrl('css/tailwind.css?v=1.3.1-rc2',true) . '">
<!-- NProgress -->
<script src="' . $options->adminUrl('js/nprogress.js',true) . '"></script>
<link rel="stylesheet" href="' . $options->adminUrl('css/nprogress.css',true) . '">
<!-- Font Awesome -->
<link href="https://cdn.garfieldtom.cool/resource/libs/fontawesome/7.2.0/css/all.min.css" rel="stylesheet">
<!-- ECharts -->
<script src="https://cdn.garfieldtom.cool/resource/libs/echarts/5.5.0/echarts.min.js"></script>
';

/** 注册一个初始化插件 */
$header = \Typecho\Plugin::factory('admin/header.php')->filter('header', $header);

?><!DOCTYPE HTML>
<html>
    <head>
        <meta charset="<?php $options->charset(); ?>">
        <meta name="renderer" content="webkit">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
        <title><?php _e('%s - %s', $menu->title, $options->title); ?></title>
        <meta name="robots" content="noindex, nofollow">
        <?php echo $header; ?>
    </head>
    <body class="<?php echo isset($bodyClass) ? $bodyClass : ''; ?>">
        <!-- NProgress Loading Indicator -->
        <div id="nprogress">
            <div class="bar" role="bar">
                <div class="peg"></div>
            </div>
            <div class="spinner">
                <div class="spinner-icon"></div>
                <span class="spinner-text"><?php _e('正在加载'); ?></span>
            </div>
        </div>
