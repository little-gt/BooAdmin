<?php if(!defined('__TYPECHO_ADMIN__')) exit; ?>
<script src="<?php $options->adminStaticUrl('js', 'purify.js'); ?>"></script>
<script>
(function () {
    $(document).ready(function () {
        var sharedConfirmModal = $('#booadmin-table-confirm-modal');
        var sharedConfirmMessage;
        var sharedConfirmCancel;
        var sharedConfirmSubmit;
        var pendingAction = null;

        function ensureSharedConfirmModal() {
            if (sharedConfirmModal.length > 0) {
                return;
            }

            sharedConfirmModal = $(
                '<div id="booadmin-table-confirm-modal" class="booadmin-modal hidden" role="dialog" aria-modal="true" aria-labelledby="booadmin-table-confirm-title">' +
                    '<div class="booadmin-dialog booadmin-dialog-sm">' +
                        '<h3 id="booadmin-table-confirm-title" class="text-lg font-bold text-discord-text mb-4"><?php _e('操作确认'); ?></h3>' +
                        '<p id="booadmin-table-confirm-message" class="text-discord-muted mb-6"><?php _e('请确认是否继续。'); ?></p>' +
                        '<div class="flex justify-end space-x-3">' +
                            '<button id="booadmin-table-confirm-cancel" type="button" class="px-4 py-2 bg-gray-200 text-discord-text font-medium hover:bg-gray-300 transition-colors text-sm"><?php _e('取消'); ?></button>' +
                            '<button id="booadmin-table-confirm-submit" type="button" class="px-4 py-2 bg-discord-accent text-white font-medium hover:bg-blue-600 transition-colors text-sm"><?php _e('确认'); ?></button>' +
                        '</div>' +
                    '</div>' +
                '</div>'
            ).appendTo('body');

            sharedConfirmMessage = $('#booadmin-table-confirm-message');
            sharedConfirmCancel = $('#booadmin-table-confirm-cancel');
            sharedConfirmSubmit = $('#booadmin-table-confirm-submit');

            function closeSharedConfirmModal() {
                sharedConfirmModal.addClass('hidden').removeClass('flex');
                pendingAction = null;
            }

            sharedConfirmCancel.on('click', closeSharedConfirmModal);
            sharedConfirmSubmit.on('click', function () {
                var action = pendingAction;
                closeSharedConfirmModal();

                if (action) {
                    action();
                }
            });

            sharedConfirmModal.on('click', function (e) {
                if (e.target === this) {
                    closeSharedConfirmModal();
                }
            });

            $(document).on('keydown.tableConfirm', function (e) {
                if (e.key === 'Escape' && sharedConfirmModal.hasClass('flex')) {
                    closeSharedConfirmModal();
                }
            });
        }

        function openSharedConfirmModal(message, onConfirm, alertOnly) {
            ensureSharedConfirmModal();
            sharedConfirmMessage.text(message || '<?php _e('请确认是否继续。'); ?>');
            pendingAction = onConfirm || null;

            if (alertOnly) {
                sharedConfirmCancel.addClass('hidden');
                sharedConfirmSubmit.text('<?php _e('我知道了'); ?>');
            } else {
                sharedConfirmCancel.removeClass('hidden');
                sharedConfirmSubmit.text('<?php _e('确认'); ?>');
            }

            sharedConfirmModal.removeClass('hidden').addClass('flex');
        }

        var EMPTY_SELECTION_MESSAGE = '<?php _e('请先选择至少一个条目。'); ?>';

        function hasSelection() {
            return $('.typecho-list-table input[type="checkbox"]:not(.typecho-table-select-all):checked').length > 0;
        }

        // 原生 actionEl 在提交前不校验选中项，这里先于 tableSelectable 绑定，
        // 未选中任何条目时阻止原生提交并给出提示
        $('.booadmin-dropdown-menu a:not([lang])').on('click.tableConfirm', function (e) {
            if (hasSelection()) {
                return true;
            }

            e.preventDefault();
            e.stopImmediatePropagation();
            openSharedConfirmModal(EMPTY_SELECTION_MESSAGE, null, true);

            return false;
        });

        $('.typecho-list-table').tableSelectable({
            checkEl     :   'input[type=checkbox]',
            rowEl       :   'tr',
            selectAllEl :   '.typecho-table-select-all',
            actionEl    :   '.dropdown-menu a:not([lang])'
        });

        $('.btn-dropdown-toggle').dropdownMenu({
            btnEl       :   '.btn-dropdown-toggle',
            menuEl      :   '.dropdown-menu'
        });

        $(document).off('click.tableConfirm', '.dropdown-menu a[lang]').on('click.tableConfirm', '.dropdown-menu a[lang]', function (e) {
            var actionLink = $(this);
            var message = actionLink.attr('lang');

            if (!message) {
                return true;
            }

            e.preventDefault();
            e.stopImmediatePropagation();

            if (!hasSelection()) {
                openSharedConfirmModal(EMPTY_SELECTION_MESSAGE, null, true);
                return false;
            }

            openSharedConfirmModal(message, function () {
                actionLink.closest('form').attr('action', actionLink.attr('href')).trigger('submit');
            });

            return false;
        });

        // 事件已绑定（tableSelectable + 确认弹窗），解除这些操作链接的原生跳转保护
        if (window.booadminArmLinks) {
            booadminArmLinks('.booadmin-dropdown-menu a');
        }
    });
})();
</script>
