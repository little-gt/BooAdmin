<?php
if (!defined('__TYPECHO_ADMIN__')) {
    exit;
}

/**
 * 样式层叠顺序是确定性的，不要调整：
 *   reset(preflight) → 组件层(style/light/dark) → utilities(tailwind.css)
 * preflight 必须在组件层之前，否则会覆盖 body/a/h1-h6 等基础规则；
 * utilities 必须最后，以保证工具类稳定覆盖组件类。
 * 两个 Tailwind 产物由 config/v3/input-base.css 与 input.css 分别构建。
 */
$assetVersion = '?v=' . BOOADMIN_VERSION;
$cdn = 'https://cdn.garfieldtom.cool/resource/libs';

$header = '
<!-- Web Fonts: preconnect + 非阻塞加载，失败时自动回退到系统字体栈 -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Cascadia+Code:ital,wght@0,200..700;1,200..700&family=Noto+Sans+SC:wght@100..900&display=swap" media="print" onload="this.media=\'all\'" crossorigin="anonymous" referrerpolicy="no-referrer">
<!-- CSS Reset & Grid -->
<link rel="stylesheet" href="' . $options->adminUrl('css/normalize.css' . $assetVersion, true) . '">
<!-- Tailwind preflight (base only, must precede component styles) -->
<link rel="stylesheet" href="' . $options->adminUrl('css/tailwind-base.css' . $assetVersion, true) . '">
<link rel="stylesheet" href="' . $options->adminUrl('css/grid.css' . $assetVersion, true) . '">
<!-- Theme Variables & Components -->
<link rel="stylesheet" href="' . $options->adminUrl('css/style.css' . $assetVersion, true) . '">
<link rel="stylesheet" href="' . $options->adminUrl('css/light.css' . $assetVersion, true) . '">
<link rel="stylesheet" href="' . $options->adminUrl('css/dark.css' . $assetVersion, true) . '">
<!-- Tailwind utilities (must load last; palette is fully variable-driven) -->
<link rel="stylesheet" href="' . $options->adminUrl('css/tailwind.css' . $assetVersion, true) . '">
<!-- NProgress -->
<script src="' . $options->adminUrl('js/nprogress.js' . $assetVersion, true) . '"></script>
<link rel="stylesheet" href="' . $options->adminUrl('css/nprogress.css' . $assetVersion, true) . '">
<!-- Font Awesome -->
<link href="' . $cdn . '/fontawesome/7.2.0/css/all.min.css" rel="stylesheet">
<!-- ECharts -->
<script defer src="' . $cdn . '/echarts/5.5.0/echarts.min.js"></script>
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
        <script>
        /* 操作类链接（AJAX / 确认弹窗）在页面脚本绑定事件之前被点击时，会直接跳转到 /action/ 接口地址。这里在捕获阶段拦截这些链接的原生跳转，直到对应脚本调用 window.booadminArmLinks() 完成接管。window load 后统一兜底解除保护，避免脚本异常时操作永久失效。 */
        (function () {
            var GUARD_SELECTOR = [
                'a.operate-delete',
                'a.operate-approved',
                'a.operate-waiting',
                'a.operate-spam',
                'a.js-tag-action',
                'a.js-category-action',
                '.booadmin-dropdown-menu a',
                '.edit-draft-notice a'
            ].join(', ');
            var ARMED_ATTR = 'data-booadmin-armed';

            function armLinks(target) {
                var list = null;

                if (!target) {
                    return;
                }

                if (typeof target === 'string') {
                    list = document.querySelectorAll(target);
                } else if (target.nodeType === 1) {
                    list = [target];
                } else if (target.jquery) {
                    list = target.toArray();
                } else if (typeof target.length === 'number') {
                    list = target;
                } else {
                    return;
                }

                for (var i = 0; i < list.length; i++) {
                    if (list[i] && list[i].setAttribute) {
                        list[i].setAttribute(ARMED_ATTR, '1');
                    }
                }
            }

            window.booadminArmLinks = armLinks;

            document.addEventListener('click', function (e) {
                var el = e.target;
                var link;

                if (!el || el.nodeType !== 1 || !el.closest) {
                    return;
                }

                link = el.closest('a');

                if (!link || link.getAttribute(ARMED_ATTR) === '1' || !link.matches(GUARD_SELECTOR)) {
                    return;
                }

                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();

                // 给出「仍在加载」的反馈，避免用户以为点击无效
                if (window.NProgress && typeof window.NProgress.start === 'function') {
                    window.NProgress.start();
                }
            }, true);

            // 兜底：页面完全加载后解除保护
            window.addEventListener('load', function () {
                armLinks(GUARD_SELECTOR);
            });
        })();
        </script>
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
