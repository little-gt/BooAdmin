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

    function fileUploadStart (file) {
        $('<li id="' + file.id + '" class="loading group flex items-center justify-between p-2 bg-white border border-gray-200 hover:border-discord-accent transition-colors">' +
            '<span class="text-sm text-gray-500 flex items-center"><i class="fas fa-spinner fa-spin mr-2 text-discord-accent"></i> ' + file.name + '</span></li>').appendTo('#file-list');
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
            li = exist.removeClass('loading').html('<span class="text-red-500 text-sm">' + fileError + '</span><span class="text-xs text-gray-400 ml-2">' + word + '</span>');
        } else {
            li = $('<li class="p-2 bg-red-50 border border-red-200 text-sm">' + fileError + '<br /><span class="text-xs text-gray-500">' + word + '</span></li>').appendTo('#file-list');
        }

        const highlightDanger = getComputedStyle(document.documentElement).getPropertyValue('--booadmin-highlight-danger').trim() || '#FBC2C4';
        li.effect('highlight', {color : highlightDanger}, 2000, function () {
            $(this).remove();
        });
    }

    function fileUploadComplete (file, attachment) {
        const li = $('#' + file.id).removeClass('loading').addClass('group flex items-center justify-between p-2 bg-white border border-gray-200 hover:border-discord-accent transition-colors').data('cid', attachment.cid)
            .data('url', attachment.url)
            .data('image', attachment.isImage)
            .html('<input type="hidden" name="attachment[]" value="' + attachment.cid + '" />' +
                '<a class="insert flex-1 text-sm text-discord-text hover:text-discord-accent truncate mr-2" target="_blank" href="###" title="<?php _e('点击插入文件'); ?>">' +
                '<i class="far fa-file mr-2 text-gray-400"></i>' + attachment.title + '</a>' +
                '<div class="info text-xs text-gray-400 flex items-center space-x-2">' + attachment.bytes +
                ' <a class="file text-gray-400 hover:text-discord-accent" target="_blank" href="<?php $options->adminUrl('media.php'); ?>?cid=' +
                attachment.cid + '" title="<?php _e('编辑'); ?>"><i class="fas fa-edit"></i></a>' +
                ' <a class="delete text-gray-400 hover:text-red-500" href="###" title="<?php _e('删除'); ?>"><i class="fas fa-trash-alt"></i></a></div>')
            .effect('highlight', 1000);

        attachInsertEvent(li);
        attachDeleteEvent(li);
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
    function showImagePreview(url, title, callback) {
        // 移除已存在的预览模态框
        $('#image-preview-modal').remove();
        
        // 创建预览模态框
        var previewModal = $('<div id="image-preview-modal" class="fixed inset-0 bg-black bg-opacity-75 z-50 flex items-center justify-center p-4"></div>')
            .appendTo('body')
            .click(function(e) {
                if ($(e.target).is('#image-preview-modal')) {
                    previewModal.remove();
                }
            });
        
        var modalContent = $('<div class="bg-white max-w-4xl w-full max-h-[90vh] flex flex-col shadow-xl"></div>')
            .appendTo(previewModal);
        
        // 模态框头部
        var modalHeader = $('<div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between"></div>')
            .appendTo(modalContent);
        
        $('<h3 class="text-lg font-bold text-discord-text"><?php _e('图片预览'); ?></h3>')
            .appendTo(modalHeader);
        
        $('<button class="text-gray-400 hover:text-gray-600 transition-colors" title="<?php _e('关闭'); ?>"><i class="fas fa-times"></i></button>')
            .click(function() {
                previewModal.remove();
            })
            .appendTo(modalHeader);
        
        // 模态框内容
        var modalBody = $('<div class="flex-1 flex items-center justify-center p-6 overflow-auto bg-gray-50"></div>')
            .appendTo(modalContent);
        
        $('<img src="' + url + '" class="max-w-full max-h-[60vh] object-contain shadow-sm" alt="' + title + '">')
            .appendTo(modalBody);
        
        // 模态框底部
        var modalFooter = $('<div class="px-6 py-4 border-t border-gray-200 bg-white"></div>')
            .appendTo(modalContent);
        
        var footerContent = $('<div class="flex items-center justify-between"></div>')
            .appendTo(modalFooter);
        
        // 图片URL显示框
        var urlContainer = $('<div class="flex-1 mr-4"></div>')
            .appendTo(footerContent);
        
        $('<input type="text" value="' + url + '" class="w-full px-3 py-2 bg-gray-100 border border-gray-300 text-sm text-gray-800 focus:outline-none" readonly>')
            .appendTo(urlContainer);
        
        // 按钮组
        var buttonContainer = $('<div class="flex space-x-3"></div>')
            .appendTo(footerContent);
        
        $('<button class="px-4 py-2 bg-gray-200 text-discord-text hover:bg-gray-300 transition-colors text-sm font-medium"><?php _e('取消'); ?></button>')
            .click(function() {
                previewModal.remove();
            })
            .appendTo(buttonContainer);
        
        $('<button class="px-4 py-2 bg-discord-accent text-white hover:bg-blue-600 transition-colors text-sm font-medium"><?php _e('插入图片'); ?></button>')
            .click(function() {
                previewModal.remove();
                callback();
            })
            .appendTo(buttonContainer);
    }

    // File delete confirmation modal
    var fileDeleteData = null;
    var fileDeleteEl = null;

    function attachDeleteEvent (el) {
        var file = $('a.insert', el).text();
        $('.delete', el).click(function () {
            fileDeleteEl = el;
            fileDeleteData = {
                cid: $(this).parents('li').data('cid'),
                file: file
            };
            $('#file-delete-confirm-message').text('<?php _e('确认要删除文件 %s 吗?'); ?>'.replace('%s', file));
            $('#file-delete-confirm-modal').removeClass('hidden');
            return false;
        });
    }

    $('#file-list li').each(function () {
        attachInsertEvent(this);
        attachDeleteEvent(this);
    });

    // File delete modal handlers
    $('#cancel-file-delete').click(function () {
        $('#file-delete-confirm-modal').addClass('hidden');
        fileDeleteData = null;
        fileDeleteEl = null;
    });

    $('#confirm-file-delete').click(function () {
        if (fileDeleteData && fileDeleteEl) {
            var $modal = $('#file-delete-confirm-modal');
            var $confirmBtn = $('#confirm-file-delete');
            
            // 禁用按钮防止重复点击
            $confirmBtn.prop('disabled', true).addClass('opacity-50');
            
            $.post('<?php $security->index('/action/contents-attachment-edit'); ?>',
                {'do' : 'delete', 'cid' : fileDeleteData.cid},
                function (response) {
                    if (response && response.success !== false) {
                        // 显示成功通知
                        if (window.TypechoNotification) {
                            TypechoNotification.success('<?php _e('文件已删除'); ?>');
                        }
                        
                        // 移除文件项
                        $(fileDeleteEl).fadeOut(function () {
                            $(this).remove();
                            updateAttachmentNumber();
                        });
                        
                        // 恢复按钮状态
                        $confirmBtn.prop('disabled', false).removeClass('opacity-50');
                        
                        // 关闭模态框并重置状态
                        $modal.addClass('hidden');
                        fileDeleteData = null;
                        fileDeleteEl = null;
                    } else {
                        // 显示错误通知
                        if (window.TypechoNotification) {
                            TypechoNotification.error(response.message || '<?php _e('删除失败，请重试'); ?>');
                        }
                        $confirmBtn.prop('disabled', false).removeClass('opacity-50');
                    }
                },
                'json'
            ).fail(function() {
                // 网络错误
                if (window.TypechoNotification) {
                    TypechoNotification.error('<?php _e('网络错误，删除失败'); ?>');
                }
                $confirmBtn.prop('disabled', false).removeClass('opacity-50');
            });
        } else {
            $('#file-delete-confirm-modal').addClass('hidden');
        }
    });

    // Close modal when clicking outside
    $('#file-delete-confirm-modal').click(function (e) {
        if (e.target === this) {
            $('#file-delete-confirm-modal').addClass('hidden');
            fileDeleteData = null;
            fileDeleteEl = null;
        }
    });
});
</script>
<!-- File Delete Confirm Modal -->
<div id="file-delete-confirm-modal" class="booadmin-modal hidden">
    <div class="booadmin-dialog booadmin-dialog-sm">
        <h3 class="text-lg font-bold text-discord-text mb-4"><?php _e('确认删除'); ?></h3>
        <p id="file-delete-confirm-message" class="text-discord-muted mb-6"></p>
        <div class="flex justify-end space-x-3">
            <button id="cancel-file-delete" class="px-4 py-2 bg-gray-200 text-discord-text font-medium hover:bg-gray-300 transition-colors text-sm">
                <?php _e('取消'); ?>
            </button>
            <button id="confirm-file-delete" class="px-4 py-2 bg-discord-accent text-white font-medium hover:bg-blue-600 transition-colors text-sm">
                <?php _e('确认删除'); ?>
            </button>
        </div>
    </div>
</div>

