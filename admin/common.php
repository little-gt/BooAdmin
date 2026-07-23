<?php
if (!defined('__DIR__')) {
    define('__DIR__', dirname(__FILE__));
}

define('__TYPECHO_ADMIN__', true);

/** 载入配置文件 */
if (!defined('__TYPECHO_ROOT_DIR__') && !@include_once __DIR__ . '/../config.inc.php') {
    file_exists(__DIR__ . '/../install.php') ? header('Location: ../install.php') : print('Missing Config File');
    exit;
}

/** 初始化组件 */
\Widget\Init::alloc();

/** 注册一个初始化插件 */
\Typecho\Plugin::factory('admin/common.php')->call('begin');

\Widget\Options::alloc()->to($options);
\Widget\User::alloc()->to($user);
\Widget\Security::alloc()->to($security);
try {
    \Widget\Menu::alloc()->to($menu);
} catch (\Typecho\Widget\Exception $e) {
    http_response_code(403);
    echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>BooAdmin - 403 - 您的权限不足</title><style>*{margin:0;padding:0;box-sizing:border-box}body{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh}.c{text-align:center;padding:2rem}.code{font-size:6rem;font-weight:700;line-height:1}.msg{font-size:.95rem;margin-top:.75rem}.back{display:inline-block;margin-top:2rem;padding:.5rem 1.25rem;text-decoration:none;font-size:.85rem;transition:background .2s}@media(prefers-color-scheme:dark){body{background:#111;color:#888}.code{color:#333}.msg{color:#666}.back{background:#1a1a1a;color:#888;border:1px solid #333}.back:hover{background:#222}}@media(prefers-color-scheme:light){body{background:#fff;color:#444}.code{color:#ccc}.msg{color:#999}.back{background:#f5f5f5;color:#444;border:1px solid #ddd}.back:hover{background:#eee}}</style></head><body><div class="c"><div class="code">403</div><div class="msg">权限不足，无法访问此页面</div><a class="back" href="' . \Typecho\Common::url('index.php', $options->adminUrl) . '">返回控制台</a></div></body></html>';
    exit;
}

/** 初始化上下文 */
$request = $options->request;
$response = $options->response;

function booadminActionUrl($path) {
    global $security;
    return $security->getTokenUrl($security->getIndex($path));
}

/**
 * 统一的头像处理函数
 * @param string $mail 邮箱地址
 * @param string $name 用户名（用于降级头像）
 * @param int $size 头像大小
 * @param string $className 自定义CSS类
 * @return string 头像HTML
 */
function getAvatar($mail, $name, $size = 48, $className = '') {
    // 生成邮箱的MD5哈希，用于cookie键名
    $mailHash = md5(strtolower(trim($mail)));
    $cookieKey = 'avatar_failed_' . $mailHash;
    
    // 检查cookie是否记录了头像加载失败
    $avatarFailed = isset($_COOKIE[$cookieKey]) && $_COOKIE[$cookieKey] == 1;
    
    // 生成用户名首字母
    $firstChar = $name ? mb_substr($name, 0, 1, 'UTF-8') : '?';
    
    // 检查是否使用HTTPS
    $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443;
    
    // 更加灵活的尺寸计算
    $baseSize = max(8, floor($size / 8));
    $sizeClass = 'w-' . $baseSize . ' h-' . $baseSize;
    
    // 根据尺寸设置合适的文本大小
    $textSizeMap = [
        4 => 'text-xs',
        5 => 'text-sm',
        6 => 'text-base',
        7 => 'text-lg',
        8 => 'text-xl',
        9 => 'text-2xl',
        10 => 'text-3xl',
        11 => 'text-4xl',
        12 => 'text-5xl'
    ];
    $textSize = $textSizeMap[$baseSize] ?? 'text-sm';
    
    // 过滤className参数，只允许有效的CSS类名字符
    $className = preg_replace('/[^a-zA-Z0-9\s\-]/', '', $className);
    
    // 如果头像加载失败过，直接返回降级头像
    if ($avatarFailed) {
        return '<div class="' . $className . ' ' . $sizeClass . ' flex items-center justify-center bg-gradient-to-br from-blue-500 to-blue-600 text-white font-bold ' . $textSize . ' border border-gray-200">' . htmlspecialchars($firstChar, ENT_QUOTES, 'UTF-8') . '</div>';
    }
    
    // 生成Gravatar URL
    $gravatarUrl = \Typecho\Common::gravatarUrl($mail, $size, 'X', 'mm', $isSecure);
    
    // 生成包含错误处理的头像HTML
    $cookieExpire = 30 * 24 * 60 * 60; // 30天
    // 对cookie键名进行安全转义，避免注入
    $safeCookieKey = addslashes($cookieKey);
    $html = '<div class="relative ' . $className . ' ' . $sizeClass . ' flex-shrink-0">';
    $html .= '<img src="' . $gravatarUrl . '" alt="' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '" class="w-full h-full object-cover border border-gray-200" onerror="this.classList.add(\'booadmin-avatar-image-hidden\'); this.nextElementSibling.classList.add(\'is-visible\'); document.cookie=\'' . $safeCookieKey . '=1; path=/; max-age=' . $cookieExpire . '\'" />';
    $html .= '<div class="booadmin-avatar-fallback w-full h-full items-center justify-center bg-gradient-to-br from-blue-500 to-blue-600 text-white font-bold ' . $textSize . ' border border-gray-200 absolute inset-0">' . htmlspecialchars($firstChar, ENT_QUOTES, 'UTF-8') . '</div>';
    $html .= '</div>';
    
    return $html;
}

/** 检测是否是第一次登录 */
$currentMenu = $menu->getCurrentMenu();

if (!empty($currentMenu)) {
    $params = parse_url($currentMenu[2]);
    $adminFile = basename($params['path']);

    if (!$user->logged && !\Typecho\Cookie::get('__typecho_first_run')) {
        if ('welcome.php' != $adminFile) {
            $response->redirect(\Typecho\Common::url('welcome.php', $options->adminUrl));
        } else {
            \Typecho\Cookie::set('__typecho_first_run', 1);
        }
    } elseif ($user->pass('administrator', true)) {
        /** 检测版本是否升级 */
        $mustUpgrade = version_compare(\Typecho\Common::VERSION, $options->version, '>');

        if ($mustUpgrade && 'upgrade.php' != $adminFile && 'backup.php' != $adminFile) {
            $response->redirect(\Typecho\Common::url('upgrade.php', $options->adminUrl));
        } elseif (!$mustUpgrade && 'upgrade.php' == $adminFile) {
            $response->redirect($options->adminUrl);
        } elseif (!$mustUpgrade && 'welcome.php' == $adminFile && $user->logged) {
            $response->redirect($options->adminUrl);
        }
    }
}
