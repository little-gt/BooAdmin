/**
 * BooAdmin 前端共享库
 * --------------------------------------------------------------------------
 * 作为后台 JavaScript 的"体系层"，沉淀跨页面复用的通用能力，避免各 PHP 内联脚本
 * 重复实现弹窗、确认框等。各页面脚本只需调用统一 API（BooAdmin.confirm / alert /
 * Modal.create），彼此保持独立、解耦。
 *
 * 依赖：jQuery（需在引入本文件之前加载）。
 */
(function ($, window, document) {
    'use strict';

    /** 多语言文案，可由 common-js.php 通过 BooAdmin.setI18n 注入翻译 */
    var i18n = {
        confirmTitle: '操作确认',
        alertTitle: '提示',
        confirm: '确认',
        cancel: '取消',
        ok: '我知道了'
    };

    function setI18n(map) {
        if (map && typeof map === 'object') {
            $.extend(i18n, map);
        }
    }

    /** 主题版本号；运行时由 common-js.php 注入（唯一数据源：admin/common.php 的 BOOADMIN_VERSION） */
    var version = '';

    /**
     * 设置主题版本号
     * @param {string} v 版本号
     */
    function setVersion(v) {
        if (typeof v === 'string' && v !== '') {
            version = v;
            // 同步到对外对象，保证 BooAdmin.version 能读到最新值
            if (window.BooAdmin) {
                window.BooAdmin.version = v;
            }
        }
    }

    /**
     * 将任意文本转义为安全的 HTML 片段。
     * confirm / alert 的 message 按“纯文本”渲染，避免把用户输入或 DOM 文本
     * （如文件名、lang 属性）当作 HTML 执行。需要富文本时请改用 BooAdmin.Modal.create。
     *
     * @param {*} str 待转义内容
     * @returns {string} 转义后的 HTML 片段
     */
    function escapeHtml(str) {
        return $('<div/>').text(str === undefined || str === null ? '' : String(str)).html();
    }

    /**
     * 创建一个模态框控制器。
     *
     * @param {Object} options
     *   id        指定已存在 DOM 的 id 则复用，否则动态创建并追加到 body
     *   title     标题文本
     *   message   正文（允许 HTML）
     *   size      '' | 'sm' | 'md'（默认 sm）
     *   showCancel 是否显示取消按钮（默认 true）
     *   confirmText 确认按钮文案
     *   cancelText  取消按钮文案
     *   onConfirm  点击确认回调，返回 false 可阻止关闭
     *   onCancel   点击取消回调
     *   onClose    任意方式关闭后的回调
     * @returns {{open:Function, close:Function, el:jQuery, setTitle:Function, setMessage:Function}}
     */
    function createModal(options) {
        options = $.extend({
            id: '',
            title: '',
            message: '',
            size: 'sm',
            showCancel: true,
            confirmText: '',
            cancelText: '',
            onConfirm: null,
            onCancel: null,
            onClose: null
        }, options);

        var seq = (createModal._seq = (createModal._seq || 0) + 1);
        // 复用同一 id 时使用稳定命名空间：保证旧监听能被 off 移除，避免重复触发与事件泄漏
        var uid = options.id ? ('bm_' + String(options.id).replace(/[^\w-]/g, '_')) : ('bm' + seq);
        var dynamic = !options.id;
        var $modal;

        if (options.id) {
            $modal = $('#' + options.id);
        }

        if (!$modal || !$modal.length) {
            var sizeClass = options.size === 'sm' ? ' booadmin-dialog-sm'
                : options.size === 'md' ? ' booadmin-dialog-md'
                : options.size === 'lg' ? ' booadmin-dialog-lg' : '';

            $modal = $(
                '<div class="booadmin-modal hidden" role="dialog" aria-modal="true"' +
                (options.id ? ' id="' + options.id + '"' : '') + '>' +
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

        // 无障碍：把标题与正文关联到容器，便于屏幕阅读器正确朗读
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

            // 每次打开重置确认按钮状态：onConfirm 内可临时禁用以防重入，复用单例时不影响下次打开
            $confirm.prop('disabled', false).removeClass('opacity-50');

            $title.text(override.title !== undefined ? override.title : (options.title || ''));
            $message.html(override.message !== undefined ? override.message : (options.message || ''));

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

        // 事件绑定只需一次（按 uid 命名空间隔离，可安全重复调用）
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
            setMessage: function (m) { $message.html(m); }
        };
    }

    /* ============================================================
     * 统一确认 / 提示（基于单例共享模态框，避免 DOM 累积）
     * ============================================================ */
    var sharedModal = null;
    var sharedCallback = null;

    function ensureShared() {
        if (sharedModal) {
            return sharedModal;
        }

        sharedModal = createModal({
            id: '__booadmin_shared_modal__',
            onConfirm: function () {
                var cb = sharedCallback;
                return typeof cb === 'function' ? cb() : undefined;
            }
        });

        return sharedModal;
    }

    /**
     * 确认弹窗
     *
     * 注意：message 按纯文本渲染（内部会做 HTML 转义），
     * 需要富文本正文请改用 BooAdmin.Modal.create。
     *
     * @param {Object} opts { title, message, confirmText, cancelText, onConfirm }
     */
    function confirm(opts) {
        opts = opts || {};
        var m = ensureShared();
        sharedCallback = typeof opts.onConfirm === 'function' ? opts.onConfirm : null;

        m.open({
            title: opts.title || i18n.confirmTitle,
            message: escapeHtml(opts.message),
            confirmText: opts.confirmText || i18n.confirm,
            cancelText: opts.cancelText || i18n.cancel,
            showCancel: opts.showCancel !== false
        });

        return m;
    }

    /**
     * 提示弹窗（仅一个按钮）
     *
     * 注意：message 按纯文本渲染（内部会做 HTML 转义）。
     *
     * @param {Object} opts { title, message, confirmText, onConfirm }
     */
    function alert(opts) {
        opts = opts || {};
        var m = ensureShared();
        sharedCallback = typeof opts.onConfirm === 'function' ? opts.onConfirm : null;

        m.open({
            title: opts.title || i18n.alertTitle,
            message: escapeHtml(opts.message),
            confirmText: opts.confirmText || i18n.ok,
            showCancel: false
        });

        return m;
    }

    /* ============================================================
     * 对外 API
     * ============================================================ */
    window.BooAdmin = {
        setI18n: setI18n,
        setVersion: setVersion,
        version: version,
        Modal: { create: createModal },
        confirm: confirm,
        alert: alert
    };
})(jQuery, window, document);
