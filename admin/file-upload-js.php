<?php if(!defined('__TYPECHO_ADMIN__')) exit; ?>
<?php
$phpMaxFilesize = function_exists('ini_get') ? trim(ini_get('upload_max_filesize')) : '0';

if (preg_match("/^([0-9]+)([a-z]{1,2})?$/i", $phpMaxFilesize, $matches)) {
    $size = intval($matches[1]);
    $unit = $matches[2] ?? 'b';

    $phpMaxFilesize = round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
}
?>

<script>
$(document).ready(function() {
    function updateAttachmentNumber () {
        var btn = $('#tab-files-btn'),
            balloon = $('.balloon', btn),
            count = $('#file-list li .insert').length;

        if (count > 0) {
            if (!balloon.length) {
                btn.html($.trim(btn.html()) + ' ');
                balloon = $('<span class="balloon"></span>').appendTo(btn);
            }

            balloon.html(count);
        } else if (0 === count && balloon.length > 0) {
            balloon.remove();
        }
    }

    updateAttachmentNumber();

    const uploadUrl = $('.upload-area').bind({
        dragenter   :   function (e) {
            $(this).parent().addClass('drag');
        },

        dragover    :   function (e) {
            e.stopPropagation();
            e.preventDefault();
            $(this).parent().addClass('drag');
        },

        drop        :   function (e) {
            e.stopPropagation();
            e.preventDefault();
            $(this).parent().removeClass('drag');

            const files = e.originalEvent.dataTransfer.files;

            if (files.length === 0) {
                return;
            }

            for (const file of files) {
                Typecho.uploadFile(file);
            }
        },

        dragend     :   function () {
            $(this).parent().removeClass('drag');
        },

        dragleave   :   function () {
            $(this).parent().removeClass('drag');
        }
    }).data('url');

    const btn = $('.upload-file');
    const fileInput = $('<input type="file" name="file" />').hide().insertAfter(btn);

    btn.click(function () {
        fileInput.click();
        return false;
    });

    fileInput.change(function () {
        if (this.files.length === 0) {
            return;
        }

        Typecho.uploadFile(this.files[0]);
    });

    // 文件名由用户输入，统一用 text 写入，避免拼接进 HTML 造成注入
    function fileUploadStart (file) {
        var $li = $('<li class="loading group flex items-center justify-between p-2 bg-white border border-gray-200 hover:border-discord-accent transition-colors"></li>')
            .attr('id', file.id);
        var $label = $('<span class="text-sm text-gray-500 flex items-center"></span>')
            .append($('<i class="fas fa-spinner fa-spin mr-2 text-discord-accent"></i>'))
            .append($('<span></span>').text(' ' + file.name));

        $li.append($label).appendTo('#file-list');
    }

    function fileUploadError (type, file) {
        let word = '<?php _e('上传出现错误'); ?>';
        
        switch (type) {
            case 'size':
                word = '<?php _e('文件大小超过限制'); ?>';
                break;
            case 'type':
                word = '<?php _e('文件扩展名不被支持'); ?>';
                break;
            case 'duplicate':
                word = '<?php _e('文件已经上传过'); ?>';
                break;
            case 'network':
            default:
                break;
        }

        var fileError = '<?php _e('%s 上传失败'); ?>'.replace('%s', file.name),
            li, exist = $('#' + file.id);

        if (exist.length > 0) {
            li = exist.removeClass('loading').empty()
                .append($('<span class="text-red-500 text-sm"></span>').text(fileError))
                .append($('<span class="text-xs text-gray-400 ml-2"></span>').text(word));
        } else {
            li = $('<li class="p-2 bg-red-50 border border-red-200 text-sm"></li>')
                .append($('<span></span>').text(fileError))
                .append('<br />')
                .append($('<span class="text-xs text-gray-500"></span>').text(word))
                .appendTo('#file-list');
        }

        const highlightDanger = getComputedStyle(document.documentElement).getPropertyValue('--booadmin-highlight-danger').trim() || '#FBC2C4';
        li.effect('highlight', {color : highlightDanger}, 2000, function () {
            $(this).remove();
        });
    }

    // 附件标题、体积等来自服务端返回，同样用 text / attr 写入，避免拼接注入
    function fileUploadComplete (file, attachment) {
        var $li = $('#' + file.id)
            .removeClass('loading')
            .addClass('group flex items-center justify-between p-2 bg-white border border-gray-200 hover:border-discord-accent transition-colors')
            .data('cid', attachment.cid)
            .data('url', attachment.url)
            .data('image', attachment.isImage)
            .empty();

        $('<input type="hidden" name="attachment[]">').val(attachment.cid).appendTo($li);

        var $link = $('<a class="insert flex-1 text-sm text-discord-text hover:text-discord-accent truncate mr-2" target="_blank" href="###"></a>')
            .attr('title', '<?php _e('点击插入文件'); ?>')
            .append($('<i class="far fa-file mr-2 text-gray-400"></i>'))
            .append($('<span></span>').text(attachment.title));
        $li.append($link);

        var $info = $('<div class="info text-xs text-gray-400 flex items-center space-x-2"></div>')
            .append($('<span></span>').text(attachment.bytes))
            .append(' ')
            .append($('<a class="file text-gray-400 hover:text-discord-accent" target="_blank"></a>')
                .attr('href', '<?php $options->adminUrl('media.php'); ?>?cid=' + encodeURIComponent(attachment.cid))
                .attr('title', '<?php _e('编辑'); ?>')
                .append($('<i class="fas fa-edit"></i>')))
            .append(' ')
            .append($('<a class="delete text-gray-400 hover:text-red-500" href="###"></a>')
                .attr('title', '<?php _e('删除'); ?>')
                .append($('<i class="fas fa-trash-alt"></i>')));
        $li.append($info);

        $li.effect('highlight', 1000);

        attachInsertEvent($li);
        attachDeleteEvent($li);
        updateAttachmentNumber();

        Typecho.uploadComplete(attachment);
    }

    Typecho.uploadFile = (function () {
        const types = '<?php echo json_encode($options->allowedAttachmentTypes); ?>';
        const maxSize = <?php echo $phpMaxFilesize ?>;
        const queue = [];
        let index = 0;

        const getUrl = function () {
            const url = new URL(uploadUrl);
            const cid = $('input[name=cid]').val();

            url.searchParams.append('cid', cid);
            return url.toString();
        };

        const upload = function () {
            const file = queue.shift();

            if (!file) {
                return;
            }

            const data = new FormData();
            data.append('file', file);

            fetch(getUrl(), {
                method: 'POST',
                body: data
            }).then(function (response) {
                if (response.ok) {
                    return response.json();
                } else {
                    throw new Error(response.statusText);
                }
            }).then(function (data) {
                if (data) {
                    const [_, attachment] = data;
                    fileUploadComplete(file, attachment);
                    upload();
                } else {
                    throw new Error('no data');
                }
            }).catch(function (error) {
                fileUploadError('network', file);
                upload();
            });
        };

        return function (file) {
            file.id = 'upload-' + (index++);

            if (file.size > maxSize) {
                return fileUploadError('size', file);
            }

            const match = file.name.match(/\.([a-z0-9]+)$/i);
            if (!match || types.indexOf(match[1].toLowerCase()) < 0) {
                return fileUploadError('type', file);
            }

            queue.push(file);
            fileUploadStart(file);
            upload();
        };
    })();

    function attachInsertEvent (el) {
        $('.insert', el).click(function () {
            var t = $(this), p = t.parents('li');
            var isImage = p.data('image');
            var url = p.data('url');
            var title = $.trim(t.text());
            
            if (isImage) {
                // 显示图片预览
                showImagePreview(url, title, function() {
                    // 图片预览后直接插入，跳过对话框
                    Typecho.insertFileToEditor(title, url, isImage, true);
                });
            } else {
                // 非图片文件使用原有逻辑
                Typecho.insertFileToEditor(title, url, isImage);
            }
            return false;
        });
    }

    // 显示图片预览模态框
    // 用 DOM 方式构建正文：url / title 通过 attr / val / text 写入，
    // 避免文件名含引号或尖括号时破坏属性、造成 HTML 注入
    function showImagePreview(url, title, callback) {
        var $body = $('<div></div>');

        var $previewWrap = $('<div class="flex-1 flex items-center justify-center p-6 overflow-auto bg-gray-50"></div>');
        $('<img class="max-w-full max-h-[60vh] object-contain shadow-sm">')
            .attr('src', url)
            .attr('alt', title)
            .appendTo($previewWrap);

        var $footer = $('<div class="px-6 py-4 border-t border-gray-200 bg-white flex items-center justify-between"></div>');
        var $urlWrap = $('<div class="flex-1 mr-4"></div>');
        // 必须用 attr 而非 val()：$body.html() 走 innerHTML 序列化，
        // val() 只改 property 不会被输出，会导致预览里的链接显示为空
        $('<input type="text" class="w-full px-3 py-2 bg-gray-100 border border-gray-300 text-sm text-gray-800 focus:outline-none" readonly>')
            .attr('value', url)
            .appendTo($urlWrap);
        $footer.append($urlWrap);

        $body.append($previewWrap, $footer);

        BooAdmin.Modal.create({
            id: 'booadmin-image-preview-modal',  // 固定 id 复用同一实例，避免 DOM 与事件累积
            title: '<?php _e('图片预览'); ?>',
            message: $body.html(),
            size: 'md',
            confirmText: '<?php _e('插入图片'); ?>',
            cancelText: '<?php _e('取消'); ?>',
            onConfirm: callback
        }).open();
    }

    // 文件删除确认
    function attachDeleteEvent (el) {
        var file = $('a.insert', el).text();
        $('.delete', el).click(function () {
            var cid = $(this).parents('li').data('cid');
            var $deleteEl = $(el);
            var deleting = false;

            var modal = BooAdmin.confirm({
                title: '<?php _e('确认删除'); ?>',
                message: '<?php _e('确认要删除文件 %s 吗?'); ?>'.replace('%s', file),
                confirmText: '<?php _e('确认删除'); ?>',
                onConfirm: function () {
                    if (deleting) {
                        return false;   // 防重入：上一个删除请求未完成时忽略重复点击
                    }
                    deleting = true;
                    modal.el.find('.booadmin-modal-confirm').prop('disabled', true).addClass('opacity-50');

                    $.post('<?php $security->index('/action/contents-attachment-edit'); ?>',
                        {'do' : 'delete', 'cid' : cid},
                        function (response) {
                            if (response && response.success !== false) {
                                if (window.TypechoNotification) {
                                    TypechoNotification.success('<?php _e('文件已删除'); ?>');
                                }
                                $deleteEl.fadeOut(function () {
                                    $(this).remove();
                                    updateAttachmentNumber();
                                });
                            } else {
                                if (window.TypechoNotification) {
                                    TypechoNotification.error(response.message || '<?php _e('删除失败，请重试'); ?>');
                                }
                            }
                            modal.close();
                        },
                        'json'
                    ).fail(function() {
                        if (window.TypechoNotification) {
                            TypechoNotification.error('<?php _e('网络错误，删除失败'); ?>');
                        }
                        modal.close();
                    });
                    return false; // 请求完成前保持弹窗打开
                }
            });
            return false;
        });
    }

    $('#file-list li').each(function () {
        attachInsertEvent(this);
        attachDeleteEvent(this);
    });
});
</script>

