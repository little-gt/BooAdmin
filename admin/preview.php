<?php

include 'common.php';

/** 获取内容 Widget */
\Widget\Archive::alloc('type=single&checkPermalink=0&preview=1')->to($content);

/** 检测是否存在 */
if (!$content->have()) {
    $response->redirect($options->adminUrl);
}

/** 检测权限 */
if (!$user->pass('editor', true) && $content->authorId != $user->uid) {
    $response->redirect($options->adminUrl);
}

/** 预览模式：native(主题原样渲染) / print(打印预览) */
$previewMode = isset($_GET['preview_mode']) ? $_GET['preview_mode'] : 'native';
if (!in_array($previewMode, ['native', 'print'])) {
    $previewMode = 'native';
}

/**
 * 预览工具栏（原生 / 打印模式切换、打印）已统一由父页面 write-js.php 的
 * 预览弹层提供。本页只负责内容渲染，不再注入任何浮动工具栏，
 * 从根源上避免与前台主题样式 / CSP 相互干扰。
 */
if ($previewMode === 'print') {
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php $content->title(); ?> - <?php _e('预览'); ?></title>
    <link rel="stylesheet" href="https://cdn.garfieldtom.cool/resource/libs/KaTeX/0.16.38/katex.min.css">
    <script defer src="https://cdn.garfieldtom.cool/resource/libs/KaTeX/0.16.38/katex.min.js"></script>
    <script defer src="https://cdn.garfieldtom.cool/resource/libs/KaTeX/0.16.38/contrib/auto-render.min.js"></script>
    <!-- 字体改为非阻塞加载；系统字体栈已内建回退 -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Noto+Sans+SC:wght@400;500;700;800&family=JetBrains+Mono:wght@400;500;700&display=swap" media="print" onload="this.media='all'" crossorigin="anonymous" referrerpolicy="no-referrer">
    <style>

        /* ========================================
         * 色板 - 亮色模式 (默认)
         * ======================================== */
        :root {
            color-scheme: light;
            --pv-bg: #f4f6fa;
            --pv-panel: #ffffff;
            --pv-panel-soft: #f8fafc;
            --pv-panel-muted: #f1f4f8;
            --pv-border: #d7dee8;
            --pv-border-strong: #bcc8d6;
            --pv-text: #223042;
            --pv-muted: #607086;
            --pv-heading: #142033;
            --pv-accent: #4e73df;
            --pv-accent-soft: #e9f0ff;
            --pv-selection: rgba(78, 115, 223, 0.18);
            --pv-code-bg: #eef2f7;
            --pv-code-text: #243447;
            --pv-pre-bg: #f3f6fb;
            --pv-pre-border: #d9e1ec;
            --pv-pre-text: #223042;
            --pv-quote-bg: #f5f8fc;
            --pv-quote-text: #415166;
            --pv-quote-border: #9eb4d6;
            --pv-table-head-bg: #eef3f8;
            --pv-table-stripe: #f8fafd;
            --pv-table-hover: #eef4ff;
            --pv-table-border: #d7dee8;
            --pv-mark-bg: #fff0b8;
            --pv-hr: #dbe3ee;
            --pv-kbd-bg: #ffffff;
            --pv-kbd-text: #27384f;
        }

        /* ========================================
         * 色板 - 暗色模式 (BooAdmin 纯黑中性)
         * ======================================== */
        @media (prefers-color-scheme: dark) {
            :root {
                color-scheme: dark;
                --pv-bg: #000000;
                --pv-panel: #0d0d0d;
                --pv-panel-soft: #141414;
                --pv-panel-muted: #0a0a0a;
                --pv-border: #222222;
                --pv-border-strong: #333333;
                --pv-text: #e4e4e7;
                --pv-muted: #8a8a99;
                --pv-heading: #f2f6fb;
                --pv-accent: #6366c7;
                --pv-accent-soft: #18182e;
                --pv-selection: rgba(99, 102, 199, 0.18);
                --pv-code-bg: #18181b;
                --pv-code-text: #e4e4e7;
                --pv-pre-bg: #0a0a0a;
                --pv-pre-border: #222222;
                --pv-pre-text: #e4e4e7;
                --pv-quote-bg: #111115;
                --pv-quote-text: #b7c3d2;
                --pv-quote-border: #444455;
                --pv-table-head-bg: #111111;
                --pv-table-stripe: #0a0a0a;
                --pv-table-hover: #151515;
                --pv-table-border: #2a2a2a;
                --pv-mark-bg: #5f5020;
                --pv-hr: #222222;
                --pv-kbd-bg: #1a1a1d;
                --pv-kbd-text: #e4e4e7;
            }
        }

        /* ========================================
         * 基础重置 & 排版 (矩形化设计 border-radius: 0)
         * ======================================== */
        * { box-sizing: border-box; }
        html { font-size: 16px; scroll-behavior: smooth; }
        body {
            margin: 0;
            font-family: "Inter", "Noto Sans SC", -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            color: var(--pv-text);
            background: var(--pv-bg);
            line-height: 1.8;
            text-rendering: optimizeLegibility;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            transition: background-color 0.3s ease, color 0.3s ease;
        }
        ::selection { background: var(--pv-selection); }

        a { color: var(--pv-accent); text-decoration: none; transition: color 0.18s ease; }
        a:hover { text-decoration: underline; text-underline-offset: 0.18em; }
        img, svg, video, canvas, iframe { max-width: 100%; }
        img { display: block; height: auto; border: 1px solid var(--pv-border); background: var(--pv-panel-soft); }
        hr { margin: 2rem 0; border: 0; border-top: 1px solid var(--pv-hr); }

        .preview-shell {
            width: min(980px, calc(100vw - 32px));
            margin: 0 auto;
            padding: 28px 0 40px;
            transition: padding 0.3s ease, max-width 0.3s ease;
        }
        .preview-header,
        .preview-article {
            background: var(--pv-panel);
            border: 1px solid var(--pv-border);
        }
        .preview-header { padding: 24px 28px; margin-bottom: 16px; }

        .preview-kicker {
            display: inline-flex;
            align-items: center; gap: 8px; min-height: 28px;
            padding: 0 10px; margin-bottom: 16px;
            border: 1px solid var(--pv-border);
            background: var(--pv-panel-soft);
            color: var(--pv-muted);
            font-size: 12px; font-weight: 700;
            letter-spacing: 0.08em; text-transform: uppercase;
        }
        .post-title { margin: 0; font-size: clamp(2rem, 3.4vw, 2.9rem); line-height: 1.18; color: var(--pv-heading); letter-spacing: -0.025em; word-break: break-word; }
        .post-meta { display: flex; flex-wrap: wrap; gap: 10px 14px; align-items: center; margin-top: 18px; color: var(--pv-muted); font-size: 0.95rem; }
        .post-meta .meta-separator { color: var(--pv-border-strong); }

        .preview-article { padding: 0; overflow: hidden; }
        .post-content { padding: 30px 28px 36px; word-wrap: break-word; overflow-wrap: anywhere; }
        .post-content > *:first-child { margin-top: 0; }
        .post-content > *:last-child { margin-bottom: 0; }
        .post-content p, .post-content ul, .post-content ol, .post-content blockquote,
        .post-content pre, .post-content table, .post-content dl, .post-content figure, .post-content details { margin: 1.2rem 0; }

        /* 标题 */
        .post-content h1, .post-content h2, .post-content h3,
        .post-content h4, .post-content h5, .post-content h6 {
            margin: 2rem 0 0.9rem; color: var(--pv-heading);
            line-height: 1.28; font-weight: 800; letter-spacing: -0.02em;
        }
        .post-content h1 { font-size: 2rem; padding-bottom: 0.55rem; border-bottom: 1px solid var(--pv-border); }
        .post-content h2 { font-size: 1.65rem; padding-bottom: 0.45rem; border-bottom: 1px solid var(--pv-border); }
        .post-content h3 { font-size: 1.35rem; }
        .post-content h4 { font-size: 1.15rem; }
        .post-content h5, .post-content h6 { font-size: 1rem; }

        /* 列表 */
        .post-content ul, .post-content ol { padding-left: 1.5rem; }
        .post-content li + li { margin-top: 0.45rem; }
        .post-content li > ul, .post-content li > ol { margin-top: 0.55rem; margin-bottom: 0.55rem; }

        /* 引用 */
        .post-content blockquote { padding: 1rem 1.1rem; border-left: 4px solid var(--pv-quote-border); background: var(--pv-quote-bg); color: var(--pv-quote-text); }
        .post-content blockquote > *:first-child { margin-top: 0; }
        .post-content blockquote > *:last-child { margin-bottom: 0; }
        .post-content blockquote blockquote { margin: 0.9rem 0 0; background: transparent; border-left-color: var(--pv-border-strong); }

        /* 代码 */
        .post-content code, .post-content kbd, .post-content samp { font-family: "JetBrains Mono", "Cascadia Code", monospace; font-size: 0.9em; }
        .post-content code { padding: 0.18rem 0.42rem; background: var(--pv-code-bg); color: var(--pv-code-text); border: 1px solid var(--pv-border); }
        .post-content pre { padding: 16px 18px; overflow-x: auto; background: var(--pv-pre-bg); color: var(--pv-pre-text); border: 1px solid var(--pv-pre-border); line-height: 1.7; tab-size: 4; }
        .post-content pre code { padding: 0; border: 0; background: transparent; color: inherit; font-size: 0.92rem; }

        /* 表格 */
        .post-content table { display: table; width: 100%; overflow-x: auto; border-collapse: collapse; border: 1px solid var(--pv-table-border); background: var(--pv-panel); }
        .post-content thead { background: var(--pv-table-head-bg); }
        .post-content th, .post-content td { padding: 12px 14px; border: 1px solid var(--pv-table-border); text-align: left; vertical-align: top; }
        .post-content th { color: var(--pv-heading); font-weight: 700; }
        .post-content tbody tr:nth-child(even) { background: var(--pv-table-stripe); }
        .post-content tbody tr:hover { background: var(--pv-table-hover); }

        /* 其他元素 */
        .post-content figure { margin-left: 0; margin-right: 0; }
        .post-content figcaption { margin-top: 0.75rem; color: var(--pv-muted); font-size: 0.92rem; text-align: center; }
        .post-content strong { color: var(--pv-heading); font-weight: 700; }
        .post-content mark { background: var(--pv-mark-bg); color: inherit; padding: 0.08em 0.22em; }
        .post-content kbd { display: inline-flex; align-items: center; min-height: 1.7rem; padding: 0 0.45rem; border: 1px solid var(--pv-border-strong); background: var(--pv-kbd-bg); color: var(--pv-kbd-text); }
        .post-content abbr[title] { text-decoration-style: dotted; cursor: help; }
        .post-content dl dt { margin-top: 1rem; font-weight: 700; color: var(--pv-heading); }
        .post-content dl dd { margin: 0.35rem 0 0 1.25rem; color: var(--pv-muted); }
        .post-content details { border: 1px solid var(--pv-border); background: var(--pv-panel-soft); }
        .post-content summary { cursor: pointer; padding: 0.9rem 1rem; font-weight: 600; color: var(--pv-heading); }
        .post-content details > :not(summary) { padding: 0 1rem 1rem; }
        .post-content input[type="checkbox"] { width: 1rem; height: 1rem; margin-right: 0.45rem; accent-color: var(--pv-accent); vertical-align: -0.12rem; }
        .post-content .contains-task-list, .post-content .task-list { list-style: none; padding-left: 0; }
        .post-content .contains-task-list li, .post-content .task-list li { padding-left: 0.15rem; }
        .post-content .footnotes { margin-top: 2.4rem; padding-top: 1.1rem; border-top: 1px solid var(--pv-border); color: var(--pv-muted); font-size: 0.94rem; }
        .post-content .footnotes hr { display: none; }
        .post-content .footnote-ref, .post-content .footnote-backref { color: var(--pv-accent); text-decoration: none; }
        .post-content .katex { font-size: 1.02em; }
        .post-content .katex-display { margin: 1.5rem 0; overflow-x: auto; overflow-y: hidden; padding: 0.2rem 0.1rem; }
        .post-content iframe, .post-content video { display: block; width: 100%; min-height: 360px; border: 1px solid var(--pv-border); background: var(--pv-panel-soft); }
        .post-content .table-wrap { margin: 1.2rem 0; overflow-x: auto; }
        .post-content .table-wrap table { margin: 0; }

        @media (max-width: 720px) {
            .preview-shell { width: min(100vw, calc(100vw - 20px)); padding-top: 10px; padding-bottom: 20px; }
            .preview-header { padding: 18px 16px; margin-bottom: 10px; }
            .post-content { padding: 20px 16px 24px; }
            .post-meta { gap: 8px 10px; font-size: 0.88rem; }
            .post-content iframe, .post-content video { min-height: 220px; }
            .post-content table { display: block; overflow-x: auto; -webkit-overflow-scrolling: touch; }
            .post-content th, .post-content td { white-space: nowrap; min-width: 72px; padding: 10px 12px; }
        }

        /* ========================================
         * 打印样式 - 强制白底黑字
         * ======================================== */
        @media print {
            :root { color-scheme: light; }
            @page { margin: 12mm 14mm; size: A4; }
            body { background: #fff !important; color: #1a1a1a !important; }
            .preview-shell { width: 100% !important; max-width: 100% !important; margin: 0 !important; padding: 0 !important; }
            .preview-header, .preview-article { box-shadow: none !important; border: 0 !important; background: transparent !important; }
            .preview-header { padding: 0 0 5mm 0 !important; margin-bottom: 4mm !important; border-bottom: 0.5pt solid #ccc !important; }
            .preview-kicker { display: none !important; }
            .post-title { font-size: 17pt !important; color: #111 !important; }
            .post-meta { font-size: 8.5pt !important; color: #888 !important; }
            .post-content { padding: 0 !important; }
            img { border: 0.5pt solid #ccc !important; }

            /* 表格打印优化 */
            .table-wrap { overflow: visible !important; }
            .post-content table,
            .post-content .table-wrap table {
                display: table !important;
                width: 100% !important;
                overflow: visible !important;
                page-break-inside: auto;
                border-collapse: collapse;
            }
            .post-content th,
            .post-content td {
                overflow: visible !important;
                white-space: normal !important;
                word-wrap: break-word;
                overflow-wrap: break-word;
            }

            /* 公式打印优化 */
            .post-content .katex-display {
                overflow: visible !important;
                overflow-x: visible !important;
                white-space: normal !important;
            }
            .post-content .katex-display > .katex {
                white-space: normal !important;
            }
            .post-content .katex {
                font-size: 1em !important;
            }

            iframe, video { display: none !important; }
            a[href]::after { content: " (" attr(href) ")"; font-size: 0.75em; color: #aaa; word-break: break-all; }
            a[href^="#"]::after, a[href^="javascript"]::after, a[href^="data:"]::after { content: none; }
        }
    </style>
</head>
<body>

<!-- 预览内容 -->
<main class="preview-shell" id="previewContent">
    <header class="preview-header">
        <div class="preview-kicker"><?php _e('预览'); ?></div>
        <h1 class="post-title"><?php $content->title(); ?></h1>
        <div class="post-meta">
            <time datetime="<?php $content->date('c'); ?>"><?php $content->date(); ?></time>
            <span class="meta-separator">&bull;</span>
            <span><?php $content->author(); ?></span>
        </div>
    </header>
    <article class="preview-article">
        <div class="post-content"><?php $content->content(); ?></div>
    </article>
</main>

<script>
(function () {
    // 表格自适应包装
    document.querySelectorAll('.post-content table').forEach(function (table) {
        if (table.parentElement && table.parentElement.classList.contains('table-wrap')) return;
        var wrapper = document.createElement('div');
        wrapper.className = 'table-wrap';
        table.parentNode.insertBefore(wrapper, table);
        wrapper.appendChild(table);
    });

    /**
     * KaTeX 渲染。外部脚本以 defer 加载，需等到 window load 后再调用；
     * CDN 不可达时 typeof 守卫直接跳过，公式保留原始文本而不报错。
     */
    function renderMath() {
        if (typeof renderMathInElement !== 'function') {
            return;
        }
        renderMathInElement(document.body, {
            delimiters: [
                { left: "$$", right: "$$", display: true },
                { left: "$", right: "$", display: false },
                { left: "\\(", right: "\\)", display: false },
                { left: "\\[", right: "\\]", display: true }
            ],
            throwOnError: false
        });
    }

    if (document.readyState === 'complete') {
        renderMath();
    } else {
        window.addEventListener('load', renderMath);
    }
})();
</script>
</body>
</html>
<?php
} elseif ($previewMode === 'native') {
    /** 原生预览：主题完整渲染，工具栏由父页面弹层提供 */
    $content->render();
}
