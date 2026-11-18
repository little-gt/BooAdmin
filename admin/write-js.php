<?php if(!defined('__TYPECHO_ADMIN__')) exit; ?>
<?php \Typecho\Plugin::factory('admin/write-js.php')->call('write'); ?>
<?php \Widget\Metas\Tag\Cloud::alloc('sort=count&desc=1&limit=200')->to($tags); ?>

<?php
booadminScriptOnce('timepicker.js');
booadminScriptOnce('tokeninput.js');
?>
<script>
$(document).ready(function() {
    // 日期时间控件
    $('#date').mask('9999-99-99 99:99').datetimepicker({
        currentText     :   '<?php _e('现在'); ?>',
        prevText        :   '<?php _e('上一月'); ?>',
        nextText        :   '<?php _e('下一月'); ?>',
        monthNames      :   ['<?php _e('一月'); ?>', '<?php _e('二月'); ?>', '<?php _e('三月'); ?>', '<?php _e('四月'); ?>',
            '<?php _e('五月'); ?>', '<?php _e('六月'); ?>', '<?php _e('七月'); ?>', '<?php _e('八月'); ?>',
            '<?php _e('九月'); ?>', '<?php _e('十月'); ?>', '<?php _e('十一月'); ?>', '<?php _e('十二月'); ?>'],
        dayNames        :   ['<?php _e('星期日'); ?>', '<?php _e('星期一'); ?>', '<?php _e('星期二'); ?>',
            '<?php _e('星期三'); ?>', '<?php _e('星期四'); ?>', '<?php _e('星期五'); ?>', '<?php _e('星期六'); ?>'],
        dayNamesShort   :   ['<?php _e('周日'); ?>', '<?php _e('周一'); ?>', '<?php _e('周二'); ?>', '<?php _e('周三'); ?>',
            '<?php _e('周四'); ?>', '<?php _e('周五'); ?>', '<?php _e('周六'); ?>'],
        dayNamesMin     :   ['<?php _e('日'); ?>', '<?php _e('一'); ?>', '<?php _e('二'); ?>', '<?php _e('三'); ?>',
            '<?php _e('四'); ?>', '<?php _e('五'); ?>', '<?php _e('六'); ?>'],
        closeText       :   '<?php _e('完成'); ?>',
        timeOnlyTitle   :   '<?php _e('选择时间'); ?>',
        timeText        :   '<?php _e('时间'); ?>',
        hourText        :   '<?php _e('时'); ?>',
        amNames         :   ['<?php _e('上午'); ?>', 'A'],
        pmNames         :   ['<?php _e('下午'); ?>', 'P'],
        minuteText      :   '<?php _e('分'); ?>',
        secondText      :   '<?php _e('秒'); ?>',

        dateFormat      :   'yy-mm-dd',
        timezone        :   <?php $options->timezone(); ?> / 60,
        hour            :   (new Date()).getHours(),
        minute          :   (new Date()).getMinutes()
    });

    // 聚焦
    $('#title').select();

    // 正文编辑框自动拉伸
    Typecho.editorResize('text', '<?php $security->index('/action/ajax?do=editorResize'); ?>');

    // 标签自动补全提示
    const tags = $('#tags'), tagsPre = [];
    
    if (tags.length > 0) {
        const items = tags.val().split(',');
        for (let i = 0; i < items.length; i ++) {
            const tag = items[i];

            if (!tag) {
                continue;
            }

            tagsPre.push({
                id      :   tag,
                tags    :   tag
            });
        }

        tags.tokenInput(<?php 
        $data = array();
        while ($tags->next()) {
            $data[] = array(
                'id'    =>  $tags->name,
                'tags'  =>  $tags->name
            );
        }
        echo json_encode($data);
        ?>, {
            propertyToSearch:   'tags',
            tokenValue      :   'tags',
            searchDelay     :   0,
            preventDuplicates   :   true,
            animateDropdown :   false,
            hintText        :   '<?php _e('请输入标签名'); ?>',
            noResultsText   :   '<?php _e('此标签不存在, 按回车创建'); ?>',
            prePopulate     :   tagsPre,

            onResult        :   function (result, query, val) {
                // 移除特殊字符
                val = val.replace(/<|>|&|"|'/g, '');

                if (!query) {
                    return result;
                }

                if (!result) {
                    result = [];
                }

                if (!result[0] || result[0]['id'] !== query) {
                    result.unshift({
                        id      :   val,
                        tags    :   val
                    });
                }

                return result.slice(0, 5);
            }
        });

        // 标签自动补全提示宽度设置
        $('#token-input-tags').focus(function() {
            const t = $('.token-input-dropdown'),
                offset = t.outerWidth() - t.width();
            t.width($('.token-input-list').outerWidth() - offset);
        });
    }

    // 缩略名自适应宽度
    const slug = $('#slug');

    if (slug.length > 0) {
        const wrap = $('<div />').css({
            'position'  :   'relative',
            'display'   :   'inline-block'
        }),
        justifySlug = $('<pre />').css({
            'display'   :   'block',
            'visibility':   'hidden',
            'height'    :   '38px',
            'padding'   :   '8px 8px',
            'margin'    :   0
        }).insertAfter(slug.wrap(wrap).css({
            'left'      :   0,
            'top'       :   0,
            'minWidth'  :   '5px',
            'position'  :   'absolute',
            'width'     :   '100%'
        }));

        function justifySlugWidth() {
            const val = slug.val();
            justifySlug.text(val.length > 0 ? val : '     ');
        }

        slug.bind('input propertychange', justifySlugWidth);
        justifySlugWidth();
    }

    // 处理保存文章的逻辑
    const form = $('form[name=write_post],form[name=write_page]'),
        idInput = $('input[name=cid]'),
        draft = $('input[name=draft]'),
        btnPreview = $('#btn-preview'),
        autoSave = $('<span id="auto-save-message"></span>').prependTo('.left');

    let cid = idInput.val(),
        draftId = draft.length > 0 ? draft.val() : 0,
        changed = false,
        written = false,
        lastSaveTime = null;

    // Markdown 开关和隐藏字段保持一致，确保保存/发布时提交正确参数
    const markdownToggle = $('#use-markdown'),
        markdownInput = $('input[data-write-markdown="1"]');

    if (markdownToggle.length > 0 && markdownInput.length > 0) {
        function syncMarkdownValue() {
            markdownInput.val(markdownToggle.prop('checked') ? '1' : '0');
        }

        markdownToggle.on('change', function () {
            syncMarkdownValue();
            form.trigger('datachange');
        });

        syncMarkdownValue();
    }

    form.on('write', function () {
        written = true;
        form.trigger('datachange');
    });

    form.on('change', function () {
        if (written) {
            form.trigger('datachange');
        }
    });

    $('button[name=do]').click(function () {
        $('input[name=do]').val($(this).val());
        // 注意：不要在此处给表单加 submitting 类。
        // 表单的 submitting 类由 form-js.php 在 submit 事件中统一添加，
        // 用于「防重复提交」与抑制 beforeunload 提示。若这里提前加上，
        // form-js 会判定为「正在提交」而 return false 拦截原生提交，
        // 导致「发布 / 保存草稿」按钮点击无反应（文章无法入库）。
    });

    // 自动检测离开页
    $(window).bind('beforeunload', function () {
        if (changed && !form.hasClass('submitting')) {
            return '<?php _e('内容已经改变尚未保存, 您确认要离开此页面吗?'); ?>';
        }
    });

    // 发送保存请求
    Typecho.savePost = function(cb) {
        if (!changed) {
            cb && cb();
            return;
        }

        const callback = function (o) {
            lastSaveTime = o.time;
            cid = o.cid;
            draftId = o.draftId;
            idInput.val(cid);
            autoSave.text('<?php _e('已保存'); ?>' + ' (' + o.time + ')').effect('highlight', 1000);

            cb && cb();
        };

        changed = false;
        autoSave.text('<?php _e('正在保存'); ?>');

        const data = new FormData(form.get(0));
        data.append('do', 'save');
        form.triggerHandler('submit');

        $.ajax({
            url: form.attr('action'),
            processData: false,
            contentType: false,
            type: 'POST',
            data: data,
            success: callback,
            error: function () {
                autoSave.text('<?php _e('保存失败, 请重试'); ?>');
            },
            complete: function () {
                form.trigger('submitted');
            }
        });
    };

    <?php if ($options->autoSave): ?>
    // 自动保存
    let saveTimer = null;
    let stopAutoSave = false;

    form.on('datachange', function () {
        changed = true;
        autoSave.text('<?php _e('尚未保存'); ?>' + (lastSaveTime ? ' (<?php _e('上次保存时间'); ?>: ' + lastSaveTime + ')' : ''));

        if (saveTimer) {
            clearTimeout(saveTimer);
        }

        saveTimer = setTimeout(function () {
            !stopAutoSave && Typecho.savePost();
        }, 3000);
    }).on('submit', function () {
        stopAutoSave = true;
    }).on('submitted', function () {
        stopAutoSave = false;
    });
    <?php else: ?>
    form.on('datachange', function () {
        changed = true;
    });
    <?php endif; ?>

    // 计算夏令时偏移
    const dstOffset = (function () {
        const d = new Date(),
            jan = new Date(d.getFullYear(), 0, 1),
            jul = new Date(d.getFullYear(), 6, 1),
            stdOffset = Math.max(jan.getTimezoneOffset(), jul.getTimezoneOffset());

        return stdOffset - d.getTimezoneOffset();
    })();
    
    if (dstOffset > 0) {
        $('<input name="dst" type="hidden" />').appendTo(form).val(dstOffset);
    }

    // 时区
    $('<input name="timezone" type="hidden" />').appendTo(form).val(- (new Date).getTimezoneOffset() * 60);

    // 预览功能
    const previewState = {
        overlay: null,
        container: null,
        frame: null,
        loading: null,
        fullScreenButton: null,
        // 预览模式切换工具栏挂在父页面弹层 header，不注入 iframe
        modeGroup: null,
        printButton: null,
        cid: 0,
        mode: 'native',
        isOpen: false,
        isFullScreen: false,
        saveCallback: null
    };
    const FULLSCREEN_BREAKPOINT = 768;

    function isNarrowPreviewViewport() {
        return window.innerWidth < FULLSCREEN_BREAKPOINT;
    }

    function resolvePreviewCid() {
        if (!!draftId) {
            return draftId;
        }

        if (!!cid) {
            return cid;
        }

        return 0;
    }

    function getPreviewUrl(previewCid, previewMode) {
        // embed=1：告知 preview.php 工具栏已由父页面接管，无需在 iframe 内再注入
        return './preview.php?cid=' + encodeURIComponent(previewCid)
            + '&embed=1&preview_mode=' + encodeURIComponent(previewMode || 'native');
    }

    /** 同步模式按钮高亮与打印按钮可用态；按钮样式用内联色值，不依赖主题 CSS */
    function syncPreviewModeButtons() {
        if (!previewState.modeGroup) {
            return;
        }

        previewState.modeGroup.find('[data-preview-mode]').each(function () {
            const $btn = $(this);
            const active = $btn.attr('data-preview-mode') === previewState.mode;
            $btn.toggleClass('active', active)
                .attr('aria-pressed', active ? 'true' : 'false')
                .css({
                    backgroundColor: active ? 'var(--booadmin-accent, #5865f2)' : 'transparent',
                    color: active ? '#fff' : 'var(--booadmin-text, #2e3338)',
                    fontWeight: active ? '600' : '500'
                });
        });

        if (previewState.printButton) {
            const canPrint = previewState.mode === 'print';
            previewState.printButton
                .prop('disabled', !canPrint)
                .css({
                    opacity: canPrint ? 1 : 0.5,
                    cursor: canPrint ? 'pointer' : 'not-allowed'
                })
                .attr('title', canPrint
                    ? '<?php _e('打印 (Ctrl+P)'); ?>'
                    : '<?php _e('请切换到打印预览模式'); ?>');
        }
    }

    /** 切换预览模式：淡出当前 iframe → 换 src → 加载完成后淡入，不发生父页面跳转 */
    function setPreviewMode(previewMode) {
        if (!previewState.isOpen || !previewState.frame) {
            return;
        }

        const mode = previewMode === 'print' ? 'print' : 'native';
        if (mode === previewState.mode) {
            return;
        }

        previewState.mode = mode;
        syncPreviewModeButtons();

        // 淡出 iframe、显示加载遮罩，避免切换瞬间看到旧内容或空白
        const $frame = previewState.frame;
        $frame.addClass('preview-frame-loading');
        if (previewState.loading) {
            previewState.loading.stop(true, true).fadeIn(120);
        }
        $frame.attr('src', getPreviewUrl(previewState.cid, mode));
    }

    /** 注入预览专用动画样式，作用域限定在 .preview-overlay 内，不污染页面其它元素 */
    function ensurePreviewStyles() {
        if (document.getElementById('booadmin-preview-anim')) {
            return;
        }
        const style = document.createElement('style');
        style.id = 'booadmin-preview-anim';
        style.textContent = [
            '.preview-overlay .preview-iframe{opacity:1;transition:opacity .18s ease}',
            '.preview-overlay .preview-iframe.preview-frame-loading{opacity:0}',
            '.preview-overlay .preview-loading{transition:opacity .18s ease}',
            '.preview-overlay .preview-spinner{width:22px;height:22px;border:2px solid rgba(0,0,0,.12);',
            'border-top-color:var(--booadmin-accent,#5865f2);border-radius:50%;',
            'animation:booadmin-preview-spin .7s linear infinite}',
            '@keyframes booadmin-preview-spin{to{transform:rotate(360deg)}}',
            '@media(prefers-reduced-motion:reduce){',
            '.preview-overlay .preview-iframe,.preview-overlay .preview-loading{transition:none}',
            '.preview-overlay .preview-spinner{animation:none}',
            '}'
        ].join('');
        document.head.appendChild(style);
    }

    function togglePreviewFullScreen() {
        if (!previewState.isOpen || isNarrowPreviewViewport()) {
            return;
        }

        previewState.isFullScreen = !previewState.isFullScreen;
        syncPreviewLayout();
    }

    function syncPreviewLayout() {
        if (!previewState.container) {
            return;
        }

        const shouldFullScreen = isNarrowPreviewViewport() || previewState.isFullScreen;
        previewState.container
            .toggleClass('w-full h-full rounded-none', shouldFullScreen)
            .toggleClass('md:w-11/12 md:h-5/6', !shouldFullScreen);

        if (!previewState.fullScreenButton) {
            return;
        }

        const hideToggle = isNarrowPreviewViewport();
        const label = '<?php _e('切换全屏预览'); ?>';

        previewState.fullScreenButton
            .toggleClass('hidden', hideToggle)
            .attr('title', label)
            .attr('aria-label', label);
    }

    function closePreview() {
        if (previewState.frame) {
            previewState.frame.off('load');
            previewState.frame.attr('src', 'about:blank');
        }

        if (previewState.overlay) {
            previewState.overlay.remove();
        }

        previewState.overlay = null;
        previewState.container = null;
        previewState.frame = null;
        previewState.loading = null;
        previewState.fullScreenButton = null;
        previewState.modeGroup = null;
        previewState.printButton = null;
        previewState.cid = 0;
        previewState.mode = 'native';
        previewState.isOpen = false;
        previewState.isFullScreen = false;

        $(document).off('.writePreview');
        $(window).off('.writePreview');
    }

    function bindPreviewEvents() {
        $(document).off('keydown.writePreview').on('keydown.writePreview', function (e) {
            if (!previewState.isOpen) {
                return;
            }

            if (e.key === 'Escape') {
                if (!isNarrowPreviewViewport() && previewState.isFullScreen) {
                    previewState.isFullScreen = false;
                    syncPreviewLayout();
                } else {
                    closePreview();
                }
            } else if (e.key === 'M' && (e.ctrlKey || e.metaKey) && e.shiftKey) {
                e.preventDefault();
                setPreviewMode(previewState.mode === 'native' ? 'print' : 'native');
            } else if (e.key === 'F11' && !isNarrowPreviewViewport()) {
                e.preventDefault();
                togglePreviewFullScreen();
            }
        });

        $(window).off('resize.writePreview').on('resize.writePreview', function () {
            if (!previewState.isOpen) {
                return;
            }

            if (isNarrowPreviewViewport()) {
                previewState.isFullScreen = true;
            }

            syncPreviewLayout();
        });
    }

    function openPreview(previewCid) {
        if (!previewCid) {
            return;
        }

        closePreview();
        ensurePreviewStyles();
        previewState.isOpen = true;
        previewState.isFullScreen = isNarrowPreviewViewport();

        const overlay = $('<div class="fixed inset-0 z-50 bg-black/75 flex items-center justify-center preview-overlay"></div>');
        const container = $('<div class="bg-white w-full h-full md:w-11/12 md:h-5/6 shadow-2xl relative flex flex-col overflow-hidden"></div>');
        const header = $('<div class="h-12 bg-gray-100 border-b border-gray-200 flex items-center justify-between px-4 flex-shrink-0"></div>');
        const title = $('<h3 class="font-bold text-gray-700 text-sm"><i class="fas fa-eye mr-2"></i><?php _e('文章预览'); ?></h3>');
        const headerButtons = $('<div class="flex items-center space-x-2"></div>');

        // 模式切换工具栏挂在父页面：后台样式与前台主题完全隔离，不受主题 CSS/CSP 干扰
        const modeGroup = $('<div class="inline-flex items-center border border-gray-300 bg-white overflow-hidden" role="group" aria-label="<?php _e('预览模式'); ?>"></div>');
        const nativeModeButton = $('<button type="button" class="px-3 py-1 text-xs transition-colors" data-preview-mode="native" aria-pressed="false"><?php _e('原生预览'); ?></button>');
        const printModeButton = $('<button type="button" class="px-3 py-1 text-xs transition-colors" data-preview-mode="print" aria-pressed="false"><?php _e('打印预览'); ?></button>');
        modeGroup.append(nativeModeButton, printModeButton);

        const printButton = $('<button type="button" class="px-3 py-1 text-xs border border-gray-300 bg-white text-gray-600 flex items-center transition-colors"><i class="fas fa-print mr-1"></i><?php _e('打印'); ?></button>');
        const fullScreenButton = $('<button type="button" class="preview-fullscreen-btn text-gray-500 hover:text-discord-accent focus:outline-none" title="<?php _e('切换全屏预览'); ?>" aria-label="<?php _e('切换全屏预览'); ?>"><i class="fas fa-expand"></i></button>');
        const closeButton = $('<button type="button" class="text-gray-500 hover:text-red-500 focus:outline-none" title="<?php _e('关闭'); ?>" aria-label="<?php _e('关闭'); ?>"><i class="fas fa-times"></i></button>');
        const body = $('<div class="relative flex-1 min-h-0 bg-white"></div>');
        const loading = $('<div class="preview-loading absolute inset-0 flex flex-col items-center justify-center bg-white text-sm text-gray-500"><div class="preview-spinner mb-3"></div><span><?php _e('正在加载预览...'); ?></span></div>');
        const frame = $('<iframe frameborder="0" class="preview-iframe preview-frame-loading w-full h-full bg-white" loading="eager"></iframe>');

        nativeModeButton.on('click', function () { setPreviewMode('native'); });
        printModeButton.on('click', function () { setPreviewMode('print'); });
        printButton.on('click', function () {
            const frameWindow = previewState.frame && previewState.frame[0]
                ? previewState.frame[0].contentWindow : null;
            if (!frameWindow) {
                return;
            }
            try {
                frameWindow.focus();
                frameWindow.print();
            } catch (err) {
                window.print();
            }
        });
        fullScreenButton.on('click', togglePreviewFullScreen);
        closeButton.on('click', closePreview);
        frame.on('load', function () {
            // 加载完成：淡出遮罩、淡入 iframe（遮罩保留在 DOM 中供模式切换复用）
            if (previewState.loading) {
                previewState.loading.stop(true, true).fadeOut(180);
            }
            previewState.frame.removeClass('preview-frame-loading');
        });

        headerButtons.append(modeGroup, printButton, fullScreenButton, closeButton);
        header.append(title, headerButtons);
        body.append(loading, frame);
        container.append(header, body);
        overlay.append(container).appendTo(document.body);
        overlay.hide().fadeIn(160);

        previewState.overlay = overlay;
        previewState.container = container;
        previewState.frame = frame;
        previewState.loading = loading;
        previewState.fullScreenButton = fullScreenButton;
        previewState.modeGroup = modeGroup;
        previewState.printButton = printButton;
        previewState.cid = previewCid;
        previewState.mode = 'native';

        syncPreviewLayout();
        syncPreviewModeButtons();
        bindPreviewEvents();

        frame.attr('src', getPreviewUrl(previewCid, previewState.mode));
    }

    btnPreview.on('click', function () {
        if (changed) {
            previewState.saveCallback = function () {
                Typecho.savePost(function () {
                    openPreview(resolvePreviewCid());
                });
            };

            BooAdmin.confirm({
                title: '<?php _e('确认保存'); ?>',
                message: '<?php _e('修改后的内容需要保存后才能预览, 是否保存?'); ?>',
                confirmText: '<?php _e('确认保存'); ?>',
                onConfirm: function () {
                    const callback = previewState.saveCallback;
                    previewState.saveCallback = null;
                    if (callback) {
                        callback();
                    }
                }
            });
            return;
        }

        openPreview(resolvePreviewCid());
    });

    // 静态元素可直接绑定；动态元素如需再改用事件委托
    $(document).on('click', '.typecho-option-tabs button, .typecho-option-tabs li', function() {
        const $this = $(this);
        const isButton = $this.is('button');
        
        if (isButton) {
            // 新结构：按钮位于 flex 容器内
            const group = $this.parent();
            // 重置分组内全部按钮样式
            group.find('button').removeClass('text-discord-text bg-white shadow-sm').addClass('text-gray-500 hover:text-discord-text');
            // 激活当前点击的按钮
            $this.removeClass('text-gray-500 hover:text-discord-text').addClass('text-discord-text bg-white shadow-sm');
        } else {
            // 旧结构：li 位于 ul 内
            $this.siblings().removeClass('active');
            $this.addClass('active');
        }
        
        // 隐藏所有可能的标签页内容容器
        let targetSelector;
        if (isButton) {
            targetSelector = $this.data('target');
        } else {
            targetSelector = $this.find('a').attr('href');
        }
        
        if (targetSelector) {
            // targetSelector 来自 data-target / a[href]，属于不可信字符串。
            // 只允许「本页已知 tab」或「纯 #id 形式」的选择器，杜绝把
            // 含 : [ ( 空格 等的内容交给 $() 解析（选择器注入 / 意外全局选中）。
            // 放宽到纯 #id 是为了兼容沿用原生 li>a 结构的第三方插件。
            var selector = String(targetSelector);
            var knownTabs = {'#tab-advance': 1, '#tab-files': 1};
            var isSafeId = /^#[A-Za-z0-9_-]+$/.test(selector);
            if (!Object.prototype.hasOwnProperty.call(knownTabs, selector) && !isSafeId) {
                return false;
            }
            $('#tab-advance, #tab-files').addClass('hidden');
            $('.tab-content').addClass('hidden');
            $(selector).removeClass('hidden');
        }

        return false;
    });

    // 自动隐藏密码框
    $('#visibility').change(function () {
        const val = $(this).val(), password = $('#post-password');

        if ('password' === val) {
            password.removeClass('hidden');
        } else {
            password.addClass('hidden');
        }
    });
    
    // 草稿删除确认
    $('.edit-draft-notice a').click(function () {
        var href = $(this).attr('href');
        BooAdmin.confirm({
            title: '<?php _e('确认删除'); ?>',
            message: '<?php _e('您确认要删除这份草稿吗?'); ?>',
            confirmText: '<?php _e('确认删除'); ?>',
            onConfirm: function () {
                if (href) {
                    window.location.href = href;
                }
            }
        });
        return false;
    });

    // 事件已绑定，解除这些操作链接的原生跳转保护
    if (window.booadminArmLinks) {
        booadminArmLinks();
    }
});
</script>

