<?php
include 'common.php';
include 'header.php';
include 'menu.php';

\Widget\Themes\Files::alloc()->to($files);
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
            <span class="mx-2 hidden md:inline">/</span>
            <span class="font-medium text-discord-text"><?php _e('编辑'); ?></span>
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
        <div class="w-full max-w-none mx-auto h-full flex flex-col">


            <div class="booadmin-panel booadmin-panel-flush overflow-hidden">
                <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                    <h2 class="text-lg font-bold text-discord-text flex items-center">
                        <i class="fas fa-code text-discord-accent mr-2"></i> <?php _e('编辑外观文件'); ?>
                    </h2>
                    <a href="<?php $options->adminUrl('themes.php'); ?>" class="text-sm text-discord-muted hover:text-discord-accent">
                        <i class="fas fa-arrow-left mr-1"></i> <?php _e('返回外观列表'); ?>
                    </a>
                </div>
                <div class="p-6">
                    <div id="editor-fullscreen-container" class="flex flex-col lg:flex-row gap-6 flex-1 min-h-0">
                        <!-- File List Sidebar -->
                        <div class="lg:w-64 bg-gray-50 border border-gray-100 flex flex-col overflow-hidden">
                            <div class="p-4 bg-gray-100 border-b border-gray-200 font-bold text-discord-text text-sm">
                                <?php _e('模板文件'); ?>
                            </div>
                            <ul class="overflow-y-auto flex-1 p-2 space-y-1">
                                <?php while ($files->next()): ?>
                                    <li>
                                        <a href="<?php $options->adminUrl('theme-editor.php?theme=' . $files->currentTheme() . '&file=' . $files->file); ?>" class="block px-3 py-2 text-sm truncate transition-colors <?php if ($files->current): ?>bg-discord-light text-discord-accent font-medium<?php else: ?>text-discord-muted hover:bg-gray-200 hover:text-discord-text<?php endif; ?>">
                                            <i class="fas fa-file-code mr-2 opacity-50"></i><?php $files->file(); ?>
                                        </a>
                                    </li>
                                <?php endwhile; ?>
                            </ul>
                        </div>

                        <!-- Editor Area -->
                                <div class="flex-1 bg-gray-50 border border-gray-100 flex flex-col overflow-hidden" id="editor-container">
                             <div class="p-4 border-b border-gray-200 flex justify-between items-center bg-gray-100">
                                <span class="font-mono text-sm text-discord-text font-medium"><?php echo $files->currentFile(); ?></span>
                                <div class="flex items-center space-x-3">
                                    <button type="button" id="fullscreen-btn" class="text-discord-muted hover:text-discord-accent transition-colors p-1 rounded hover:bg-gray-200" title="<?php _e('切换全屏'); ?>" aria-label="<?php _e('切换全屏'); ?>">
                                        <i class="fas fa-expand"></i>
                                    </button>
                                    <?php if (!$files->currentIsWriteable()): ?>
                                        <span class="text-xs text-red-500 bg-red-50 px-2 py-1 border border-red-100"><i class="fas fa-lock mr-1"></i> <?php _e('只读'); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <form method="post" name="theme" id="theme" action="<?php $security->index('/action/themes-edit'); ?>" class="flex-1 flex flex-col p-0 m-0">
                                <textarea name="content" id="content" class="flex-1 w-full p-4 font-mono text-sm bg-gray-900 text-gray-200 border-none resize-none focus:outline-none" spellcheck="false" <?php if (!$files->currentIsWriteable()): ?>readonly<?php endif; ?>><?php echo $files->currentContent(); ?></textarea>
                                
                                <div class="p-4 border-t border-gray-200 bg-gray-100 flex justify-end">
                                    <?php if ($files->currentIsWriteable()): ?>
                                        <input type="hidden" name="theme" value="<?php echo $files->currentTheme(); ?>"/>
                                        <input type="hidden" name="edit" value="<?php echo $files->currentFile(); ?>"/>
                                        <button type="button" id="save-btn" class="px-6 py-2 bg-discord-accent text-white font-medium hover:bg-blue-600 transition-colors text-sm">
                                            <i class="fas fa-save mr-1"></i> <?php _e('保存文件'); ?>
                                        </button>
                                    <?php else: ?>
                                        <button type="button" disabled class="px-6 py-2 bg-gray-300 text-gray-500 font-medium cursor-not-allowed text-sm">
                                            <?php _e('无法保存'); ?>
                                        </button>
                                    <?php endif; ?>
                                </div>
                                <?php $security->form('themes-edit'); ?>
                            </form>

                            <!-- 保存确认提示框 -->
                            <div id="save-confirm-modal" class="booadmin-modal hidden">
                                <div class="booadmin-dialog booadmin-dialog-sm">
                                    <h3 class="text-lg font-bold text-discord-text mb-4"><?php _e('确认保存'); ?></h3>
                                    <p class="text-discord-muted mb-6"><?php _e('您确定要保存此文件吗？此操作不可逆。'); ?></p>
                                    <div class="flex justify-end space-x-3">
                                        <button type="button" id="cancel-save" class="px-4 py-2 bg-gray-200 text-discord-text font-medium hover:bg-gray-300 transition-colors text-sm">
                                            <?php _e('取消'); ?>
                                        </button>
                                        <button type="button" id="confirm-save" class="px-4 py-2 bg-discord-accent text-white font-medium hover:bg-blue-600 transition-colors text-sm">
                                            <?php _e('确认保存'); ?>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Footer自然跟随内容 -->
    <?php include 'copyright.php'; ?>
</main>

<?php
include 'common-js.php';
?>

<script>
// 全屏切换功能
const fullscreenBtn = document.getElementById('fullscreen-btn');
const editorContainer = document.getElementById('editor-container');

fullscreenBtn.addEventListener('click', function() {
    const isEditorFullscreen = document.fullscreenElement === editorContainer;

    if (!isEditorFullscreen) {
        if (editorContainer.requestFullscreen) {
            editorContainer.requestFullscreen();
        } else if (editorContainer.mozRequestFullScreen) {
            editorContainer.mozRequestFullScreen();
        } else if (editorContainer.webkitRequestFullscreen) {
            editorContainer.webkitRequestFullscreen();
        } else if (editorContainer.msRequestFullscreen) {
            editorContainer.msRequestFullscreen();
        }
        fullscreenBtn.innerHTML = '<i class="fas fa-compress"></i>';
    } else {
        if (document.exitFullscreen) {
            document.exitFullscreen();
        } else if (document.mozCancelFullScreen) {
            document.mozCancelFullScreen();
        } else if (document.webkitExitFullscreen) {
            document.webkitExitFullscreen();
        } else if (document.msExitFullscreen) {
            document.msExitFullscreen();
        }
        fullscreenBtn.innerHTML = '<i class="fas fa-expand"></i>';
    }
});

// 监听全屏状态变化
document.addEventListener('fullscreenchange', function() {
    if (document.fullscreenElement === editorContainer) {
        fullscreenBtn.innerHTML = '<i class="fas fa-compress"></i>';
    } else {
        fullscreenBtn.innerHTML = '<i class="fas fa-expand"></i>';
    }
});

// 保存确认提示框
const saveBtn = document.getElementById('save-btn');
const saveConfirmModal = document.getElementById('save-confirm-modal');
const cancelSave = document.getElementById('cancel-save');
const confirmSave = document.getElementById('confirm-save');
const themeForm = document.getElementById('theme');

if (saveBtn) {
    saveBtn.addEventListener('click', function() {
        saveConfirmModal.classList.remove('hidden');
    });
}

if (cancelSave) {
    cancelSave.addEventListener('click', function() {
        saveConfirmModal.classList.add('hidden');
    });
}

if (confirmSave) {
    confirmSave.addEventListener('click', function() {
        themeForm.submit();
    });
}

// 点击模态框外部关闭
if (saveConfirmModal) {
    saveConfirmModal.addEventListener('click', function(e) {
        if (e.target === saveConfirmModal) {
            saveConfirmModal.classList.add('hidden');
        }
    });
}
</script>

<?php
\Typecho\Plugin::factory('admin/theme-editor.php')->call('bottom', $files);
include 'footer.php';
?>
