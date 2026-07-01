<?php if(!defined('__TYPECHO_ADMIN__')) exit; ?>
<?php $content = !empty($post) ? $post : $page; ?>
<script>
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
(function () {
    const textarea = $('#text');

    // 原始的插入图片和文件
    Typecho.insertFileToEditor = function (file, url, isImage) {
        const sel = textarea.getSelection(),
            html = isImage ? '<img src="' + url + '" alt="' + file + '" />'
                : '<a href="' + url + '">' + file + '</a>',
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

    // 自动跟随
    converter.enableHtml(true);
    converter.enableLine(true);
    const reloadScroll = scrollableEditor(textarea, preview);

    // 修正白名单
    converter.hook('makeHtml', function (html) {
        html = html.replace('<p><!--more--></p>', '<!--more-->');
        
        if (html.indexOf('<!--more-->') > 0) {
            var parts = html.split(/\s*<\!\-\-more\-\->\s*/),
                summary = parts.shift(),
                details = parts.join('');

            html = '<div class="summary">' + summary + '</div>'
                + '<div class="details">' + details + '</div>';
        }

        // 替换block
        html = html.replace(/<(iframe|embed)\s+([^>]*)>/ig, function (all, tag, src) {
            if (src[src.length - 1] === '/') {
                src = src.substring(0, src.length - 1);
            }

            return '<div class="embed"><strong>'
                + tag + '</strong> : ' + $.trim(src) + '</div>';
        });

        return DOMPurify.sanitize(html, {USE_PROFILES: {html: true}});
    });

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
        }, 30);
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
        fallbackTimer = setTimeout(finish, 1200);

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

    editor.hooks.chain('enterFakeFullScreen', function () {
        th = textarea.height();
        $(document.body).addClass('fullscreen');
        const h = $(window).height() - toolbar.outerHeight();
        
        textarea.css('height', h);
        preview.css('height', h);
        isFullScreen = true;
    });

    editor.hooks.chain('enterFullScreen', function () {
        $(document.body).addClass('fullscreen');
        
        const h = window.screen.height - toolbar.outerHeight();
        textarea.css('height', h);
        preview.css('height', h);
        isFullScreen = true;
    });

    editor.hooks.chain('exitFullScreen', function () {
        $(document.body).removeClass('fullscreen');
        textarea.height(th);
        preview.css('height', '');
        isFullScreen = false;
    });

    editor.hooks.chain('commandExecuted', function () {
        textarea.trigger('input');
    });

    editor.hooks.chain('save', function () {
        Typecho.savePost();
    });

    function initMarkdown() {
        editor.run();

        const imageButton = $('#wmd-image-button'),
            linkButton = $('#wmd-link-button');

        Typecho.insertFileToEditor = function (file, url, isImage, skipDialog) {
            // 如果 skipDialog 为 true，直接插入，不弹出对话框
            if (skipDialog) {
                const sel = textarea.getSelection(),
                    markdown = isImage ? '![' + file + '](' + url + ')' : '[' + file + '](' + url + ')',
                    offset = (sel ? sel.start : 0) + markdown.length;

                // 保存当前滚动位置
                const scrollTop = textarea.scrollTop();

                textarea.replaceSelection(markdown);
                textarea.setSelection(offset, offset);
                textarea.trigger('input');

                // 恢复滚动位置
                textarea.scrollTop(scrollTop);
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

        // 编辑预览切换
        const edittab = $('#wmd-button-bar').append('<div class="wmd-edittab"><a href="#wmd-editarea" class="active"><?php _e('撰写'); ?></a><a href="#wmd-preview"><?php _e('预览'); ?></a></div>'),
            editarea = $(textarea.parent()).attr("id", "wmd-editarea");

        $(".wmd-edittab a").click(function() {
            $(".wmd-edittab a").removeClass('active');
            $(this).addClass("active");
            $("#wmd-editarea, #wmd-preview").addClass("wmd-hidetab");

            const selected_tab = $(this).attr("href"),
                selected_el = $(selected_tab).removeClass("wmd-hidetab");

            // 预览时隐藏编辑器按钮
            if (selected_tab === "#wmd-preview") {
                $("#wmd-button-row").addClass("wmd-visualhide");

                // 确保预览时 LaTeX 公式已渲染
                syncPreviewAfterLayout();
            } else {
                $("#wmd-button-row").removeClass("wmd-visualhide");
            }

            // 重新调整工具栏高度和布局
            adjustToolbarHeight();

            // Auto-size preview height outside fullscreen
            if (!isFullScreen && selected_tab === "#wmd-preview") {
                preview.css('height', '');
            }

            if (selected_tab === "#wmd-preview") {
                syncPreviewAfterLayout();
            }

            return false;
        });

        // 剪贴板复制图片
        textarea.bind('paste', function (e) {
            const items = (e.clipboardData || e.originalEvent.clipboardData).items;

            for (const item of items) {
                if (item.kind === 'file') {
                    const file = item.getAsFile();

                    if (file.size > 0) {
                        if (!file.name) {
                            file.name = (new Date()).toISOString().replace(/\..+$/, '')
                                + '.' + file.type.split('/').pop();
                        }

                        Typecho.uploadFile(file);
                    }
                }
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

