<?php
include 'common.php';
include 'header.php';
include 'menu.php';

\Widget\Metas\Tag\Admin::alloc()->to($tags);
?>

<main class="flex-1 flex flex-col overflow-hidden bg-discord-light">
    <!-- Header -->
    <header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-6 z-10">
        <div class="flex items-center text-discord-muted">
             <button id="mobile-menu-btn" class="mr-4 md:hidden text-discord-text focus:outline-none">
                <i class="fas fa-bars"></i>
            </button>
            <i class="fas fa-tags mr-2 hidden md:inline"></i>
            <span class="mx-2 hidden md:inline">/</span>
            <span class="font-medium text-discord-text"><?php _e('管理标签'); ?></span>
        </div>
        
        <div class="flex items-center space-x-4">
            <a href="<?php $options->siteUrl(); ?>" class="text-discord-muted hover:text-discord-accent transition-colors" title="<?php _e('查看网站'); ?>" target="_blank">
                <i class="fas fa-globe"></i>
            </a>
            <a href="<?php $options->adminUrl('profile.php'); ?>" class="text-discord-muted hover:text-discord-accent transition-colors" title="<?php _e('个人资料'); ?>">
                <i class="fas fa-user-circle"></i>
            </a>
        </div>
    </header>

    <div class="flex-1 overflow-y-auto p-4 md:p-8">
        <div class="w-full max-w-none mx-auto grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Tag List -->
            <div class="lg:col-span-2">
                <div class="bg-white border border-gray-200 overflow-hidden h-full flex flex-col">
                    <form method="post" name="manage_tags" class="operate-form flex flex-col h-full">
                        <div class="booadmin-operate-bar operate-bar">
                             <div class="flex items-center space-x-2">
                                 <label class="booadmin-select-all">
                                     <input type="checkbox" class="typecho-table-select-all text-discord-accent focus:ring-discord-accent border-gray-300">
                                     <span><?php _e('全选'); ?></span>
                                 </label>
                                 <div class="relative group">
                                    <button type="button" class="btn-dropdown-toggle booadmin-dropdown-toggle">
                                        <i class="fas fa-tasks mr-1"></i><?php _e('选中项'); ?> <i class="fas fa-chevron-down ml-1"></i>
                                    </button>
                                    <div class="dropdown-menu booadmin-dropdown-menu w-64 hidden">
                                        <a lang="<?php _e('你确认要删除这些标签吗?'); ?>" data-confirm-message="<?php _e('你确认要删除这些标签吗?'); ?>" href="<?php echo booadminActionUrl('/action/metas-tag-edit?do=delete'); ?>" class="js-tag-action block px-4 py-2 text-sm text-red-600 hover:bg-red-50 hover:text-red-700"><i class="fas fa-trash-alt mr-1"></i><?php _e('删除'); ?></a>
                                        <a lang="<?php _e('刷新标签可能需要等待较长时间, 你确认要刷新这些标签吗?'); ?>" data-confirm-message="<?php _e('刷新标签可能需要等待较长时间, 你确认要刷新这些标签吗?'); ?>" href="<?php echo booadminActionUrl('/action/metas-tag-edit?do=refresh'); ?>" class="js-tag-action block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"><i class="fas fa-sync-alt mr-1"></i><?php _e('刷新'); ?></a>
                                        <div class="border-t border-gray-100 my-1"></div>
                                        <div class="px-4 py-2">
                                            <div class="flex items-center space-x-2">
                                                <button type="button" lang="<?php _e('你确认要合并这些标签吗?'); ?>" data-confirm-message="<?php _e('你确认要合并这些标签吗?'); ?>" class="btn-merge px-2 py-1 text-xs bg-discord-accent text-white hover:bg-blue-600 transition-colors" rel="<?php echo booadminActionUrl('/action/metas-tag-edit?do=merge'); ?>"><i class="fas fa-compress-alt mr-1"></i><?php _e('合并到'); ?></button>
                                                <input type="text" name="merge" class="text-xs border border-gray-300 px-2 py-1 focus:outline-none focus:border-discord-accent w-24" placeholder="<?php _e('标签名'); ?>">
                                            </div>
                                        </div>
                                    </div>
                                 </div>
                             </div>
                        </div>

                        <div class="p-4 flex-1 overflow-y-auto custom-scrollbar">
                            <?php if ($tags->have()): ?>
                                <ul class="flex flex-wrap gap-2 typecho-list-notable tag-list">
                                    <?php while ($tags->next()): ?>
                                        <li class="relative group" id="<?php $tags->theId(); ?>">
                                            <input type="checkbox" value="<?php $tags->mid(); ?>" name="mid[]" class="absolute opacity-0 pointer-events-none peer">
                                            <div class="flex items-center px-3 py-1.5 bg-gray-100 border border-gray-200 hover:border-discord-accent hover:bg-blue-50 transition-all cursor-pointer select-none tag-item">
                                                <i class="fas fa-check text-xs mr-1.5 w-3.5 flex-shrink-0 tag-check-icon"></i>
                                                <span class="text-sm font-medium mr-1" rel="<?php echo $request->makeUriByRequest('mid=' . $tags->mid); ?>"><?php $tags->name(); ?></span>
                                                <span class="text-xs opacity-60 bg-gray-200 px-1.5 text-gray-600 ml-1 group-hover:bg-white tag-count"><?php $tags->count(); ?></span>
                                                <a class="ml-2 text-gray-400 hover:text-discord-accent opacity-0 group-hover:opacity-100 transition-opacity" href="<?php echo $request->makeUriByRequest('mid=' . $tags->mid); ?>" title="<?php _e('编辑'); ?>"><i class="fas fa-pen mr-1"></i></a>
                                            </div>
                                            <!-- Checkbox logic via JS needs to toggle checked state on click of the div -->
                                        </li>
                                    <?php endwhile; ?>
                                </ul>
                            <?php else: ?>
                                <div class="text-center py-10 text-gray-500">
                                    <i class="fas fa-tags text-4xl mb-3 text-gray-300"></i>
                                    <p><?php _e('没有任何标签'); ?></p>
                                </div>
                            <?php endif; ?>
                            <input type="hidden" name="do" value="delete"/>
                        </div>
                        <?php $security->form('metas-tag-edit'); ?>
                    </form>
                </div>
            </div>

            <!-- Edit Form -->
            <div class="lg:col-span-1">
                <div class="booadmin-panel sticky top-4">
                    <h3 class="booadmin-panel-title"><?php _e(isset($request->mid) ? '编辑标签' : '新增标签'); ?></h3>
                    <?php \Widget\Metas\Tag\Edit::alloc()->form()->render(); ?>
                    <?php if (isset($request->mid)): ?>
                        <div class="mt-4 text-center">
                            <a href="<?php $options->adminUrl('manage-tags.php'); ?>" class="text-sm text-discord-accent hover:underline"><?php _e('取消编辑，返回新增'); ?></a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <!-- Footer在main内部 -->
    <?php include 'copyright.php'; ?>
</main>

<div id="tags-confirm-modal" class="booadmin-modal hidden" role="dialog" aria-modal="true" aria-labelledby="tags-confirm-modal-title">
    <div class="booadmin-dialog booadmin-dialog-sm">
        <h3 id="tags-confirm-modal-title" class="text-lg font-bold text-discord-text mb-4"><?php _e('操作确认'); ?></h3>
        <p id="tags-confirm-modal-message" class="text-discord-muted mb-6"><?php _e('请确认是否继续。'); ?></p>
        <div class="flex justify-end space-x-3">
            <button id="tags-modal-cancel" type="button" class="px-4 py-2 bg-gray-200 text-discord-text font-medium hover:bg-gray-300 transition-colors text-sm">
                <?php _e('取消'); ?>
            </button>
            <button id="tags-modal-confirm" type="button" class="px-4 py-2 bg-discord-accent text-white font-medium hover:bg-blue-600 transition-colors text-sm">
                <?php _e('确认'); ?>
            </button>
        </div>
    </div>
</div>

<style>
/* 标签选中样式优化 */
.tag-list li input:checked ~ .tag-item {
    border: 2px solid var(--booadmin-accent);
    background: var(--booadmin-selection-bg, rgba(var(--booadmin-accent-rgb, 99, 102, 241), 0.08));
    box-shadow: 0 0 0 1px var(--booadmin-accent),
                0 0 10px rgba(var(--booadmin-accent-rgb, 99, 102, 241), 0.25);
    position: relative;
    z-index: 1;
}

.tag-list li input:checked ~ .tag-item .tag-count {
    background: rgba(var(--booadmin-accent-rgb, 99, 102, 241), 0.15);
    color: var(--booadmin-accent);
    border: 1px solid rgba(var(--booadmin-accent-rgb, 99, 102, 241), 0.3);
    font-weight: 600;
}

/* 对钩图标控制 */
.tag-check-icon {
    display: none;
    color: var(--booadmin-accent);
}

.tag-list li input:checked ~ .tag-item .tag-check-icon {
    display: inline-flex;
}
</style>

<?php
include 'common-js.php';
include 'form-js.php';
?>

<script type="text/javascript">
(function () {
    $(document).ready(function () {
        var form = $('form[name="manage_tags"]'),
            confirmModal = $('#tags-confirm-modal'),
            confirmTitle = $('#tags-confirm-modal-title'),
            confirmMessage = $('#tags-confirm-modal-message'),
            confirmButton = $('#tags-modal-confirm'),
            cancelButton = $('#tags-modal-cancel'),
            pendingSubmit = null,
            pendingActionEl = null,
            replayingNativeAction = false;

        function openModal(title, message, onConfirm, confirmOnly) {
            confirmTitle.text(title);
            confirmMessage.text(message);
            pendingSubmit = onConfirm || null;

            if (confirmOnly) {
                cancelButton.addClass('hidden');
                confirmButton.text('<?php _e('我知道了'); ?>');
            } else {
                cancelButton.removeClass('hidden');
                confirmButton.text('<?php _e('确认'); ?>');
            }

            confirmModal.removeClass('hidden').addClass('flex');
        }

        function closeModal() {
            confirmModal.removeClass('flex').addClass('hidden');
            pendingSubmit = null;
        }

        function hasSelectedTags() {
            return form.find('input[name="mid[]"]:checked').length > 0;
        }

        function requestBatchAction(actionUrl, message) {
            if (!hasSelectedTags()) {
                openModal('<?php _e('提示'); ?>', '<?php _e('请先选择至少一个标签。'); ?>', null, true);
                return;
            }

            openModal('<?php _e('操作确认'); ?>', message, function () {
                form.attr('action', actionUrl).submit();
            }, false);
        }

        $('.dropdown-menu a.js-tag-action').on('click.tagsConfirm', function (e) {
            var actionLink = $(this),
                message = actionLink.data('confirm-message') || actionLink.attr('lang') || '<?php _e('你确认要执行该操作吗?'); ?>';

            if (replayingNativeAction) {
                return true;
            }

            e.preventDefault();
            e.stopImmediatePropagation();

            if (!hasSelectedTags()) {
                openModal('<?php _e('提示'); ?>', '<?php _e('请先选择至少一个标签。'); ?>', null, true);
                return false;
            }

            pendingActionEl = actionLink;
            openModal('<?php _e('操作确认'); ?>', message, function () {
                var target = pendingActionEl,
                    originalLang;

                if (!target || target.length === 0) {
                    return;
                }

                originalLang = target.attr('lang');
                if (typeof originalLang !== 'undefined') {
                    target.removeAttr('lang');
                }

                replayingNativeAction = true;
                target.trigger('click');
                replayingNativeAction = false;

                if (typeof originalLang !== 'undefined') {
                    target.attr('lang', originalLang);
                }

                pendingActionEl = null;
            }, false);

            return false;
        });

        // Reuse Typecho's row/select-all behavior.
        $('.tag-list').tableSelectable({
            checkEl     :   'input[type=checkbox]',
            rowEl       :   'li',
            selectAllEl :   '.typecho-table-select-all',
            actionEl    :   '.dropdown-menu a.js-tag-action'
        });

        $('.btn-dropdown-toggle').dropdownMenu({
            btnEl       :   '.btn-dropdown-toggle',
            menuEl      :   '.dropdown-menu'
        });

        $('.btn-merge').click(function () {
            var btn = $(this),
                message = btn.data('confirm-message') || btn.attr('lang') || '<?php _e('你确认要执行该操作吗?'); ?>';

            requestBatchAction(btn.attr('rel'), message);
        });

        confirmButton.on('click', function () {
            var callback = pendingSubmit;
            closeModal();
            if (callback) {
                callback();
            }
        });

        cancelButton.on('click', function () {
            closeModal();
        });

        confirmModal.on('click', function (e) {
            if (e.target === this) {
                closeModal();
            }
        });

        $(document).on('keydown', function (e) {
            if (e.key === 'Escape' && confirmModal.hasClass('flex')) {
                closeModal();
            }
        });
        
        // Ensure form JS works correctly
        $('.typecho-option input').first().focus();

        <?php if (isset($request->mid)): ?>
        var selectedTag = <?php echo json_encode((string) $request->mid, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
        $('#' + selectedTag + ' div').addClass('ring-2 ring-offset-1 ring-yellow-400 border-yellow-400');
        <?php endif; ?>
    });
})();
</script>


