<?php
include 'common.php';
include 'header.php';
include 'menu.php';

\Widget\Metas\Category\Admin::alloc()->to($categories);
?>

<main class="flex-1 flex flex-col overflow-hidden bg-discord-light">
    <!-- Header -->
    <header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-6 z-10">
        <div class="flex items-center text-discord-muted">
             <button id="mobile-menu-btn" class="mr-4 md:hidden text-discord-text focus:outline-none">
                <i class="fas fa-bars"></i>
            </button>
            <i class="fas fa-folder mr-2 hidden md:inline"></i>
            <span class="mx-2 hidden md:inline">/</span>
            <span class="font-medium text-discord-text"><?php _e('管理分类'); ?></span>
        </div>
        
        <div class="flex items-center space-x-4">
            <a href="<?php $options->adminUrl('category.php'); ?>" class="flex items-center px-4 py-2 bg-discord-accent text-white hover:bg-blue-600 transition-colors text-sm font-medium">
                <i class="fas fa-plus mr-1"></i> <?php _e('新增'); ?>
            </a>
            <a href="<?php $options->siteUrl(); ?>" class="text-discord-muted hover:text-discord-accent transition-colors" title="<?php _e('查看网站'); ?>" target="_blank">
                <i class="fas fa-globe"></i>
            </a>
            <a href="<?php $options->adminUrl('profile.php'); ?>" class="text-discord-muted hover:text-discord-accent transition-colors" title="<?php _e('个人资料'); ?>">
                <i class="fas fa-user-circle"></i>
            </a>
        </div>
    </header>

    <div class="flex-1 overflow-y-auto p-4 md:p-8">
        <div class="w-full max-w-none mx-auto">

            <!-- Category List -->
            <div class="bg-white border border-gray-200 overflow-hidden mb-4">
                <form method="post" name="manage_categories" class="operate-form">
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
                                    <a lang="<?php _e('此分类下的所有内容将被删除, 你确认要删除这些分类吗?'); ?>" data-confirm-message="<?php _e('此分类下的所有内容将被删除, 你确认要删除这些分类吗?'); ?>" href="<?php $security->index('/action/metas-category-edit?do=delete'); ?>" class="js-category-action block px-4 py-2 text-sm text-red-600 hover:bg-red-50 hover:text-red-700"><i class="fas fa-trash-alt mr-1"></i><?php _e('删除'); ?></a>
                                    <a lang="<?php _e('刷新分类可能需要等待较长时间, 你确认要刷新这些分类吗?'); ?>" data-confirm-message="<?php _e('刷新分类可能需要等待较长时间, 你确认要刷新这些分类吗?'); ?>" href="<?php $security->index('/action/metas-category-edit?do=refresh'); ?>" class="js-category-action block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"><i class="fas fa-sync-alt mr-1"></i><?php _e('刷新'); ?></a>
                                    <div class="border-t border-gray-100 my-1"></div>
                                    <div class="px-4 py-2">
                                        <div class="flex items-center space-x-2">
                                            <button type="button" lang="<?php _e('你确认要合并这些分类吗?'); ?>" data-confirm-message="<?php _e('你确认要合并这些分类吗?'); ?>" class="btn-merge px-2 py-1 text-xs bg-discord-accent text-white hover:bg-blue-600 transition-colors" rel="<?php $security->index('/action/metas-category-edit?do=merge'); ?>"><i class="fas fa-compress-alt mr-1"></i><?php _e('合并到'); ?></button>
                                            <select name="merge" class="text-xs border border-gray-300 px-2 py-1 focus:outline-none focus:border-discord-accent w-24">
                                                <?php $categories->parse('<option value="{mid}">{name}</option>'); ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                             </div>
                         </div>
                         <div class="text-sm text-gray-500">
                            <?php $categories->backLink(); ?>
                         </div>
                    </div>

                    <div class="table-wrapper" data-table-scroll>
                    <table class="w-full text-left border-collapse typecho-list-table">
                        <thead>
                            <tr class="text-xs font-bold text-gray-500 uppercase border-b border-gray-100 bg-gray-50/50 nodrag">
                                <th class="w-10 pl-4 py-3"></th>
                                <th class="py-3"><?php _e('名称'); ?></th>
                                <th class="py-3 hidden md:table-cell"><?php _e('子分类'); ?></th>
                                <th class="py-3 hidden md:table-cell"><?php _e('缩略名'); ?></th>
                                <th class="py-3 w-24 text-center"><?php _e('文章数'); ?></th>
                                <th class="py-3 w-24 text-right pr-4"><?php _e('操作'); ?></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php if ($categories->have()): ?>
                                <?php while ($categories->next()): ?>
                                    <tr id="mid-<?php $categories->theId(); ?>" class="group hover:bg-gray-50 transition-colors">
                                        <td class="pl-4 py-3">
                                            <input type="checkbox" value="<?php $categories->mid(); ?>" name="mid[]" class="text-discord-accent focus:ring-discord-accent border-gray-300">
                                        </td>
                                        <td class="py-3">
                                            <div class="flex items-center">
                                                <a href="<?php $options->adminUrl('category.php?mid=' . $categories->mid); ?>" class="font-medium text-discord-text hover:text-discord-accent transition-colors"><?php $categories->name(); ?></a>
                                                <?php if ($options->defaultCategory == $categories->mid): ?>
                                                    <span class="ml-2 px-1.5 py-0.5 text-xs bg-gray-200 text-gray-600"><?php _e('默认'); ?></span>
                                                <?php endif; ?>
                                                <a href="<?php $categories->permalink(); ?>" target="_blank" class="ml-2 text-gray-400 hover:text-discord-accent opacity-0 group-hover:opacity-100 transition-opacity" title="<?php _e('浏览 %s', $categories->name); ?>"><i class="fas fa-external-link-alt text-xs"></i></a>
                                            </div>
                                        </td>
                                        <td class="py-3 hidden md:table-cell text-sm text-gray-600">
                                            <?php if (count($categories->children) > 0): ?>
                                                <a href="<?php $options->adminUrl('manage-categories.php?parent=' . $categories->mid); ?>" class="text-discord-accent hover:underline bg-discord-light px-2 py-0.5 text-xs font-medium"><?php echo _n('1', '%d', count($categories->children)); ?></a>
                                            <?php else: ?>
                                                <a href="<?php $options->adminUrl('category.php?parent=' . $categories->mid); ?>" class="text-gray-400 hover:text-discord-accent text-xs"><i class="fas fa-folder-plus mr-1"></i> <?php echo _e('新增'); ?></a>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-3 hidden md:table-cell text-sm text-gray-600 font-mono text-xs">
                                            <?php $categories->slug(); ?>
                                        </td>
                                        <td class="py-3 text-center">
                                            <a href="<?php $options->adminUrl('manage-posts.php?category=' . $categories->mid); ?>" class="inline-flex items-center justify-center px-2 py-0.5 text-xs font-medium <?php echo $categories->count > 0 ? 'bg-discord-accent text-white' : 'bg-gray-100 text-gray-500'; ?> hover:bg-blue-600 transition-colors">
                                                <?php $categories->count(); ?>
                                            </a>
                                        </td>
                                        <td class="py-3 pr-4 text-right text-sm">
                                            <div class="flex justify-end space-x-3 opacity-0 group-hover:opacity-100 transition-opacity">
                                                <a href="<?php $options->adminUrl('category.php?mid=' . $categories->mid); ?>" class="text-gray-500 hover:text-discord-accent" title="<?php _e('编辑'); ?>"><i class="fas fa-edit"></i></a>
                                                <?php if ($options->defaultCategory != $categories->mid): ?>
                                                    <a href="<?php $security->index('/action/metas-category-edit?do=default&mid=' . $categories->mid); ?>" class="text-gray-500 hover:text-discord-accent" title="<?php _e('设为默认'); ?>"><i class="fas fa-check-circle"></i></a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                        <div class="min-h-[260px] flex flex-col items-center justify-center">
                                            <div class="mb-3 text-5xl text-gray-300"><i class="far fa-folder-open"></i></div>
                                            <p class="text-sm text-gray-500"><?php _e('没有找到任何分类'); ?></p>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Footer自然跟随内容 -->
    <?php include 'copyright.php'; ?>
</main>

<div id="categories-confirm-modal" class="booadmin-modal hidden" role="dialog" aria-modal="true" aria-labelledby="categories-confirm-modal-title">
    <div class="booadmin-dialog booadmin-dialog-sm">
        <h3 id="categories-confirm-modal-title" class="text-lg font-bold text-discord-text mb-4"><?php _e('操作确认'); ?></h3>
        <p id="categories-confirm-modal-message" class="text-discord-muted mb-6"><?php _e('请确认是否继续。'); ?></p>
        <div class="flex justify-end space-x-3">
            <button id="categories-modal-cancel" type="button" class="px-4 py-2 bg-gray-200 text-discord-text font-medium hover:bg-gray-300 transition-colors text-sm">
                <?php _e('取消'); ?>
            </button>
            <button id="categories-modal-confirm" type="button" class="px-4 py-2 bg-discord-accent text-white font-medium hover:bg-blue-600 transition-colors text-sm">
                <?php _e('确认'); ?>
            </button>
        </div>
    </div>
</div>

<?php
include 'common-js.php';
?>

<script type="text/javascript">
(function () {
    $(document).ready(function () {
        var form = $('form[name="manage_categories"]'),
            confirmModal = $('#categories-confirm-modal'),
            confirmTitle = $('#categories-confirm-modal-title'),
            confirmMessage = $('#categories-confirm-modal-message'),
            confirmButton = $('#categories-modal-confirm'),
            cancelButton = $('#categories-modal-cancel'),
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

        function hasSelectedCategories() {
            return form.find('input[name="mid[]"]:checked').length > 0;
        }

        function requestBatchAction(actionUrl, message) {
            if (!hasSelectedCategories()) {
                openModal('<?php _e('提示'); ?>', '<?php _e('请先选择至少一个分类。'); ?>', null, true);
                return;
            }

            openModal('<?php _e('操作确认'); ?>', message, function () {
                form.attr('action', actionUrl).submit();
            }, false);
        }

        var table = $('.typecho-list-table').tableDnD({
            onDrop: function () {
                var ids = [];

                $('input[type=checkbox]', table).each(function () {
                    ids.push($(this).val());
                });

                $.post('<?php $security->index('/action/metas-category-edit?do=sort'); ?>',
                    $.param({mid: ids}));
            }
        });

        $('.dropdown-menu a.js-category-action').on('click.categoriesConfirm', function (e) {
            var actionLink = $(this),
                message = actionLink.data('confirm-message') || actionLink.attr('lang') || '<?php _e('你确认要执行该操作吗?'); ?>';

            if (replayingNativeAction) {
                return true;
            }

            e.preventDefault();
            e.stopImmediatePropagation();

            if (!hasSelectedCategories()) {
                openModal('<?php _e('提示'); ?>', '<?php _e('请先选择至少一个分类。'); ?>', null, true);
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

        $('.typecho-list-table').tableSelectable({
            checkEl     :   'input[type=checkbox]',
            rowEl       :   'tr',
            selectAllEl :   '.typecho-table-select-all',
            actionEl    :   '.dropdown-menu a.js-category-action'
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

        <?php if (isset($request->mid)): ?>
        (function () {
            var highlightSoft = getComputedStyle(document.documentElement).getPropertyValue('--booadmin-highlight-soft').trim() || '#F3F4F6';
            $('#mid-<?php echo $request->mid; ?>').effect('highlight', {color: highlightSoft}, 1500);
        })();
        <?php endif; ?>
    });
})();
</script>

