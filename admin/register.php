<?php
include 'common.php';

if ($user->hasLogin() || !$options->allowRegister) {
    $response->redirect($options->siteUrl);
}
$rememberName = htmlspecialchars(\Typecho\Cookie::get('__typecho_remember_name', ''));
$rememberMail = htmlspecialchars(\Typecho\Cookie::get('__typecho_remember_mail', ''));
\Typecho\Cookie::delete('__typecho_remember_name');
\Typecho\Cookie::delete('__typecho_remember_mail');

$bodyClass = 'body-100';

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

    <!-- Right Side: Register Form -->
    <div class="w-full md:w-1/2 flex items-center justify-center p-8 sm:p-12 lg:p-16 bg-white overflow-y-auto">
        <div class="w-full max-w-md space-y-8">
            <div class="text-center md:text-left">
                <h2 class="text-3xl font-bold text-gray-900 mb-2"><?php _e('创建账号'); ?></h2>
                <p class="text-gray-500"><?php _e('只需几步即可完成注册'); ?></p>
            </div>

            <form action="<?php $options->registerAction(); ?>" method="post" name="register" role="form" class="space-y-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1"><?php _e('用户名'); ?> <span class="text-red-500">*</span></label>
                    <input type="text" id="name" name="name" value="<?php echo $rememberName; ?>" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 text-gray-900 focus:outline-none focus:ring-2 focus:ring-discord-accent/50 focus:border-discord-accent transition-all" required autofocus placeholder="<?php _e('请输入用户名'); ?>" />
                    <p class="mt-1 text-xs text-gray-400"><?php _e('用于登录和显示的名称'); ?></p>
                </div>
                
                <div>
                    <label for="mail" class="block text-sm font-medium text-gray-700 mb-1"><?php _e('电子邮箱'); ?> <span class="text-red-500">*</span></label>
                    <input type="email" id="mail" name="mail" value="<?php echo $rememberMail; ?>" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 text-gray-900 focus:outline-none focus:ring-2 focus:ring-discord-accent/50 focus:border-discord-accent transition-all" required placeholder="<?php _e('请输入有效的邮箱地址'); ?>" />
                    <p class="mt-1 text-xs text-gray-400"><?php _e('我们将向此邮箱发送初始密码'); ?></p>
                </div>
                
                <div class="pt-2">
                    <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium text-white bg-discord-accent hover:bg-discord-accent/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-discord-accent transition-all transform hover:-translate-y-0.5">
                        <?php _e('立即注册'); ?>
                    </button>
                    <input type="hidden" name="referer" value="<?php echo htmlspecialchars($request->get('referer')); ?>" />
                </div>
            </form>

            <div class="relative">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-200"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="px-2 bg-white text-gray-500"><?php _e('已有账号？'); ?></span>
                </div>
            </div>

            <div class="text-center">
                <a href="<?php $options->adminUrl('login.php'); ?>" class="font-medium text-discord-accent hover:text-discord-accent/80 hover:underline">
                    <?php _e('返回直接登录'); ?>
                </a>
            </div>
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
</script>
<?php
include 'footer.php';
?>
