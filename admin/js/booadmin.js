/**
 * BooAdmin 前端体系内核
 * ==========================================================================
 * 单一全局命名空间 window.BooAdmin，向所有后台页面与第三方插件提供：
 *
 *   config / i18n   服务端注入值的唯一入口，PHP 侧一律经 booadminJson() 编码
 *   html            统一安全层：esc / attr / url / httpUrl / sanitize（fail-closed）
 *   hooks           事件总线，对齐 Typecho 的 Plugin::factory()->call() 插件化模型
 *   module          模块注册：依赖顺序、异常边界、生命周期（destroy）与命名空间事件
 *   services        可变服务门面：把「ready 之后才存在」的实现变成稳定可调用的入口
 *   ui              Modal / confirm / alert / notify
 *
 * 设计约定
 * --------
 * 1. 安全是默认行为：所有 HTML 拼接必须经 html.*；净化器缺失时降级为纯文本，
 *    绝不回退为原样 HTML（fail-closed）。
 * 2. 扩展不靠覆写：禁止覆盖 Typecho.* / $.fn.* 核心 API；通过 hooks 挂接、通过
 *    services 注册实现。历史覆写点逐步迁移，第三方插件可安全共存。
 * 3. 单个模块 / 单个钩子处理器抛错，不得影响其它模块或页面。
 * 4. 本文件只提供机制，不含页面业务逻辑；业务逻辑放在各页面脚本里用 module.define 注册。
 *
 * 依赖：jQuery（须在引入本文件之前加载）。DOMPurify 可选，缺失时走安全降级。
 */
(function ($, window, document) {
    'use strict';

    var BooAdmin = window.BooAdmin || {};

    /* ====================================================================
     * 1. 配置与多语言
     * ------------------------------------------------------------------ */

    var config = {};
    var i18n = {
        confirmTitle: '操作确认',
        alertTitle: '提示',
        confirm: '确认',
        cancel: '取消',
        ok: '我知道了'
    };

    BooAdmin.config = {
        /** 合并注入。PHP 侧用 booadminJson() 输出，值已是安全的 JS 字面量 */
        set: function (map) {
            if (map && typeof map === 'object') {
                $.extend(config, map);
            }
            return config;
        },
        get: function (key, fallback) {
            return Object.prototype.hasOwnProperty.call(config, key) ? config[key] : fallback;
        },
        all: function () {
            return $.extend({}, config);
        }
    };

    BooAdmin.i18n = {
        set: function (map) {
            if (map && typeof map === 'object') {
                $.extend(i18n, map);
            }
            return i18n;
        },
        /** 读取翻译；缺失时回退到 key 本身或调用方给的默认值 */
        t: function (key, fallback) {
            return Object.prototype.hasOwnProperty.call(i18n, key)
                ? i18n[key]
                : (fallback === undefined ? key : fallback);
        },
        all: function () {
            return $.extend({}, i18n);
        }
    };

    // ---- 向后兼容：旧页面仍调用 setI18n / setVersion ----
    BooAdmin.setI18n = BooAdmin.i18n.set;
    BooAdmin.version = '';
    BooAdmin.setVersion = function (v) {
        if (typeof v === 'string' && v !== '') {
            BooAdmin.version = v;
            BooAdmin.config.set({ version: v });
        }
    };

    /* ====================================================================
     * 2. 统一安全层 BooAdmin.html
     * ------------------------------------------------------------------
     * 全站唯一的一组输出编码 / 净化函数，取代散落在各页面里的 6 套重复实现。
     * ================================================================== */

    /** 文本 → HTML 内容（保守转义 5 个字符，可同时用于文本节点与属性前的预处理） */
    function esc(value) {
        return String(value === undefined || value === null ? '' : value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    /** 文本 → 属性值（额外处理反引号，兼容老 IE 的反引号属性分隔） */
    function escAttr(value) {
        return esc(value).replace(/`/g, '&#96;');
    }

    /**
     * URL 协议白名单。
     *
     * 允许：http/https、mailto、协议相对 //、根相对 /、锚点 #、以及不含冒号的
     * 相对路径（Typecho 附件 URL 可能是相对路径，不能一律打成 '#'）。
     * 拒绝：javascript: / data: / vbscript: / 其它伪协议、控制字符、HTML 实体绕过。
     */
    function safeUrl(raw, opts) {
        opts = opts || {};
        var fallback = opts.fallback === undefined ? '#' : opts.fallback;
        var s = String(raw === undefined || raw === null ? '' : raw)
            .replace(/[\u0000-\u001F\u007F]/g, '')
            .trim();

        if (s === '') {
            return fallback;
        }
        // 实体编码可能让 javascript: 在属性解码后复活，直接拒绝
        if (/&#/i.test(s)) {
            return fallback;
        }
        // 允许的绝对协议
        if (/^(https?:\/\/|mailto:)/i.test(s)) {
            return s;
        }
        // 允许的协议相对 / 根相对 / 锚点
        if (s.indexOf('//') === 0 || s.charAt(0) === '/' || s.charAt(0) === '#') {
            return s;
        }
        // 无冒号即视为相对路径
        if (s.indexOf(':') === -1) {
            return s;
        }
        return fallback;
    }

    /** 严格白名单：仅 http/https 完整 URL，用于外部 feed / 版本公告等不可信来源 */
    function safeHttpUrl(raw, fallback) {
        var s = String(raw === undefined || raw === null ? '' : raw)
            .replace(/[\u0000-\u001F\u007F]/g, '')
            .trim();
        return /^https?:\/\//i.test(s)
            ? s
            : (fallback === undefined ? '#' : fallback);
    }

    /** 净化器是否可用（供调用方判断是否需要降级 UI） */
    function hasPurify() {
        return typeof window.DOMPurify !== 'undefined'
            && window.DOMPurify
            && typeof window.DOMPurify.sanitize === 'function';
    }

    var purifyWarned = false;

    /**
     * 富文本净化。
     *
     * fail-closed：DOMPurify 不可用时返回**纯文本转义结果**，绝不原样输出 HTML。
     * 这是与历史 sanitizeNotice() 最关键的区别——后者在库缺失时直接回退原样，
     * 使可被改写的 notice cookie 成为 XSS 入口。
     */
    function sanitize(html) {
        var raw = String(html === undefined || html === null ? '' : html);
        if (hasPurify()) {
            return window.DOMPurify.sanitize(raw, { USE_PROFILES: { html: true } });
        }
        if (!purifyWarned && window.console && typeof console.warn === 'function') {
            purifyWarned = true;
            console.warn('[BooAdmin] DOMPurify 未加载，富文本已降级为纯文本渲染');
        }
        return esc(raw);
    }

    /** 安全地按 id 取元素，避免把不可信字符串拼进 jQuery 选择器 */
    function byId(id) {
        var node = document.getElementById(String(id === undefined || id === null ? '' : id));
        return $(node || []);
    }

    BooAdmin.html = {
        esc: esc,
        attr: escAttr,
        escAttr: escAttr,       // 兼容旧调用名
        url: safeUrl,
        safeUrl: safeUrl,
        httpUrl: safeHttpUrl,
        sanitize: sanitize,
        hasPurify: hasPurify,
        byId: byId
    };

    /* ====================================================================
     * 3. 钩子总线 BooAdmin.hooks
     * ------------------------------------------------------------------
     * 对齐原版 Typecho 的插件化模型：核心流程在关键点 emit / filter，
     * 插件只需 on() 挂接，不必覆写任何全局 API。
     * ================================================================== */

    var listeners = {};

    function normalizeHandlers(name) {
        if (!Object.prototype.hasOwnProperty.call(listeners, name)) {
            listeners[name] = [];
        }
        return listeners[name];
    }

    BooAdmin.hooks = {
        /** on(name, handler, priority) priority 越大越先执行，默认 10 */
        on: function (name, handler, priority) {
            if (typeof name !== 'string' || typeof handler !== 'function') {
                return BooAdmin.hooks;
            }
            normalizeHandlers(name).push({
                fn: handler,
                priority: typeof priority === 'number' ? priority : 10,
                once: false
            });
            normalizeHandlers(name).sort(function (a, b) { return b.priority - a.priority; });
            return BooAdmin.hooks;
        },
        once: function (name, handler, priority) {
            if (typeof name !== 'string' || typeof handler !== 'function') {
                return BooAdmin.hooks;
            }
            normalizeHandlers(name).push({
                fn: handler,
                priority: typeof priority === 'number' ? priority : 10,
                once: true
            });
            normalizeHandlers(name).sort(function (a, b) { return b.priority - a.priority; });
            return BooAdmin.hooks;
        },
        off: function (name, handler) {
            if (!Object.prototype.hasOwnProperty.call(listeners, name)) {
                return BooAdmin.hooks;
            }
            if (typeof handler !== 'function') {
                delete listeners[name];
                return BooAdmin.hooks;
            }
            listeners[name] = listeners[name].filter(function (item) { return item.fn !== handler; });
            return BooAdmin.hooks;
        },
        /**
         * 广播动作。单个处理器抛错只记录、不中断其它处理器。
         * @returns {number} 成功执行的处理器数量
         */
        emit: function (name) {
            var args = Array.prototype.slice.call(arguments, 1);
            var list = (listeners[name] || []).slice();
            var executed = 0;

            for (var i = 0; i < list.length; i++) {
                var item = list[i];
                if (item.once) {
                    BooAdmin.hooks.off(name, item.fn);
                }
                try {
                    item.fn.apply(null, args);
                    executed++;
                } catch (e) {
                    if (window.console && typeof console.error === 'function') {
                        console.error('[BooAdmin] 钩子处理器异常: ' + name, e);
                    }
                }
            }
            return executed;
        },
        /**
         * 过滤值。每个处理器返回非 undefined 的值即成为下一环节的输入。
         * 单个处理器抛错时跳过，保留上一环节的值。
         */
        filter: function (name, value) {
            var args = Array.prototype.slice.call(arguments, 1);
            var list = (listeners[name] || []).slice();

            for (var i = 0; i < list.length; i++) {
                if (list[i].once) {
                    BooAdmin.hooks.off(name, list[i].fn);
                }
                try {
                    var next = list[i].fn.apply(null, args);
                    if (next !== undefined) {
                        value = next;
                        args[0] = next;
                    }
                } catch (e) {
                    if (window.console && typeof console.error === 'function') {
                        console.error('[BooAdmin] 过滤器异常: ' + name, e);
                    }
                }
            }
            return value;
        }
    };

    /* ====================================================================
     * 4. 服务门面 BooAdmin.services
     * ------------------------------------------------------------------
     * 解决「实现在 ready 之后才创建、调用点却要立即可用」的时序耦合。
     * 旧代码靠 Object.defineProperty getter + 全局变量，这里改为显式注册表。
     * ================================================================== */

    var services = {};

    BooAdmin.services = {
        register: function (name, implementation) {
            services[name] = implementation;
            BooAdmin.hooks.emit('service:registered', name, implementation);
            return implementation;
        },
        get: function (name) {
            return services[name] || null;
        },
        has: function (name) {
            return !!services[name];
        }
    };

    /* ====================================================================
     * 5. 模块系统 BooAdmin.module
     * ------------------------------------------------------------------
     * 每个模块独立注册、独立异常边界、可声明依赖、可卸载。
     * 事件命名空间统一为 .booadmin.<name>，destroy 时自动解绑，避免泄漏与冲突。
     * ================================================================== */

    var modules = {};
    var moduleOrder = [];
    var booted = false;

    function startModule(name, stack) {
        var record = modules[name];
        if (!record || record.state === 'running' || record.state === 'ready') {
            return record ? record.state : 'missing';
        }
        if (record.state === 'error') {
            return 'error';
        }
        if (stack.indexOf(name) !== -1) {
            record.state = 'error';
            record.error = new Error('循环依赖: ' + stack.concat(name).join(' -> '));
            return 'error';
        }

        var nextStack = stack.concat(name);
        for (var i = 0; i < record.deps.length; i++) {
            var depState = startModule(record.deps[i], nextStack);
            if (depState !== 'ready' && depState !== 'running') {
                record.state = 'error';
                record.error = new Error('依赖不可用: ' + record.deps[i] + ' (' + depState + ')');
                BooAdmin.hooks.emit('module:error', name, record.error);
                return 'error';
            }
        }

        record.state = 'running';
        BooAdmin.hooks.emit('module:before', name);
        try {
            record.exports = record.init({
                name: name,
                ns: '.booadmin.' + name,
                html: BooAdmin.html,
                hooks: BooAdmin.hooks,
                config: BooAdmin.config,
                t: BooAdmin.i18n.t
            }) || {};

            // 模块可在导出对象里提供 destroy()，作为 opts.destroy 之外的另一种生命周期写法
            if (!record.destroy && typeof record.exports.destroy === 'function') {
                record.destroy = record.exports.destroy;
            }
            record.state = 'ready';
            BooAdmin.hooks.emit('module:after', name, record.exports);
            return 'ready';
        } catch (e) {
            record.state = 'error';
            record.error = e;
            if (window.console && typeof console.error === 'function') {
                console.error('[BooAdmin] 模块初始化失败: ' + name, e);
            }
            BooAdmin.hooks.emit('module:error', name, e);
            return 'error';
        }
    }

    BooAdmin.module = {
        /**
         * define(name, init, opts)
         * opts.deps    依赖模块名数组
         * opts.destroy 卸载回调（可省略；默认会解绑命名空间事件）
         * init 返回的对象作为模块导出，可被其它模块通过 get() 读取
         */
        define: function (name, init, opts) {
            if (typeof name !== 'string' || typeof init !== 'function') {
                throw new Error('BooAdmin.module.define 需要 (name, init)');
            }
            opts = opts || {};
            if (!modules[name]) {
                moduleOrder.push(name);
            }
            modules[name] = {
                name: name,
                init: init,
                deps: Array.isArray(opts.deps) ? opts.deps.slice() : [],
                destroy: typeof opts.destroy === 'function' ? opts.destroy : null,
                state: 'pending',
                exports: {},
                error: null
            };
            if (booted) {
                startModule(name, []);
            }
            return BooAdmin.module;
        },
        /** 按注册 + 依赖顺序启动全部模块；DOM ready 后自动调用，也可手动调用 */
        boot: function () {
            booted = true;
            BooAdmin.hooks.emit('boot:before');
            for (var i = 0; i < moduleOrder.length; i++) {
                startModule(moduleOrder[i], []);
            }
            BooAdmin.hooks.emit('boot:after', BooAdmin.module.list());
            return BooAdmin.module;
        },
        get: function (name) {
            return modules[name] ? modules[name].exports : null;
        },
        state: function (name) {
            return modules[name] ? modules[name].state : 'missing';
        },
        /** 卸载模块：执行 destroy 回调并解绑 .booadmin.<name> 命名空间事件 */
        destroy: function (name) {
            var record = modules[name];
            if (!record) {
                return false;
            }
            if (typeof record.destroy === 'function') {
                try {
                    record.destroy();
                } catch (e) {
                    if (window.console && typeof console.error === 'function') {
                        console.error('[BooAdmin] 模块卸载异常: ' + name, e);
                    }
                }
            }
            // 自动解绑该模块的命名空间事件，覆盖 document / window / body 三个常见宿主
            var ns = '.booadmin.' + name;
            $(document).off(ns);
            $(window).off(ns);
            $('body').off(ns);
            record.state = 'destroyed';
            BooAdmin.hooks.emit('module:destroyed', name);
            return true;
        },
        /** 诊断用：返回全部模块状态快照 */
        list: function () {
            return moduleOrder.map(function (name) {
                return { name: name, state: modules[name].state };
            });
        }
    };

    /* ====================================================================
     * 6. UI 层
     * ================================================================== */

    /** 渲染消息：默认按净化后的 HTML；messageAsText 时按纯文本 */
    function renderMessage($el, value, asText) {
        if (asText) {
            $el.text(value === undefined || value === null ? '' : String(value));
        } else {
            $el.html(sanitize(value));
        }
    }

    /**
     * 创建模态框控制器。
     * message 默认走 sanitize（保留合法富文本、清除脚本）；需要纯文本时传
     * messageAsText: true。这样图片预览等富文本调用点无需改动也自动获得净化。
     */
    function createModal(options) {
        options = $.extend({
            id: '',
            title: '',
            message: '',
            messageAsText: false,
            size: 'sm',
            showCancel: true,
            confirmText: '',
            cancelText: '',
            onConfirm: null,
            onCancel: null,
            onClose: null
        }, options);

        var seq = (createModal._seq = (createModal._seq || 0) + 1);
        var uid = options.id ? ('bm_' + String(options.id).replace(/[^\w-]/g, '_')) : ('bm' + seq);
        var dynamic = !options.id;
        var $modal = options.id ? $('#' + options.id) : $();

        if (!$modal.length) {
            var sizeClass = options.size === 'sm' ? ' booadmin-dialog-sm'
                : options.size === 'md' ? ' booadmin-dialog-md'
                : options.size === 'lg' ? ' booadmin-dialog-lg' : '';

            $modal = $(
                '<div class="booadmin-modal hidden" role="dialog" aria-modal="true"' +
                (options.id ? ' id="' + escAttr(options.id) + '"' : '') + '>' +
                    '<div class="booadmin-dialog' + sizeClass + '">' +
                        '<h3 class="booadmin-modal-title text-lg font-bold text-discord-text mb-4"></h3>' +
                        '<div class="booadmin-modal-message text-discord-muted mb-6"></div>' +
                        '<div class="flex justify-end space-x-3">' +
                            '<button type="button" class="booadmin-modal-cancel px-4 py-2 bg-gray-200 text-discord-text font-medium hover:bg-gray-300 transition-colors text-sm"></button>' +
                            '<button type="button" class="booadmin-modal-confirm px-4 py-2 bg-discord-accent text-white font-medium hover:bg-blue-600 transition-colors text-sm"></button>' +
                        '</div>' +
                    '</div>' +
                '</div>'
            ).appendTo('body');
        }

        var $title = $modal.find('.booadmin-modal-title'),
            $message = $modal.find('.booadmin-modal-message'),
            $confirm = $modal.find('.booadmin-modal-confirm'),
            $cancel = $modal.find('.booadmin-modal-cancel');

        var titleId = uid + '_title',
            messageId = uid + '_message';
        $modal.attr('aria-labelledby', titleId).attr('aria-describedby', messageId);
        $title.attr('id', titleId);
        $message.attr('id', messageId);

        function close() {
            $modal.addClass('hidden');
            $(document).off('keydown.' + uid);
            if (dynamic) {
                $modal.remove();
            }
            if (typeof options.onClose === 'function') {
                options.onClose();
            }
        }

        function open(override) {
            override = override || {};
            $confirm.prop('disabled', false).removeClass('opacity-50');

            $title.text(override.title !== undefined ? override.title : (options.title || ''));
            var message = override.message !== undefined ? override.message : options.message;
            var asText = override.messageAsText !== undefined
                ? override.messageAsText : options.messageAsText;
            renderMessage($message, message, asText);

            var showCancel = override.showCancel !== undefined ? override.showCancel : options.showCancel;
            $cancel.toggleClass('hidden', !showCancel);
            if (showCancel) {
                $cancel.text(override.cancelText || options.cancelText || i18n.cancel);
            }
            $confirm.text(override.confirmText || options.confirmText || i18n.confirm);

            $modal.removeClass('hidden');
            $(document).off('keydown.' + uid).on('keydown.' + uid, function (e) {
                if (e.key === 'Escape' && !$modal.hasClass('hidden')) {
                    close();
                }
            });
        }

        $confirm.off('click.' + uid).on('click.' + uid, function () {
            var keepOpen = typeof options.onConfirm === 'function' && options.onConfirm() === false;
            if (!keepOpen) {
                close();
            }
        });

        $cancel.off('click.' + uid).on('click.' + uid, function () {
            close();
            if (typeof options.onCancel === 'function') {
                options.onCancel();
            }
        });

        $modal.off('click.' + uid).on('click.' + uid, function (e) {
            if (e.target === this) {
                close();
            }
        });

        return {
            open: open,
            close: close,
            el: $modal,
            setTitle: function (t) { $title.text(t); },
            setMessage: function (m, asText) { renderMessage($message, m, !!asText); }
        };
    }

    var sharedModal = null;
    var sharedCallback = null;

    function ensureShared() {
        if (!sharedModal) {
            sharedModal = createModal({
                id: '__booadmin_shared_modal__',
                onConfirm: function () {
                    return typeof sharedCallback === 'function' ? sharedCallback() : undefined;
                }
            });
        }
        return sharedModal;
    }

    /** 确认弹窗：message 强制按纯文本渲染 */
    function confirmDialog(opts) {
        opts = opts || {};
        sharedCallback = typeof opts.onConfirm === 'function' ? opts.onConfirm : null;
        return ensureShared().open({
            title: opts.title || i18n.confirmTitle,
            message: opts.message,
            messageAsText: true,
            confirmText: opts.confirmText || i18n.confirm,
            cancelText: opts.cancelText || i18n.cancel,
            showCancel: opts.showCancel !== false
        });
    }

    /** 提示弹窗：message 强制按纯文本渲染 */
    function alertDialog(opts) {
        opts = opts || {};
        sharedCallback = typeof opts.onConfirm === 'function' ? opts.onConfirm : null;
        return ensureShared().open({
            title: opts.title || i18n.alertTitle,
            message: opts.message,
            messageAsText: true,
            confirmText: opts.confirmText || i18n.ok,
            showCancel: false
        });
    }

    BooAdmin.ui = {
        Modal: { create: createModal },
        confirm: confirmDialog,
        alert: alertDialog
    };

    // ---- 向后兼容：旧页面直接调用 BooAdmin.confirm / alert / Modal ----
    BooAdmin.confirm = confirmDialog;
    BooAdmin.alert = alertDialog;
    BooAdmin.Modal = BooAdmin.ui.Modal;

    /**
     * notify：稳定门面。实现由 notification 模块在 ready 时通过 services 注册，
     * 读取时刻总能拿到最新实例；同时兼容历史 window.TypechoNotification 全局。
     */
    function resolveNotify() {
        return BooAdmin.services.get('notify') || window.TypechoNotification || null;
    }

    Object.defineProperty(BooAdmin, 'notify', {
        configurable: true,
        enumerable: true,
        get: resolveNotify
    });

    /* ====================================================================
     * 7. 启动
     * ================================================================== */

    window.BooAdmin = BooAdmin;

    // DOM ready 后统一启动模块；页面脚本可在此之前 define，也可之后 define（立即启动）
    $(function () {
        BooAdmin.module.boot();
    });
})(jQuery, window, document);
