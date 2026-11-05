<?php if(!defined('__TYPECHO_ADMIN__')) exit; ?>
<?php
/**
 * 所有后台脚本统一经 booadminScriptOnce() 输出：
 * 版本号集中附加、同页重复 include 自动去重。
 */
booadminScriptOnce('jquery.js');
booadminScriptOnce('jquery-ui.js');
booadminScriptOnce('typecho.js');
booadminScriptOnce('booadmin.js');
if ($booadminCompatNeo) {
    // neo 兼容层仅扩展页加载，且需 defer 等 DOM 就绪
    booadminScriptOnce('neo-theme.js', true);
}
?>
<script>
    /**
     * 核心脚本能力自检
     *
     * admin/js/typecho.js 是 Typecho 核心脚本的副本（源见 config/js/typecho.js）。
     * 核心升级后若与主题副本错配，页面会以「按钮无反应」等难以定位的方式失效。
     * 这里把错配变成一条明确的控制台诊断。
     */
    (function () {
        var missing = [];
        var required = [
            ['$.fn.tableSelectable', function () { return window.jQuery && $.fn.tableSelectable; }],
            ['$.fn.dropdownMenu',    function () { return window.jQuery && $.fn.dropdownMenu; }],
            ['$.fn.resizeable',      function () { return window.jQuery && $.fn.resizeable; }],
            ['$.cookie',             function () { return window.jQuery && $.cookie; }],
            ['window.Typecho',       function () { return window.Typecho; }],
            ['window.BooAdmin',      function () { return window.BooAdmin; }]
        ];

        for (var i = 0; i < required.length; i++) {
            try {
                if (!required[i][1]()) {
                    missing.push(required[i][0]);
                }
            } catch (e) {
                missing.push(required[i][0]);
            }
        }

        if (missing.length && window.console && typeof console.error === 'function') {
            console.error(
                '[BooAdmin] 核心脚本版本不匹配，缺失 API: ' + missing.join(', ') +
                '。请用 config/js/ 源文件重新生成 admin/js/ 产物，详见 config/js/README.md。'
            );
        }
    })();
</script>
<script>
    (function () {
        'use strict';

        /**
         * 每个模块独立注册 ready 回调并自带异常边界。
         * 任一模块抛错都不会阻断后续模块，也不会导致
         * window.TypechoNotification / $.fn.booadminDropdown 等关键定义缺失。
         * 注册顺序即执行顺序，与原单一回调保持一致。
         */
        function module(name, init) {
            $(function () {
                try {
                    init();
                } catch (e) {
                    if (window.console && typeof console.error === 'function') {
                        console.error('[BooAdmin] 模块初始化失败: ' + name, e);
                    }
                }
            });
        }

        // ========================================
        // 页面加载进度条
        // ========================================
        module('nprogress', function () {
            if (typeof window.NProgress === 'undefined') {
                return;
            }

            /**
             * DOM 完全交给 NProgress 自己管理。
             *
             * 历史写法在 header.php 里静态输出 #nprogress，再靠
             * ensureNProgressStructure() 补偿 NProgress.remove() 删掉整个元素；
             * 这是脆弱耦合：render() 检测到已存在 #nprogress 会跳过 template，
             * remove() 又会销毁静态 DOM，只能不断打补丁。
             *
             * 现改为把自定义 spinner 结构放进 template，由库按生命周期创建/销毁。
             * 一次配置即可，不需要任何重建逻辑，也不再有静态 DOM。
             */
            NProgress.configure({
                minimum: 0.1,
                trickleSpeed: 200,
                showSpinner: true,
                speed: 300,
                easing: 'ease',
                parent: 'body',
                template:
                    '<div class="bar" role="bar"><div class="peg"></div></div>' +
                    '<div class="spinner" role="spinner"><div class="spinner-icon"></div>' +
                    '<span class="spinner-text"><?php _e('正在加载'); ?></span></div>'
            });

            /** 取当前生命周期内的 spinner（每次重新查询，避免缓存失效节点） */
            function spinner() {
                return $('#nprogress .spinner');
            }

            NProgress.start();

            // 页面完全加载后收起进度条
            $(window).on('load', function () {
                setTimeout(function () {
                    spinner().addClass('hide');
                    NProgress.done();
                }, 100);
            });

            /**
             * 只对「确实会导致页面跳转」的同域链接显示进度条。
             * 被 preventDefault 的 AJAX / 确认弹窗链接不应启动，否则没有配对的
             * done() 会让进度条永久卡住；这里用 setTimeout 0 检查 defaultPrevented。
             */
            $(document).on('click',
                'a[href]:not([href^="#"]):not([href^="javascript:"]):not([target="_blank"]):not([href^="mailto:"])',
                function (e) {
                    var href = $(this).attr('href');
                    if (!href || (href.indexOf('http') === 0 && href.indexOf(window.location.hostname) === -1)) {
                        return;
                    }
                    setTimeout(function () {
                        if (e.isDefaultPrevented()) {
                            return;
                        }
                        spinner().removeClass('hide');
                        NProgress.start();
                    }, 0);
                });
            });

            // ========================================
            // 现代化通知系统
            // ========================================
            module('notification', function () {
                // PHP 值统一用 json_encode 注入 JS，避免引号或脚本结束标记破坏本段代码
                var prefix = <?php echo json_encode(\Typecho\Cookie::getPrefix(), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>,
                    cookies = {
                        notice      :   $.cookie(prefix + '__typecho_notice'),
                        noticeType  :   $.cookie(prefix + '__typecho_notice_type'),
                        highlight   :   $.cookie(prefix + '__typecho_notice_highlight')
                    },
                    path = <?php echo json_encode(\Typecho\Cookie::getPath(), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>,
                    domain = <?php echo json_encode(\Typecho\Cookie::getDomain(), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>,
                    secure = <?php echo json_encode(\Typecho\Cookie::getSecure()); ?>;

                // 创建通知容器（如果不存在）
                if ($('#typecho-notification-container').length === 0) {
                    $('body').append('<div id="typecho-notification-container"></div>');
                }

                /** 通知正文按纯文本渲染，避免 cookie / 调用方内容被当作 HTML 执行 */
                function escapeHtml(value) {
                    return $('<div/>').text(value == null ? '' : String(value)).html();
                }

                var allowedTypes = ['success', 'notice', 'error', 'info', 'warning'];

                // 显示通知函数
                function showNotification(messages, type) {
                    // type 会进入 class 属性，必须精确白名单，防止属性注入
                    type = allowedTypes.indexOf(type) >= 0 ? type : 'info';

                    // 图标映射
                    var icons = {
                        'success': '<i class="fas fa-check-circle"></i>',
                        'error': '<i class="fas fa-times-circle"></i>',
                        'notice': '<i class="fas fa-exclamation-triangle"></i>',
                        'info': '<i class="fas fa-info-circle"></i>',
                        'warning': '<i class="fas fa-exclamation-triangle"></i>'
                    };
                    
                    // 标题映射
                    var titles = {
                        'success': '<?php _e('操作成功'); ?>',
                        'error': '<?php _e('出错了'); ?>',
                        'notice': '<?php _e('注意'); ?>',
                        'info': '<?php _e('提示'); ?>',
                        'warning': '<?php _e('警告'); ?>'
                    };
                    
                    // 构建消息列表
                    var messageHTML = '';
                    if (Array.isArray(messages)) {
                        if (messages.length === 1) {
                            messageHTML = '<div class="typecho-notification-messages">' + escapeHtml(messages[0]) + '</div>';
                        } else {
                            messageHTML = '<ul class="typecho-notification-messages">';
                            messages.forEach(function(msg) {
                                messageHTML += '<li>' + escapeHtml(msg) + '</li>';
                            });
                            messageHTML += '</ul>';
                        }
                    } else {
                        messageHTML = '<div class="typecho-notification-messages">' + escapeHtml(messages) + '</div>';
                    }
                    
                    // 创建通知元素
                    var notification = $('<div class="typecho-notification ' + type + '">' +
                        '<div class="typecho-notification-icon">' + icons[type] + '</div>' +
                        '<div class="typecho-notification-content">' +
                            '<div class="typecho-notification-title">' + titles[type] + '</div>' +
                            messageHTML +
                        '</div>' +
                        '<button class="typecho-notification-close" aria-label="关闭">' +
                            '<i class="fas fa-times"></i>' +
                        '</button>' +
                    '</div>');
                    
                    // 添加到容器
                    $('#typecho-notification-container').append(notification);
                    
                    // 显示动画
                    setTimeout(function() {
                        notification.addClass('show');
                    }, 10);
                    
                    // 关闭按钮事件
                    notification.find('.typecho-notification-close').on('click', function() {
                        closeNotification(notification);
                    });
                    
                    // 5秒后自动关闭
                    setTimeout(function() {
                        closeNotification(notification);
                    }, 5000);
                }
                
                // 关闭通知函数
                function closeNotification(notification) {
                    notification.addClass('hide');
                    setTimeout(function() {
                        notification.remove();
                    }, 300);
                }
                
                // 检查并显示Cookie中的通知（精确白名单，避免 indexOf 子串误判）
                if (!!cookies.notice && allowedTypes.indexOf(cookies.noticeType) >= 0) {
                    try {
                        var messages = $.parseJSON(cookies.notice);
                        showNotification(messages, cookies.noticeType);
                    } catch(e) {
                        console.error('通知消息解析失败:', e);
                    }
                    
                    // 清除Cookie
                    $.cookie(prefix + '__typecho_notice', null, {path : path, domain: domain, secure: secure});
                    $.cookie(prefix + '__typecho_notice_type', null, {path : path, domain: domain, secure: secure});
                }

                // 高亮元素：cookie 值不拼进选择器，避免特殊字符导致语法异常
                if (cookies.highlight) {
                    var highlightNode = document.getElementById(String(cookies.highlight));
                    var $highlightEl = $(highlightNode || []);
                    if ($highlightEl.length > 0) {
                        // 使用CSS动画进行高亮
                        $highlightEl.css({
                            'animation': 'highlight-flash 1s ease-in-out',
                            'animation-iteration-count': '2'
                        });
                        
                        // 添加临时样式
                        if (!$('#highlight-animation-style').length) {
                            $('<style id="highlight-animation-style">' +
                                '@keyframes highlight-flash {' +
                                    '0%, 100% { background-color: transparent; }' +
                                    '50% { background-color: rgba(var(--booadmin-accent-rgb), 0.2); }' +
                                '}' +
                            '</style>').appendTo('head');
                        }
                    }
                    $.cookie(prefix + '__typecho_notice_highlight', null, {path : path, domain: domain, secure: secure});
                }
                
                // 全局通知方法（供其他脚本调用）
                window.TypechoNotification = {
                    show: showNotification,
                    success: function(messages) { showNotification(messages, 'success'); },
                    error: function(messages) { showNotification(messages, 'error'); },
                    notice: function(messages) { showNotification(messages, 'notice'); },
                    info: function(messages) { showNotification(messages, 'info'); },
                    warning: function(messages) { showNotification(messages, 'warning'); }
                };
            });

            // ========================================
            // 分页样式优化 + 外链处理
            // 登录/注册页是独立全屏布局（body.body-100），不参与这些改写
            // ========================================
            module('pagination', function () {
                if ($('body').hasClass('body-100')) {
                    return;
                }
                // 查找所有可能的类型，包括直接的 .typecho-pager，或者是包含分页链接的 div/nav
                var $pagers = $('.typecho-pager');
                
                // 如果找不到标准的，尝试寻找包含 li.prev/next 的容器
                if ($pagers.length === 0) {
                     // 尝试找到包含分页链接的容器
                    $pagers = $('li.prev, li.next, li.current').parent();
                }

                if ($pagers.length > 0) {
                    $pagers.each(function() {
                        var $pager = $(this);
                        
                        // 1. 容器样式优化
                        // 移除默认的 list-style，增加 flex 布局
                        $pager.addClass('flex flex-wrap justify-center items-center gap-2 mt-8 mb-6 pl-0 list-none');
                        // 强制移除原有样式干扰
                        $pager.css('list-style', 'none');

                        // 2. 列表项 li 样式优化
                        var $items = $pager.find('li');
                        $items.each(function() {
                            var $li = $(this);
                            
                            // 移除 li 的默认圆点
                            $li.addClass('list-none m-0 p-0 inline-flex');
                            $li.css('list-style', 'none'); 

                            // 3. 链接/文字内容样式优化
                            var $child = $li.children('a, span');
                            
                            // 基础形状和排版
                            // min-w-[2rem] h-8: 确保它是至少 32x32 的方块
                            // px-3: 给文字留出水平空间
                            $child.addClass('flex items-center justify-center min-w-[2rem] h-8 px-3 text-sm font-medium transition-all duration-200 no-underline leading-none');
                            
                            // 针对不同状态的样式
                            if ($li.hasClass('current')) {
                                // 当前页：高亮背景
                                $child.addClass('bg-discord-accent text-white border border-discord-accent hover:bg-discord-accent hover:text-white cursor-default');
                            } else {
                                // 普通页/前后页：白底灰字，hover变色
                                $child.addClass('bg-white text-gray-500 hover:text-discord-text hover:bg-discord-light hover:border-discord-light border border-gray-200');
                            }
                        });
                    });
                }
                
                /**
                 * 用字符串前缀判断代替原先由 PHP 拼出的正则。
                 * 旧写法 substr(preg_quote(...), 0, -1) 会截掉末字符，
                 * 当 URL 以 / 结尾时留下孤立的反斜杠，导致 action 链接误判为外链。
                 */
                var adminBase = <?php echo json_encode($options->adminUrl, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
                var actionBase = <?php echo json_encode(\Typecho\Common::url('action', $options->index), JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;

                $('a').each(function () {
                    var t = $(this), href = t.attr('href');

                    if (!href || href.charAt(0) === '#'
                        || href.indexOf('javascript:') === 0
                        || href.indexOf('mailto:') === 0
                        || href.indexOf(adminBase) === 0
                        || href.indexOf(actionBase) === 0
                        || href.indexOf('/action/') !== -1) {
                        return;
                    }

                    t.attr('target', '_blank')
                        .attr('rel', 'noopener noreferrer');
                });
            });

            // ========================================
            // 表格滚动指示
            // ========================================
            module('table-scroll', function () {
            function updateTableScrollIndicator() {
                $('.table-wrapper[data-table-scroll]').each(function() {
                    var $wrapper = $(this);
                    var scrollLeft = $wrapper.scrollLeft();
                    var scrollWidth = $wrapper[0].scrollWidth;
                    var clientWidth = $wrapper[0].clientWidth;
                    
                    // 已滚动到最左边
                    if (scrollLeft <= 1) {
                        $wrapper.removeClass('scrolled-start');
                    } else {
                        $wrapper.addClass('scrolled-start');
                    }
                    
                    // 已滚动到最右边
                    if (scrollLeft + clientWidth >= scrollWidth - 1) {
                        $wrapper.removeClass('scrolled-end');
                        $wrapper.addClass('scrolled-end');
                    } else {
                        $wrapper.removeClass('scrolled-end');
                    }
                });
            }
            
            // 初始化并绑定事件
            $('.table-wrapper[data-table-scroll]').on('scroll', updateTableScrollIndicator);
            $(window).on('resize', updateTableScrollIndicator);
            updateTableScrollIndicator(); // 初始检查
            });

            // ========================================
            // 下拉菜单增强（独立插件，不覆写 Typecho 核心 API）
            // ========================================
            module('dropdown', function() {
                /**
                 * 历史上这里直接覆写 $.fn.dropdownMenu（Typecho 核心 typecho.js 提供）。
                 * 替换式架构下，核心升级后主题副本与核心行为可能错配，且第三方插件
                 * 调用 dropdownMenu 时会意外拿到被改写的行为。
                 * 现改为独立命名空间 $.fn.booadminDropdown，核心 API 保持原样，
                 * 仅 BooAdmin 自己的页面显式使用增强版。
                 */
                var $doc = $(document);
                var activeDropdown = null;

                function resolveDropdownParts($el, options) {
                    var $container = $el.closest('.relative, .group');
                    var $menu;
                    var $btn;

                    if ($container.length === 0) {
                        return null;
                    }

                    $menu = options && options.menuEl
                        ? $container.find(options.menuEl)
                        : $container.find('.dropdown-menu');

                    if ($menu.length === 0) {
                        return null;
                    }

                    if (options && options.btnEl) {
                        $btn = $el.is(options.btnEl) ? $el : $container.find(options.btnEl).first();
                    } else {
                        $btn = $el;
                    }

                    if ($btn.length === 0) {
                        return null;
                    }

                    return { container : $container, btn : $btn, menu : $menu };
                }

                // 独立插件：遇到非 BooAdmin 结构时静默跳过，不再回退到核心实现
                $.fn.booadminDropdown = function(options) {
                    return this.each(function() {
                        var parts = resolveDropdownParts($(this), options);
                        if (!parts) {
                            return;
                        }
                        var $container = parts.container;
                        var $btn = parts.btn;
                        var $menu = parts.menu;

                        function setDropdownState(isOpen) {
                            $menu.toggleClass('is-open', isOpen)
                                .toggleClass('hidden', !isOpen)
                                .attr('aria-hidden', isOpen ? 'false' : 'true');
                            $btn.toggleClass('active', isOpen)
                                .attr('aria-expanded', isOpen ? 'true' : 'false');
                        }

                        function closeOtherDropdowns() {
                            $('.btn-dropdown-toggle').not($btn).each(function() {
                                var $otherBtn = $(this);
                                var $otherContainer = $otherBtn.closest('.relative, .group');
                                var $otherMenu = $otherContainer.find('.dropdown-menu');
                                $otherMenu.removeClass('is-open').addClass('hidden').attr('aria-hidden', 'true');
                                $otherBtn.removeClass('active').attr('aria-expanded', 'false');
                            });
                        }
                        
                        $btn.off('click.dropdown').on('click.dropdown', function(e) {
                            e.preventDefault();
                            e.stopPropagation();
                            
                            var isVisible = $menu.hasClass('is-open');
                            
                            // 先关闭其它下拉菜单
                            closeOtherDropdowns();
                            
                            if (isVisible) {
                                setDropdownState(false);
                                activeDropdown = null;
                            } else {
                                setDropdownState(true);
                                activeDropdown = $container;
                            }
                            
                            return false;
                        });
                    });
                };

                // 点击外部时关闭下拉菜单
                $doc.on('click.dropdown', function(e) {
                    if (activeDropdown && !$(e.target).closest(activeDropdown).length) {
                        $('.dropdown-menu').removeClass('is-open').addClass('hidden').attr('aria-hidden', 'true');
                        $('.btn-dropdown-toggle').removeClass('active').attr('aria-expanded', 'false');
                        activeDropdown = null;
                    }
                });

                // 点击菜单项时关闭下拉菜单
                $doc.on('click.dropdown', '.dropdown-menu a', function() {
                    $('.dropdown-menu').removeClass('is-open').addClass('hidden').attr('aria-hidden', 'true');
                    $('.btn-dropdown-toggle').removeClass('active').attr('aria-expanded', 'false');
                    activeDropdown = null;
                });

                // 初始化菜单可见性的语义状态（已在 ready 内，无需再嵌套 ready）
                $('.btn-dropdown-toggle').each(function() {
                    var $btn = $(this);
                    var $container = $btn.closest('.relative, .group');
                    var $menu = $container.find('.dropdown-menu');

                    $menu.removeClass('is-open').addClass('hidden').attr('aria-hidden', 'true');
                    $btn.attr('aria-expanded', 'false');
                });
            });

            // ========================================
            // 视图模式切换（表格/卡片）
            // ========================================
            module('view-mode', function() {
                if ($('.view-toggle').length === 0) {
                    return;
                }
                var VIEW_MODE_KEY = 'typecho_list_view_mode';
                var VIEW_MODE_USER_SET_KEY = 'typecho_list_view_user_set';

                function isMobileViewport() {
                    return window.matchMedia('(max-width: 767px)').matches;
                }

                function getSavedViewMode() {
                    try {
                        return localStorage.getItem(VIEW_MODE_KEY) || null;
                    } catch(e) {
                        return null;
                    }
                }

                function saveViewMode(mode) {
                    try {
                        localStorage.setItem(VIEW_MODE_KEY, mode);
                    } catch(e) {
                        // 忽略 localStorage 错误
                    }
                }

                function hasUserViewPreference() {
                    try {
                        return localStorage.getItem(VIEW_MODE_USER_SET_KEY) === 'true';
                    } catch(e) {
                        return false;
                    }
                }

                function markUserViewPreference() {
                    try {
                        localStorage.setItem(VIEW_MODE_USER_SET_KEY, 'true');
                    } catch(e) {
                        // 忽略 localStorage 错误
                    }
                }

                function getDefaultViewMode() {
                    return isMobileViewport() ? 'card' : 'table';
                }

                function applyViewMode(mode) {
                    var $container = $('.operate-form').closest('.bg-white');
                    var $tableBtn = $('.view-toggle .btn-table-view');
                    var $cardBtn = $('.view-toggle .btn-card-view');

                    if (mode === 'card') {
                        $container.addClass('view-mode-card').removeClass('view-mode-table').attr('data-view-mode', 'card');
                        $tableBtn.removeClass('active').attr('aria-pressed', 'false');
                        $cardBtn.addClass('active').attr('aria-pressed', 'true');
                    } else {
                        $container.removeClass('view-mode-card').addClass('view-mode-table').attr('data-view-mode', 'table');
                        $tableBtn.addClass('active').attr('aria-pressed', 'true');
                        $cardBtn.removeClass('active').attr('aria-pressed', 'false');
                    }
                }

                function initializeViewMode() {
                    var savedMode = getSavedViewMode();
                    var finalMode = hasUserViewPreference() && savedMode ? savedMode : getDefaultViewMode();
                    applyViewMode(finalMode);
                }

                $('.view-toggle button').on('click', function(e) {
                    e.preventDefault();
                    var $btn = $(this);
                    var newMode = $btn.hasClass('btn-table-view') ? 'table' : 'card';

                    markUserViewPreference();
                    saveViewMode(newMode);
                    applyViewMode(newMode);

                    return false;
                });

                initializeViewMode();

                // 单向响应式：屏幕变窄时自动由表格视图切换为卡片视图，
                // 但屏幕变宽时不自动切回表格视图。
                var wasMobile = isMobileViewport();
                var resizeTimer = null;
                $(window).on('resize.viewMode', function() {
                    if (resizeTimer) {
                        clearTimeout(resizeTimer);
                    }

                    resizeTimer = setTimeout(function() {
                        var isMobile = isMobileViewport();
                        // 仅当从宽屏过渡到窄屏时，自动切换为卡片视图
                        if (isMobile && !wasMobile) {
                            applyViewMode('card');
                        }
                        // 从窄屏变回宽屏时不自动切回表格视图
                        wasMobile = isMobile;
                    }, 150);
                });

                $(document).on('change', '.content-card .card-checkbox', function() {
                    var $checkbox = $(this);
                    var cid = $checkbox.val();
                    var isChecked = $checkbox.prop('checked');

                    $('.typecho-list-table input[type="checkbox"][value="' + cid + '"]').prop('checked', isChecked);
                    updateSelectAllState();
                });

                $(document).on('change', '.typecho-list-table input[type="checkbox"]:not(.typecho-table-select-all)', function() {
                    var $checkbox = $(this);
                    var cid = $checkbox.val();
                    var isChecked = $checkbox.prop('checked');

                    $('.content-card .card-checkbox[value="' + cid + '"]').prop('checked', isChecked);
                    updateSelectAllState();
                });

                function updateSelectAllState() {
                    var $allCheckboxes = $('.typecho-list-table input[type="checkbox"]:not(.typecho-table-select-all)');
                    var $checkedCheckboxes = $allCheckboxes.filter(':checked');
                    var $selectAll = $('.typecho-table-select-all');

                    if ($checkedCheckboxes.length === 0) {
                        $selectAll.prop('checked', false).prop('indeterminate', false);
                    } else if ($checkedCheckboxes.length === $allCheckboxes.length) {
                        $selectAll.prop('checked', true).prop('indeterminate', false);
                    } else {
                        $selectAll.prop('checked', false).prop('indeterminate', true);
                    }
                }

                $(document).on('change', '.typecho-table-select-all', function() {
                    var isChecked = $(this).prop('checked');
                    var isIndeterminate = $(this).prop('indeterminate');

                    if (isIndeterminate) {
                        return;
                    }

                    $('.content-card .card-checkbox').prop('checked', isChecked);
                });
            });
    })();
</script>
<script>
    // BooAdmin 共享库 注入翻译与通知别名，统一后台前端"体系层"
    if (window.BooAdmin) {
        BooAdmin.setI18n({
            confirmTitle: '<?php _e('操作确认'); ?>',
            alertTitle: '<?php _e('提示'); ?>',
            confirm: '<?php _e('确认'); ?>',
            cancel: '<?php _e('取消'); ?>',
            ok: '<?php _e('我知道了'); ?>'
        });
        // 注入版本号
        BooAdmin.setVersion('<?php echo BOOADMIN_VERSION; ?>');
        /**
         * 通知对象在上方 notification 模块（ready 回调）中才创建，
         * 此处同步赋值会恒为 null。改用 getter，读取时总能拿到最新实例，
         * 不再依赖脚本执行顺序。
         */
        Object.defineProperty(BooAdmin, 'notify', {
            configurable: true,
            enumerable: true,
            get: function () {
                return window.TypechoNotification || null;
            }
        });
    }
</script>
