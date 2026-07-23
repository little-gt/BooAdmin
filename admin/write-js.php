<?php if(!defined('__TYPECHO_ADMIN__')) exit; ?>
<?php \Typecho\Plugin::factory('admin/write-js.php')->call('write'); ?>
<?php \Widget\Metas\Tag\Cloud::alloc('sort=count&desc=1&limit=200')->to($tags); ?>

<script src="<?php $options->adminStaticUrl('js', 'timepicker.js'); ?>"></script>
<script src="<?php $options->adminStaticUrl('js', 'tokeninput.js'); ?>"></script>
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

    // text 自动拉伸
    Typecho.editorResize('text', '<?php echo booadminActionUrl('/action/ajax?do=editorResize'); ?>');

    // tag autocomplete 提示
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
                // remove special chars
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

        // tag autocomplete 提示宽度设置
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
        form.addClass('submitting');
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
    const previewSaveModal = $('#preview-save-confirm-modal');
    const previewState = {
        overlay: null,
        container: null,
        frame: null,
        loading: null,
        fullScreenButton: null,
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

    function getPreviewUrl(previewCid) {
        return './preview.php?cid=' + encodeURIComponent(previewCid);
    }

    function hidePreviewSaveConfirm() {
        previewSaveModal.addClass('hidden');
        previewState.saveCallback = null;
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
        previewState.isOpen = true;
        previewState.isFullScreen = isNarrowPreviewViewport();

        const overlay = $('<div class="fixed inset-0 z-50 bg-black/75 flex items-center justify-center preview-overlay"></div>');
        const container = $('<div class="bg-white w-full h-full md:w-11/12 md:h-5/6 shadow-2xl relative flex flex-col overflow-hidden"></div>');
        const header = $('<div class="h-12 bg-gray-100 border-b border-gray-200 flex items-center justify-between px-4 flex-shrink-0"></div>');
        const title = $('<h3 class="font-bold text-gray-700 text-sm"><i class="fas fa-eye mr-2"></i><?php _e('文章预览'); ?></h3>');
        const headerButtons = $('<div class="flex items-center space-x-2"></div>');
        const fullScreenButton = $('<button type="button" class="preview-fullscreen-btn text-gray-500 hover:text-discord-accent focus:outline-none" title="<?php _e('切换全屏预览'); ?>" aria-label="<?php _e('切换全屏预览'); ?>"><i class="fas fa-expand"></i></button>');
        const closeButton = $('<button type="button" class="text-gray-500 hover:text-red-500 focus:outline-none" title="<?php _e('关闭'); ?>" aria-label="<?php _e('关闭'); ?>"><i class="fas fa-times"></i></button>');
        const body = $('<div class="relative flex-1 min-h-0 bg-white"></div>');
        const loading = $('<div class="absolute inset-0 flex items-center justify-center bg-white text-sm text-gray-500"><span><?php _e('正在加载预览...'); ?></span></div>');
        const frame = $('<iframe frameborder="0" class="w-full h-full bg-white" loading="eager"></iframe>');

        fullScreenButton.on('click', togglePreviewFullScreen);
        closeButton.on('click', closePreview);
        frame.on('load', function () {
            if (previewState.loading) {
                previewState.loading.remove();
                previewState.loading = null;
            }
        });

        headerButtons.append(fullScreenButton, closeButton);
        header.append(title, headerButtons);
        body.append(loading, frame);
        container.append(header, body);
        overlay.append(container).appendTo(document.body);

        previewState.overlay = overlay;
        previewState.container = container;
        previewState.frame = frame;
        previewState.loading = loading;
        previewState.fullScreenButton = fullScreenButton;

        syncPreviewLayout();
        bindPreviewEvents();

        frame.attr('src', getPreviewUrl(previewCid));
    }

    btnPreview.on('click', function () {
        if (changed) {
            previewState.saveCallback = function () {
                Typecho.savePost(function () {
                    openPreview(resolvePreviewCid());
                });
            };

            previewSaveModal.removeClass('hidden');
            return;
        }

        openPreview(resolvePreviewCid());
    });

    $('#cancel-preview-save').on('click', hidePreviewSaveConfirm);

    $('#confirm-preview-save').on('click', function () {
        const callback = previewState.saveCallback;

        hidePreviewSaveConfirm();

        if (callback) {
            callback();
        }
    });

    previewSaveModal.on('click', function (e) {
        if (e.target === this) {
            hidePreviewSaveConfirm();
        }
    });

    // Use 'on' for dynamic elements if needed, but direct click is fine for static
    $(document).on('click', '.typecho-option-tabs button, .typecho-option-tabs li', function() {
        const $this = $(this);
        const isButton = $this.is('button');
        
        if (isButton) {
            // New structure: button inside a flex container
            const group = $this.parent();
            // Reset all buttons in the group
            group.find('button').removeClass('text-discord-text bg-white shadow-sm').addClass('text-gray-500 hover:text-discord-text');
            // Activate clicked button
            $this.removeClass('text-gray-500 hover:text-discord-text').addClass('text-discord-text bg-white shadow-sm');
        } else {
            // Old structure: li inside ul
            $this.siblings().removeClass('active');
            $this.addClass('active');
        }
        
        // Hide all potential tab content containers
        let targetSelector;
        if (isButton) {
            targetSelector = $this.data('target');
        } else {
            targetSelector = $this.find('a').attr('href');
        }
        
        if (targetSelector) {
            $('#tab-advance, #tab-files').addClass('hidden'); 
            $('.tab-content').addClass('hidden'); 
            $(targetSelector).removeClass('hidden');
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
    
    // Draft delete confirmation modal
    var draftDeleteHref = null;
    $('.edit-draft-notice a').click(function () {
        draftDeleteHref = $(this).attr('href');
        $('#draft-delete-confirm-modal').removeClass('hidden');
        return false;
    });

    $('#cancel-draft-delete').click(function () {
        $('#draft-delete-confirm-modal').addClass('hidden');
        draftDeleteHref = null;
    });

    $('#confirm-draft-delete').click(function () {
        if (draftDeleteHref) {
            window.location.href = draftDeleteHref;
        }
        $('#draft-delete-confirm-modal').addClass('hidden');
    });

    // Close modal when clicking outside
    $('#draft-delete-confirm-modal').click(function (e) {
        if (e.target === this) {
            $('#draft-delete-confirm-modal').addClass('hidden');
            draftDeleteHref = null;
        }
    });
});
</script>
<!-- Preview Save Confirm Modal -->
<div id="preview-save-confirm-modal" class="booadmin-modal hidden">
    <div class="booadmin-dialog booadmin-dialog-sm">
        <h3 class="text-lg font-bold text-discord-text mb-4"><?php _e('确认保存'); ?></h3>
        <p class="text-discord-muted mb-6"><?php _e('修改后的内容需要保存后才能预览, 是否保存?'); ?></p>
        <div class="flex justify-end space-x-3">
            <button id="cancel-preview-save" class="px-4 py-2 bg-gray-200 text-discord-text font-medium hover:bg-gray-300 transition-colors text-sm">
                <?php _e('取消'); ?>
            </button>
            <button id="confirm-preview-save" class="px-4 py-2 bg-discord-accent text-white font-medium hover:bg-blue-600 transition-colors text-sm">
                <?php _e('确认保存'); ?>
            </button>
        </div>
    </div>
</div>
<!-- Draft Delete Confirm Modal -->
<div id="draft-delete-confirm-modal" class="booadmin-modal hidden">
    <div class="booadmin-dialog booadmin-dialog-sm">
        <h3 class="text-lg font-bold text-discord-text mb-4"><?php _e('确认删除'); ?></h3>
        <p class="text-discord-muted mb-6"><?php _e('您确认要删除这份草稿吗?'); ?></p>
        <div class="flex justify-end space-x-3">
            <button id="cancel-draft-delete" class="px-4 py-2 bg-gray-200 text-discord-text font-medium hover:bg-gray-300 transition-colors text-sm">
                <?php _e('取消'); ?>
            </button>
            <button id="confirm-draft-delete" class="px-4 py-2 bg-discord-accent text-white font-medium hover:bg-blue-600 transition-colors text-sm">
                <?php _e('确认删除'); ?>
            </button>
        </div>
    </div>
</div>

