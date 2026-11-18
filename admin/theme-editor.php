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
                        <div class="lg:w-64 bg-gray-50 border border-gray-100 flex flex-col overflow-hidden theme-file-list">
                            <div class="p-4 bg-gray-100 border-b border-gray-200 font-bold text-discord-text text-sm theme-file-list-header">
                                <?php _e('模板文件'); ?>
                            </div>
                            <ul class="overflow-y-auto flex-1 p-2 space-y-1">
                                <?php while ($files->next()): ?>
                                    <li>
                                        <a href="<?php $options->adminUrl('theme-editor.php?theme=' . $files->currentTheme() . '&file=' . $files->file); ?>" class="block px-3 py-2 text-sm truncate transition-colors theme-file-item <?php if ($files->current): ?>bg-discord-light text-discord-accent font-medium theme-file-item-active<?php else: ?>text-discord-muted hover:bg-gray-200 hover:text-discord-text<?php endif; ?>">
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
                            
                            <form method="post" name="theme" id="theme" action="<?php $security->index('/action/themes-edit'); ?>" class="flex-1 flex flex-col p-0 m-0 min-h-0">
                                <div class="booadmin-code-wrap flex-1 min-h-0 overflow-hidden">
                                    <pre id="php-highlight-backdrop" class="booadmin-code-layer p-4" aria-hidden="true"><code id="php-highlight-code"></code></pre>
                                    <textarea name="content" id="content" class="booadmin-code-layer p-4 focus:outline-none" spellcheck="false" wrap="off" <?php if (!$files->currentIsWriteable()): ?>readonly<?php endif; ?>><?php echo $files->currentContent(); ?></textarea>
                                </div>
                                
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
                            </form>
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

<style>
/* 主题编辑器：轻量 PHP 高亮（紧急快速编辑用，无外部依赖） */
.booadmin-code-wrap {
    position: relative;
    background: #1f2937;
}
@media (prefers-color-scheme: dark) {
    .booadmin-code-wrap { background: var(--booadmin-bg); }
}
.booadmin-code-layer {
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", monospace;
    font-size: 0.875rem;
    line-height: 1.5rem;
    tab-size: 4;
    white-space: pre;
    overflow: auto;
    box-sizing: border-box;
    margin: 0;
    border: 0;
}
/* dark.css 对 textarea/pre/code 用了 !important，此处必须同级覆盖，否则暗色下出现双份文字与底色块 */
#php-highlight-backdrop {
    position: absolute;
    inset: 0;
    overflow: hidden;
    background: transparent !important;
    color: #d1d5db !important;
    pointer-events: none;
    z-index: 0;
}
#php-highlight-code {
    display: block;
    padding: 0;
    background: transparent !important;
    color: inherit !important;
}
#content.booadmin-code-layer {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    background: transparent !important;
    border: 0 !important;
    line-height: 1.5rem !important;
    color: #d1d5db !important;
    caret-color: #f9fafb !important;
    resize: none;
    z-index: 1;
}
/* 高亮就绪后才隐藏原生文字；脚本异常时退化为普通深色编辑器 */
.booadmin-code-wrap.php-highlight-ready #content.booadmin-code-layer {
    color: transparent !important;
    -webkit-text-fill-color: transparent !important;
}
.booadmin-code-wrap.php-highlight-ready #content.booadmin-code-layer::selection {
    background: rgba(59, 130, 246, 0.45);
}
.booadmin-code-layer .tok-tag { color: #c586c0; }
.booadmin-code-layer .tok-key { color: #569cd6; }
.booadmin-code-layer .tok-var { color: #9cdcfe; }
.booadmin-code-layer .tok-str { color: #ce9178; }
.booadmin-code-layer .tok-com { color: #6a9955; font-style: italic; }
.booadmin-code-layer .tok-num { color: #b5cea8; }
</style>

<script>
// 原生全屏会把元素提升到浏览器顶层，普通 z-index 无法覆盖；
// 因此全屏期间把共享弹窗临时移入全屏元素，退出全屏后移回 body。
const SHARED_MODAL_ID = '__booadmin_shared_modal__';

function currentFullscreenElement() {
    return document.fullscreenElement || document.webkitFullscreenElement || document.mozFullScreenElement || null;
}

function syncModalLayer() {
    const modal = document.getElementById(SHARED_MODAL_ID);
    if (!modal) return;
    const fs = currentFullscreenElement();
    if (fs && modal.parentElement !== fs) {
        fs.appendChild(modal);
    } else if (!fs && modal.parentElement !== document.body) {
        document.body.appendChild(modal);
    }
}

// 全屏切换功能
const fullscreenBtn = document.getElementById('fullscreen-btn');
const editorContainer = document.getElementById('editor-container');

fullscreenBtn.addEventListener('click', function() {
    const fs = currentFullscreenElement();

    if (fs !== editorContainer) {
        if (editorContainer.requestFullscreen) {
            editorContainer.requestFullscreen();
        } else if (editorContainer.mozRequestFullScreen) {
            editorContainer.mozRequestFullScreen();
        } else if (editorContainer.webkitRequestFullscreen) {
            editorContainer.webkitRequestFullscreen();
        } else if (editorContainer.msRequestFullscreen) {
            editorContainer.msRequestFullscreen();
        }
    } else if (document.exitFullscreen) {
        document.exitFullscreen();
    } else if (document.mozCancelFullScreen) {
        document.mozCancelFullScreen();
    } else if (document.webkitExitFullscreen) {
        document.webkitExitFullscreen();
    } else if (document.msExitFullscreen) {
        document.msExitFullscreen();
    }
});

// 监听全屏状态变化：同步按钮图标与弹窗层级
function onFullscreenChange() {
    const active = currentFullscreenElement() === editorContainer;
    fullscreenBtn.innerHTML = active ? '<i class="fas fa-compress"></i>' : '<i class="fas fa-expand"></i>';
    syncModalLayer();
}
document.addEventListener('fullscreenchange', onFullscreenChange);
document.addEventListener('webkitfullscreenchange', onFullscreenChange);
document.addEventListener('mozfullscreenchange', onFullscreenChange);

// 保存确认提示框
const saveBtn = document.getElementById('save-btn');
const themeForm = document.getElementById('theme');

if (saveBtn) {
    saveBtn.addEventListener('click', function() {
        BooAdmin.confirm({
            title: '<?php _e('确认保存'); ?>',
            message: '<?php _e('您确定要保存此文件吗？此操作不可逆。'); ?>',
            confirmText: '<?php _e('确认保存'); ?>',
            onConfirm: function () {
                if (themeForm) {
                    themeForm.submit();
                }
            }
        });
        syncModalLayer();
    });
}

// ── 轻量 PHP 高亮：textarea 文字透明，背后 pre 同步渲染着色文本 ──
const contentEl = document.getElementById('content');
const highlightEl = document.getElementById('php-highlight-code');
const backdropEl = document.getElementById('php-highlight-backdrop');

function escapeHtml(text) {
    return text.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
}

const PHP_TOKEN_RE = /(\x3c\x3fphp\b|\x3c\x3f=|\x3f>)|('(?:\\.|[^'\\])*'|"(?:\\.|[^"\\])*")|(\/\*[\s\S]*?\*\/|\/\/[^\n]*|#[^\n]*)|(\$[A-Za-z_]\w*)|\b(abstract|and|array|as|break|case|catch|class|clone|const|continue|declare|default|die|do|echo|else|elseif|empty|enddeclare|endfor|endforeach|endif|endswitch|endwhile|extends|final|finally|fn|for|foreach|function|global|goto|if|implements|include|include_once|instanceof|insteadof|interface|isset|list|match|namespace|new|or|print|private|protected|public|readonly|require|require_once|return|static|switch|throw|trait|try|unset|use|var|while|xor|yield|true|false|null)\b|(\b\d+(?:\.\d+)?\b)/gi;

function highlightPhp(source) {
    let out = '';
    let inPhp = false;
    let last = 0;
    let match;
    PHP_TOKEN_RE.lastIndex = 0;
    while ((match = PHP_TOKEN_RE.exec(source)) !== null) {
        out += escapeHtml(source.slice(last, match.index));
        const text = match[0];
        let cls = '';
        if (match[1]) {
            cls = 'tok-tag';
            inPhp = /^<\?/.test(text);
        } else if (inPhp) {
            if (match[2]) cls = 'tok-str';
            else if (match[3]) cls = 'tok-com';
            else if (match[4]) cls = 'tok-var';
            else if (match[5]) cls = 'tok-key';
            else if (match[6]) cls = 'tok-num';
        }
        out += cls ? '<span class="' + cls + '">' + escapeHtml(text) + '</span>' : escapeHtml(text);
        last = PHP_TOKEN_RE.lastIndex;
    }
    return out + escapeHtml(source.slice(last));
}

function syncHighlight() {
    if (!contentEl || !highlightEl || !backdropEl) return;
    const value = contentEl.value;
    highlightEl.innerHTML = highlightPhp(value) + (value.endsWith('\n') ? '\n' : '');
    backdropEl.scrollTop = contentEl.scrollTop;
    backdropEl.scrollLeft = contentEl.scrollLeft;
    contentEl.parentElement.classList.add('php-highlight-ready');
}

if (contentEl) {
    contentEl.addEventListener('input', syncHighlight);
    contentEl.addEventListener('scroll', syncHighlight);
    syncHighlight();
}
</script>

<?php
\Typecho\Plugin::factory('admin/theme-editor.php')->call('bottom', $files);
include 'footer.php';
?>
