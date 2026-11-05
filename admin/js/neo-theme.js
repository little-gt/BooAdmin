/**
 * BooAdmin —— Typecho 原生后台兼容皮肤（neo）的侧栏交互
 * ==========================================================================
 * 与 css/neo.css 配套，仅在扩展页（extending.php）由 common-js.php 加载。
 * 某些扩展插件自带 .neo-shell / .neo-sidebar / .neo-overlay 骨架，
 * 本脚本负责其折叠、遮罩与响应式行为；普通后台页没有这些节点，直接退出。
 *
 * 所有浏览器 API 均做能力检测：隐私模式下 localStorage 会抛异常，
 * 旧版 Safari 的 MediaQueryList 只有 addListener。
 */
(function () {
    'use strict';

    var body = document.body;
    var sidebar = body && document.querySelector('.neo-sidebar');

    // 插件未输出 neo 骨架时无需运行
    if (!sidebar) {
        return;
    }

    var toggles = document.querySelectorAll('[data-sidebar-toggle]');
    var overlay = document.querySelector('.neo-overlay');
    var media = window.matchMedia ? window.matchMedia('(max-width: 1024px)') : null;
    var STORAGE_KEY = 'neoSidebarCollapsed';

    function readSaved() {
        try {
            return window.localStorage ? window.localStorage.getItem(STORAGE_KEY) : null;
        } catch (e) {
            return null;
        }
    }

    function persist(value) {
        try {
            if (window.localStorage) {
                window.localStorage.setItem(STORAGE_KEY, value ? 'true' : 'false');
            }
        } catch (e) {
            // 隐私模式 / 存储配额耗尽：忽略，仅影响记忆功能
        }
    }

    function isNarrow() {
        return !!(media && media.matches);
    }

    function applyState(collapsed, shouldPersist) {
        if (isNarrow()) {
            body.classList.toggle('sidebar-open', !collapsed);
        } else {
            body.classList.remove('sidebar-open');
        }
        body.classList.toggle('sidebar-collapsed', collapsed);

        if (shouldPersist) {
            persist(collapsed);
        }
    }

    // 窄屏始终折叠，宽屏恢复上次记忆
    applyState(isNarrow() ? true : readSaved() === 'true', false);

    Array.prototype.forEach.call(toggles, function (btn) {
        btn.addEventListener('click', function () {
            applyState(!body.classList.contains('sidebar-collapsed'), true);
        });
    });

    if (overlay) {
        overlay.addEventListener('click', function () {
            applyState(true, false);
        });
    }

    if (media) {
        var onMediaChange = function () {
            applyState(isNarrow() ? true : body.classList.contains('sidebar-collapsed'), false);
        };
        if (typeof media.addEventListener === 'function') {
            media.addEventListener('change', onMediaChange);
        } else if (typeof media.addListener === 'function') {
            media.addListener(onMediaChange);
        }
    }
})();
