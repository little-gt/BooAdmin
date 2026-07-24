<?php
include 'common.php';
include 'header.php';
include 'menu.php';

$users = \Widget\Users\Admin::alloc();
?>
<main class="flex-1 flex flex-col overflow-hidden bg-discord-light">
    <!-- Header -->
    <header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-6 z-10">
        <div class="flex items-center text-discord-muted">
             <button id="mobile-menu-btn" class="mr-4 md:hidden text-discord-text focus:outline-none">
                <i class="fas fa-bars"></i>
            </button>
            <i class="fas fa-users mr-2 hidden md:inline"></i>
            <span class="mx-2 hidden md:inline">/</span>
            <span class="font-medium text-discord-text"><?php _e('管理用户'); ?></span>
        </div>
        
        <div class="flex items-center space-x-4">
            <a href="<?php $options->adminUrl('user.php'); ?>" class="flex items-center px-4 py-2 bg-discord-accent text-white hover:bg-blue-600 transition-colors text-sm font-medium">
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
            
            <!-- Filters -->
            <div class="flex flex-col md:flex-row md:items-center justify-between mb-6 gap-4">
                <div class="flex items-center space-x-2 text-sm text-gray-500">
                    <?php if ('' != $request->keywords): ?>
                        <a href="<?php $options->adminUrl('manage-users.php'); ?>" class="px-2 py-1 bg-gray-200 hover:bg-gray-300 transition-colors"><?php _e('&laquo; 取消筛选'); ?></a>
                    <?php endif; ?>
                </div>

                <form method="get" class="flex flex-wrap items-center gap-2">
                    <div class="flex items-center bg-white border border-gray-100 focus-within:border-discord-accent transition-colors search-input-container">
                        <div class="px-3 flex items-center">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                        <input type="text" name="keywords" value="<?php echo htmlspecialchars($request->keywords ?? ''); ?>" placeholder="<?php _e('请输入关键字'); ?>" class="pr-3 py-1.5 bg-white text-sm focus:outline-none w-48 md:w-64 border-0">
                    </div>
                    <button type="submit" class="px-3 py-1.5 bg-gray-100 text-gray-600 hover:bg-gray-200 transition-colors text-sm font-medium flex items-center">
                        <i class="fas fa-filter mr-1"></i><?php _e('筛选'); ?>
                    </button>
                </form>
            </div>

            <!-- User List -->
            <div class="bg-white border border-gray-200 overflow-hidden rounded-xl shadow-sm">
                <form method="post" name="manage_users" class="operate-form">
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
                                    <div class="dropdown-menu booadmin-dropdown-menu w-40 hidden">
                                        <a lang="<?php _e('你确认要删除这些用户吗?'); ?>" href="<?php $security->index('/action/users-edit?do=delete'); ?>" class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50 hover:text-red-700"><i class="fas fa-trash-alt mr-1"></i><?php _e('删除'); ?></a>
                                    </div>
                                 </div>
                             </div>
                    </div>

                    <div class="table-wrapper" data-table-scroll>
                    <table class="w-full text-left border-collapse typecho-list-table draggable">
                                <thead>
                            <tr class="text-xs font-bold text-gray-500 uppercase border-b border-gray-100 bg-gray-50/50 nodrag">
                                <th class="w-10 pl-4 py-3"></th>
                                <th class="w-16 py-3 text-center"><?php _e('文章数'); ?></th>
                                <th class="py-3"><?php _e('用户名'); ?></th>
                                <th class="py-3 hidden lg:table-cell"><?php _e('昵称'); ?></th>
                                <th class="py-3 hidden lg:table-cell"><?php _e('电子邮件'); ?></th>
                                <th class="py-3 pr-4 text-right"><?php _e('用户组'); ?></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php if ($users->have()): ?>
                                <?php while ($users->next()): ?>
                                    <tr id="user-<?php $users->uid(); ?>" class="group hover:bg-gray-50 transition-colors">
                                        <td class="pl-4 py-3">
                                            <input type="checkbox" value="<?php $users->uid(); ?>" name="uid[]" class="text-discord-accent focus:ring-discord-accent border-gray-300">
                                        </td>
                                        <td class="py-3 text-center">
                                            <a href="<?php $options->adminUrl('manage-posts.php?__typecho_all_posts=off&uid=' . $users->uid); ?>" 
                                               class="inline-flex items-center justify-center px-2 py-0.5 text-xs font-medium <?php echo $users->postsNum > 0 ? 'bg-discord-accent text-white' : 'bg-gray-100 text-gray-500'; ?>">
                                                <?php $users->postsNum(); ?>
                                            </a>
                                        </td>
                                        <td class="py-3 min-w-0">
                                            <div class="flex items-center min-w-0">
                                                <div class="mr-3 flex-shrink-0">
                                                    <?php if ($users->mail): ?>
                                                        <?php echo getAvatar($users->mail, $users->screenName, 64); ?>
                                                    <?php else: ?>
                                                        <div class="w-9 h-9 bg-gray-100 border border-gray-200 flex items-center justify-center text-gray-500 overflow-hidden flex-shrink-0">
                                                            <i class="fas fa-user text-sm"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="min-w-0">
                                                    <a href="<?php $options->adminUrl('user.php?uid=' . $users->uid); ?>" class="text-discord-text font-medium hover:text-discord-accent transition-colors block break-all leading-6">
                                                        <?php $users->name(); ?>
                                                    </a>
                                                    <div class="mt-1 flex flex-wrap items-center gap-2">
                                                        <a href="<?php $options->adminUrl('user.php?uid=' . $users->uid); ?>" class="inline-flex items-center px-2.5 py-1 text-xs font-medium bg-gray-100 text-gray-600 hover:bg-gray-200 hover:text-discord-text transition-colors whitespace-nowrap">
                                                            <i class="fas fa-edit mr-1"></i><?php _e('编辑'); ?>
                                                        </a>
                                                        <a href="<?php $users->permalink(); ?>" target="_blank" class="inline-flex items-center text-xs text-gray-400 hover:text-discord-accent transition-colors whitespace-nowrap md:opacity-0 md:group-hover:opacity-100" title="<?php _e('浏览 %s', $users->screenName); ?>">
                                                            <i class="fas fa-external-link-alt mr-1"></i><?php _e('主页'); ?>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-3 hidden lg:table-cell text-sm text-gray-600 break-all">
                                            <?php $users->screenName(); ?>
                                        </td>
                                        <td class="py-3 hidden lg:table-cell text-sm text-gray-600 break-all">
                                            <?php if ($users->mail): ?>
                                                <a href="mailto:<?php $users->mail(); ?>" class="hover:text-discord-accent break-all"><?php $users->mail(); ?></a>
                                            <?php else: ?>
                                                <span class="text-gray-400"><?php _e('暂无'); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-3 pr-4 text-left text-sm">
                                            <?php 
                                            $groupClass = 'bg-gray-100 text-gray-500';
                                            $groupName = '';
                                            switch ($users->group) {
                                                case 'administrator':
                                                    $groupClass = 'bg-red-100 text-red-600';
                                                    $groupName = _t('管理员');
                                                    break;
                                                case 'editor':
                                                    $groupClass = 'bg-blue-100 text-blue-600';
                                                    $groupName = _t('编辑');
                                                    break;
                                                case 'contributor':
                                                    $groupClass = 'bg-green-100 text-green-600';
                                                    $groupName = _t('贡献者');
                                                    break;
                                                case 'subscriber':
                                                    $groupClass = 'bg-yellow-100 text-yellow-600';
                                                    $groupName = _t('关注者');
                                                    break;
                                                case 'visitor':
                                                    $groupClass = 'bg-gray-100 text-gray-500';
                                                    $groupName = _t('访问者');
                                                    break;
                                                default:
                                                    $groupClass = 'bg-gray-100 text-gray-500';
                                                    $groupName = $users->group;
                                                    break;
                                            } 
                                            ?>
                                            <span class="px-2 py-0.5 text-xs font-medium whitespace-nowrap <?php echo $groupClass; ?>"><?php echo $groupName; ?></span>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                        <div class="min-h-[260px] flex flex-col items-center justify-center">
                                            <div class="mb-3 text-5xl text-gray-300"><i class="far fa-users"></i></div>
                                            <p class="text-sm text-gray-500"><?php _e('没有找到任何用户'); ?></p>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    </div>
                </form>
            </div>
            
            <?php if ($users->have()): ?>
                <div class="mt-4 flex justify-end">
                     <?php $users->pageNav('&laquo;', '&raquo;', 1, '...', array(
                         'wrapTag' => 'ul', 
                         'wrapClass' => 'flex items-center space-x-1 typecho-pager list-none', 
                         'itemTag' => 'li', 
                         'textTag' => 'span', 
                         'currentClass' => 'current', 
                         'prevClass' => 'prev', 
                         'nextClass' => 'next'
                     )); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <!-- Footer在main内部 -->
    <?php include 'copyright.php'; ?>
</main>
<style>
.typecho-pager li a, .typecho-pager li span {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 32px;
    height: 32px;
    padding: 0 8px;
    background-color: var(--booadmin-surface);
    color: var(--booadmin-muted);
    font-size: 0.875rem;
    border: 1px solid var(--booadmin-border);
    transition: all 0.2s;
    text-decoration: none;
}
.typecho-pager li a:hover {
    background-color: var(--booadmin-surface-2);
    color: var(--booadmin-accent);
    border-color: var(--booadmin-border-strong);
}
.typecho-pager li.current span {
    background-color: var(--booadmin-accent);
    color: white;
    border-color: var(--booadmin-accent);
    font-weight: 600;
}
</style>

<?php
include 'common-js.php';
include 'table-js.php';
?>
