<?php
include 'common.php';
include 'header.php';
include 'menu.php';
?>


<main class="flex-1 flex flex-col overflow-hidden bg-discord-light">
    <!-- Top Header -->
    <header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-6 z-10">
        <div class="flex items-center text-discord-muted">
            <button id="mobile-menu-btn" class="mr-4 md:hidden text-discord-text focus:outline-none">
                <i class="fas fa-bars"></i>
            </button>
            <i class="fas fa-paint-brush mr-2 hidden md:inline"></i>
            <span class="mx-2 hidden md:inline">/</span>
            <span class="font-medium text-discord-text"><?php _e('外观管理'); ?></span>
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
            <div class="bg-white border border-gray-100 overflow-hidden mb-8">
                <div class="p-6 border-b border-gray-100 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <h2 class="text-xl font-bold text-discord-text flex items-center">
                        <i class="fas fa-swatchbook text-discord-accent mr-2"></i><?php _e('主题列表'); ?>
                    </h2>
                    <div class="flex flex-wrap gap-2">
                        <a href="<?php $options->adminUrl('theme-editor.php'); ?>" class="booadmin-back-button">
                            <i class="fas fa-code mr-1"></i> <?php _e('编辑当前主题'); ?>
                        </a>
                        <a href="<?php $options->adminUrl('options-theme.php'); ?>" class="flex items-center px-4 py-2 bg-discord-accent text-white hover:bg-blue-600 transition-colors text-sm font-medium">
                            <i class="fas fa-cog mr-1"></i> <?php _e('主题设置'); ?>
                        </a>
                    </div>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                        <?php \Widget\Themes\Rows::alloc()->to($themes); ?>
                        <?php if ($themes->have()): ?>
                            <?php while ($themes->next()): ?>
                                <div class="bg-gray-50 p-4 border border-gray-200 flex flex-col group transition-transform transform hover:-translate-y-1 <?php if ($themes->activated): ?>ring-2 ring-discord-accent<?php endif; ?>">
                                    <div class="relative h-44 bg-gray-200 overflow-hidden">
                                        <?php if ($themes->screen): ?>
                                            <img src="<?php $themes->screen(); ?>" alt="<?php $themes->name(); ?>" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                                        <?php else: ?>
                                            <div class="w-full h-full flex items-center justify-center bg-gray-100 text-gray-400">
                                                <i class="fas fa-image text-4xl"></i>
                                            </div>
                                        <?php endif; ?>
                                        <?php if ($themes->activated): ?>
                                            <div class="absolute top-2 right-2 bg-green-500 text-white text-xs font-bold px-2 py-1">
                                                <i class="fas fa-check mr-1"></i> <?php _e('使用中'); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="flex-1 flex flex-col mt-4">
                                        <div class="flex justify-between items-start mb-2">
                                            <h3 class="text-base font-bold text-discord-text truncate" title="<?php $themes->title(); ?>"><?php $themes->title(); ?></h3>
                                            <span class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5"><?php $themes->version(); ?></span>
                                        </div>
                                        <p class="text-sm text-discord-muted mb-4 line-clamp-2 flex-1"><?php echo strip_tags($themes->description); ?></p>
                                        <div class="flex items-center justify-between mt-auto pt-4 border-t border-gray-100">
                                            <div class="text-xs text-gray-500 flex items-center">
                                                <i class="fas fa-user mr-1"></i> <?php $themes->author(); ?>
                                            </div>
                                            <div class="flex space-x-2">
                                                <?php if (!$themes->activated): ?>
                                                    <a href="<?php echo booadminActionUrl('/action/themes-edit?change=' . $themes->name); ?>" class="flex items-center px-3 py-1.5 bg-discord-accent text-white text-xs font-medium hover:bg-blue-600 transition-colors">
                                                        <i class="fas fa-check mr-1"></i> <?php _e('启用'); ?>
                                                    </a>
                                                <?php else: ?>
                                                    <a href="<?php $options->adminUrl('options-theme.php'); ?>" class="flex items-center px-3 py-1.5 bg-gray-100 text-discord-text text-xs font-medium hover:bg-gray-200 transition-colors">
                                                        <i class="fas fa-cog mr-1"></i> <?php _e('设置'); ?>
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="col-span-full text-center py-10 bg-gray-50 border border-dashed border-gray-300">
                                <p class="text-gray-500 text-lg mb-2 flex items-center justify-center"><i class="fas fa-info-circle mr-2"></i><?php _e('没有可用主题'); ?></p>
                                <p class="text-gray-400 text-sm"><?php _e('请上传主题到 <span class="font-mono bg-gray-100 px-1">/usr/themes</span> 目录'); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include 'copyright.php'; ?>
</main>

<?php
include 'common-js.php';
include 'footer.php';
?>
