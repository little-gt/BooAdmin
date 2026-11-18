<?php
include 'common.php';
include 'header.php';
include 'menu.php';

$stat = \Widget\Stat::alloc();
$comments = \Widget\Comments\Admin::alloc();
$isAllComments = ('on' == $request->get('__typecho_all_comments') || 'on' == \Typecho\Cookie::get('__typecho_all_comments'));
?>
<main class="flex-1 flex flex-col overflow-hidden bg-discord-light">
    <!-- Header -->
    <header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-6 z-10">
        <div class="flex items-center text-discord-muted">
             <button id="mobile-menu-btn" class="mr-4 md:hidden text-discord-text focus:outline-none">
                <i class="fas fa-bars"></i>
            </button>
            <i class="fas fa-comments mr-2 hidden md:inline"></i>
            <span class="mx-2 hidden md:inline">/</span>
            <span class="font-medium text-discord-text"><?php _e('管理评论'); ?></span>
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
        <div class="w-full max-w-none mx-auto">
            
            <!-- Tabs & Filters -->
            <div class="flex flex-col md:flex-row md:items-center justify-between mb-6 gap-4">
                <div class="flex items-center bg-gray-100 p-1 select-none self-start">
                     <a href="<?php $options->adminUrl('manage-comments.php' . (isset($request->cid) ? '?cid=' . $request->filter('encode')->cid : '')); ?>" 
                        class="px-4 py-1.5 text-sm font-medium transition-all duration-200 <?php if (!isset($request->status) || 'approved' == $request->get('status')): ?>bg-white text-discord-text<?php else: ?>text-gray-500 hover:text-discord-text<?php endif; ?>">
                        <?php _e('已通过'); ?>
                     </a>
                     <a href="<?php $options->adminUrl('manage-comments.php?status=waiting' . (isset($request->cid) ? '&cid=' . $request->filter('encode')->cid : '')); ?>" 
                        class="px-4 py-1.5 text-sm font-medium transition-all duration-200 <?php if ('waiting' == $request->get('status')): ?>bg-white text-discord-text<?php else: ?>text-gray-500 hover:text-discord-text<?php endif; ?>">
                        <?php _e('待审核'); ?>
                        <?php if(!$isAllComments && $stat->myWaitingCommentsNum > 0 && !isset($request->cid)): ?> 
                            <span class="ml-1 px-1.5 py-0.5 bg-red-100 text-red-600 text-xs"><?php $stat->myWaitingCommentsNum(); ?></span>
                        <?php elseif($isAllComments && $stat->waitingCommentsNum > 0 && !isset($request->cid)): ?>
                            <span class="ml-1 px-1.5 py-0.5 bg-red-100 text-red-600 text-xs"><?php $stat->waitingCommentsNum(); ?></span>
                        <?php elseif(isset($request->cid) && $stat->currentWaitingCommentsNum > 0): ?>
                            <span class="ml-1 px-1.5 py-0.5 bg-red-100 text-red-600 text-xs"><?php $stat->currentWaitingCommentsNum(); ?></span>
                        <?php endif; ?>
                     </a>
                     <a href="<?php $options->adminUrl('manage-comments.php?status=spam' . (isset($request->cid) ? '&cid=' . $request->filter('encode')->cid : '')); ?>" 
                        class="px-4 py-1.5 text-sm font-medium transition-all duration-200 <?php if ('spam' == $request->get('status')): ?>bg-white text-discord-text<?php else: ?>text-gray-500 hover:text-discord-text<?php endif; ?>">
                        <?php _e('垃圾'); ?>
                        <?php if(!$isAllComments && $stat->mySpamCommentsNum > 0 && !isset($request->cid)): ?> 
                            <span class="ml-1 px-1.5 py-0.5 bg-red-100 text-red-600 text-xs"><?php $stat->mySpamCommentsNum(); ?></span>
                        <?php elseif($isAllComments && $stat->spamCommentsNum > 0 && !isset($request->cid)): ?>
                            <span class="ml-1 px-1.5 py-0.5 bg-red-100 text-red-600 text-xs"><?php $stat->spamCommentsNum(); ?></span>
                        <?php elseif(isset($request->cid) && $stat->currentSpamCommentsNum > 0): ?>
                            <span class="ml-1 px-1.5 py-0.5 bg-red-100 text-red-600 text-xs"><?php $stat->currentSpamCommentsNum(); ?></span>
                        <?php endif; ?>
                     </a>
                </div>

                <form method="get" class="flex flex-wrap items-center gap-2">
                    <div class="flex items-center bg-white  border border-gray-100 focus-within:border-discord-accent transition-colors search-input-container">
                        <div class="px-3 flex items-center">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                        <input type="text" name="keywords" value="<?php echo htmlspecialchars($request->keywords ?? ''); ?>" placeholder="<?php _e('请输入关键字'); ?>" class="pr-3 py-1.5 bg-white text-sm focus:outline-none w-48 md:w-64 border-0">
                    </div>
                    <?php if(isset($request->status)): ?>
                        <input type="hidden" value="<?php echo $request->filter('html')->status; ?>" name="status" />
                    <?php endif; ?>
                    <?php if(isset($request->cid)): ?>
                        <input type="hidden" value="<?php echo $request->filter('html')->cid; ?>" name="cid" />
                    <?php endif; ?>
                    <button type="submit" class="px-3 py-1.5 bg-gray-100 text-gray-600 hover:bg-gray-200 transition-colors text-sm font-medium flex items-center">
                        <i class="fas fa-filter mr-1"></i><?php _e('筛选'); ?>
                    </button>
                    <?php if ('' != $request->keywords || '' != $request->category): ?>
                        <a href="<?php $options->adminUrl('manage-comments.php' . (isset($request->status) || isset($request->cid) ? '?' . (isset($request->status) ? 'status=' . $request->filter('encode')->status : '') . (isset($request->cid) ? (isset($request->status) ? '&' : '') . 'cid=' . $request->filter('encode')->cid : '') : '')); ?>" class="px-2 py-1 bg-gray-200 hover:bg-gray-300 transition-colors text-xs text-gray-600"><?php _e('取消筛选'); ?></a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Comment List -->
            <div class="bg-white border border-gray-200 overflow-hidden">
                <form method="post" name="manage_comments" class="operate-form">
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
                                    <a href="<?php $security->index('/action/comments-edit?do=approved'); ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"><i class="fas fa-check mr-1 text-green-600"></i><?php _e('通过'); ?></a>
                                    <a href="<?php $security->index('/action/comments-edit?do=waiting'); ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"><i class="fas fa-clock mr-1 text-yellow-600"></i><?php _e('待审核'); ?></a>
                                    <a href="<?php $security->index('/action/comments-edit?do=spam'); ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"><i class="fas fa-ban mr-1 text-orange-500"></i><?php _e('标记垃圾'); ?></a>
                                    <div class="border-t border-gray-100 my-1"></div>
                                    <a href="<?php $security->index('/action/comments-edit?do=delete'); ?>" class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50 hover:text-red-700"><i class="fas fa-trash-alt mr-1"></i><?php _e('删除'); ?></a>
                                </div>
                             </div>
                             <?php if('spam' == $request->get('status')): ?>
                                <button lang="<?php _e('你确认要删除所有垃圾评论吗?'); ?>" class="px-3 py-1 text-xs font-medium bg-red-50 border border-red-200 hover:bg-red-100 text-red-600 btn-operate flex items-center" href="<?php $security->index('/action/comments-edit?do=delete-spam'); ?>"><i class="fas fa-trash mr-1"></i><?php _e('删除所有垃圾评论'); ?></button>
                             <?php endif; ?>
                         </div>
                         <div class="flex items-center space-x-4">
                             <?php if($user->pass('editor', true) && !isset($request->cid)): ?>
                                <div class="flex items-center space-x-2 text-xs">
                                    <a href="<?php echo $request->makeUriByRequest('__typecho_all_comments=on'); ?>" class="<?php if($isAllComments): ?>text-discord-accent font-bold<?php else: ?>text-gray-500 hover:text-discord-text<?php endif; ?>"><?php _e('所有'); ?></a>
                                    <span class="text-gray-300">|</span>
                                    <a href="<?php echo $request->makeUriByRequest('__typecho_all_comments=off'); ?>" class="<?php if(!$isAllComments): ?>text-discord-accent font-bold<?php else: ?>text-gray-500 hover:text-discord-text<?php endif; ?>"><?php _e('我的'); ?></a>
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

                    <?php
                    /**
                     * 评论数据源：表格视图与卡片视图共用，归一化后只构建一次。
                     *
                     * 历史上表格视图在 while($comments->next()) 里直接输出 HTML，卡片视图
                     * 另存一份 $commentsData；两处的数据契约随之分叉（rel / data-rel、
                     * tr[data-comment] / .content-card[data-comment]、jQuery .data() 缓存 /
                     * 属性解析），导致某一视图的操作会静默失效且难以定位。现在统一为
                     * 「一次构建、两处消费」，两个视图都只认 $commentsData 这一份数据。
                     *
                     * 正文必须在当前行（$comments 指针正确）时捕获 Typecho 过滤后的安全
                     * HTML，否则所有行都会渲染成同一份内容。
                     */
                    $commentsData = [];
                    while ($comments->next()):
                        $coid = $comments->coid;

                        ob_start();
                        $comments->content();
                        $contentHtml = ob_get_clean();

                        // 交互数据契约：与卡片模式一致，两个视图、所有 JS 读写都只认这一份
                        $commentData = [
                            'author' => $comments->author,
                            'mail'   => $comments->mail,
                            'url'    => $comments->url,
                            'ip'     => $comments->ip,
                            'type'   => $comments->type,
                            'text'   => $comments->text
                        ];

                        $commentsData[] = [
                            'coid'        => $coid,
                            'author'      => $comments->author,
                            'mail'        => $comments->mail,
                            'url'         => $comments->url,
                            'ip'          => $comments->ip,
                            'type'        => $comments->type,
                            'status'      => $comments->status,
                            'dateWord'    => $comments->dateWord,
                            'title'       => $comments->title,
                            'permalink'   => $comments->permalink,
                            'gravatar'    => ('comment' == $comments->type)
                                ? getAvatar($comments->mail, $comments->author, 40) : '',
                            'content'     => $contentHtml,
                            // booadminJson 用 JSON_HEX_* 全套编码，可安全放进单引号属性
                            'commentJson' => booadminJson($commentData),
                            'contentAttr' => booadminAttr($contentHtml),
                            'replyUrl'    => $security->getIndex('/action/comments-edit?do=reply&coid=' . $coid),
                            'editUrl'     => $security->getIndex('/action/comments-edit?do=edit&coid=' . $coid),
                            'approvedUrl' => $security->getIndex('/action/comments-edit?do=approved&coid=' . $coid),
                            'spamUrl'     => $security->getIndex('/action/comments-edit?do=spam&coid=' . $coid),
                            'deleteUrl'   => $security->getIndex('/action/comments-edit?do=delete&coid=' . $coid)
                        ];
                    endwhile;
                    ?>

                    <div class="table-wrapper" data-table-scroll>
                    <table class="w-full text-left border-collapse typecho-list-table">
                        <thead>
                            <tr class="text-xs font-bold text-gray-500 uppercase border-b border-gray-100 bg-gray-50/50">
                                <th class="w-10 pl-4 py-3"></th>
                                <th class="w-16 py-3 text-center"><?php _e('头像'); ?></th>
                                <th class="py-3"><?php _e('作者'); ?></th>
                                <th class="py-3"><?php _e('内容'); ?></th>
                                <th class="py-3 w-48 text-left pr-4"><?php _e('操作'); ?></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php if (!empty($commentsData)): ?>
                                <?php foreach ($commentsData as $row): ?>
                                    <tr id="comment-<?php echo booadminAttr($row['coid']); ?>"
                                        class="group hover:bg-gray-50 transition-colors comment-row"
                                        data-comment-id="<?php echo booadminAttr($row['coid']); ?>"
                                        data-comment='<?php echo $row['commentJson']; ?>'>
                                        <td class="pl-4 py-3 align-top">
                                            <input type="checkbox" value="<?php echo booadminAttr($row['coid']); ?>" name="coid[]" class="text-discord-accent focus:ring-discord-accent border-gray-300 mt-1">
                                        </td>
                                        <td class="py-3 text-center align-top">
                                            <div class="w-10 h-10 mx-auto flex items-center justify-center">
                                                <?php if ($row['gravatar']): ?>
                                                    <?php echo $row['gravatar']; ?>
                                                <?php else: ?>
                                                    <div class="flex items-center justify-center w-full h-full bg-gray-200 text-gray-500"><i class="fas fa-quote-right"></i></div>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td class="py-3 align-top text-sm comment-author">
                                            <div class="font-medium text-discord-text comment-author-name">
                                                <?php if ($row['url']): ?>
                                                    <a href="<?php echo booadminAttr($row['url']); ?>" target="_blank" rel="noopener noreferrer" class="hover:underline"><?php echo booadminAttr($row['author']); ?></a>
                                                <?php else: ?>
                                                    <?php echo booadminAttr($row['author']); ?>
                                                <?php endif; ?>
                                            </div>
                                            <div class="text-xs text-gray-400 mt-0.5">
                                                <div class="comment-author-mail">
                                                    <?php if ($row['mail']): ?>
                                                        <a href="mailto:<?php echo booadminAttr($row['mail']); ?>" class="hover:text-discord-accent block"><?php echo booadminAttr($row['mail']); ?></a>
                                                    <?php endif; ?>
                                                </div>
                                                <span class="block mt-0.5"><?php echo booadminAttr($row['ip']); ?></span>
                                            </div>
                                        </td>
                                        <td class="py-3 align-top text-sm text-gray-600">
                                            <div class="mb-1 text-xs text-gray-400">
                                                <?php echo booadminAttr($row['dateWord']); ?> 于 <a href="<?php echo booadminAttr($row['permalink']); ?>" target="_blank" rel="noopener noreferrer" class="text-discord-accent hover:underline"><?php echo booadminAttr($row['title']); ?></a>
                                            </div>
                                            <div class="comment-content prose prose-sm max-w-none text-gray-700"
                                                 data-comment-content="<?php echo $row['contentAttr']; ?>"></div>
                                        </td>
                                        <td class="py-3 pr-4 text-left align-top text-sm">
                                            <div class="flex flex-col items-start space-y-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                                <a href="#" class="operate-reply text-discord-accent hover:underline" data-coid="<?php echo booadminAttr($row['coid']); ?>" data-rel="<?php echo booadminAttr($row['replyUrl']); ?>"><i class="fas fa-reply mr-1"></i><?php _e('回复'); ?></a>
                                                <a href="#" class="operate-edit text-gray-500 hover:text-discord-accent" data-coid="<?php echo booadminAttr($row['coid']); ?>" data-rel="<?php echo booadminAttr($row['editUrl']); ?>"><i class="fas fa-edit mr-1"></i><?php _e('编辑'); ?></a>

                                                <?php if ('approved' != $row['status']): ?>
                                                    <a href="<?php echo booadminAttr($row['approvedUrl']); ?>" class="operate-approved text-green-600 hover:underline"><i class="fas fa-check mr-1"></i><?php _e('通过'); ?></a>
                                                <?php endif; ?>

                                                <?php if ('spam' != $row['status']): ?>
                                                    <a href="<?php echo booadminAttr($row['spamUrl']); ?>" class="operate-spam text-orange-500 hover:underline"><i class="fas fa-ban mr-1"></i><?php _e('标为垃圾'); ?></a>
                                                <?php endif; ?>

                                                <a href="<?php echo booadminAttr($row['deleteUrl']); ?>" class="operate-delete text-red-500 hover:underline" lang="<?php echo booadminAttr(_t('你确认要删除%s的评论吗?', $row['author'])); ?>"><i class="fas fa-trash-alt mr-1"></i><?php _e('删除'); ?></a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                                        <div class="min-h-[260px] flex flex-col items-center justify-center">
                                            <div class="mb-3 text-5xl text-gray-300"><i class="far fa-comments"></i></div>
                                            <p class="text-sm text-gray-500"><?php _e('没有找到任何评论'); ?></p>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    </div>

                                        <!-- Card View Container -->
                    <div class="card-view-container">
                        <?php if (!empty($commentsData)): ?>
                            <?php foreach ($commentsData as $row): ?>
                                <div class="content-card comment-row"
                                     id="card-<?php echo booadminAttr($row['coid']); ?>"
                                     data-comment-id="<?php echo booadminAttr($row['coid']); ?>"
                                     data-comment='<?php echo $row['commentJson']; ?>'>
                                    <input type="checkbox" value="<?php echo booadminAttr($row['coid']); ?>" name="coid[]" class="card-checkbox text-discord-accent focus:ring-discord-accent border-gray-300">

                                    <div class="card-header comment-author">
                                        <div class="flex items-center space-x-3 flex-1">
                                            <div class="w-10 h-10 flex-shrink-0 flex items-center justify-center">
                                                <?php if ($row['gravatar']): ?>
                                                    <?php echo $row['gravatar']; ?>
                                                <?php else: ?>
                                                    <div class="flex items-center justify-center w-full h-full bg-gray-200 text-gray-500"><i class="fas fa-quote-right"></i></div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div class="card-title font-medium text-discord-text comment-author-name">
                                                    <?php if ($row['url']): ?>
                                                        <a href="<?php echo booadminAttr($row['url']); ?>" target="_blank" rel="noopener noreferrer" class="hover:underline"><?php echo booadminAttr($row['author']); ?></a>
                                                    <?php else: ?>
                                                        <?php echo booadminAttr($row['author']); ?>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="text-xs text-gray-400 truncate comment-author-mail">
                                                    <?php if ($row['mail']): ?>
                                                        <a href="mailto:<?php echo booadminAttr($row['mail']); ?>" class="hover:text-discord-accent"><?php echo booadminAttr($row['mail']); ?></a>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card-meta">
                                        <div class="card-meta-item">
                                            <i class="fas fa-clock text-gray-400"></i>
                                            <span><?php echo booadminAttr($row['dateWord']); ?></span>
                                        </div>
                                        <div class="card-meta-item">
                                            <i class="fas fa-file-alt text-gray-400"></i>
                                            <a href="<?php echo booadminAttr($row['permalink']); ?>" class="hover:text-discord-accent truncate" target="_blank" rel="noopener noreferrer"><?php echo booadminAttr($row['title']); ?></a>
                                        </div>
                                        <?php if ($row['ip']): ?>
                                        <div class="card-meta-item">
                                            <i class="fas fa-map-marker-alt text-gray-400"></i>
                                            <span><?php echo booadminAttr($row['ip']); ?></span>
                                        </div>
                                        <?php endif; ?>
                                    </div>

                                    <!-- 正文与表格视图共用 data-comment-content，由 renderCommentContents() 统一净化渲染 -->
                                    <div class="card-content comment-content prose prose-sm max-w-none text-gray-700"
                                         data-comment-content="<?php echo $row['contentAttr']; ?>"></div>

                                    <div class="card-actions">
                                        <a href="#" class="operate-reply" data-coid="<?php echo booadminAttr($row['coid']); ?>" data-rel="<?php echo booadminAttr($row['replyUrl']); ?>">
                                            <i class="fas fa-reply"></i> <?php _e('回复'); ?>
                                        </a>
                                        <a href="#" class="operate-edit" data-coid="<?php echo booadminAttr($row['coid']); ?>" data-rel="<?php echo booadminAttr($row['editUrl']); ?>">
                                            <i class="fas fa-edit"></i> <?php _e('编辑'); ?>
                                        </a>
                                        <?php if ('approved' != $row['status']): ?>
                                            <a href="<?php echo booadminAttr($row['approvedUrl']); ?>" class="operate-approved text-green-600"><i class="fas fa-check"></i> <?php _e('通过'); ?></a>
                                        <?php endif; ?>
                                        <?php if ('spam' != $row['status']): ?>
                                            <a href="<?php echo booadminAttr($row['spamUrl']); ?>" class="operate-spam text-orange-500"><i class="fas fa-ban"></i> <?php _e('标为垃圾'); ?></a>
                                        <?php endif; ?>
                                        <a href="<?php echo booadminAttr($row['deleteUrl']); ?>" class="operate-delete text-red-500" lang="<?php echo booadminAttr(_t('你确认要删除%s的评论吗?', $row['author'])); ?>">
                                            <i class="fas fa-trash-alt"></i> <?php _e('删除'); ?>
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="px-4 py-8 text-center text-gray-500 min-h-[260px] flex flex-col items-center justify-center">
                                <div class="mb-3 text-5xl text-gray-300"><i class="far fa-comments"></i></div>
                                <p class="text-sm text-gray-500"><?php _e('没有找到任何评论'); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php if(isset($request->cid)): ?>
                    <input type="hidden" value="<?php echo $request->filter('html')->cid; ?>" name="cid" />
                    <?php endif; ?>
                </form>
            </div>
            
            <?php if ($comments->have()): ?>
                <div class="mt-4 flex justify-end">
                     <?php $comments->pageNav('&laquo;', '&raquo;', 1, '...', array(
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

<!-- Comment Reply Modal -->
<div id="replyModal" class="comment-modal">
    <div class="booadmin-dialog booadmin-dialog-lg">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-discord-text"><?php _e('回复评论'); ?></h3>
            <button type="button" class="text-gray-400 hover:text-discord-text text-xl leading-none" onclick="closeReplyModal()"><i class="fas fa-times"></i></button>
        </div>
        <div class="mb-4">
            <div id="originalCommentArea" class="bg-gray-50 border border-gray-100 p-4"></div>
        </div>
        <form id="replyForm" method="post">
            <div>
                <label for="replyText" class="block text-sm font-medium text-discord-text mb-2"><?php _e('回复内容'); ?></label>
                <textarea id="replyText" name="text" required class="w-full px-3 py-2 border border-gray-300 text-sm focus:outline-none focus:border-discord-accent" rows="6"></textarea>
            </div>
        </form>
        <div class="flex justify-end space-x-3 mt-6">
            <button type="button" class="px-4 py-2 bg-gray-200 text-discord-text font-medium hover:bg-gray-300 transition-colors text-sm flex items-center" onclick="closeReplyModal()"><?php _e('取消'); ?></button>
            <button type="button" class="px-4 py-2 bg-discord-accent text-white font-medium hover:bg-blue-600 transition-colors text-sm flex items-center" onclick="submitReply()"><?php _e('提交回复'); ?></button>
        </div>
    </div>
</div>

<!-- Comment Edit Modal -->
<div id="editModal" class="comment-modal">
    <div class="booadmin-dialog booadmin-dialog-lg">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-discord-text"><?php _e('编辑评论'); ?></h3>
            <button type="button" class="text-gray-400 hover:text-discord-text text-xl leading-none" onclick="closeEditModal()"><i class="fas fa-times"></i></button>
        </div>
        <form id="editForm" method="post" class="space-y-4">
            <div>
                <label for="editAuthor" class="block text-sm font-medium text-discord-text mb-2"><?php _e('用户名'); ?></label>
                <input type="text" id="editAuthor" name="author" required class="w-full px-3 py-2 border border-gray-300 text-sm focus:outline-none focus:border-discord-accent">
            </div>
            <div>
                <label for="editMail" class="block text-sm font-medium text-discord-text mb-2"><?php _e('电子邮箱'); ?></label>
                <input type="email" id="editMail" name="mail" class="w-full px-3 py-2 border border-gray-300 text-sm focus:outline-none focus:border-discord-accent">
            </div>
            <div>
                <label for="editUrl" class="block text-sm font-medium text-discord-text mb-2"><?php _e('个人主页'); ?></label>
                <input type="url" id="editUrl" name="url" class="w-full px-3 py-2 border border-gray-300 text-sm focus:outline-none focus:border-discord-accent">
            </div>
            <div>
                <label for="editText" class="block text-sm font-medium text-discord-text mb-2"><?php _e('内容'); ?></label>
                <textarea id="editText" name="text" required class="w-full px-3 py-2 border border-gray-300 text-sm focus:outline-none focus:border-discord-accent" rows="7"></textarea>
            </div>
        </form>
        <div class="flex justify-end space-x-3 mt-6">
            <button type="button" class="px-4 py-2 bg-gray-200 text-discord-text font-medium hover:bg-gray-300 transition-colors text-sm flex items-center" onclick="closeEditModal()"><?php _e('取消'); ?></button>
            <button type="button" class="px-4 py-2 bg-discord-accent text-white font-medium hover:bg-blue-600 transition-colors text-sm flex items-center" onclick="submitEdit()"><?php _e('确认保存'); ?></button>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="comment-modal">
    <div class="booadmin-dialog booadmin-dialog-sm">
        <h3 class="text-lg font-bold text-discord-text mb-4"><?php _e('操作确认'); ?></h3>
        <p class="text-discord-muted mb-2" id="deleteAuthorName"><?php _e('确认删除此评论？'); ?></p>
        <p class="text-discord-muted mb-6"><?php _e('此操作不可逆，删除后无法恢复评论内容。'); ?></p>
        <div class="flex justify-end space-x-3">
            <button type="button" class="px-4 py-2 bg-gray-200 text-discord-text font-medium hover:bg-gray-300 transition-colors text-sm flex items-center" onclick="closeDeleteModal()"><?php _e('取消'); ?></button>
            <button type="button" class="px-4 py-2 bg-discord-accent text-white font-medium hover:bg-blue-600 transition-colors text-sm flex items-center" onclick="confirmDelete()"><?php _e('确认删除'); ?></button>
        </div>
    </div>
</div>

<!-- Message Modal -->
<div id="messageModal" class="comment-modal">
    <div class="booadmin-dialog booadmin-dialog-sm">
        <h3 class="text-lg font-bold text-discord-text mb-4" id="messageModalTitle"><?php _e('操作确认'); ?></h3>
        <p class="text-discord-muted mb-6" id="messageModalContent"></p>
        <div class="flex justify-end space-x-3">
            <button type="button" id="messageModalCancel" class="px-4 py-2 bg-gray-200 text-discord-text font-medium hover:bg-gray-300 transition-colors text-sm hidden">
                <?php _e('取消'); ?>
            </button>
            <button type="button" id="messageModalConfirm" class="px-4 py-2 bg-discord-accent text-white font-medium hover:bg-blue-600 transition-colors text-sm flex items-center"><?php _e('确定'); ?></button>
        </div>
    </div>
</div>

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

.comment-content img {
    max-width: 100%;
}

/* 确保操作链接的点击事件正常工作 */
.operate-reply, .operate-edit, .operate-delete, .operate-approved, .operate-spam {
    cursor: pointer;
}

.operate-reply svg, .operate-edit svg, .operate-delete svg, 
.operate-approved svg, .operate-spam svg,
.operate-reply i, .operate-edit i, .operate-delete i,
.operate-approved i, .operate-spam i {
    pointer-events: none;
}

/* Card View Styles */
.card-view-container {
    display: none;
    padding: 1rem;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 1rem;
}

.view-mode-card .table-wrapper {
    display: none !important;
}

.view-mode-card .card-view-container {
    display: grid;
}

.content-card {
    position: relative;
    display: flex;
    flex-direction: column;
    background: var(--booadmin-surface);
    border: 1px solid var(--booadmin-border);
    padding: 1rem;
    min-height: 340px;
    transition: all 0.2s;
}

.content-card:hover {
    border-color: var(--booadmin-accent);
    background: var(--booadmin-highlight-soft);
}

.card-checkbox {
    position: absolute;
    top: 0.75rem;
    right: 0.75rem;
}

.card-header {
    display: flex;
    align-items: flex-start;
    margin-bottom: 0.75rem;
    padding-right: 1.5rem;
}

.card-header .w-10.h-10 img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.card-title {
    font-size: 0.95rem;
    font-weight: 600;
    color: var(--booadmin-text);
    margin-bottom: 0.25rem;
}

.card-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
    margin-bottom: 0.75rem;
    font-size: 0.75rem;
    color: var(--booadmin-muted);
}

.card-meta-item {
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.card-meta-item i {
    font-size: 0.7rem;
}

.card-content {
    flex: 1;
    margin-bottom: 0.75rem;
    min-height: 100px;
    padding: 0.75rem;
    background: var(--booadmin-surface-2);
    font-size: 0.875rem;
    line-height: 1.5;
    max-height: 150px;
    overflow-y: auto;
}

.card-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
    margin-top: auto;
    font-size: 0.75rem;
    padding-top: 0.75rem;
    border-top: 1px solid var(--booadmin-border);
}

.card-actions a {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    color: var(--booadmin-muted);
    transition: color 0.2s;
}

.card-actions a:hover {
    color: var(--booadmin-accent);
}

.card-actions a i {
    font-size: 0.7rem;
}

/* Modal Styles */
.comment-modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: var(--booadmin-overlay);
    z-index: 9999;
    align-items: center;
    justify-content: center;
    padding: 1rem;
}

.comment-modal.active {
    display: flex;
}

.original-comment {
    background: var(--booadmin-surface-2);
    border: 1px solid var(--booadmin-border);
    padding: 1rem;
    margin-bottom: 1rem;
}

.original-comment-header {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 0.5rem;
}

.original-comment-header img {
    width: 32px;
    height: 32px;
}

.original-comment-author {
    font-weight: 600;
    color: var(--booadmin-text);
}

.original-comment-meta {
    font-size: 0.75rem;
    color: var(--booadmin-muted);
}

.original-comment-content {
    font-size: 0.875rem;
    line-height: 1.5;
    color: var(--booadmin-muted);
    max-height: 200px;
    overflow-y: auto;
    word-break: break-word;
}


@media (max-width: 768px) {
    .card-view-container {
        grid-template-columns: 1fr;
    }
}
</style>
<?php
include 'common-js.php';
include 'table-js.php';
?>
<script type="text/javascript">
// 模态框状态的全局变量
var currentReplyUrl = '';
var currentEditUrl = '';
// 归一化后表格与卡片都只有一个行容器 id，不再分别维护 rowId / cardId
var currentEditRowId = '';
var currentDeleteUrl = '';
var currentDeleteTarget = null;

/**
 * 输出转义工具（兼容别名）
 *
 * 实现统一收敛到内核 BooAdmin.html，避免各页面各写一套、行为不一致：
 * 本页历史上曾出现「净化策略自相矛盾」的 XSS 正是源于此。
 * 保留函数名是为了不改动既有调用点；新代码请直接用 BooAdmin.html.*。
 */
var sec = BooAdmin.html;

function escapeHtml(value) { return sec.esc(value); }
function escapeAttr(value) { return sec.attr(value); }
function safeHttpUrl(url) { return sec.httpUrl(url); }

// 模态框控制函数
function showMessageModal(title, content, isConfirm) {
    $('#messageModalTitle').text(title);
    $('#messageModalContent').text(content);
    $('#messageModal').data('is-confirm', isConfirm || false);
    
    if (isConfirm) {
        $('#messageModalCancel').removeClass('hidden');
    } else {
        $('#messageModalCancel').addClass('hidden');
    }
    
    $('#messageModal').addClass('active');
}

function closeMessageModal() {
    $('#messageModal').removeClass('active');
    $('#messageModal').removeData('confirm-url');
    $('#messageModal').removeData('is-confirm');
}
function openReplyModal(commentData, actionUrl) {
    currentReplyUrl = actionUrl;

    // 作者 / 邮箱 / IP / 正文均来自访客提交，全部按文本或净化富文本输出
    var author = sec.esc(commentData.author),
        authorFirstChar = sec.esc(commentData.author ? commentData.author.charAt(0) : '?');

    var gravatarHtml = '';
    if (commentData.type === 'comment') {
        // 归一化：表格与卡片都是 .comment-row，头像只需按统一容器查找一次，
        // 不再有 td:eq(1) / 遍历卡片比对两套逻辑。
        var gravatarUrl = '';
        var $row = sec.byId(commentData.id);
        if ($row.length > 0) {
            var $gravatar = $row.find('img.booadmin-avatar-image').first();
            if ($gravatar.length > 0) {
                gravatarUrl = $gravatar.attr('src');
            }
        }

        // URL 走协议白名单，杜绝 javascript:/data: 进入 src
        var safeGravatar = gravatarUrl ? sec.url(gravatarUrl, {fallback: ''}) : '';

        if (safeGravatar) {
            // 不打内联 onerror：复用 common-js.php 中 avatar 模块的事件委托契约
            gravatarHtml = '<div class="relative w-8 h-8"><img src="' + sec.attr(safeGravatar) + '" alt="' + sec.attr(commentData.author) + '" class="w-full h-full object-cover border border-gray-200 booadmin-avatar-image" /><div class="booadmin-avatar-fallback w-full h-full items-center justify-center bg-gradient-to-br from-blue-500 to-blue-600 text-white font-bold text-xs border border-gray-200 absolute inset-0">' + authorFirstChar + '</div></div>';
        } else {
            gravatarHtml = '<div class="w-8 h-8 flex items-center justify-center bg-gradient-to-br from-blue-500 to-blue-600 text-white font-bold text-xs border border-gray-200">' + authorFirstChar + '</div>';
        }
    }

    if (!gravatarHtml) {
        gravatarHtml = '<div class="flex items-center justify-center w-8 h-8 bg-gray-200 text-gray-500"><i class="fas fa-quote-right"></i></div>';
    }

    var originalCommentHtml = '<div class="original-comment-header">'
        + gravatarHtml
        + '<div>'
        + '<div class="original-comment-author">' + author + '</div>'
        + '<div class="original-comment-meta">';

    if (commentData.mail) {
        originalCommentHtml += sec.esc(commentData.mail) + ' · ';
    }
    if (commentData.ip) {
        originalCommentHtml += sec.esc(commentData.ip);
    }

    originalCommentHtml += '</div></div></div>'
        + '<div class="original-comment-content">' + sec.sanitize(commentData.text) + '</div>';

    $('#originalCommentArea').html(BooAdmin.hooks.filter('comment:originalHtml', originalCommentHtml, commentData));
    $('#replyText').val('');
    $('#replyModal').addClass('active');
    $('#replyText').focus();
}

function closeReplyModal() {
    $('#replyModal').removeClass('active');
    currentReplyUrl = '';
}

function submitReply() {
    var text = $('#replyText').val().trim();
    if (!text) {
        showMessageModal('<?php _e('提示'); ?>', '<?php _e('请输入回复内容'); ?>');
        return;
    }
    
    // 在发起请求前快照目标, 避免异步回调期间被 closeReplyModal() 清空
    var _currentReplyUrl = currentReplyUrl;

    $.post(_currentReplyUrl, {text: text}, function(o) {
        if (o && o.comment) {
            closeReplyModal();
            showMessageModal('<?php _e('成功'); ?>', '<?php _e('回复成功'); ?>');
            setTimeout(function() {
                window.location.reload();
            }, 1000);
        }
    }, 'json').fail(function() {
        showMessageModal('<?php _e('错误'); ?>', '<?php _e('回复失败，请重试'); ?>');
    });
}

/** rowId 为统一的 .comment-row 容器 id（表格 / 卡片都是同一个契约） */
function openEditModal(commentData, actionUrl, rowId) {
    currentEditUrl = actionUrl;
    currentEditRowId = rowId;

    $('#editAuthor').val(commentData.author);
    $('#editMail').val(commentData.mail || '');
    $('#editUrl').val(commentData.url || '');
    $('#editText').val(commentData.text);

    $('#editModal').addClass('active');
    $('#editAuthor').focus();
}

function closeEditModal() {
    $('#editModal').removeClass('active');
    currentEditUrl = '';
    currentEditRowId = '';
}

function openDeleteModal(authorName, deleteUrl, $target) {
    currentDeleteUrl = deleteUrl;
    currentDeleteTarget = $target;
    
    var confirmText = '<?php _e('确认删除来自「%s」的评论？'); ?>';
    confirmText = confirmText.replace('%s', authorName);
    $('#deleteAuthorName').text(confirmText);
    
    $('#deleteModal').addClass('active');
}

function closeDeleteModal() {
    $('#deleteModal').removeClass('active');
    currentDeleteUrl = '';
    currentDeleteTarget = null;
}

function confirmDelete() {
    if (!currentDeleteUrl) return;

    var _currentDeleteUrl = currentDeleteUrl;
    var $target = currentDeleteTarget;
    // 归一化：表格 <tr> 与卡片 .content-card 都是 .comment-row
    var $row = $target.closest('.comment-row');

    function rememberScroll () {
        $(window).bind('beforeunload', function () {
            $.cookie('__typecho_comments_scroll', $('body').scrollTop());
        });
    }

    if ($row.length > 0) {
        $row.fadeOut(function () {
            rememberScroll();
            window.location.href = _currentDeleteUrl;
        });
    } else {
        rememberScroll();
        window.location.href = _currentDeleteUrl;
    }

    closeDeleteModal();
}

function submitEdit() {
    var formData = {
        author: $('#editAuthor').val().trim(),
        mail: $('#editMail').val().trim(),
        url: $('#editUrl').val().trim(),
        text: $('#editText').val().trim()
    };
    
    if (!formData.author || !formData.text) {
        showMessageModal('<?php _e('提示'); ?>', '<?php _e('用户名和内容不能为空'); ?>');
        return;
    }
    
    // 在发起请求前快照目标, 避免异步回调期间被 closeEditModal() 清空
    var _currentEditUrl = currentEditUrl;
    var _currentEditRowId = currentEditRowId;

    $.post(_currentEditUrl, formData, function(o) {
        if (o && o.comment) {
            // 归一化：按 coid 找出该评论在「所有视图」中的 .comment-row，
            // 用同一套逻辑回写，保证表格/卡片切换后数据一致。
            // 用 byId 取源行、再遍历比对 data-comment-id，避免把 id 拼进选择器。
            var $origin = sec.byId(_currentEditRowId);
            var coid = $origin.attr('data-comment-id');
            // 兜底：若源行丢失或没有 coid，只处理来源行本身，绝不批量误改
            var $rows = coid
                ? $('.comment-row').filter(function () {
                    return $(this).attr('data-comment-id') === coid;
                })
                : $origin;

            var authorHtml = formData.url
                ? '<a href="' + escapeAttr(safeHttpUrl(formData.url)) + '" target="_blank" rel="noopener noreferrer" class="hover:underline">' + escapeHtml(formData.author) + '</a>'
                : escapeHtml(formData.author);
            var mailHtml = formData.mail
                ? '<a href="mailto:' + escapeAttr(formData.mail) + '" class="hover:text-discord-accent">' + escapeHtml(formData.mail) + '</a>'
                : '';
            // 正文：data 属性只存净化后的 HTML，避免 AJAX 原文回流成为渲染污染入口
            var content = sec.sanitize(o.comment.content);

            $rows.each(function () {
                var $row = $(this);

                // 统一只回写 data-comment 属性（历史上表格写 .data() 缓存、
                // 卡片写属性，两套状态会漂移）
                var comment = {};
                try {
                    comment = JSON.parse($row.attr('data-comment')) || {};
                } catch (e) {
                    if (window.console && console.warn) {
                        console.warn('[BooAdmin] 编辑回写时数据解析失败', e);
                    }
                }
                comment.author = formData.author;
                comment.mail = formData.mail;
                comment.url = formData.url;
                comment.text = formData.text;
                $row.attr('data-comment', JSON.stringify(comment));

                $row.find('.comment-author-name').html(authorHtml).effect('highlight');
                $row.find('.comment-author-mail').html(mailHtml);
                $row.find('.comment-content').html(content)
                    .attr('data-comment-content', content)
                    .data('rendered', true)
                    .effect('highlight');
            });

            closeEditModal();
        }
    }, 'json').fail(function() {
        showMessageModal('<?php _e('错误'); ?>', '<?php _e('保存失败，请重试'); ?>');
    });
}

$(document).ready(function () {
    /**
     * 渲染评论正文（表格与卡片共用同一条路径）。
     *
     * 归一化后两个视图都只把正文放进 data-comment-content（经 booadminAttr 转义），
     * 真正的 HTML 渲染统一交给内核 BooAdmin.html.sanitize，与 AJAX 编辑路径
     * 使用同一套净化策略；内核在 DOMPurify 缺失时自动降级为纯文本，
     * 不会出现「库缺失 → 正文空白」或「库缺失 → 原样输出」两种失效模式。
     */
    function renderCommentContents() {
        $('.comment-content[data-comment-content]').each(function () {
            var $el = $(this);
            var raw = $el.attr('data-comment-content');
            if (typeof raw === 'undefined' || $el.data('rendered')) {
                return;
            }
            $el.html(sec.sanitize(raw));
            $el.data('rendered', true);
        });
    }

    renderCommentContents();

    // 记住滚动条
    function rememberScroll () {
        $(window).bind('beforeunload', function () {
            $.cookie('__typecho_comments_scroll', $('body').scrollTop());
        });
    }

    // 处理 btn-operate 按钮（删除所有垃圾评论）
    $('.btn-operate').click(function() {
        var t = $(this);
        var href = t.attr('href');
        var lang = t.attr('lang');
        
        if (lang) {
            showMessageModal('<?php _e('操作确认'); ?>', lang);
            $('#messageModal').data('confirm-url', href);
            $('#messageModal').data('is-confirm', true);
        }
        return false;
    });

    // 自动滚动
    (function () {
        var scroll = $.cookie('__typecho_comments_scroll');
        if (scroll) {
            $.cookie('__typecho_comments_scroll', null);
            $('html, body').scrollTop(scroll);
        }
    })();

    // ===== 操作按钮：命名空间化事件绑定 =====
    //
    // 历史实现用 document.addEventListener('click', fn, true) 在捕获阶段拦截全部点击，
    // 并对每个分支调用 stopImmediatePropagation()：这会让 Typecho 核心与第三方插件
    // 挂在同一事件上的监听器全部收不到通知，是可扩展性上的硬阻塞。
    //
    // 现改为 jQuery 命名空间事件（.booadmin.comments），只 preventDefault 本组件的
    // 默认跳转、不阻断事件传播；并在每个动作点 emit 钩子，插件可安全挂接。
    //
    // 注意：这里必须直接绑定在 a.operate-* 链接上，不能委托到 document。
    // table-js.php 的 tableSelectable 会给 .typecho-list-table 的每个 <tr> 挂 click，
    // 点击 a/i 时调用 e.stopPropagation() 防止误勾选行；若用 document 级委托，
    // 表格视图的事件在 <tr> 处就被截断，永远到不了 document —— 这正是「表格模式
    // 编辑/回复失效、卡片模式正常」的根因（卡片不在 .typecho-list-table 内）。
    // 直接绑定在 <a> 上，处理器先于 <tr> 的 stopPropagation 执行，两视图行为一致。
    var ACTION_NS = '.booadmin.comments';

    /**
     * 行容器契约（表格 <tr> 与卡片 .content-card 完全一致）：
     *   .comment-row[data-comment][data-comment-id]
     * 两个视图共用同一份 $commentsData，故这里不再有 tr / .content-card 分叉，
     * 也不再混用 jQuery .data() 缓存与属性解析（历史上正是双轨导致某一视图静默失效）。
     */
    var COMMENT_ROW = '.comment-row[data-comment]';

    /** 读取统一的数据契约；失败时给出可见诊断，不再静默吞掉 */
    function readCommentRow($target) {
        var $row = $target.closest(COMMENT_ROW);
        if ($row.length === 0) {
            return null;
        }

        var raw = $row.attr('data-comment');
        if (typeof raw !== 'string' || raw === '') {
            return null;
        }

        try {
            var data = JSON.parse(raw);
            if (!data || typeof data !== 'object') {
                return null;
            }
            return $.extend({}, data, {
                id: $row.attr('id'),
                coid: $row.attr('data-comment-id')
            });
        } catch (err) {
            if (window.console && console.warn) {
                console.warn('[BooAdmin] 评论数据解析失败', err, $row.attr('id'));
            }
            return null;
        }
    }

    /**
     * 动作 URL 统一取 data-rel（两个视图一致）。
     * 保留 rel 作为兼容兜底，供第三方插件沿用旧 DOM 时仍可工作。
     */
    function readActionUrl($target) {
        return $target.attr('data-rel') || $target.attr('rel') || '';
    }

    /** 归一化入口：缺数据/缺 URL 时明确告警并提示，避免「点了没反应还不报错」 */
    function resolveAction($target, actionName) {
        var commentData = readCommentRow($target);
        var actionUrl = readActionUrl($target);

        if (!commentData || !actionUrl) {
            if (window.console && console.warn) {
                console.warn('[BooAdmin] 无法执行 ' + actionName
                    + '：commentData=' + (!!commentData) + ' actionUrl=' + (!!actionUrl));
            }
            showMessageModal(<?php echo booadminJson(_t('提示')); ?>,
                <?php echo booadminJson(_t('无法读取该评论的数据，请刷新页面后重试')); ?>);
            return null;
        }
        return {data: commentData, url: actionUrl, row: $target.closest(COMMENT_ROW)};
    }

    // 直接绑定在链接上：表格行的 tableSelectable 会 stopPropagation，
    // document 级委托在表格视图收不到事件（参考原版 manage-comments.php 的绑定方式）。
    $('a.operate-reply').on('click' + ACTION_NS, function (e) {
        e.preventDefault();
        var ctx = resolveAction($(this), 'reply');
        if (!ctx) { return; }

        BooAdmin.hooks.emit('comment:reply', ctx.data, ctx.url);
        openReplyModal(ctx.data, ctx.url);
    });

    $('a.operate-edit').on('click' + ACTION_NS, function (e) {
        e.preventDefault();
        var ctx = resolveAction($(this), 'edit');
        if (!ctx) { return; }

        BooAdmin.hooks.emit('comment:edit', ctx.data, ctx.url);
        openEditModal(ctx.data, ctx.url, ctx.row.attr('id'));
    });

    $('a.operate-delete').on('click' + ACTION_NS, function (e) {
        e.preventDefault();
        var $target = $(this);
        var commentData = readCommentRow($target);
        var authorName = commentData ? commentData.author : '';
        BooAdmin.hooks.emit('comment:delete', commentData, $target.attr('href'));
        openDeleteModal(authorName || <?php echo booadminJson(_t('该用户')); ?>,
            $target.attr('href'), $target);
    });

    $('a.operate-approved, a.operate-spam').on('click' + ACTION_NS, function (e) {
        e.preventDefault();
        var $target = $(this);
        BooAdmin.hooks.emit('comment:moderate', $target.attr('href'), $target.hasClass('operate-spam'));
        rememberScroll();
        window.location.href = $target.attr('href');
    });
    // 事件已绑定，解除这些操作链接的原生跳转保护
    if (window.booadminArmLinks) {
        booadminArmLinks();
    }

    // 点击背景关闭模态框
    $('.comment-modal').on('click', function(e) {
        if ($(e.target).hasClass('comment-modal')) {
            if ($(this).attr('id') === 'replyModal') {
                closeReplyModal();
            } else if ($(this).attr('id') === 'editModal') {
                closeEditModal();
            } else if ($(this).attr('id') === 'deleteModal') {
                closeDeleteModal();
            } else if ($(this).attr('id') === 'messageModal') {
                closeMessageModal();
            }
        }
    });
    
    // 消息模态框确认按钮点击
    $('#messageModalConfirm').click(function() {
        var $modal = $('#messageModal');
        // 先快照数据, 再关闭弹窗, 最后执行跳转, 避免残留状态影响后续操作
        var isConfirm = $modal.data('is-confirm');
        var confirmUrl = $modal.data('confirm-url');

        closeMessageModal();

        if (isConfirm && confirmUrl) {
            rememberScroll();
            window.location.href = confirmUrl;
        }
    });
    
    // 消息模态框取消按钮点击
    $('#messageModalCancel').click(function() {
        closeMessageModal();
    });
    
    // 回复文本框中按回车提交
    $('#replyText').on('keydown', function(e) {
        if (e.ctrlKey && e.keyCode === 13) {
            submitReply();
        }
    });
    
    // ESC 键关闭模态框
    $(document).on('keydown', function(e) {
        if (e.keyCode === 27) { // ESC 键
            if ($('#replyModal').hasClass('active')) {
                closeReplyModal();
            }
            if ($('#editModal').hasClass('active')) {
                closeEditModal();
            }
            if ($('#deleteModal').hasClass('active')) {
                closeDeleteModal();
            }
            if ($('#messageModal').hasClass('active')) {
                closeMessageModal();
            }
        }
    });
});
</script>



<?php include 'footer.php'; ?>
