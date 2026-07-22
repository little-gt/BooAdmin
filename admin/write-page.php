<?php
include 'common.php';
include 'header.php';
include 'menu.php';

$page = \Widget\Contents\Page\Edit::alloc()->prepare();

$parentPageId = $page->getParent();
$parentPages = [0 => _t('不选择')];
$parents = \Widget\Contents\Page\Admin::allocWithAlias(
    'options',
    'ignoreRequest=1' . ($request->is('cid') ? '&ignore=' . $request->get('cid') : '')
);

while ($parents->next()) {
    $parentPages[$parents->cid] = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $parents->levels) . $parents->title;
}
?>
<main class="write-screen flex-1 flex flex-col overflow-hidden bg-discord-light">
    <!-- Top Header -->
    <header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-6 z-10">
        <div class="flex items-center text-discord-muted">
            <button id="mobile-menu-btn" class="mr-4 md:hidden text-discord-text focus:outline-none">
                <i class="fas fa-bars"></i>
            </button>
            <i class="fas fa-file-alt mr-2 hidden md:inline"></i>
            <span class="mx-2 hidden md:inline">/</span>
            <span class="font-medium text-discord-text"><?php _e('创建页面'); ?></span>
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

    <!-- Content Area -->
    <div class="flex-1 min-w-0 overflow-x-hidden overflow-y-auto p-4 md:p-8">
        <div class="w-full max-w-none min-w-0 mx-auto h-full flex flex-col">
            <form class="flex flex-col lg:flex-row gap-6 flex-1 min-h-0 min-w-0" action="<?php $security->index('/action/contents-page-edit'); ?>" method="post" name="write_page">
                <!-- Main Editor Area -->
                <div class="flex-1 min-w-0 flex flex-col min-h-0 overflow-y-auto lg:overflow-visible">
                    <?php if ($page->draft): ?>
                        <div class="mb-4 bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 text-sm">
                            <?php if ($page->draft['cid'] != $page->cid): ?>
                                <?php $pageModifyDate = new \Typecho\Date($page->draft['modified']); ?>
                                <cite><?php _e('你正在编辑的是保存于 %s 的修订版, 你也可以 <a href="%s" class="underline">删除它</a>', $pageModifyDate->word(),
                                        $security->getIndex('/action/contents-page-edit?do=deleteDraft&cid=' . $page->cid)); ?></cite>
                            <?php else: ?>
                                <cite><?php _e('当前正在编辑的是未发布的草稿'); ?></cite>
                            <?php endif; ?>
                            <input name="draft" type="hidden" value="<?php echo $page->draft['cid'] ?>"/>
                        </div>
                    <?php endif; ?>

                    <div class="bg-white border border-gray-100 p-6 mb-6 min-w-0 overflow-x-hidden">
                        <label for="title" class="sr-only"><?php _e('标题'); ?></label>
                        <input type="text" id="title" name="title" autocomplete="off" value="<?php $page->title(); ?>"
                               placeholder="<?php _e('在此输入标题'); ?>" class="w-full text-2xl font-bold border-none focus:outline-none focus:ring-0 placeholder-gray-300 text-discord-text mb-4 p-0"/>
                        
                        <!-- Permalink -->
                        <?php $permalink = \Typecho\Common::url($options->routingTable['page']['url'], $options->index);
                        [$scheme, $permalink] = explode(':', $permalink, 2);
                        $permalink = ltrim($permalink, '/');
                        $permalink = preg_replace("/\[([_a-z0-9-]+)[^\]]*\]/i", "{\\1}", $permalink);
                        if ($page->have()) {
                            $permalink = preg_replace_callback(
                                "/\{(cid)\}/i",
                                function ($matches) use ($page) {
                                    $key = $matches[1];
                                    return $page->getRouterParam($key);
                                },
                                $permalink
                            );
                        }
                        $input = '<input type="text" id="slug" name="slug" autocomplete="off" value="' . htmlspecialchars($page->slug ?? '') . '" class="mono border-b border-gray-300 focus:border-discord-accent focus:outline-none px-2 py-2 text-sm w-48" style="min-height: 28px;" />';
                        ?>
                        <p class="text-sm text-discord-muted flex items-center mb-4 font-mono">
                             <i class="fas fa-link mr-2 text-gray-400"></i>
                             <span class="mr-1"><?php echo $scheme . '://'; ?></span>
                             <span class="truncate"><?php echo preg_replace_callback("/\{(slug|directory)\}/i", function ($matches) use ($input) {
                                if ($matches[1] == 'slug') {
                                    return $input;
                                } else {
                                    return '{directory/' . $input . '}';
                                }
                            }, $permalink); ?></span>
                        </p>

                         <!-- Editor -->
                        <div class="editor-container pt-4">
                            <label for="text" class="sr-only"><?php _e('页面内容'); ?></label>
                            <textarea style="height: <?php $options->editorSize(); ?>px" autocomplete="off" id="text"
                                      name="text" class="w-full mono border-none focus:outline-none focus:ring-0 resize-none text-discord-text bg-transparent" placeholder="<?php _e('开始撰写...'); ?>"><?php echo htmlspecialchars($page->text); ?></textarea>
                        </div>
                        
                        <?php include 'custom-fields.php'; ?>
                    </div>
                    
                    <div class="flex items-center justify-between mb-8 px-1">
                        <div class="flex items-center space-x-3 ml-auto">
                            <input type="hidden" name="do" value="publish" />
                            <input type="hidden" name="cid" value="<?php $page->cid(); ?>"/>
                            <?php if ($options->markdown): ?>
                                <input type="hidden" name="markdown" value="<?php echo (!$page->have() || $page->isMarkdown) ? '1' : '0'; ?>" data-write-markdown="1"/>
                            <?php endif; ?>

                            <button type="button" id="btn-preview" class="px-4 py-2 bg-white border border-gray-300 text-discord-text hover:bg-gray-50 transition-colors text-sm font-medium">
                                <i class="fas fa-eye mr-1"></i> <?php _e('预览'); ?>
                            </button>
                            <button type="submit" name="do" value="save" id="btn-save" class="px-4 py-2 bg-white border border-gray-300 text-discord-text hover:bg-gray-50 transition-colors text-sm font-medium">
                                <i class="fas fa-save mr-1"></i> <?php _e('保存草稿'); ?>
                            </button>
                            <button type="submit" name="do" value="publish" id="btn-submit" class="px-6 py-2 bg-discord-accent text-white font-medium hover:bg-blue-600 transition-colors text-sm">
                                <i class="fas fa-paper-plane mr-1"></i> <?php _e('发布页面'); ?>
                            </button>
                        </div>
                    </div>

                    <?php \Typecho\Plugin::factory('admin/write-page.php')->call('content', $page); ?>
                </div>

                <!-- Sidebar Options -->
                <div class="lg:w-96 flex-shrink-0 flex flex-col">
                    <!-- Tabs Header -->
                    <div class="flex items-center space-x-1 mb-4 typecho-option-tabs bg-gray-100 p-1 select-none">
                         <button type="button" class="flex-1 py-2 text-sm font-medium text-discord-text bg-white focus:outline-none transition-all duration-200" data-target="#tab-advance"><?php _e('设置'); ?></button>
                         <button type="button" class="flex-1 py-2 text-sm font-medium text-gray-500 hover:text-discord-text focus:outline-none transition-all duration-200" id="tab-files-btn" data-target="#tab-files"><?php _e('附件'); ?></button>
                    </div>
                    <br>
                    <!-- Tab Content Container -->
                    <div class="bg-white border border-gray-100 overflow-hidden flex-1">
                        <div id="tab-advance" class="p-6 space-y-6 tab-content h-full overflow-y-auto custom-scrollbar">
                            <!-- Date -->
                            <div class="group">
                                <label for="date" class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2 group-focus-within:text-discord-accent transition-colors"><?php _e('发布日期'); ?></label>
                                <div class="relative">
                                    <input class="w-full pl-3 pr-10 py-2.5 bg-gray-50 border border-gray-200 text-sm text-discord-text focus:outline-none focus:border-discord-accent focus:bg-white focus:ring-2 focus:ring-discord-accent/10 transition-all" type="text" name="date" id="date" autocomplete="off"
                                          value="<?php $page->have() && $page->created > 0 ? $page->date('Y-m-d H:i') : ''; ?>"/>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-400">
                                        <i class="far fa-calendar-alt"></i>
                                    </div>
                                </div>
                            </div>

                            <!-- Order -->
                            <div class="group">
                                <label for="order" class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2 group-focus-within:text-discord-accent transition-colors"><?php _e('页面顺序'); ?></label>
                                <input type="number" id="order" name="order" value="<?php $page->order(); ?>" class="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 text-sm focus:outline-none focus:border-discord-accent focus:bg-white focus:ring-2 focus:ring-discord-accent/10 transition-all"/>
                                <p class="text-xs text-gray-400 mt-1 flex items-center"><i class="fas fa-info-circle mr-1"></i> <?php _e('数字越小越靠前'); ?></p>
                            </div>

                            <!-- Template -->
                            <div class="group">
                                <label for="template" class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2 group-focus-within:text-discord-accent transition-colors"><?php _e('自定义模板'); ?></label>
                                <div class="relative flex">
                                    <select name="template" id="template" class="flex-1 min-w-0 pl-3 pr-10 py-2.5 bg-gray-50 border border-gray-200 text-sm focus:outline-none focus:border-discord-accent focus:bg-white focus:ring-2 focus:ring-discord-accent/10 transition-all appearance-none">
                                        <option value=""><?php _e('不选择'); ?></option>
                                        <?php $templates = $page->getTemplates();
                                        foreach ($templates as $template => $name): ?>
                                            <option value="<?php echo $template; ?>"<?php if ($template == $page->template): ?> selected="true"<?php endif; ?>><?php echo $name; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-400">
                                        <i class="fas fa-chevron-down text-xs"></i>
                                    </div>
                                </div>
                            </div>

                            <!-- Parent Page -->
                            <div class="group">
                                <label for="parent" class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2 group-focus-within:text-discord-accent transition-colors"><?php _e('父级页面'); ?></label>
                                <div class="relative flex">
                                    <select name="parent" id="parent" class="flex-1 min-w-0 pl-3 pr-10 py-2.5 bg-gray-50 border border-gray-200 text-sm focus:outline-none focus:border-discord-accent focus:bg-white focus:ring-2 focus:ring-discord-accent/10 transition-all appearance-none">
                                        <?php foreach ($parentPages as $pageId => $pageTitle): ?>
                                            <option value="<?php echo $pageId; ?>"<?php if ($pageId == ($page->parent ?? $parentPageId)): ?> selected="true"<?php endif; ?>><?php echo $pageTitle; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-400">
                                        <i class="fas fa-chevron-down text-xs"></i>
                                    </div>
                                </div>
                            </div>

                            <?php \Typecho\Plugin::factory('admin/write-page.php')->call('option', $page); ?>

                            <!-- Advanced Toggle -->
                            <details id="advance-panel" class="group border-t border-gray-100 pt-4">
                                <summary class="flex items-center cursor-pointer text-sm text-discord-accent font-medium select-none py-2 hover:bg-gray-50 px-2 -mx-2 transition-colors">
                                    <span class="bg-discord-accent/10 text-discord-accent p-1 mr-2 group-open:rotate-90 transition-transform duration-200">
                                        <i class="fas fa-chevron-right text-xs"></i>
                                    </span>
                                    <?php _e('高级选项'); ?>
                                </summary>

                                <div class="space-y-6 pt-4 px-2">
                                     <?php if ($options->markdown): ?>
                                        <div>
                                            <label class="block text-sm font-bold text-discord-text mb-2"><?php _e('写作格式'); ?></label>
                                            <label class="flex items-center">
                                                <input id="use-markdown" type="checkbox" class="mr-2 text-discord-accent focus:ring-discord-accent" <?php if (!$page->have() || $page->isMarkdown): ?>checked="true"<?php endif; ?> />
                                                <span class="text-sm text-discord-text"><?php _e('使用 Markdown 撰写'); ?></span>
                                            </label>
                                        </div>
                                    <?php endif; ?>

                                     <div>
                                        <label for="visibility" class="block text-sm font-bold text-discord-text mb-2"><?php _e('公开度'); ?></label>
                                        <select id="visibility" name="visibility" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 text-sm focus:outline-none focus:border-discord-accent transition-colors">
                                            <option value="publish"<?php if ($page->status == 'publish' || !$page->status): ?> selected<?php endif; ?>><?php _e('公开'); ?></option>
                                            <option value="hidden"<?php if ($page->status == 'hidden'): ?> selected<?php endif; ?>><?php _e('隐藏'); ?></option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-bold text-discord-text mb-2"><?php _e('权限控制'); ?></label>
                                        <ul class="space-y-2">
                                            <li class="flex items-center">
                                                <input id="allowComment" name="allowComment" type="checkbox" value="1" class="mr-2 text-discord-accent focus:ring-discord-accent" <?php if ($page->allow('comment')): ?>checked="true"<?php endif; ?> />
                                                <label for="allowComment" class="text-sm text-discord-text"><?php _e('允许评论'); ?></label>
                                            </li>
                                            <li class="flex items-center">
                                                <input id="allowPing" name="allowPing" type="checkbox" value="1" class="mr-2 text-discord-accent focus:ring-discord-accent" <?php if ($page->allow('ping')): ?>checked="true"<?php endif; ?> />
                                                <label for="allowPing" class="text-sm text-discord-text"><?php _e('允许被引用'); ?></label>
                                            </li>
                                            <li class="flex items-center">
                                                <input id="allowFeed" name="allowFeed" type="checkbox" value="1" class="mr-2 text-discord-accent focus:ring-discord-accent" <?php if ($page->allow('feed')): ?>checked="true"<?php endif; ?> />
                                                <label for="allowFeed" class="text-sm text-discord-text"><?php _e('允许在聚合中出现'); ?></label>
                                            </li>
                                        </ul>
                                    </div>

                                    <?php \Typecho\Plugin::factory('admin/write-page.php')->call('advanceOption', $page); ?>
                                </div>
                            </details>
                            
                            <?php if ($page->have()): ?>
                                <?php $modified = new \Typecho\Date($page->modified); ?>
                                <div class="pt-4 border-t border-gray-100 text-xs text-gray-400">
                                    <p class="mb-1"><?php _e('作者:'); ?> <a href="<?php $options->adminUrl('manage-pages.php?uid=' . $page->author->uid); ?>" class="text-discord-accent hover:underline"><?php $page->author->screenName(); ?></a></p>
                                    <p><?php _e('最后更新: %s', $modified->word()); ?></p>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div id="tab-files" class="p-5 hidden tab-content h-full overflow-y-auto custom-scrollbar">
                            <?php include 'file-upload.php'; ?>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Footer自然跟随内容 -->
    <?php include 'copyright.php'; ?>
</main>

<?php
include 'common-js.php';
include 'form-js.php';
include 'write-js.php';

\Typecho\Plugin::factory('admin/write-page.php')->trigger($plugged)->call('richEditor', $page);
if (!$plugged) {
    include 'editor-js.php';
}

include 'file-upload-js.php';
include 'custom-fields-js.php';
\Typecho\Plugin::factory('admin/write-page.php')->bottom($page);
include 'footer.php';
?>
