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

                    <div class="table-wrapper" data-table-scroll>
                    <table class="w-full text-left border-collapse typecho-list-table">
                        <colgroup>
                            <col style="width:40px">
                            <col style="width:64px;text-align:center">
                            <col style="width:120px;text-align:left">
                            <col style="text-align:left">
                            <col style="width:192px;text-align:right">
                        </colgroup>
                        <thead>
                            <tr class="text-xs font-bold text-gray-500 uppercase border-b border-gray-100 bg-gray-50/50">
                                <th class="pl-4 py-3"></th>
                                <th class="py-3"><?php _e('头像'); ?></th>
                                <th class="py-3"><?php _e('作者'); ?></th>
                                <th class="py-3"><?php _e('内容'); ?></th>
                                <th class="py-3 pr-4"><?php _e('操作'); ?></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php if($comments->have()): ?>
                                <?php 
                                // Store comments data for card view
                                $commentsData = [];
                                while($comments->next()): 
                                    $comment = array(
                                        'author'    =>  $comments->author,
                                        'mail'      =>  $comments->mail,
                                        'url'       =>  $comments->url,
                                        'ip'        =>  $comments->ip,
                                        'type'        =>  $comments->type,
                                        'text'      =>  $comments->text
                                    );
                                    
                                    // Use getAvatar function for consistent avatar handling
                                    $gravatarHtml = '';
                                    if ('comment' == $comments->type) {
                                        $gravatarHtml = getAvatar($comments->mail, $comments->author, 40);
                                    }
                                    
                                    // Store data for card view
                                    $commentsData[] = [
                                        'coid' => $comments->coid,
                                        'id' => $comments->theId,
                                        'author' => $comments->author,
                                        'mail' => $comments->mail,
                                        'url' => $comments->url,
                                        'ip' => $comments->ip,
                                        'type' => $comments->type,
                                        'text' => $comments->text,
                                        'content' => $comments->content,
                                        'dateWord' => $comments->dateWord,
                                        'title' => $comments->title,
                                        'permalink' => $comments->permalink,
                                        'status' => $comments->status,
                                        'gravatar' => $gravatarHtml,
                                        'replyUrl' => $security->getIndex('/action/comments-edit?do=reply&coid=' . $comments->coid),
                                        'editUrl' => $security->getIndex('/action/comments-edit?do=edit&coid=' . $comments->coid),
                                        'approvedUrl' => $security->getIndex('/action/comments-edit?do=approved&coid=' . $comments->coid),
                                        'spamUrl' => $security->getIndex('/action/comments-edit?do=spam&coid=' . $comments->coid),
                                        'deleteUrl' => $security->getIndex('/action/comments-edit?do=delete&coid=' . $comments->coid),
                                        'comment' => $comment
                                    ];
                                ?>
                                    <tr id="<?php $comments->theId(); ?>" data-comment="<?php echo htmlspecialchars(json_encode($comment));
                                    ?>" class="group hover:bg-gray-50 transition-colors">
                                        <td class="pl-4 py-3 align-top">
                                            <input type="checkbox" value="<?php $comments->coid(); ?>" name="coid[]" class="text-discord-accent focus:ring-discord-accent border-gray-300 mt-1">
                                        </td>
                                        <td class="py-3 align-top">
                                            <div class="w-10 h-10 mx-auto flex items-center justify-center">
                                                <?php if ('comment' == $comments->type): ?>
                                                    <?php echo getAvatar($comments->mail, $comments->author, 40); ?>
                                                <?php else: ?>
                                                    <div class="flex items-center justify-center w-full h-full bg-gray-200 text-gray-500"><i class="fas fa-quote-right"></i></div>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td class="py-3 align-top text-sm">
                                            <div class="font-medium text-discord-text">
                                                <?php if($comments->url): ?>
                                                    <a href="<?php $comments->url(); ?>" target="_blank" class="hover:underline"><?php $comments->author(); ?></a>
                                                <?php else: ?>
                                                    <?php $comments->author(); ?>
                                                <?php endif; ?>
                                            </div>
                                            <div class="text-xs text-gray-400 mt-0.5">
                                                <?php if($comments->mail): ?>
                                                    <a href="mailto:<?php $comments->mail(); ?>" class="hover:text-discord-accent block"><?php $comments->mail(); ?></a>
                                                <?php endif; ?>
                                                <span class="block mt-0.5"><?php $comments->ip(); ?></span>
                                            </div>
                                        </td>
                                        <td class="py-3 align-top text-sm text-gray-600">
                                            <div class="mb-1 text-xs text-gray-400">
                                                <?php $comments->dateWord(); ?> 于 <a href="<?php $comments->permalink(); ?>" class="text-discord-accent hover:underline"><?php $comments->title(); ?></a>
                                            </div>
                                            <div class="comment-content prose prose-sm max-w-none text-gray-700">
                                                <?php $comments->content(); ?>
                                            </div>
                                        </td>
                                        <td class="py-3 pr-4 align-top text-sm">
                                            <div class="flex flex-col items-end space-y-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                                <a href="#<?php $comments->theId(); ?>" rel="<?php $security->index('/action/comments-edit?do=reply&coid=' . $comments->coid); ?>" class="operate-reply text-discord-accent hover:underline"><i class="fas fa-reply mr-1"></i><?php _e('回复'); ?></a>
                                                <a href="#<?php $comments->theId(); ?>" rel="<?php $security->index('/action/comments-edit?do=edit&coid=' . $comments->coid); ?>" class="operate-edit text-gray-500 hover:text-discord-accent"><i class="fas fa-edit mr-1"></i><?php _e('编辑'); ?></a>
                                                
                                                <?php if('approved' == $comments->status): ?>
                                                    <!-- Current status approved -->
                                                <?php else: ?>
                                                    <a href="<?php $security->index('/action/comments-edit?do=approved&coid=' . $comments->coid); ?>" class="operate-approved text-green-600 hover:underline"><i class="fas fa-check mr-1"></i><?php _e('通过'); ?></a>
                                                <?php endif; ?>
                                                
                                                <?php if('spam' == $comments->status): ?>
                                                    <!-- Current status spam -->
                                                <?php else: ?>
                                                    <a href="<?php $security->index('/action/comments-edit?do=spam&coid=' . $comments->coid); ?>" class="operate-spam text-orange-500 hover:underline"><i class="fas fa-ban mr-1"></i><?php _e('标为垃圾'); ?></a>
                                                <?php endif; ?>
                                                
                                                <a lang="<?php _e('你确认要删除%s的评论吗?', htmlspecialchars($comments->author)); ?>" href="<?php $security->index('/action/comments-edit?do=delete&coid=' . $comments->coid); ?>" class="operate-delete text-red-500 hover:underline"><i class="fas fa-trash-alt mr-1"></i><?php _e('删除'); ?></a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
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
                            <?php foreach ($commentsData as $commentData): ?>
                                <div class="content-card" id="card-<?php echo $commentData['coid']; ?>" data-comment='<?php echo htmlspecialchars(json_encode($commentData['comment'])); ?>'>
                                    <input type="checkbox" value="<?php echo $commentData['coid']; ?>" name="coid[]" class="card-checkbox text-discord-accent focus:ring-discord-accent border-gray-300">
                                    
                                    <div class="card-header">
                                        <div class="flex items-center space-x-3 flex-1">
                                            <div class="w-10 h-10 flex-shrink-0 flex items-center justify-center">
                                                <?php if ('comment' == $commentData['type'] && $commentData['gravatar']): ?>
                                                    <?php echo getAvatar($commentData['mail'], $commentData['author'], 40); ?>
                                                <?php else: ?>
                                                    <div class="flex items-center justify-center w-full h-full bg-gray-200 text-gray-500"><i class="fas fa-quote-right"></i></div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div class="card-title font-medium text-discord-text">
                                                    <?php if($commentData['url']): ?>
                                                        <a href="<?php echo $commentData['url']; ?>" target="_blank" class="hover:underline"><?php echo htmlspecialchars($commentData['author']); ?></a>
                                                    <?php else: ?>
                                                        <?php echo htmlspecialchars($commentData['author']); ?>
                                                    <?php endif; ?>
                                                </div>
                                                <?php if($commentData['mail']): ?>
                                                    <div class="text-xs text-gray-400 truncate">
                                                        <a href="mailto:<?php echo $commentData['mail']; ?>" class="hover:text-discord-accent"><?php echo htmlspecialchars($commentData['mail']); ?></a>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="card-meta">
                                        <div class="card-meta-item">
                                            <i class="fas fa-clock text-gray-400"></i>
                                            <span><?php echo $commentData['dateWord']; ?></span>
                                        </div>
                                        <div class="card-meta-item">
                                            <i class="fas fa-file-alt text-gray-400"></i>
                                            <a href="<?php echo $commentData['permalink']; ?>" class="hover:text-discord-accent truncate" target="_blank"><?php echo htmlspecialchars($commentData['title']); ?></a>
                                        </div>
                                        <?php if($commentData['ip']): ?>
                                        <div class="card-meta-item">
                                            <i class="fas fa-map-marker-alt text-gray-400"></i>
                                            <span><?php echo htmlspecialchars($commentData['ip']); ?></span>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="card-content comment-content prose prose-sm max-w-none text-gray-700">
                                        <?php echo $commentData['content']; ?>
                                    </div>
                                    
                                    <div class="card-actions">
                                        <a href="#" class="operate-reply" data-coid="<?php echo $commentData['coid']; ?>" data-rel="<?php echo $commentData['replyUrl']; ?>">
                                            <i class="fas fa-reply"></i> <?php _e('回复'); ?>
                                        </a>
                                        <a href="#" class="operate-edit" data-coid="<?php echo $commentData['coid']; ?>" data-rel="<?php echo $commentData['editUrl']; ?>">
                                            <i class="fas fa-edit"></i> <?php _e('编辑'); ?>
                                        </a>
                                        <?php if('approved' != $commentData['status']): ?>
                                            <a href="<?php echo $commentData['approvedUrl']; ?>" class="operate-approved text-green-600"><i class="fas fa-check"></i> <?php _e('通过'); ?></a>
                                        <?php endif; ?>
                                        <?php if('spam' != $commentData['status']): ?>
                                            <a href="<?php echo $commentData['spamUrl']; ?>" class="operate-spam text-orange-500"><i class="fas fa-ban"></i> <?php _e('标为垃圾'); ?></a>
                                        <?php endif; ?>
                                        <a href="<?php echo $commentData['deleteUrl']; ?>" class="operate-delete text-red-500" lang="<?php _e('你确认要删除%s的评论吗?', htmlspecialchars($commentData['author'])); ?>">
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
// Global variables for modal state
var currentReplyUrl = '';
var currentEditUrl = '';
var currentEditRowId = '';
var currentEditCardId = '';
var currentDeleteUrl = '';
var currentDeleteTarget = null;

// Modal control functions
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
    
    var gravatarHtml = '';
    if (commentData.type === 'comment') {
        var authorFirstChar = commentData.author ? commentData.author.charAt(0) : '?';
        var gravatarUrl = '';
        
        // Try to get gravatar from the row
        var $row = $('#' + commentData.id);
        if ($row.length > 0) {
            var $gravatar = $row.find('td:eq(1) img');
            if ($gravatar.length > 0) {
                gravatarUrl = $gravatar.attr('src');
            }
        }
        // If not found in table, try card view
        if (!gravatarUrl) {
            var $card = $('.content-card[data-comment*=\'"author":"' + commentData.author + '"\']').first();
            if ($card.length > 0) {
                var $cardGravatar = $card.find('.card-header img');
                if ($cardGravatar.length > 0) {
                    gravatarUrl = $cardGravatar.attr('src');
                }
            }
        }
        
        if (gravatarUrl) {
            gravatarHtml = '<div class="relative w-8 h-8"><img src="' + gravatarUrl + '" alt="' + commentData.author + '" class="w-full h-full object-cover border border-gray-200" onerror="this.classList.add(\'booadmin-avatar-image-hidden\'); this.nextElementSibling.classList.add(\'is-visible\');" /><div class="booadmin-avatar-fallback w-full h-full items-center justify-center bg-gradient-to-br from-blue-500 to-blue-600 text-white font-bold text-xs border border-gray-200 absolute inset-0">' + authorFirstChar + '</div></div>';
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
        + '<div class="original-comment-author">' + commentData.author + '</div>'
        + '<div class="original-comment-meta">';
    
    if (commentData.mail) {
        originalCommentHtml += commentData.mail + ' · ';
    }
    if (commentData.ip) {
        originalCommentHtml += commentData.ip;
    }
    
    originalCommentHtml += '</div></div></div>'
        + '<div class="original-comment-content">' + DOMPurify.sanitize(commentData.text, {USE_PROFILES: {html: true}}) + '</div>';
    
    $('#originalCommentArea').html(originalCommentHtml);
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
    
    $.post(currentReplyUrl, {text: text}, function(o) {
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

function openEditModal(commentData, actionUrl, rowId, cardId) {
    currentEditUrl = actionUrl;
    currentEditRowId = rowId;
    currentEditCardId = cardId;
    
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
    currentEditCardId = '';
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
    
    var $target = currentDeleteTarget;
    var $tr = $target.closest('tr');
    var $card = $target.closest('.content-card');
    
    function rememberScroll () {
        $(window).bind('beforeunload', function () {
            $.cookie('__typecho_comments_scroll', $('body').scrollTop());
        });
    }
    
    if ($tr.length > 0) {
        $tr.fadeOut(function () {
            rememberScroll();
            window.location.href = currentDeleteUrl;
        });
    } else if ($card.length > 0) {
        $card.fadeOut(function () {
            rememberScroll();
            window.location.href = currentDeleteUrl;
        });
    } else {
        rememberScroll();
        window.location.href = currentDeleteUrl;
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
    
    $.post(currentEditUrl, formData, function(o) {
        if (o && o.comment) {
            // Update table row if exists
            if (currentEditRowId) {
                var $row = $('#' + currentEditRowId);
                if ($row.length > 0) {
                    // Update data attribute
                    var oldComment = $row.data('comment');
                    oldComment.author = formData.author;
                    oldComment.mail = formData.mail;
                    oldComment.url = formData.url;
                    oldComment.text = formData.text;
                    $row.data('comment', oldComment);
                    
                    // Update author info column
                    var authorHtml = '<div class="font-medium text-discord-text">'
                        + (formData.url ? '<a target="_blank" href="' + formData.url + '" class="hover:underline">' + formData.author + '</a>' : formData.author)
                        + '</div><div class="text-xs text-gray-400 mt-0.5">';
                    
                    if (formData.mail) {
                        authorHtml += '<a href="mailto:' + formData.mail + '" class="hover:text-discord-accent block">' + formData.mail + '</a>';
                    }
                    
                    if (oldComment.ip) {
                        authorHtml += '<span class="block mt-0.5">' + oldComment.ip + '</span>';
                    }
                    
                    authorHtml += '</div>';
                    
                    $row.find('td:eq(2)').html(authorHtml).effect('highlight');
                    
                    // Update content
                    var content = DOMPurify.sanitize(o.comment.content, {USE_PROFILES: {html: true}});
                    $row.find('.comment-content').html(content).effect('highlight');
                }
            }
            
            // Update card if exists
            if (currentEditCardId) {
                var $card = $('#' + currentEditCardId);
                if ($card.length > 0) {
                    // Update data attribute
                    var oldComment = JSON.parse($card.attr('data-comment'));
                    oldComment.author = formData.author;
                    oldComment.mail = formData.mail;
                    oldComment.url = formData.url;
                    oldComment.text = formData.text;
                    $card.attr('data-comment', JSON.stringify(oldComment));
                    
                    // Update author info
                    var authorHtml = formData.url ? 
                        '<a href="' + formData.url + '" target="_blank" class="hover:underline">' + formData.author + '</a>' : 
                        formData.author;
                    $card.find('.card-title').html(authorHtml);
                    
                    if (formData.mail) {
                        $card.find('.card-header .text-xs').html('<a href="mailto:' + formData.mail + '" class="hover:text-discord-accent">' + formData.mail + '</a>');
                    } else {
                        $card.find('.card-header .text-xs').html('');
                    }
                    
                    // Update content
                    var content = DOMPurify.sanitize(o.comment.content, {USE_PROFILES: {html: true}});
                    $card.find('.card-content').html(content).effect('highlight');
                }
            }
            
            closeEditModal();
        }
    }, 'json').fail(function() {
        showMessageModal('<?php _e('错误'); ?>', '<?php _e('保存失败，请重试'); ?>');
    });
}

$(document).ready(function () {
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

    // ===== 直接劫持所有操作按钮的点击事件 =====
    // 使用捕获阶段优先拦截所有点击
    document.addEventListener('click', function(e) {
        var target = e.target;
        
        // 向上查找a标签
        while (target && target.tagName !== 'A' && target !== document.body) {
            target = target.parentElement;
        }
        
        if (!target || target.tagName !== 'A') return;
        
        var $target = $(target);
        
        // 处理回复按钮
        if ($target.hasClass('operate-reply')) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            
            var actionUrl = $target.attr('rel') || $target.data('rel');
            var $tr = $target.closest('tr');
            var $card = $target.closest('.content-card');
            var commentData = null;
            
            if ($tr.length > 0) {
                commentData = $tr.data('comment');
                if (commentData) {
                    commentData.id = $tr.attr('id');
                }
            } else if ($card.length > 0) {
                commentData = JSON.parse($card.attr('data-comment'));
                commentData.id = $card.attr('id');
            }
            
            if (commentData && actionUrl) {
                openReplyModal(commentData, actionUrl);
            }
            return false;
        }
        
        // 处理编辑按钮
        if ($target.hasClass('operate-edit')) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            
            var actionUrl = $target.attr('rel') || $target.data('rel');
            var $tr = $target.closest('tr');
            var $card = $target.closest('.content-card');
            var commentData = null;
            var rowId = '';
            var cardId = '';
            
            if ($tr.length > 0) {
                commentData = $tr.data('comment');
                rowId = $tr.attr('id');
            } else if ($card.length > 0) {
                commentData = JSON.parse($card.attr('data-comment'));
                cardId = $card.attr('id');
            }
            
            if (commentData && actionUrl) {
                openEditModal(commentData, actionUrl, rowId, cardId);
            }
            return false;
        }
        
        // 处理删除按钮
        if ($target.hasClass('operate-delete')) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            
            var href = $target.attr('href');
            var authorName = '';
            
            // 获取作者名称
            var $tr = $target.closest('tr');
            var $card = $target.closest('.content-card');
            
            if ($tr.length > 0) {
                var commentData = $tr.data('comment');
                if (commentData) {
                    authorName = commentData.author;
                }
            } else if ($card.length > 0) {
                var commentData = JSON.parse($card.attr('data-comment'));
                if (commentData) {
                    authorName = commentData.author;
                }
            }
            
            openDeleteModal(authorName || '<?php _e('该用户'); ?>', href, $target);
            return false;
        }
        
        // 处理通过/待审核/垃圾标记按钮
        if ($target.hasClass('operate-approved') || $target.hasClass('operate-waiting') || $target.hasClass('operate-spam')) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            
            rememberScroll();
            window.location.href = $target.attr('href');
            return false;
        }
    }, true); // 使用捕获阶段
    
    // Modal close on background click
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
    
    // Message modal confirm button click
    $('#messageModalConfirm').click(function() {
        var $modal = $('#messageModal');
        var isConfirm = $modal.data('is-confirm');
        var confirmUrl = $modal.data('confirm-url');
        
        if (isConfirm && confirmUrl) {
            rememberScroll();
            window.location.href = confirmUrl;
        } else {
            closeMessageModal();
        }
    });
    
    // Message modal cancel button click
    $('#messageModalCancel').click(function() {
        closeMessageModal();
    });
    
    // Enter key in reply textarea submits
    $('#replyText').on('keydown', function(e) {
        if (e.ctrlKey && e.keyCode === 13) {
            submitReply();
        }
    });
    
    // ESC key closes modals
    $(document).on('keydown', function(e) {
        if (e.keyCode === 27) { // ESC key
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


