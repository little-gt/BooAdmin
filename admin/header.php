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
<link rel="stylesheet" href="' . $options->adminUrl('css/tailwind.css' . $assetVersion, true) . '">';

/**
 * neo 兼容层：仅扩展页加载。
 * 部分扩展插件沿用 Typecho 原生后台 DOM（.typecho-* / .btn / .primary / .neo-*），
 * 与 BooAdmin 组件层不兼容；neo.css 为这类页面兜底。
 * 放在 utilities 之后以保证其组件规则能覆盖通用工具类，
 * 但绝不加载到普通后台页，避免全局规则污染。
 */
if ($booadminCompatNeo) {
    $header .= '<link rel="stylesheet" href="' . $options->adminUrl('css/neo.css' . $assetVersion, true) . '">';
}

$header .= '
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
    <body class="<?php echo trim((isset($bodyClass) ? $bodyClass : '') . ($booadminCompatNeo ? ' neo-admin' : '')); ?>">
        <script>
        /**
         * 操作类链接守卫
         *
         * 在捕获阶段拦截「脚本尚未接管」的操作链接，避免直接跳到 /action/ 裸接口。
         * 各页面脚本绑定完事件后调用 window.booadminArmLinks() 解除保护。
         *
         * 选择器只在这里定义一次，并导出为 window.booadminGuardSelector；
         * 各页不要再硬编码选择器字符串，避免命名漂移与死选择器堆积。
         *
         * 说明：.edit-draft-notice a 由 Typecho 核心的撰写 Widget 动态输出
         * （静态模板里搜不到，CSS 见 style.css / dark.css），故予以保留。
         */
        (function () {
            var GUARD_SELECTORS = [
                'a.operate-delete',
                'a.operate-approved',
                'a.operate-spam',
                'a.js-tag-action',
                'a.js-category-action',
                '.dropdown-menu a',
                '.edit-draft-notice a'
            ];
            var GUARD_SELECTOR = GUARD_SELECTORS.join(', ');
            var ARMED_ATTR = 'data-booadmin-armed';

            /** @param {string|Element|NodeList|jQuery} [target] 省略时解除全部守卫链接 */
            function armLinks(target) {
                var list;

                if (target === undefined || target === null || target === '') {
                    list = document.querySelectorAll(GUARD_SELECTOR);
                } else if (typeof target === 'string') {
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
            window.booadminGuardSelector = GUARD_SELECTOR;

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
                // 不调用 NProgress.start()：此时页面不会跳转，start 后没有配对的
                // done()，会让进度条/spinner 永久卡住。仅静默吞掉这次过早的点击。
            }, true);

            // 安全网：页面完全加载后仍未接管的链接一律放行，避免脚本异常时操作永久失效
            window.addEventListener('load', function () {
                armLinks();
            });
        })();
        </script>