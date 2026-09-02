<?php if(!defined('__TYPECHO_ADMIN__')) exit; ?>
<script src="<?php $options->adminStaticUrl('js', 'purify.js'); ?>"></script>
<script>
(function () {
    $(document).ready(function () {
        function openSharedConfirmModal(message, onConfirm, alertOnly) {
            if (alertOnly) {
                BooAdmin.alert({
                    title: '<?php _e('操作确认'); ?>',
                    message: message || '<?php _e('请确认是否继续。'); ?>',
                    confirmText: '<?php _e('我知道了'); ?>'
                });
            } else {
                BooAdmin.confirm({
                    title: '<?php _e('操作确认'); ?>',
                    message: message || '<?php _e('请确认是否继续。'); ?>',
                    onConfirm: onConfirm
                });
            }
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
