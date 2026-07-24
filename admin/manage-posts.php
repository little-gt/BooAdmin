<?php
include 'common.php';
include 'header.php';
include 'menu.php';

$stat = \Widget\Stat::alloc();
$posts = \Widget\Contents\Post\Admin::alloc();
$isAllPosts = ('on' == $request->get('__typecho_all_posts') || 'on' == \Typecho\Cookie::get('__typecho_all_posts'));
?>
<main class="flex-1 flex flex-col overflow-hidden bg-discord-light">
    <!-- Header -->
    <header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-6 z-10">
        <div class="flex items-center text-discord-muted">
             <button id="mobile-menu-btn" class="mr-4 md:hidden text-discord-text focus:outline-none">
                <i class="fas fa-bars"></i>
            </button>
            <i class="fas fa-layer-group mr-2 hidden md:inline"></i>
            <span class="mx-2 hidden md:inline">/</span>
            <span class="font-medium text-discord-text"><?php _e('管理文章'); ?></span>
        </div>
        
        <div class="flex items-center space-x-4">
            <a href="<?php $options->adminUrl('write-post.php'); ?>" class="flex items-center px-4 py-2 bg-discord-accent text-white hover:bg-blue-600 transition-colors text-sm font-medium">
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
            
            <!-- Tabs & Filters -->
            <div class="flex flex-col md:flex-row md:items-center justify-between mb-6 gap-4">
                <div class="flex items-center bg-gray-100 p-1 select-none self-start">
                     <a href="<?php $options->adminUrl('manage-posts.php' . (isset($request->uid) ? '?uid=' . $request->filter('encode')->uid : '')); ?>" 
                        class="px-4 py-1.5 text-sm font-medium transition-all duration-200 <?php if (!isset($request->status) || 'all' == $request->get('status')): ?>bg-white text-discord-text<?php else: ?>text-gray-500 hover:text-discord-text<?php endif; ?>">
                        <?php _e('可用'); ?>
                     </a>
                     <a href="<?php $options->adminUrl('manage-posts.php?status=waiting' . (isset($request->uid) ? '&uid=' . $request->filter('encode')->uid : '')); ?>" 
                        class="px-4 py-1.5 text-sm font-medium transition-all duration-200 <?php if ('waiting' == $request->get('status')): ?>bg-white text-discord-text<?php else: ?>text-gray-500 hover:text-discord-text<?php endif; ?>">
                        <?php _e('待审核'); ?>
                        <?php if (!$isAllPosts && $stat->myWaitingPostsNum > 0 && !isset($request->uid)): ?>
                            <span class="ml-1 px-1.5 py-0.5 bg-red-100 text-red-600 text-xs"><?php $stat->myWaitingPostsNum(); ?></span>
                        <?php elseif ($isAllPosts && $stat->waitingPostsNum > 0 && !isset($request->uid)): ?>
                            <span class="ml-1 px-1.5 py-0.5 bg-red-100 text-red-600 text-xs"><?php $stat->waitingPostsNum(); ?></span>
                        <?php elseif (isset($request->uid) && $stat->currentWaitingPostsNum > 0): ?>
                            <span class="ml-1 px-1.5 py-0.5 bg-red-100 text-red-600 text-xs"><?php $stat->currentWaitingPostsNum(); ?></span>
                        <?php endif; ?>
                     </a>
                     <a href="<?php $options->adminUrl('manage-posts.php?status=draft' . (isset($request->uid) ? '&uid=' . $request->filter('encode')->uid : '')); ?>" 
                        class="px-4 py-1.5 text-sm font-medium transition-all duration-200 <?php if ('draft' == $request->get('status')): ?>bg-white text-discord-text<?php else: ?>text-gray-500 hover:text-discord-text<?php endif; ?>">
                        <?php _e('草稿'); ?>
                        <?php if (!$isAllPosts && $stat->myDraftPostsNum > 0 && !isset($request->uid)): ?>
                            <span class="ml-1 px-1.5 py-0.5 bg-yellow-100 text-yellow-600 text-xs"><?php $stat->myDraftPostsNum(); ?></span>
                        <?php elseif ($isAllPosts && $stat->draftPostsNum > 0 && !isset($request->uid)): ?>
                            <span class="ml-1 px-1.5 py-0.5 bg-yellow-100 text-yellow-600 text-xs"><?php $stat->draftPostsNum(); ?></span>
                        <?php elseif (isset($request->uid) && $stat->currentDraftPostsNum > 0): ?>
                            <span class="ml-1 px-1.5 py-0.5 bg-yellow-100 text-yellow-600 text-xs"><?php $stat->currentDraftPostsNum(); ?></span>
                        <?php endif; ?>
                     </a>
                </div>

                <form method="get" class="flex flex-wrap items-center gap-2">
                    <div class="flex items-center bg-white border border-gray-100 focus-within:border-discord-accent transition-colors search-input-container">
                        <div class="px-3 flex items-center">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                        <input type="text" name="keywords" value="<?php echo htmlspecialchars($request->keywords ?? ''); ?>" placeholder="<?php _e('请输入关键字'); ?>" class="pr-3 py-1.5 bg-white text-sm focus:outline-none w-48 md:w-64 border-0">
                    </div>
                    <select name="category" class="pl-3 pr-8 py-1.5 bg-white border border-gray-100 focus:outline-none focus:border-discord-accent transition-colors">
                        <option value=""><?php _e('所有分类'); ?></option>
                        <?php \Widget\Metas\Category\Rows::alloc()->to($category); ?>
                        <?php while ($category->next()): ?>
                            <option value="<?php $category->mid(); ?>"<?php if ($request->get('category') == $category->mid): ?> selected="true"<?php endif; ?>><?php $category->name(); ?></option>
                        <?php endwhile; ?>
                    </select>
                    <button type="submit" class="px-3 py-1.5 bg-gray-100 text-gray-600 hover:bg-gray-200 transition-colors text-sm font-medium flex items-center">
                        <i class="fas fa-filter mr-1"></i><?php _e('筛选'); ?>
                    </button>
                    
                    <?php if (isset($request->uid)): ?>
                        <input type="hidden" value="<?php echo htmlspecialchars($request->uid); ?>" name="uid"/>
                    <?php endif; ?>
                    <?php if (isset($request->status)): ?>
                        <input type="hidden" value="<?php echo htmlspecialchars($request->status); ?>" name="status"/>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Post List -->
            <div class="bg-white border border-gray-200 overflow-hidden">
                <form method="post" name="manage_posts" class="operate-form">
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
                                    <a href="<?php $security->index('/action/contents-post-edit?do=delete'); ?>" class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50 hover:text-red-700"><i class="fas fa-trash-alt mr-1"></i><?php _e('删除'); ?></a>
                                    <?php if ($user->pass('editor', true)): ?>
                                        <div class="border-t border-gray-100 my-1"></div>
                                        <a href="<?php $security->index('/action/contents-post-edit?do=mark&status=publish'); ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"><i class="fas fa-eye mr-1"></i><?php _e('标记为公开'); ?></a>
                                        <a href="<?php $security->index('/action/contents-post-edit?do=mark&status=waiting'); ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"><i class="fas fa-hourglass-half mr-1"></i><?php _e('标记为待审核'); ?></a>
                                        <a href="<?php $security->index('/action/contents-post-edit?do=mark&status=hidden'); ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"><i class="fas fa-eye-slash mr-1"></i><?php _e('标记为隐藏'); ?></a>
                                    <?php endif; ?>
                                </div>
                             </div>
                         </div>
                         <div class="flex items-center space-x-4">
                         <?php if ($user->pass('editor', true) && !isset($request->uid)): ?>
                            <div class="flex items-center space-x-2 text-xs">
                                <a href="<?php echo $request->makeUriByRequest('__typecho_all_posts=on&page=1'); ?>" class="<?php if ($isAllPosts): ?>text-discord-accent font-bold<?php else: ?>text-gray-500 hover:text-discord-text<?php endif; ?>"><?php _e('所有'); ?></a>
                                <span class="text-gray-300">|</span>
                                <a href="<?php echo $request->makeUriByRequest('__typecho_all_posts=off&page=1'); ?>" class="<?php if (!$isAllPosts): ?>text-discord-accent font-bold<?php else: ?>text-gray-500 hover:text-discord-text<?php endif; ?>"><?php _e('我的'); ?></a>
                            </div>
                         <?php endif; ?>
                         <div class="view-toggle">
                             <button type="button" class="btn-table-view active" title="<?php _e('表格视图'); ?>">
                                 <i class="fas fa-table"></i>
                                 <span class="hidden sm:inline"><?php _e('表格'); ?></span>
                             </button>
                             <button type="button" class="btn-card-view" title="<?php _e('卡片视图'); ?>">
                                 <i class="fas fa-th-large"></i>
                                 <span class="hidden sm:inline"><?php _e('卡片'); ?></span>
                             </button>
                         </div>
                         </div>
                    </div>

                    <div class="table-wrapper" data-table-scroll>
                    <table class="w-full text-left border-collapse typecho-list-table">
                        <colgroup>
                            <col style="width:40px">
                            <col style="width:64px;text-align:center">
                            <col style="text-align:left">
                            <col class="hidden md:table-cell" style="width:100px;text-align:left">
                            <col class="hidden md:table-cell" style="width:140px;text-align:left">
                            <col style="width:140px;text-align:right">
                        </colgroup>
                        <thead>
                            <tr class="text-xs font-bold text-gray-500 uppercase border-b border-gray-100 bg-gray-50/50">
                                <th class="pl-4 py-3"></th>
                                <th class="py-3"><?php _e('评论数'); ?></th>
                                <th class="py-3"><?php _e('标题'); ?></th>
                                <th class="py-3 hidden md:table-cell"><?php _e('作者'); ?></th>
                                <th class="py-3 hidden md:table-cell"><?php _e('分类'); ?></th>
                                <th class="py-3 pr-4"><?php _e('日期'); ?></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php if ($posts->have()): ?>
                                <?php 
                                // Store posts data for card view
                                $postsData = [];
                                while ($posts->next()): 
                                    // Store all necessary data in an array
                                    $postsData[] = [
                                        'cid' => $posts->cid,
                                        'title' => $posts->title,
                                        'type' => $posts->type,
                                        'status' => $posts->status,
                                        'password' => $posts->password,
                                        'revision' => $posts->revision,
                                        'modified' => $posts->modified,
                                        'created' => $posts->created,
                                        'commentsNum' => $posts->commentsNum,
                                        'parentId' => $posts->parentId,
                                        'permalink' => $posts->permalink,
                                        'author' => [
                                            'uid' => $posts->author->uid,
                                            'screenName' => $posts->author->screenName
                                        ],
                                        'categories' => $posts->categories
                                    ];
                                ?>
                                    <tr id="<?php $posts->theId(); ?>" class="group hover:bg-gray-50 transition-colors">
                                        <td class="pl-4 py-3">
                                            <input type="checkbox" value="<?php $posts->cid(); ?>" name="cid[]" class="text-discord-accent focus:ring-discord-accent border-gray-300">
                                        </td>
                                        <td class="py-3">
                                            <a href="<?php $options->adminUrl('manage-comments.php?cid=' . ($posts->parentId ? $posts->parentId : $posts->cid)); ?>" 
                                               class="inline-flex items-center justify-center px-2 py-0.5 text-xs font-medium <?php echo $posts->commentsNum > 0 ? 'bg-discord-accent text-white' : 'bg-gray-100 text-gray-500'; ?>">
                                                <?php $posts->commentsNum(); ?>
                                            </a>
                                        </td>
                                        <td class="py-3">
                                            <div class="flex items-center">
                                                <a href="<?php $options->adminUrl('write-post.php?cid=' . $posts->cid); ?>" class="text-discord-text font-medium hover:text-discord-accent transition-colors">
                                                    <?php $posts->title(); ?>
                                                </a>
                                                <?php
                                                if ('post_draft' == $posts->type) echo '<span class="ml-2 px-1.5 py-0.5 text-xs bg-yellow-100 text-yellow-700">' . _t('草稿') . '</span>';
                                                elseif ($posts->revision) echo '<span class="ml-2 px-1.5 py-0.5 text-xs bg-green-100 text-green-700">' . _t('有修订') . '</span>';
                                                
                                                if ('hidden' == $posts->status) echo '<span class="ml-2 px-1.5 py-0.5 text-xs bg-gray-200 text-gray-600">' . _t('隐藏') . '</span>';
                                                elseif ('waiting' == $posts->status) echo '<span class="ml-2 px-1.5 py-0.5 text-xs bg-red-100 text-red-700">' . _t('待审') . '</span>';
                                                elseif ('private' == $posts->status) echo '<span class="ml-2 px-1.5 py-0.5 text-xs bg-purple-100 text-purple-700">' . _t('私密') . '</span>';
                                                elseif ($posts->password) echo '<span class="ml-2 px-1.5 py-0.5 text-xs bg-gray-600 text-white">' . _t('加密') . '</span>';
                                                ?>
                                            </div>
                                            <div class="mt-1 flex items-center space-x-3 text-xs text-gray-400 opacity-0 group-hover:opacity-100 transition-opacity">
                                                <a href="<?php $options->adminUrl('write-post.php?cid=' . $posts->cid); ?>" class="hover:text-discord-accent"><i class="fas fa-edit mr-1"></i><?php _e('编辑'); ?></a>
                                                <?php if ('post_draft' != $posts->type): ?>
                                                    <a href="<?php $posts->permalink(); ?>" target="_blank" class="hover:text-discord-accent"><i class="fas fa-external-link-alt mr-1"></i><?php _e('查看'); ?></a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td class="py-3 hidden md:table-cell text-sm text-gray-600">
                                            <a href="<?php $options->adminUrl('manage-posts.php?__typecho_all_posts=off&uid=' . $posts->author->uid); ?>" class="hover:underline"><?php $posts->author(); ?></a>
                                        </td>
                                        <td class="py-3 hidden md:table-cell text-sm text-gray-600">
                                            <?php foreach($posts->categories as $index => $category): ?>
                                                <?php echo ($index > 0 ? ', ' : '') . '<a href="';
                                                $options->adminUrl('manage-posts.php?category=' . $category['mid'] . (isset($request->uid) ? '&uid=' . $request->filter('encode')->uid : '') . (isset($request->status) ? '&status=' . $request->filter('encode')->status : ''));
                                                echo '" class="hover:underline hover:text-discord-accent">' . $category['name'] . '</a>'; ?>
                                            <?php endforeach; ?>
                                        </td>
                                        <td class="py-3 pr-4 text-sm text-gray-500">
                                            <?php if ('post_draft' == $posts->type || $posts->revision): ?>
                                                <span class="block text-xs text-green-600"><?php $modifyDate = new \Typecho\Date($posts->revision ? $posts->revision['modified'] : $posts->modified); _e('保存于 %s', $modifyDate->word()); ?></span>
                                            <?php else: ?>
                                                <?php $posts->dateWord(); ?>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                        <div class="mb-2 text-4xl text-gray-300"><i class="far fa-folder-open"></i></div>
                                        <?php _e('没有找到任何文章'); ?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    </div>

                    <!-- Card View Container -->
                    <div class="card-view-container">
                        <?php if (!empty($postsData)): ?>
                            <?php foreach ($postsData as $post): ?>
                                <div class="content-card" id="card-<?php echo $post['cid']; ?>">
                                    <input type="checkbox" value="<?php echo $post['cid']; ?>" name="cid[]" class="card-checkbox text-discord-accent focus:ring-discord-accent border-gray-300">
                                    
                                    <div class="card-header">
                                        <div class="flex-1">
                                            <a href="<?php $options->adminUrl('write-post.php?cid=' . $post['cid']); ?>" class="card-title">
                                                <?php echo htmlspecialchars($post['title']); ?>
                                            </a>
                                            <div class="card-badges">
                                                <?php
                                                if ('post_draft' == $post['type']) echo '<span class="px-1.5 py-0.5 text-xs bg-yellow-100 text-yellow-700">' . _t('草稿') . '</span>';
                                                elseif ($post['revision']) echo '<span class="px-1.5 py-0.5 text-xs bg-green-100 text-green-700">' . _t('有修订') . '</span>';
                                                
                                                if ('hidden' == $post['status']) echo '<span class="px-1.5 py-0.5 text-xs bg-gray-200 text-gray-600">' . _t('隐藏') . '</span>';
                                                elseif ('waiting' == $post['status']) echo '<span class="px-1.5 py-0.5 text-xs bg-red-100 text-red-700">' . _t('待审') . '</span>';
                                                elseif ('private' == $post['status']) echo '<span class="px-1.5 py-0.5 text-xs bg-purple-100 text-purple-700">' . _t('私密') . '</span>';
                                                elseif ($post['password']) echo '<span class="px-1.5 py-0.5 text-xs bg-gray-600 text-white">' . _t('加密') . '</span>';
                                                ?>
                                            </div>
                                        </div>
                                        <a href="<?php $options->adminUrl('manage-comments.php?cid=' . ($post['parentId'] ? $post['parentId'] : $post['cid'])); ?>" 
                                           class="card-comment-badge flex-shrink-0 <?php echo $post['commentsNum'] > 0 ? 'bg-discord-accent text-white' : 'bg-gray-100 text-gray-500'; ?>">
                                            <?php echo $post['commentsNum']; ?>
                                        </a>
                                    </div>
                                    
                                    <div class="card-meta">
                                        <div class="card-meta-item">
                                            <i class="fas fa-user text-gray-400"></i>
                                            <a href="<?php $options->adminUrl('manage-posts.php?__typecho_all_posts=off&uid=' . $post['author']['uid']); ?>" class="hover:text-discord-accent">
                                                <?php echo htmlspecialchars($post['author']['screenName']); ?>
                                            </a>
                                        </div>
                                        <?php if (!empty($post['categories'])): ?>
                                        <div class="card-meta-item">
                                            <i class="fas fa-folder text-gray-400"></i>
                                            <span>
                                                <?php foreach($post['categories'] as $index => $category): ?>
                                                    <?php echo ($index > 0 ? ', ' : '') . '<a href="';
                                                    $options->adminUrl('manage-posts.php?category=' . $category['mid'] . (isset($request->uid) ? '&uid=' . $request->filter('encode')->uid : '') . (isset($request->status) ? '&status=' . $request->filter('encode')->status : ''));
                                                    echo '" class="hover:text-discord-accent">' . htmlspecialchars($category['name']) . '</a>'; ?>
                                                <?php endforeach; ?>
                                            </span>
                                        </div>
                                        <?php endif; ?>
                                        <div class="card-meta-item">
                                            <i class="fas fa-clock text-gray-400"></i>
                                            <span>
                                                <?php if ('post_draft' == $post['type'] || $post['revision']): ?>
                                                    <?php $modifyDate = new \Typecho\Date($post['revision'] ? $post['revision']['modified'] : $post['modified']); echo $modifyDate->word(); ?>
                                                <?php else: ?>
                                                    <?php echo (new \Typecho\Date($post['created']))->word(); ?>
                                                <?php endif; ?>
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <div class="card-actions">
                                        <a href="<?php $options->adminUrl('write-post.php?cid=' . $post['cid']); ?>">
                                            <i class="fas fa-edit"></i> <?php _e('编辑'); ?>
                                        </a>
                                        <?php if ('post_draft' != $post['type']): ?>
                                            <a href="<?php echo $post['permalink']; ?>" target="_blank">
                                                <i class="fas fa-external-link-alt"></i> <?php _e('查看'); ?>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <!-- Empty state -->
                            <div class="col-span-full px-4 py-8 text-center text-gray-500">
                                <div class="mb-2 text-4xl text-gray-300"><i class="far fa-folder-open"></i></div>
                                <?php _e('没有找到任何文章'); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
            
            <?php if ($posts->have()): ?>
                <div class="mt-4 flex justify-end">
                     <?php $posts->pageNav('&laquo;', '&raquo;', 1, '...', array(
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
    font-size: 0.875rem; /* text-sm */
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

.content-card {
    display: flex;
    flex-direction: column;
    min-height: 240px;
    max-height: 320px;
}

.card-header {
    flex: 1;
    min-height: 0;
    overflow-y: auto;
}

.card-title {
    display: block;
    max-height: 5rem;
    overflow-y: auto;
    line-height: 1.5;
}

.card-badges {
    max-height: 2.5rem;
    overflow-y: auto;
}

.card-meta {
    flex: 0 0 auto;
    min-height: 0;
    max-height: 88px;
    overflow-y: auto;
}

.card-actions {
    margin-top: 0.25rem;
    flex-shrink: 0;
}
</style>

<?php
include 'common-js.php';
include 'table-js.php';
?>
