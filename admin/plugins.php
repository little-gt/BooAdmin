<?php
include 'common.php';

// 检测推荐插件是否已安装激活
$activates = \Typecho\Plugin::export();
$hasPasskey = isset($activates['activated']) && in_array('Passkey', array_keys($activates['activated']));
$hasPassport = isset($activates['activated']) && in_array('Passport', array_keys($activates['activated']));

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
            <i class="fas fa-plug mr-2 hidden md:inline"></i>
            <span class="mx-2 hidden md:inline">/</span>
            <span class="font-medium text-discord-text"><?php _e('插件管理'); ?></span>
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
    <div class="flex-1 overflow-y-auto p-4 md:p-8">
        <div class="w-full max-w-none mx-auto">
            
            <!-- 推荐插件提示 -->
            <?php if (!$hasPasskey || !$hasPassport): ?>
            <div class="mb-6 p-5 border" style="background-color: var(--booadmin-info-bg); border-color: var(--booadmin-border);">
                <div class="flex items-start gap-3">
                    <svg class="flex-shrink-0 mt-0.5" style="color: var(--booadmin-info);" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="12" y1="16" x2="12" y2="12"/>
                        <line x1="12" y1="8" x2="12.01" y2="8"/>
                    </svg>
                    <div class="flex-1">
                        <h3 class="font-semibold mb-2" style="color: var(--booadmin-info-hover);"><?php _e('推荐安装配套插件'); ?></h3>
                        <p class="text-sm mb-3" style="color: var(--booadmin-muted);"><?php _e('以下插件为 BooAdmin 配套插件，可增强后台安全与功能体验。'); ?></p>
                        <div class="space-y-2">
                            <?php if (!$hasPasskey): ?>
                            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between p-3 border gap-3" style="background-color: var(--booadmin-surface); border-color: var(--booadmin-border);">
                                <div class="flex-1 min-w-0">
                                    <span class="font-medium" style="color: var(--booadmin-text);">Passkey</span>
                                    <span class="text-xs ml-2 block sm:inline" style="color: var(--booadmin-info);"><?php _e('通行密钥登录插件，完全开源，且符合 IEEE 安全标准，支持同一账号的多设备通信秘钥绑定，轻松实现同微软等大厂一致快捷、安全的无密码登录体验。'); ?></span>
                                </div>
                                <div class="flex items-center gap-2 text-xs shrink-0">
                                    <a href="https://cnb.cool/little-gt/Passkey" target="_blank" class="px-2 py-1 border hover:opacity-80 transition-opacity whitespace-nowrap" style="color: var(--booadmin-link); border-color: var(--booadmin-border); background-color: var(--booadmin-bg);"><?php _e('国内下载'); ?></a>
                                    <a href="https://github.com/little-gt/Passkey" target="_blank" class="px-2 py-1 border hover:opacity-80 transition-opacity whitespace-nowrap" style="color: var(--booadmin-link); border-color: var(--booadmin-border); background-color: var(--booadmin-bg);"><?php _e('GitHub'); ?></a>
                                </div>
                            </div>
                            <?php endif; ?>
                            <?php if (!$hasPassport): ?>
                            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between p-3 border gap-3" style="background-color: var(--booadmin-surface); border-color: var(--booadmin-border);">
                                <div class="flex-1 min-w-0">
                                    <span class="font-medium" style="color: var(--booadmin-text);">Passport</span>
                                    <span class="text-xs ml-2 block sm:inline" style="color: var(--booadmin-info);"><?php _e('账户密码找回插件，完全开源，且支持自定义邮件模版，内置网安标准的行为日志系统，可实现快速、安全的密码找回体验。'); ?></span>
                                </div>
                                <div class="flex items-center gap-2 text-xs shrink-0">
                                    <a href="https://cnb.cool/little-gt/Passport" target="_blank" class="px-2 py-1 border hover:opacity-80 transition-opacity whitespace-nowrap" style="color: var(--booadmin-link); border-color: var(--booadmin-border); background-color: var(--booadmin-bg);"><?php _e('国内下载'); ?></a>
                                    <a href="https://github.com/little-gt/Passport" target="_blank" class="px-2 py-1 border hover:opacity-80 transition-opacity whitespace-nowrap" style="color: var(--booadmin-link); border-color: var(--booadmin-border); background-color: var(--booadmin-bg);"><?php _e('GitHub'); ?></a>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="bg-white border border-gray-100 overflow-hidden">
                <div class="p-6 border-b border-gray-100 flex justify-between items-center">
                    <h2 class="text-xl font-bold text-discord-text flex items-center">
                        <i class="fas fa-cubes text-discord-accent mr-2"></i> <?php _e('插件列表'); ?>
                    </h2>
                    <a href="https://docs.typecho.org/plugins/download" target="_blank" class="text-sm text-discord-accent hover:underline flex items-center">
                        <i class="fas fa-external-link-alt mr-1"></i> <?php _e('获取更多插件'); ?>
                    </a>
                </div>

                <div class="p-6">
                    <?php \Widget\Plugins\Rows::allocWithAlias('activated', 'activated=1')->to($activatedPlugins); ?>
                    
                    <?php if ($activatedPlugins->have() || !empty($activatedPlugins->activatedPlugins)): ?>
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-discord-text mb-4 pl-3 border-l-4 border-green-500"><?php _e('已启用插件'); ?></h3>
                            <div class="grid grid-cols-1 gap-4">
                                <?php while ($activatedPlugins->next()): ?>
                                    <div class="bg-gray-50 p-4 border border-gray-200 flex flex-col md:flex-row items-start md:items-center justify-between" id="plugin-<?php $activatedPlugins->name(); ?>">
                                        <div class="flex-1 min-w-0 pr-4">
                                            <div class="flex items-center mb-1">
                                                <h4 class="text-base font-bold text-discord-text mr-2"><?php $activatedPlugins->title(); ?></h4>
                                                <span class="bg-gray-200 text-gray-600 text-xs px-2 py-0.5"><?php $activatedPlugins->version(); ?></span>
                                                <?php if (!$activatedPlugins->dependence): ?>
                                                    <span class="ml-2 text-red-500 text-xs" title="<?php _e('无法在此版本的 Typecho 下正常工作'); ?>"><i class="fas fa-exclamation-triangle"></i></span>
                                                <?php endif; ?>
                                            </div>
                                            <p class="text-sm text-discord-muted mb-1"><?php $activatedPlugins->description(); ?></p>
                                            <div class="text-xs text-gray-400">
                                                <?php _e('作者:'); ?> 
                                                <?php echo empty($activatedPlugins->homepage) ? $activatedPlugins->author : '<a href="' . $activatedPlugins->homepage . '" target="_blank" class="hover:text-discord-accent">' . $activatedPlugins->author . '</a>'; ?>
                                            </div>
                                        </div>
                                        <div class="mt-4 md:mt-0 flex items-center space-x-3 shrink-0">
                                            <?php if ($activatedPlugins->activate || $activatedPlugins->deactivate || $activatedPlugins->config || $activatedPlugins->personalConfig): ?>
                                                <?php if ($activatedPlugins->config): ?>
                                                    <a href="<?php $options->adminUrl('options-plugin.php?config=' . $activatedPlugins->name); ?>" class="flex items-center px-3 py-1.5 bg-discord-light text-discord-text hover:bg-gray-200 transition-colors text-sm font-medium">
                                                        <i class="fas fa-cog mr-1.5"></i> <?php _e('设置'); ?>
                                                    </a>
                                                <?php endif; ?>
                                                <a href="<?php echo booadminActionUrl('/action/plugins-edit?deactivate=' . $activatedPlugins->name); ?>" class="flex items-center px-3 py-1.5 bg-red-50 text-red-600 hover:bg-red-100 transition-colors text-sm font-medium">
                                                    <i class="fas fa-power-off mr-1.5"></i> <?php _e('禁用'); ?>
                                                </a>
                                            <?php else: ?>
                                                <span class="text-green-600 text-sm font-medium flex items-center"><i class="fas fa-check-circle mr-1"></i> <?php _e('即插即用'); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endwhile; ?>

                                <?php if (!empty($activatedPlugins->activatedPlugins)): ?>
                                    <?php foreach ($activatedPlugins->activatedPlugins as $key => $val): ?>
                                        <div class="bg-red-50 p-4 border border-red-200 flex items-center justify-between">
                                            <div>
                                                <h4 class="text-base font-bold text-red-700"><?php echo $key; ?></h4>
                                                <p class="text-sm text-red-600 mt-1"><i class="fas fa-exclamation-circle mr-1"></i> <?php _e('此插件文件已经损坏或者被不安全移除, 强烈建议你禁用它'); ?></p>
                                            </div>
                                            <a href="<?php echo booadminActionUrl('/action/plugins-edit?deactivate=' . $key); ?>" class="px-3 py-1.5 bg-red-100 text-red-700 hover:bg-red-200 transition-colors text-sm font-medium">
                                                <?php _e('禁用'); ?>
                                            </a>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php \Widget\Plugins\Rows::allocWithAlias('unactivated', 'activated=0')->to($deactivatedPlugins); ?>
                    
                    <?php if ($deactivatedPlugins->have() || !$activatedPlugins->have()): ?>
                        <div>
                            <h3 class="text-lg font-semibold text-discord-text mb-4 pl-3 border-l-4 border-gray-300"><?php _e('禁用的插件'); ?></h3>
                            <div class="grid grid-cols-1 gap-4">
                                <?php if ($deactivatedPlugins->have()): ?>
                                    <?php while ($deactivatedPlugins->next()): ?>
                                        <div class="bg-white p-4 border border-gray-200 flex flex-col md:flex-row items-start md:items-center justify-between opacity-75 hover:opacity-100" id="plugin-<?php $deactivatedPlugins->name(); ?>">
                                            <div class="flex-1 min-w-0 pr-4">
                                                <div class="flex items-center mb-1">
                                                    <h4 class="text-base font-bold text-gray-600 mr-2"><?php $deactivatedPlugins->title(); ?></h4>
                                                    <span class="bg-gray-100 text-gray-500 text-xs px-2 py-0.5"><?php $deactivatedPlugins->version(); ?></span>
                                                </div>
                                                <p class="text-sm text-gray-500 mb-1"><?php $deactivatedPlugins->description(); ?></p>
                                                <div class="text-xs text-gray-400">
                                                    <?php _e('作者:'); ?> 
                                                    <?php echo empty($deactivatedPlugins->homepage) ? $deactivatedPlugins->author : '<a href="' . $deactivatedPlugins->homepage . '" target="_blank" class="hover:text-discord-accent">' . $deactivatedPlugins->author . '</a>'; ?>
                                                </div>
                                            </div>
                                            <div class="mt-4 md:mt-0 shrink-0">
                                                <a href="<?php echo booadminActionUrl('/action/plugins-edit?activate=' . $deactivatedPlugins->name); ?>" class="flex items-center px-4 py-1.5 bg-discord-accent text-white hover:bg-blue-600 transition-colors text-sm font-medium">
                                                    <i class="fas fa-play mr-1.5"></i> <?php _e('启用'); ?>
                                                </a>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <div class="text-center py-10 bg-gray-50 border border-dashed border-gray-300">
                                        <p class="text-gray-500"><?php _e('没有安装插件'); ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>
    
    <!-- Footer自然跟随内容 -->
    <?php include 'copyright.php'; ?>
</main>

<?php
include 'common-js.php';
include 'footer.php';
?>
