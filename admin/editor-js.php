<?php if(!defined('__TYPECHO_ADMIN__')) exit; ?>
<?php $content = !empty($post) ? $post : $page; ?>
<script>
// ========================================
// 编辑器：正文文本域通用事件（Markdown 与非 Markdown 模式共用）
// ========================================
(function () {
    $('#text').on('change', function (e) {
        e.preventDefault();
        e.stopPropagation();
    }).on('input', function () {
        $(this).parents('form').trigger('write');
    });
})();
</script>
<?php if (!$options->markdown): ?>
<script>
// ========================================
// 编辑器：非 Markdown 模式，插入内容使用原生 HTML
// ========================================
(function () {
    const textarea = $('#text');

    // 转义 HTML 特殊字符：文件名或 URL 可能含引号、尖括号，
    // 直接拼接会破坏属性结构并造成注入，插入编辑器前必须先转义
    function escapeHtmlAttr (str) {
        return String(str === undefined || str === null ? '' : str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    // 插入图片 / 文件到正文
    Typecho.insertFileToEditor = function (file, url, isImage) {
        const sel = textarea.getSelection(),
            html = isImage ? '<img src="' + escapeHtmlAttr(url) + '" alt="' + escapeHtmlAttr(file) + '" />'
                : '<a href="' + escapeHtmlAttr(url) + '">' + escapeHtmlAttr(file) + '</a>',
            offset = (sel ? sel.start : 0) + html.length;

        textarea.replaceSelection(html);
        textarea.setSelection(offset, offset);
    };
})();
</script>
<?php else: ?>
<script src="<?php $options->adminStaticUrl('js', 'hyperdown.js'); ?>"></script>
<script src="<?php $options->adminStaticUrl('js', 'pagedown.js'); ?>"></script>
<script src="<?php $options->adminStaticUrl('js', 'purify.js'); ?>"></script>

<!-- KaTeX Resources -->
<link rel="stylesheet" href="https://cdn.garfieldtom.cool/resource/libs/KaTeX/0.16.38/katex.min.css">
<script src="https://cdn.garfieldtom.cool/resource/libs/KaTeX/0.16.38/katex.min.js"></script>
<script src="https://cdn.garfieldtom.cool/resource/libs/KaTeX/0.16.38/contrib/auto-render.min.js"></script>

<script>
$(document).ready(function () {
    const textarea = $('#text'),
        toolbar = $('<div class="editor" id="wmd-button-bar" />').insertBefore(textarea.parent()),
        preview = $('<div id="wmd-preview" class="wmd-hidetab" />').insertAfter('.editor');
    let isFullScreen = false;

    const options = {},
        defaultMarkdown = <?php echo json_encode(!$content->have() || $content->isMarkdown); ?>,
        markdownInput = $('input[data-write-markdown="1"]'),
        isMarkdown = markdownInput.length > 0 ? markdownInput.val() === '1' : defaultMarkdown;

    options.strings = {
        bold: '<?php _e('加粗'); ?> <strong> Ctrl+B',
        boldexample: '<?php _e('加粗文字'); ?>',
        italic: '<?php _e('斜体'); ?> <em> Ctrl+I',
        italicexample: '<?php _e('斜体文字'); ?>',

        link: '<?php _e('链接'); ?> <a> Ctrl+L',
        linkdescription: '<?php _e('请输入链接描述'); ?>',

        quote:  '<?php _e('引用'); ?> <blockquote> Ctrl+Q',
        quoteexample: '<?php _e('引用文字'); ?>',

        code: '<?php _e('代码'); ?> <pre><code> Ctrl+K',
        codeexample: '<?php _e('请输入代码'); ?>',

        image: '<?php _e('图片'); ?> <img> Ctrl+G',
        imagedescription: '<?php _e('请输入图片描述'); ?>',

        olist: '<?php _e('数字列表'); ?> <ol> Ctrl+O',
        ulist: '<?php _e('普通列表'); ?> <ul> Ctrl+U',
        litem: '<?php _e('列表项目'); ?>',

        heading: '<?php _e('标题'); ?> <h1>/<h2> Ctrl+H',
        headingexample: '<?php _e('标题文字'); ?>',

        hr: '<?php _e('分割线'); ?> <hr> Ctrl+R',
        more: '<?php _e('摘要分割线'); ?> <!--more--> Ctrl+M',

        undo: '<?php _e('撤销'); ?> - Ctrl+Z',
        redo: '<?php _e('重做'); ?> - Ctrl+Y',
        redomac: '<?php _e('重做'); ?> - Ctrl+Shift+Z',

        fullscreen: '<?php _e('全屏'); ?> - Ctrl+J',
        exitFullscreen: '<?php _e('退出全屏'); ?> - Ctrl+E',
        fullscreenUnsupport: '<?php _e('此浏览器不支持全屏操作'); ?>',

        imagedialog: '<p><b><?php _e('插入图片'); ?></b></p><p><?php _e('请在下方的输入框内输入要插入的远程图片地址'); ?></p><p><?php _e('您也可以使用附件功能插入上传的本地图片'); ?></p>',
        linkdialog: '<p><b><?php _e('插入链接'); ?></b></p><p><?php _e('请在下方的输入框内输入要插入的链接地址'); ?></p>',

        ok: '<?php _e('确定'); ?>',
        cancel: '<?php _e('取消'); ?>',

        help: '<?php _e('Markdown语法帮助'); ?>'
    };

    const converter = new HyperDown(),
        editor = new Markdown.Editor(converter, '', options);

    // 预览自动跟随编辑区滚动
    converter.enableHtml(true);
    converter.enableLine(true);
    const reloadScroll = scrollableEditor(textarea, preview);

    // 渲染收尾：拆分摘要/正文、替换嵌入标签，并对结果做 HTML 净化
    converter.hook('makeHtml', function (html) {
        html = html.replace('<p><!--more--></p>', '<!--more-->');

        // 存在摘要分割线时，拆成 summary / details 两段
        if (html.indexOf('<!--more-->') > 0) {
            const parts = html.split(/\s*<\!\-\-more\-\->\s*/),
                summary = parts.shift(),
                details = parts.join('');

            html = '<div class="summary">' + summary + '</div>'
                + '<div class="details">' + details + '</div>';
        }

        // iframe / embed 转为占位块，避免外部内容撑破预览区
        html = html.replace(/<(iframe|embed)\s+([^>]*)>/ig, function (all, tag, src) {
            if (src[src.length - 1] === '/') {
                src = src.substring(0, src.length - 1);
            }

            return '<div class="embed"><strong>'
                + tag + '</strong> : ' + $.trim(src) + '</div>';
        });

        return DOMPurify.sanitize(html, {USE_PROFILES: {html: true}});
    });

    // ========================================
    // 预览区：公式渲染、溢出容器与滚动同步
    // ========================================
    // 等待布局稳定后再同步的防抖间隔（ms）
    const PREVIEW_SYNC_DELAY = 30;
    // 图片 load / error 迟迟不触发时的兜底超时（ms）
    const PREVIEW_SYNC_FALLBACK = 1200;

    const mathRenderOptions = {
        delimiters: [
            { left: "$$", right: "$$", display: true },
            { left: "$", right: "$", display: false },
            { left: "\\(", right: "\\)", display: false },
            { left: "\\[", right: "\\]", display: true }
        ],
        throwOnError: false
    };

    function ensurePreviewOverflowContainers() {
        preview.find('table, pre, .katex-display').each(function () {
            const target = $(this);

            if (target.parent('.wmd-overflow-wrap').length > 0) {
                return;
            }

            if (target.is('table') && target.parent('.table-wrap').length > 0) {
                return;
            }

            const wrapper = $('<div class="wmd-overflow-wrap" />');

            if (target.is('table')) {
                wrapper.addClass('is-table');
            } else if (target.is('pre')) {
                wrapper.addClass('is-code');
            } else {
                wrapper.addClass('is-math');
            }

            target.wrap(wrapper);
        });
    }

    let previewSyncTimer = null;
    function syncPreviewAfterLayout() {
        if (previewSyncTimer) {
            clearTimeout(previewSyncTimer);
        }

        // 等待布局稳定后再同步，降低图片尺寸变化造成的偏移
        previewSyncTimer = setTimeout(function () {
            previewSyncTimer = null;

            if (typeof renderMathInElement === 'function') {
                renderMathInElement(preview[0], mathRenderOptions);
            }

            ensurePreviewOverflowContainers();
            reloadScroll(true);
        }, PREVIEW_SYNC_DELAY);
    }

    editor.hooks.chain('onPreviewRefresh', function () {
        const images = $('img', preview);
        let pending = 0;
        let finished = false;
        let fallbackTimer = null;

        const finish = function () {
            if (finished) {
                return;
            }

            finished = true;

            if (fallbackTimer) {
                clearTimeout(fallbackTimer);
                fallbackTimer = null;
            }

            syncPreviewAfterLayout();
        };

        if (images.length === 0) {
            finish();
            return;
        }

        images.each(function () {
            const img = this;
            const loaded = img.complete && (typeof img.naturalWidth === 'undefined' || img.naturalWidth > 0);

            if (loaded) {
                return;
            }

            pending ++;

            $(img).one('load error', function () {
                pending --;

                if (pending <= 0) {
                    finish();
                }
            });
        });

        // 某些缓存/异常图片不会再触发 load/error，超时后兜底同步
        fallbackTimer = setTimeout(finish, PREVIEW_SYNC_FALLBACK);

        if (pending === 0) {
            finish();
        }
    });

    <?php \Typecho\Plugin::factory('admin/editor-js.php')->call('markdownEditor', $content); ?>

    let th = textarea.height();
    const uploadBtn = $('<button type="button" id="btn-fullscreen-upload" class="btn btn-link">'
            + '<i class="i-upload"><?php _e('附件'); ?></i></button>')
            .prependTo('.submit .right')
            .click(function() {
                $('a', $('.typecho-option-tabs li').not('.active')).trigger('click');
                return false;
            });

    $('.typecho-option-tabs li').click(function () {
        uploadBtn.find('i').toggleClass('i-upload-active',
            $('#tab-files-btn', this).length > 0);
    });

    // 进入全屏：可用高度由调用方决定，两个全屏钩子共用，避免重复实现
    function applyFullscreenHeight (getViewportHeight) {
        $(document.body).addClass('fullscreen');

        const height = getViewportHeight() - toolbar.outerHeight();

        textarea.css('height', height);
        preview.css('height', height);
        isFullScreen = true;
    }

    editor.hooks.chain('enterFakeFullScreen', function () {
        // 记录原始高度，供退出全屏时还原
        th = textarea.height();
        applyFullscreenHeight(function () {
            return $(window).height();
        });
    });

    editor.hooks.chain('enterFullScreen', function () {
        applyFullscreenHeight(function () {
            return window.screen.height;
        });
    });

    editor.hooks.chain('exitFullScreen', function () {
        $(document.body).removeClass('fullscreen');
        textarea.height(th);
        // 清除内联高度，交回样式表控制
        preview.css('height', '');
        isFullScreen = false;
    });

    editor.hooks.chain('commandExecuted', function () {
        textarea.trigger('input');
    });

    editor.hooks.chain('save', function () {
        Typecho.savePost();
    });

    // 转义 markdown 链接文本中的方括号，避免被提前闭合
    function escapeMarkdownText (text) {
        return String(text === undefined || text === null ? '' : text).replace(/([\\\[\]])/g, '\\$1');
    }

    // 行首 0~3 个空格的引用式定义：  [N]: url
    const LINK_DEF_PATTERN = /^[ ]{0,3}\[(\d+)\]:[ \t]*(\S+)[ \t]*$/;

    /**
     * 收集正文中已有的引用式定义
     * @param {string} text 正文内容
     * @returns {{defs: Object, maxNum: number}} defs 为「编号 -> url」映射，maxNum 为当前最大编号
     */
    function collectLinkDefs (text) {
        const lines = String(text === undefined || text === null ? '' : text).split('\n');
        const defs = {};
        let maxNum = 0;

        for (const line of lines) {
            const matched = line.match(LINK_DEF_PATTERN);

            if (matched) {
                defs[matched[1]] = matched[2];
                maxNum = Math.max(maxNum, parseInt(matched[1], 10));
            }
        }

        return {defs: defs, maxNum: maxNum};
    }

    /**
     * 格式化定义中的 URL：空白转 %20，含尖括号时用 <> 包裹，避免解析错位
     * @param {string} url 原始地址
     * @returns {string}
     */
    function formatLinkDefUrl (url) {
        const safe = String(url === undefined || url === null ? '' : url).replace(/\s/g, '%20');

        return /[<>]/.test(safe) ? '<' + safe.replace(/</g, '%3C').replace(/>/g, '%3E') + '>' : safe;
    }

    /**
     * 以引用式（reference-style）插入图片/链接
     *
     * 正文写 ![alt][N] / [文本][N]，链接定义 "  [N]: url" 追加到文末，
     * 与 pagedown 默认行为保持一致（前置空行、两个空格缩进）；
     * 同一 URL 复用已有定义，避免重复堆积。
     *
     * @param {string} file 文件名/描述
     * @param {string} url 资源地址
     * @param {boolean} isImage 是否为图片
     */
    function insertReferenceLink (file, url, isImage) {
        const collected = collectLinkDefs(textarea.val()),
            defs = collected.defs;
        let num = null;

        // 同一 URL 已存在定义时直接复用
        for (const key in defs) {
            if (Object.prototype.hasOwnProperty.call(defs, key) && defs[key] === url) {
                num = parseInt(key, 10);
                break;
            }
        }

        // 否则分配下一个可用编号
        if (num === null) {
            num = collected.maxNum + 1;
        }

        const sel = textarea.getSelection(),
            snippet = (isImage ? '![' : '[') + escapeMarkdownText(file) + '][' + num + ']',
            offset = (sel ? sel.start : 0) + snippet.length,
            scrollTop = textarea.scrollTop();

        textarea.replaceSelection(snippet);

        // 新定义追加到文末（仅在确实新建编号时写入）
        if (num > collected.maxNum) {
            textarea.val(textarea.val() + '\n\n  [' + num + ']: ' + formatLinkDefUrl(url));
        }

        textarea.setSelection(offset, offset);
        textarea.trigger('input');

        // 恢复滚动位置
        textarea.scrollTop(scrollTop);
    }

    function initMarkdown() {
        editor.run();

        const imageButton = $('#wmd-image-button'),
            linkButton = $('#wmd-link-button');

        Typecho.insertFileToEditor = function (file, url, isImage, skipDialog) {
            // skipDialog 为 true：跳过 pagedown 对话框（避免与文件预览弹窗重复弹出），
            // 但仍以引用式写入，保持「底部链接定义」的原有格式
            if (skipDialog) {
                insertReferenceLink(file, url, isImage);
                return;
            }

            // 否则使用原有逻辑（弹出对话框）
            const button = isImage ? imageButton : linkButton;

            options.strings[isImage ? 'imagename' : 'linkname'] = file;
            button.trigger('click');

            let checkDialog = setInterval(function () {
                if ($('.wmd-prompt-dialog').length > 0) {
                    $('.wmd-prompt-dialog input').val(url).select();
                    clearInterval(checkDialog);
                    checkDialog = null;
                }
            }, 10);
        };

        Typecho.uploadComplete = function (attachment) {
            Typecho.insertFileToEditor(attachment.title, attachment.url, attachment.isImage);
        };

        function adjustToolbarHeight() {
            const buttonRow = $('#wmd-button-row');
            if (buttonRow.length > 0) {
                // 总是设置为自动高度，确保按钮换行时能正确显示
                buttonRow.css('height', 'auto');
                buttonRow.css('flex-wrap', 'wrap');
                buttonRow.css('align-items', 'center');

                // 同时调整整个工具栏容器的高度
                const buttonBar = $('#wmd-button-bar');
                if (buttonBar.length > 0) {
                    buttonBar.css('height', 'auto');
                    buttonBar.css('flex-wrap', 'wrap');
                    buttonBar.css('align-items', 'center');
                }

                // 移除编辑/预览标签的内联样式，保持默认设计
                const editTab = $('.wmd-edittab');
                if (editTab.length > 0) {
                    editTab.css('margin-top', '');
                    editTab.css('width', '');
                    editTab.css('text-align', '');
                }
            }
        }

        adjustToolbarHeight();
        $(window).on('resize', function () {
            adjustToolbarHeight();
            syncPreviewAfterLayout();
        });

        // 编辑 / 预览切换
        $('#wmd-button-bar').append('<div class="wmd-edittab"><a href="#wmd-editarea" class="active"><?php _e('撰写'); ?></a><a href="#wmd-preview"><?php _e('预览'); ?></a></div>');
        textarea.parent().attr('id', 'wmd-editarea');

        $('.wmd-edittab a').click(function () {
            const $link = $(this),
                target = $link.attr('href'),
                isPreview = target === '#wmd-preview';

            $('.wmd-edittab a').removeClass('active');
            $link.addClass('active');
            $('#wmd-editarea, #wmd-preview').addClass('wmd-hidetab');
            $(target).removeClass('wmd-hidetab');

            // 预览时隐藏工具栏按钮
            $('#wmd-button-row').toggleClass('wmd-visualhide', isPreview);

            // 工具栏换行后高度会变化，切换后需重新计算
            adjustToolbarHeight();

            if (isPreview) {
                // 非全屏下清除内联高度，交回样式表控制
                if (!isFullScreen) {
                    preview.css('height', '');
                }

                // 渲染 LaTeX 公式并同步滚动
                syncPreviewAfterLayout();
            }

            return false;
        });

        // 粘贴图片时自动上传
        textarea.bind('paste', function (e) {
            const clipboard = e.originalEvent && e.originalEvent.clipboardData
                ? e.originalEvent.clipboardData
                : e.clipboardData;

            // 部分浏览器或场景下取不到剪贴板数据，直接跳过避免报错
            if (!clipboard || !clipboard.items) {
                return;
            }

            for (const item of clipboard.items) {
                if (item.kind !== 'file') {
                    continue;
                }

                const file = item.getAsFile();

                if (!file || file.size === 0) {
                    continue;
                }

                // 无名文件（如部分截图）按时间戳生成文件名
                if (!file.name) {
                    file.name = (new Date()).toISOString().replace(/\..+$/, '')
                        + '.' + file.type.split('/').pop();
                }

                Typecho.uploadFile(file);
            }
        });
    }

    if (isMarkdown) {
        initMarkdown();
    } else {
        const notice = $('<div class="message notice"><?php _e('这篇文章不是由Markdown语法创建的, 继续使用Markdown编辑它吗?'); ?> '
            + '<button class="btn btn-xs primary yes"><?php _e('是'); ?></button> '
            + '<button class="btn btn-xs no"><?php _e('否'); ?></button></div>')
            .hide().insertBefore(textarea).slideDown();

        $('.yes', notice).click(function () {
            notice.remove();
            if (markdownInput.length > 0) {
                markdownInput.val('1');
                $('#use-markdown').prop('checked', true);
            } else {
                $('<input type="hidden" name="markdown" value="1" data-write-markdown="1" />').appendTo(textarea.closest('form'));
            }
            initMarkdown();
        });

        $('.no', notice).click(function () {
            notice.remove();
        });
    }
});
</script>
<?php endif; ?>

