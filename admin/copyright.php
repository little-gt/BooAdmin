<?php if(!defined('__TYPECHO_ADMIN__')) exit; ?>

<div class="booadmin-copyright-overlay" id="booadminCopyrightOverlay"></div>

<footer id="admin-footer" class="flex-shrink-0 py-4 text-center text-xs text-discord-muted bg-gray-50 border-t border-gray-200" role="contentinfo">
    <div class="mb-2">
        <span><a href="https://typecho.org" class="font-medium hover:text-discord-accent transition-colors" target="_blank" rel="noopener noreferrer">Typecho</a></span>
        <span class="mx-2 text-gray-300">&bull;</span>
        <span><?php _e('由 <a href="https://typecho.org" class="hover:text-discord-accent" target="_blank" rel="noopener">%s</a> 驱动, 版本 %s', $options->software, $options->version); ?></span>
        <span class="mx-2 text-gray-300">&bull;</span>
        <span>Theme by <span class="booadmin-copyright-tooltip">
            <a href="https://github.com/little-gt/BooAdmin" class="font-medium hover:text-discord-accent transition-colors" target="_blank" rel="noopener">BooAdmin</a>
            <div class="booadmin-copyright-popup" id="booadminCopyrightPopup">
                <button class="close-btn" onclick="closePopup(); event.stopPropagation();">&times;</button>
                <h3>关于 BooAdmin</h3>
                <div class="version">版本 1.2.4</div>
                <div class="content">
                    <div class="left">
                        <p class="main-copy"><strong>BooAdmin 是免费开源项目。</strong>BooAdmin 的开源维护、CDN资源分发与新功能更新都离不开您的捐助。您的支持将帮助我覆盖以下成本：</p>
                        <ul class="support-points">
                            <li><strong>开源维护成本：</strong>进行版本适配、问题修复、体验优化与 LTS 支持。</li>
                            <li><strong>服务运行成本：</strong>提供静态资源分发、国际/国内线路与 IPv6 优化保障。</li>
                        </ul>
                        <div class="action">
                            <a href="https://github.com/little-gt/BooAdmin" target="_blank" rel="noopener">访问 GitHub 开源项目</a>
                            <a href="https://cnb.cool/little-gt/BooAdmin/" class="secondary" target="_blank" rel="noopener">国内仓库</a>
                        </div>
                    </div>
                    <div class="right">
                        <div class="donation-card">
                            <img src="https://cnb.cool/little-gt/BooAdmin/-/git/raw/main/screenshot/supportme.jpg" alt="支持 BooAdmin 开源维护" />
                        </div>
                    </div>
                </div>
            </div>
            </span>
        </span>
    </div>
    <nav class="space-x-3 text-gray-400">
        <a href="https://docs.typecho.org" class="hover:text-discord-accent transition-colors" target="_blank" rel="noopener"><?php _e('帮助文档'); ?></a>
        <span>|</span>
        <a href="https://forum.typecho.org" class="hover:text-discord-accent transition-colors" target="_blank" rel="noopener"><?php _e('支持论坛'); ?></a>
        <span>|</span>
        <a href="https://github.com/typecho/typecho/issues" class="hover:text-discord-accent transition-colors" target="_blank" rel="noopener"><?php _e('报告错误'); ?></a>
        <span>|</span>
        <a href="https://typecho.org/download" class="hover:text-discord-accent transition-colors" target="_blank" rel="noopener"><?php _e('资源下载'); ?></a>
    </nav>
</footer>

<script>
    function togglePopup() {
        const popup = document.getElementById('booadminCopyrightPopup');
        const overlay = document.getElementById('booadminCopyrightOverlay');
        popup.classList.toggle('show');
        overlay.classList.toggle('show');
    }
    
    function openPopup() {
        const popup = document.getElementById('booadminCopyrightPopup');
        const overlay = document.getElementById('booadminCopyrightOverlay');
        if (!popup.classList.contains('show')) {
            popup.classList.add('show');
            overlay.classList.add('show');
        }
    }
    
    function closePopup() {
        const popup = document.getElementById('booadminCopyrightPopup');
        const overlay = document.getElementById('booadminCopyrightOverlay');
        popup.classList.remove('show');
        overlay.classList.remove('show');
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        const tooltip = document.querySelector('.booadmin-copyright-tooltip');
        const popup = document.getElementById('booadminCopyrightPopup');
        const overlay = document.getElementById('booadminCopyrightOverlay');
        
        tooltip.addEventListener('click', function(e) {
            e.preventDefault();
            openPopup();
        });
        
        overlay.addEventListener('click', function() {
            closePopup();
        });
        
        popup.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    });
</script>
