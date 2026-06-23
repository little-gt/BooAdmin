<?php
include 'common.php';

if ($user->hasLogin()) {
    $response->redirect($options->adminUrl);
}
$rememberName = htmlspecialchars(\Typecho\Cookie::get('__typecho_remember_name', ''));
\Typecho\Cookie::delete('__typecho_remember_name');

$bodyClass = 'body-100';

// 检测 Passkey 插件是否已安装激活
$activates = \Typecho\Plugin::export();
$hasPasskey = isset($activates['activated']) && in_array('Passkey', array_keys($activates['activated']));

// 生成分钟级时间戳用于缓存破坏
$cacheTs = floor(time() / 60);

include 'header.php';
?>
<div class="min-h-screen flex bg-discord-light text-discord-text">
    <!-- Left Side: Hero Image -->
    <div class="hidden md:flex md:w-1/2 flex-col justify-center items-center bg-cover bg-center relative" style="background-image: url('https://cdn.garfieldtom.cool/img/wldairy/poster/horizontal/%E9%81%87%E8%A7%81%E4%BD%A0%E7%9A%84%E7%8C%AB_%E9%82%A3%E4%B8%80%E5%A4%A9.jpg');">
        <div class="absolute inset-0 pointer-events-none" style="background-color: var(--booadmin-hero-overlay);"></div>
        <div class="relative z-10 text-white p-12 text-center">
            <h1 class="text-4xl font-bold mb-4"><?php $options->title(); ?></h1>
            <p class="text-lg opacity-90"><?php $options->description(); ?></p>
        </div>
        <div class="absolute bottom-6 text-white/50 text-xs">
            &copy; <?php echo date('Y'); ?> BooAdmin Team.
        </div>
    </div>

    <!-- Right Side: Login Form -->
    <div class="w-full md:w-1/2 flex items-center justify-center p-8 sm:p-12 lg:p-16 bg-white overflow-y-auto">
        <div class="w-full max-w-md space-y-8">
            <div class="text-center md:text-left">
                <h2 class="text-3xl font-bold text-gray-900 mb-2"><?php _e('欢迎回来'); ?></h2>
                <p class="text-gray-500"><?php _e('请登录您的账号以继续'); ?></p>
            </div>

            <form action="<?php $options->loginAction(); ?>" method="post" name="login" role="form" class="space-y-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1"><?php _e('用户名'); ?></label>
                    <input type="text" id="name" name="name" value="<?php echo $rememberName; ?>" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 text-gray-900 focus:outline-none focus:ring-2 focus:ring-discord-accent/50 focus:border-discord-accent transition-all" required autofocus placeholder="<?php _e('请输入用户名或邮箱'); ?>" />
                </div>
                
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label for="password" class="block text-sm font-medium text-gray-700"><?php _e('密码'); ?></label>
                        <div class="text-sm">
                            <?php 
                            $hasPassport = false;
                            if (isset($activates['activated']) && in_array('Passport', array_keys($activates['activated']))) {
                                $hasPassport = true;
                                echo '<a href="' . \Typecho\Common::url('passport/forgot', $options->index) . '" class="font-medium text-discord-accent hover:text-discord-accent/80">' . _t('忘记密码？') . '</a>';
                            }
                            ?>
                        </div>
                    </div>
                    <input type="password" id="password" name="password" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 text-gray-900 focus:outline-none focus:ring-2 focus:ring-discord-accent/50 focus:border-discord-accent transition-all" required placeholder="<?php _e('请输入密码'); ?>" />
                    <?php if (!$hasPassport): ?>
                    <p class="mt-2 text-xs text-gray-400">
                        <?php _e('未安装找回密码插件？试试开源的 Passport 插件吧。'); ?> 
                        <a href="https://cnb.cool/little-gt/Passport" target="_blank" class="text-discord-accent hover:underline"><?php _e('国内下载'); ?></a>
                        <span class="text-gray-300 mx-1">|</span>
                        <a href="https://github.com/little-gt/PLUGION-Passport" target="_blank" class="text-discord-accent hover:underline"><?php _e('GitHub'); ?></a>
                    </p>
                    <?php endif; ?>
                </div>
                <div class="flex items-center">
                    <input id="remember" name="remember" type="checkbox" value="1" class="h-4 w-4 text-discord-accent focus:ring-discord-accent border-gray-300">
                    <label for="remember" class="ml-2 block text-sm text-gray-900">
                        <?php _e('下次自动登录'); ?>
                    </label>
                </div>

                <div>
                    <button type="submit" class="w-full flex justify-center py-3 px-4 border text-sm font-medium text-white bg-discord-accent hover:bg-discord-accent/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-discord-accent transition-all">
                        <?php _e('登 录'); ?>
                    </button>
                    <input type="hidden" name="referer" value="<?php echo htmlspecialchars($request->get('referer')); ?>" />
                </div>
            </form>

            <!-- Passkey 登录区域 -->
            <?php if ($hasPasskey): ?>
            <link rel="stylesheet" href="<?php echo \Typecho\Common::url('usr/plugins/Passkey/assist/css/style.css?t=' . $cacheTs, $options->rootUrl); ?>">
            <script>var PASSKEY_ACTION_URL = "<?php echo \Typecho\Common::url('action/passkey', $options->index); ?>";</script>
            <script src="<?php echo \Typecho\Common::url('usr/plugins/Passkey/assist/js/passkey.js?t=' . $cacheTs, $options->rootUrl); ?>"></script>
            <div id="passkey-login-container">
                <button type="button" id="passkey-login-btn"
                    class="w-full flex justify-center py-3 px-4 border text-sm font-medium text-white bg-discord-accent hover:bg-discord-accent/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-discord-accent transition-all">
                    <span id="passkey-btn-text"><?php _e('使用 Passkey 登录'); ?></span>
                </button>
            </div>
            <script>
            document.addEventListener('DOMContentLoaded', function () {
                var btn = document.getElementById('passkey-login-btn');
                var btnText = document.getElementById('passkey-btn-text');
                if (!btn) return;

                btn.addEventListener('click', function () {
                    if (btn.disabled) return;
                    btn.disabled = true;
                    btn.classList.add('opacity-60', 'cursor-not-allowed');
                    btnText.textContent = '<?php _e('正在验证...'); ?>';

                    PasskeyManager.login()
                        .catch(function (err) {
                            console.error('Passkey login failed:', err);
                        })
                        .finally(function () {
                            btn.disabled = false;
                            btn.classList.remove('opacity-60', 'cursor-not-allowed');
                            btnText.textContent = '<?php _e('使用 Passkey 登录'); ?>';
                        });
                });
            });
            </script>
            <?php else: ?>
            <!-- Passkey 插件未安装提示 -->
            <div class="flex items-start gap-3 p-4 border text-sm" style="background-color: var(--booadmin-warning-bg); border-color: var(--booadmin-border); color: var(--booadmin-warning-text);">
                <!-- 警告图标 -->
                <svg class="flex-shrink-0 mt-0.5" style="color: var(--booadmin-warning);" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="8" x2="12" y2="12"/>
                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                <div>
                    <p class="font-medium mb-1" style="color: var(--booadmin-warning);"><?php _e('未检测到 Passkey 插件'); ?></p>
                    <p class="mb-2" style="color: var(--booadmin-muted);"><?php _e('Passkey 是开源免费的通行密钥插件，符合 IEEE 安全标准，可实现无密码快速登录。'); ?></p>
                    <div class="flex gap-2 text-xs">
                        <a href="https://cnb.cool/little-gt/Passkey" target="_blank" rel="noopener noreferrer" class="hover:underline" style="color: var(--booadmin-link);"><?php _e('国内下载'); ?></a>
                        <span style="color: var(--booadmin-border-strong);">|</span>
                        <a href="https://github.com/little-gt/PLUGION-Passkey" target="_blank" rel="noopener noreferrer" class="hover:underline" style="color: var(--booadmin-link);"><?php _e('GitHub'); ?></a>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($options->allowRegister): ?>
            <div class="relative">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-200"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="px-2 bg-white text-gray-500"><?php _e('没有账号？'); ?></span>
                </div>
            </div>

            <div class="text-center">
                <a href="<?php $options->registerUrl(); ?>" class="text-sm font-medium text-discord-accent hover:text-discord-accent/80 hover:underline">
                    <?php _e('创建一个账号'); ?>
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php 
include 'common-js.php';
?>
<script>
$(document).ready(function () {
    $('#name').focus();
});
document.addEventListener('DOMContentLoaded', function () {
    var btn = document.getElementById('passkey-login-btn');
    if (!btn) return;
    var isDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    // Passkey 按钮由外部 JS 注入，需强制修正颜色
    btn.style.setProperty('background-color', isDark ? '#3a3c6e' : 'var(--booadmin-accent)', 'important');
    btn.style.setProperty('color', isDark ? '#c4c8e8' : '#ffffff', 'important');
    btn.style.setProperty('font-size', '14px', 'important');
    btn.style.setProperty('font-weight', '500', 'important');
    if (isDark) {
        btn.style.setProperty('border-color', '#484a78', 'important');
    }
    // 悬停时保持颜色一致
    btn.addEventListener('mouseenter', function () {
        btn.style.setProperty('background-color', isDark ? '#46487d' : 'var(--booadmin-accent-hover)', 'important');
    });
    btn.addEventListener('mouseleave', function () {
        btn.style.setProperty('background-color', isDark ? '#3a3c6e' : 'var(--booadmin-accent)', 'important');
    });
});
</script>
<?php
include 'footer.php';
?>