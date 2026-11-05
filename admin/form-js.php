<?php if(!defined('__TYPECHO_ADMIN__')) exit; ?>
<script>
(function () {
    $(document).ready(function () {
        const error = $('.typecho-option .error:first');

        if (error.length > 0) {
            $('html,body').scrollTop(error.parents('.typecho-option').offset().top);
        }

        /**
         * 后台主内容区是 <main class="flex-1 ...">，Typecho 原生后台则使用
         * <div class="main">。旧代码只写了 $('.main form')，在 BooAdmin 下
         * 恒为空集，导致「提交防重」整段失效。这里同时兼容两种结构。
         */
        const $forms = $('main form, .main form');

        $forms.on('submit', function () {
            const self = $(this);

            if (self.hasClass('submitting')) {
                return false;
            } else {
                $('button[type=submit], input[type=submit]', this).prop('disabled', true);
                self.addClass('submitting');
            }
        }).on('submitted', function () {
            $('button[type=submit], input[type=submit]', this).prop('disabled', false);
            $(this).removeClass('submitting');
        });

        $('label input[type=text]').click(function (e) {
            const check = $('#' + $(this).parents('label').attr('for'));
            check.prop('checked', true);
            return false;
        });

        $forms.find('input[type="url"]').each(function () {
            const self = $(this);
            const input = $('<input type="hidden" />').attr('name', self.attr('name'));

            function setInput() {
                const url = self.val();

                try {
                    const urlObj = new URL(url);
                    input.val(urlObj.toString());
                } catch {
                    // 忽略
                }
            }

            self.removeAttr('name').after(input).on('input', setInput);
            setInput();
        });
    });
})();
</script>
