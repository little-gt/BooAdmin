<?php if(!defined('__TYPECHO_ADMIN__')) exit; ?>
<script src="<?php $options->adminStaticUrl('js', 'jquery.js'); ?>"></script>
<script src="<?php $options->adminStaticUrl('js', 'jquery-ui.js'); ?>"></script>
<script src="<?php $options->adminStaticUrl('js', 'typecho.js'); ?>"></script>
<script src="<?php $options->adminStaticUrl('js', 'booadmin.js'); ?>"></script>
<script>
    (function () {
        $(document).ready(function() {
            // ========================================
            // 页面加载进度条
            // ========================================
            (function() {
                // 确保 NProgress 元素存在且结构完整
                function ensureNProgressStructure() {
                    var $nprogress = $('#nprogress');
                    if ($nprogress.length === 0) {
                        $nprogress = $('<div id="nprogress"><div class="bar" role="bar"><div class="peg"></div></div><div class="spinner"><div class="spinner-icon"></div><span class="spinner-text"><?php _e('正在加载'); ?></span></div></div>').appendTo('body');
                    } else {
                        // NProgress.remove() 会删除整个元素后由此处重建，确保 bar 结构完整
                        if ($nprogress.find('[role="bar"]').length === 0) {
                            $nprogress.prepend('<div class="bar" role="bar"><div class="peg"></div></div>');
                        }
                        var $spinner = $nprogress.find('.spinner');
                        if ($spinner.length === 0) {
                            $spinner = $('<div class="spinner"><div class="spinner-icon"></div><span class="spinner-text"><?php _e('正在加载'); ?></span></div>').appendTo($nprogress);
                        } else if ($spinner.find('.spinner-text').length === 0) {
                            $spinner.append('<span class="spinner-text"><?php _e('正在加载'); ?></span>');
                        }
                    }
                    return $nprogress.find('.spinner');
                }
                
                // 配置 NProgress
                NProgress.configure({
                    minimum: 0.1,
                    trickleSpeed: 200,
                    showSpinner: true,
                    speed: 300,
                    easing: 'ease',
                    parent: 'body'
                });
                
                // 初始化 spinner
                var $spinner = ensureNProgressStructure();
                
                // 启动进度条（默认加载状态）
                NProgress.start();
                
                // 页面完全加载后完成进度条
                $(window).on('load', function() {
                    setTimeout(function() {
                        $spinner.addClass('hide');
                        NProgress.done();
                    }, 100);
                });
                
                // 监听页面内的链接点击，显示进度条
                $(document).on('click', 'a[href]:not([href^="#"]):not([href^="javascript:"]):not([target="_blank"]):not([href^="mailto:"])', function(e) {
                    var href = $(this).attr('href');
                    
                    // 只对同域链接启用进度条
                    if (href && (href.indexOf('http') !== 0 || href.indexOf(window.location.hostname) !== -1)) {
                        // 确保结构完整
                        $spinner = ensureNProgressStructure();
                        // 移除 hide 类以显示加载指示器
                        $spinner.removeClass('hide');
                        NProgress.start();
                    }
                });
                
                // 页面卸载时保持加载状态
                $(window).on('beforeunload', function() {
                    // 页面跳转时保持加载状态，不隐藏
                });
            })();

            // ========================================
            // 现代化通知系统
            // ========================================
            (function () {
                var prefix = '<?php echo \Typecho\Cookie::getPrefix(); ?>',
                    cookies = {
                        notice      :   $.cookie(prefix + '__typecho_notice'),
                        noticeType  :   $.cookie(prefix + '__typecho_notice_type'),
                        highlight   :   $.cookie(prefix + '__typecho_notice_highlight')
                    },
                    path = '<?php echo \Typecho\Cookie::getPath(); ?>',
                    domain = '<?php echo \Typecho\Cookie::getDomain(); ?>',
                    secure = <?php echo json_encode(\Typecho\Cookie::getSecure()); ?>;

                // 创建通知容器（如果不存在）
                if ($('#typecho-notification-container').length === 0) {
                    $('body').append('<div id="typecho-notification-container"></div>');
                }

                // 显示通知函数
                function showNotification(messages, type) {
                    type = type || 'info';
                    
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
                            messageHTML = '<div class="typecho-notification-messages">' + messages[0] + '</div>';
                        } else {
                            messageHTML = '<ul class="typecho-notification-messages">';
                            messages.forEach(function(msg) {
                                messageHTML += '<li>' + msg + '</li>';
                            });
                            messageHTML += '</ul>';
                        }
                    } else {
                        messageHTML = '<div class="typecho-notification-messages">' + messages + '</div>';
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
                
                // 检查并显示Cookie中的通知
                if (!!cookies.notice && 'success|notice|error|info|warning'.indexOf(cookies.noticeType) >= 0) {
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

                // 高亮元素
                if (cookies.highlight) {
                    var $highlightEl = $('#' + cookies.highlight);
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
            })();

            if ($('.typecho-login').length == 0) {                // 现代化分页样式优化 v2
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
                
                $('a').each(function () {
                    var t = $(this), href = t.attr('href');

                    if ((href && href[0] == '#')
                        || /^<?php echo preg_quote($options->adminUrl, '/'); ?>.*$/.exec(href) 
                            || /^<?php echo substr(preg_quote(\Typecho\Common::url('s', $options->index), '/'), 0, -1); ?>action\/[_a-zA-Z0-9\/]+.*$/.exec(href)) {
                        return;
                    }

                    t.attr('target', '_blank')
                        .attr('rel', 'noopener noreferrer');
                });
            }
            
            // 表格滚动指示
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

            // ========================================
            // 下拉菜单增强
            // ========================================
            (function() {
                // 覆写 dropdownMenu 插件行为
                var $doc = $(document);
                var activeDropdown = null;
                // 保留原生实现，遇到非 BooAdmin 下拉结构时回退，避免插件按原生方式调用时失效
                var nativeDropdownMenu = $.fn.dropdownMenu;

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

                // 覆写 dropdownMenu 插件
                $.fn.dropdownMenu = function(options) {
                    var useNative = false;

                    this.each(function() {
                        if (!resolveDropdownParts($(this), options)) {
                            useNative = true;
                            return false;
                        }
                    });

                    if (useNative) {
                        return nativeDropdownMenu.apply(this, arguments);
                    }

                    return this.each(function() {
                        var parts = resolveDropdownParts($(this), options);
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

                // 页面加载时初始化下拉菜单
                $(function() {
                    $('.btn-dropdown-toggle').each(function() {
                        var $btn = $(this);
                        var $container = $btn.closest('.relative, .group');
                        var $menu = $container.find('.dropdown-menu');

                        // 初始化菜单可见性的语义状态
                        $menu.removeClass('is-open').addClass('hidden').attr('aria-hidden', 'true');
                        $btn.attr('aria-expanded', 'false');
                    });
                });
            })();

            // ========================================
            // 视图模式切换（表格/卡片）
            // ========================================
            (function() {
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
            })();
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
        BooAdmin.notify = window.TypechoNotification || null;
    }
</script>
