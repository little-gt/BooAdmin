<?php if (!defined('__TYPECHO_ADMIN__')) exit; ?>
<?php
// 配置获取网站标题
$options = Typecho_Widget::widget('Widget_Options');
$title = trim($options->title);

// 统一菜单配置：[分组标题 => [[url, 图标, 标题, 最低权限组], ...]]
// url 为 'settings' 时表示可折叠设置子菜单（模板渲染时特殊处理）
$menuConfig = [
    '撰写' => [
        ['write-post.php', 'fa-pen-fancy', '撰写文章', 'contributor', 'sidebar-icon--blue'],
        ['write-page.php', 'fa-file-alt', '创建页面', 'editor', 'sidebar-icon--violet'],
    ],
    '管理' => [
        ['manage-posts.php', 'fa-layer-group', '文章', 'contributor', 'sidebar-icon--blue'],
        ['manage-pages.php', 'fa-file', '页面', 'editor', 'sidebar-icon--violet'],
        ['manage-comments.php', 'fa-comments', '评论', 'editor', 'sidebar-icon--emerald'],
        ['manage-medias.php', 'fa-images', '文件', 'editor', 'sidebar-icon--cyan'],
    ],
    '数据' => [
        ['manage-categories.php', 'fa-folder', '分类', 'editor', 'sidebar-icon--amber'],
        ['manage-tags.php', 'fa-tags', '标签', 'editor', 'sidebar-icon--rose'],
        ['manage-users.php', 'fa-users', '用户', 'administrator', 'sidebar-icon--orange'],
    ],
    '系统' => [
        ['themes.php', 'fa-paint-brush', '外观', 'administrator', 'sidebar-icon--rose'],
        ['plugins.php', 'fa-plug', '插件', 'administrator', 'sidebar-icon--purple'],
        ['settings', 'fa-cog', '设置', 'administrator', 'sidebar-icon--slate'],
        ['backup.php', 'fa-download', '备份', 'administrator', 'sidebar-icon--teal'],
    ],
];

// 设置子菜单项
$settingsSubItems = [
    'options-general.php' => '基本',
    'options-discussion.php' => '评论',
    'options-reading.php' => '阅读',
    'options-permalink.php' => '永久链接',
];
$inSettingsPage = in_array($menu->current, array_keys($settingsSubItems), true);

// 按权限过滤，仅保留用户可访问的分组与项目
$visibleMenu = [];
foreach ($menuConfig as $label => $items) {
    $visibleMenu[$label] = array_values(array_filter($items, fn($item) => $user->pass($item[3], true)));
}
$visibleMenu = array_filter($visibleMenu);

// 是否处于拓展页面
$isExtendPage = (strpos($_SERVER['REQUEST_URI'], 'extending.php') !== false);
?>
<?php if(!$isExtendPage): ?>
<!-- Sidebar -->
<aside class="w-64 bg-white border-r border-gray-200 flex-shrink-0 flex flex-col transition-all duration-300 transform md:translate-x-0 fixed md:relative z-20 h-full" id="sidebar">
    <div class="h-16 flex items-center justify-between px-6 border-b border-gray-100 bg-white">
        <h1 class="text-xl font-bold text-discord-accent flex items-center">
            <span class="sidebar-brand-mark" aria-hidden="true"><i class="fas fa-cubes-stacked"></i></span>
            <span class="sidebar-text text-gray-800 tracking-tight" title="<?php echo htmlspecialchars($title ?: 'BooAdmin'); ?>"><?php echo mb_strimwidth($title ?: 'BooAdmin', 0, 15, '...'); ?></span>
        </h1>
        <button id="sidebar-toggle" class="md:hidden text-gray-400 hover:text-gray-600 focus:outline-none">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <nav class="flex-1 overflow-y-auto py-4 custom-scrollbar">
        <ul class="space-y-1 px-3">
            <!-- Dashboard -->
            <li>
                <a href="<?php $options->adminUrl('index.php'); ?>" style="text-decoration:none" class="sidebar-link flex items-center px-3 py-2 text-gray-600 <?php if($menu->current == 'index.php') echo 'bg-blue-50 text-discord-accent'; else echo 'hover:bg-gray-100 hover:text-gray-900'; ?> font-medium transition-all group">
                    <span class="sidebar-icon sidebar-icon--blue"><i class="fas fa-tachometer-alt" aria-hidden="true"></i></span>
                    <span class="sidebar-text"><?php _e('控制台'); ?></span>
                </a>
            </li>
            
            <!-- Menu -->
            <?php foreach ($visibleMenu as $sectionLabel => $sectionItems):
                if ($sectionLabel === '系统') continue; ?>
            <li class="sidebar-section-title mt-5 mb-2 px-3 text-xs font-bold text-gray-400 uppercase tracking-wider sidebar-text"><?php _e($sectionLabel); ?></li>
            <?php foreach ($sectionItems as $item): ?>
            <li>
                <a href="<?php $options->adminUrl($item[0]); ?>" style="text-decoration:none" class="sidebar-link flex items-center px-3 py-2 text-gray-600 hover:bg-gray-100 hover:text-gray-900 transition-colors <?php if($menu->current == $item[0]) echo 'bg-blue-50 text-discord-accent'; ?>">
                    <span class="sidebar-icon <?php echo $item[4]; ?>"><i class="fas <?php echo $item[1]; ?>" aria-hidden="true"></i></span>
                    <span class="sidebar-text"><?php _e($item[2]); ?></span>
                </a>
            </li>
            <?php endforeach; ?>
            <?php endforeach; ?>

            <!-- Dynamic Plugin Menu Items -->
            <?php 
            // 捕获原始菜单输出
            ob_start();
            $menu->output();
            $menuOutput = ob_get_clean();
            
            // 使用 DOMDocument 解析菜单
            if (!empty($menuOutput)) {
                $dom = new DOMDocument();
                @$dom->loadHTML('<?xml encoding="utf-8" ?>' . $menuOutput, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
                $xpath = new DOMXPath($dom);
                
                // 查找所有带有 extending.php 的链接（插件菜单）
                $pluginLinks = $xpath->query('//a[contains(@href, "extending.php")]');
                
                if ($pluginLinks->length > 0) {
                    echo '<li class="sidebar-section-title mt-5 mb-2 px-3 text-xs font-bold text-gray-400 uppercase tracking-wider sidebar-text">';
                    _e('扩展');
                    echo '</li>';
                    
                    foreach ($pluginLinks as $link) {
                        $href = $link->getAttribute('href');
                        $text = trim($link->textContent);
                        $isActive = (!empty($_GET['panel']) && strpos($href, $_GET['panel']) !== false);
                        
                        echo '<li>';
                        echo '<a href="' . htmlspecialchars($href, ENT_QUOTES, 'UTF-8') . '" style="text-decoration:none" class="sidebar-link flex items-center px-3 py-2 text-gray-600 hover:bg-gray-100 hover:text-gray-900 transition-colors';
                        if ($isActive) echo ' bg-blue-50 text-discord-accent';
                        echo '">';
                        echo '<span class="sidebar-icon sidebar-icon--purple"><i class="fas fa-dice-d6" aria-hidden="true"></i></span>';
                        echo '<span class="sidebar-text">' . htmlspecialchars($text, ENT_QUOTES, 'UTF-8') . '</span>';
                        echo '</a>';
                        echo '</li>';
                    }
                }
            }
            ?>

            <!-- System -->
            <?php if (!empty($visibleMenu['系统'])): ?>
            <li class="sidebar-section-title mt-5 mb-2 px-3 text-xs font-bold text-gray-400 uppercase tracking-wider sidebar-text"><?php _e('系统'); ?></li>
            <?php foreach ($visibleMenu['系统'] as $item): ?>
            <?php if ($item[0] === 'settings'): ?>
            <li>
                <div class="relative group-settings">
                    <button class="sidebar-link w-full flex items-center px-3 py-2 text-gray-600 hover:bg-gray-100 hover:text-gray-900 transition-colors focus:outline-none justify-between <?php if($inSettingsPage) echo 'bg-blue-50 text-discord-accent'; ?>" onclick="const expanded = this.nextElementSibling.classList.toggle('hidden'); this.querySelector('.settings-chevron').classList.toggle('-rotate-90'); this.querySelector('.settings-chevron').classList.toggle('rotate-0'); this.setAttribute('aria-expanded', expanded ? 'false' : 'true');" aria-expanded="<?php echo $inSettingsPage ? 'true' : 'false'; ?>">
                        <div class="flex items-center">
                            <span class="sidebar-icon <?php echo $item[4]; ?>"><i class="fas <?php echo $item[1]; ?>" aria-hidden="true"></i></span>
                            <span class="sidebar-text"><?php _e($item[2]); ?></span>
                        </div>
                        <i class="fas fa-chevron-down text-xs transition-transform duration-200 settings-chevron <?php echo $inSettingsPage ? 'rotate-0' : '-rotate-90'; ?>"></i>
                    </button>
                    <ul class="mt-1 ml-2 pl-6 space-y-1 border-l-2 border-gray-100 <?php if(!$inSettingsPage) echo 'hidden'; ?>">
                        <?php foreach ($settingsSubItems as $page => $label): ?>
                        <li><a href="<?php $options->adminUrl($page); ?>" style="text-decoration:none" class="block px-2 py-1.5 text-sm text-gray-500 hover:text-discord-accent <?php if($menu->current == $page) echo 'text-discord-accent font-medium'; ?>"><?php _e($label); ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </li>
            <?php else: ?>
            <li>
                    <a href="<?php $options->adminUrl($item[0]); ?>" style="text-decoration:none" class="sidebar-link flex items-center px-3 py-2 text-gray-600 hover:bg-gray-100 hover:text-gray-900 transition-colors <?php if($menu->current == $item[0]) echo 'bg-blue-50 text-discord-accent'; ?>">
                    <span class="sidebar-icon <?php echo $item[4]; ?>"><i class="fas <?php echo $item[1]; ?>" aria-hidden="true"></i></span>
                    <span class="sidebar-text"><?php _e($item[2]); ?></span>
                </a>
            </li>
            <?php endif; ?>
            <?php endforeach; ?>
            <?php endif; ?>
        </ul>
    </nav>

    <div class="p-3 md:p-4 border-t border-gray-100 bg-white">
        <div class="sidebar-profile flex items-center group cursor-pointer hover:bg-gray-50 p-2 transition-colors gap-2 md:gap-0">
            <div class="flex-shrink-0">
                <?php echo getAvatar($user->mail, $user->screenName, 32, 'user-avatar md:w-9 md:h-9'); ?>
            </div>
            <div class="ml-2 md:ml-3 overflow-hidden min-w-0 flex-1 sidebar-text">
                <p class="text-sm font-semibold text-gray-800 truncate"><a href="<?php $options->adminUrl('profile.php'); ?>" style="text-decoration:none"><?php $user->screenName(); ?></a></p>
                <p class="text-xs text-gray-500 truncate"><?php echo $user->group; ?></p>
            </div>
            <!-- Plugin Injection Point -->
            <span class="flex items-center space-x-1 md:space-x-2 flex-shrink-0">
                <?php \Typecho\Plugin::factory('admin/menu.php')->call('navBar'); ?>
                <a href="<?php $options->logoutUrl(); ?>" class="text-gray-400 hover:text-red-500 sidebar-text p-1.5 md:p-2 hover:bg-red-50 transition-colors" title="<?php _e('登出'); ?>">
                    <i class="fas fa-sign-out-alt text-sm md:text-base"></i>
                </a>
            </span>
        </div>
    </div>
</aside>

<!-- Overlay for mobile sidebar -->
<div id="sidebar-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-10 hidden md:hidden"></div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const toggleBtn = document.getElementById('sidebar-toggle');
    const overlay = document.getElementById('sidebar-overlay');
    const mobileMenuBtn = document.getElementById('mobile-menu-btn'); // Will be added in header
    const nav = sidebar.querySelector('nav');
    const SIDEBAR_SCROLL_KEY = 'typecho_sidebar_scroll';

    function openSidebar() {
        sidebar.classList.remove('-translate-x-full');
        overlay.classList.remove('hidden');
        document.body.classList.add('sidebar-open');
    }

    function closeSidebar() {
        sidebar.classList.add('-translate-x-full');
        overlay.classList.add('hidden');
        document.body.classList.remove('sidebar-open');
    }

    function toggleSidebar() {
        if (sidebar.classList.contains('-translate-x-full')) {
            openSidebar();
        } else {
            closeSidebar();
        }
    }

    // 保存滚动位置
    function saveScrollPosition() {
        if (nav) {
            try {
                localStorage.setItem(SIDEBAR_SCROLL_KEY, nav.scrollTop);
            } catch(e) {
                // 忽略 localStorage 错误
            }
        }
    }

    // 恢复滚动位置
    function restoreScrollPosition() {
        if (nav) {
            try {
                const scrollTop = localStorage.getItem(SIDEBAR_SCROLL_KEY);
                if (scrollTop !== null) {
                    nav.scrollTop = parseInt(scrollTop);
                }
            } catch(e) {
                // 忽略 localStorage 错误
            }
        }
    }


    if (toggleBtn) toggleBtn.addEventListener('click', toggleSidebar);
    if (overlay) overlay.addEventListener('click', closeSidebar);
    if (mobileMenuBtn) mobileMenuBtn.addEventListener('click', toggleSidebar);

    // 监听滚动事件，保存位置
    if (nav) {
        nav.addEventListener('scroll', saveScrollPosition);
    }

    // 页面加载时恢复滚动位置
    restoreScrollPosition();

    // Initial check for mobile
    if (window.innerWidth < 768) {
        closeSidebar();
    }

    window.addEventListener('resize', function() {
        if (window.innerWidth >= 768) {
            sidebar.classList.remove('-translate-x-full');
            overlay.classList.add('hidden');
            document.body.classList.remove('sidebar-open');
        } else {
            closeSidebar();
        }
    });
});
</script>
<?php endif; ?>

<?php if($isExtendPage): ?>
<!-- Extend page top bar -->
<header class="extend-topbar h-16 flex items-center justify-between px-6 z-10 fixed top-0 left-0 right-0">
    <div class="flex items-center">
        <a href="<?php $options->adminUrl('index.php'); ?>" class="extend-topbar-back" title="<?php _e('返回控制台'); ?>">
            <i class="fas fa-arrow-left"></i>
        </a>
        <span class="extend-topbar-divider"></span>
        <h1 class="extend-topbar-title"><?php echo htmlspecialchars($title ?: 'BooAdmin'); ?></h1>
    </div>
    <div class="flex items-center gap-3">
        <div class="extend-topbar-user">
            <?php echo getAvatar($user->mail, $user->screenName, 28, 'extend-topbar-avatar'); ?>
            <div class="extend-topbar-user-info">
                <span class="extend-topbar-username"><?php $user->screenName(); ?></span>
                <span class="extend-topbar-role"><?php echo $user->group; ?></span>
            </div>
        </div>
        <a href="<?php $options->logoutUrl(); ?>" class="extend-topbar-logout" title="<?php _e('登出'); ?>">
            <i class="fas fa-sign-out-alt"></i>
        </a>
    </div>
</header>
<script>
document.body.classList.add('has-extend-topbar');
</script>
<?php endif; ?>

