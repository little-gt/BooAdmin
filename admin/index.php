<?php
include 'common.php';
include 'header.php';
include 'menu.php';

$stat = \Widget\Stat::alloc();

// 权限预检查
$canWrite = $user->pass('contributor', true);
$canEdit  = $user->pass('editor', true);

/**
 * 获取最近 7 天的文章与评论趋势。
 *
 * 原实现按天循环执行 7×2=14 次 COUNT 查询，每次控制台加载都要跑一遍。
 * 现改为「2 次区间查询 + PHP 端按日分桶」：只取 created 一列，
 * 在内存里归入 7 个桶，结果完全一致，但查询次数从 14 降到 2。
 * 以服务器本地时区 dayStart 计算，与原 strtotime('Y-m-d ...') 口径一致。
 */
$db = \Typecho\Db::get();
$days = [];
$dayIndex = [];
for ($i = 6; $i >= 0; $i--) {
    $ts = strtotime("-{$i} days");
    $dayKey = date('Y-m-d', $ts);
    $dayIndex[$dayKey] = count($days);
    $days[] = date('m/d', $ts);
}

$postsData = array_fill(0, 7, 0);
$commentsData = array_fill(0, 7, 0);
$startTime = strtotime(date('Y-m-d', strtotime('-6 days')) . ' 00:00:00');
$endTime = strtotime(date('Y-m-d') . ' 23:59:59');

/** 把查询结果按 created 日期累加到对应桶 */
$bucket = function ($rows, array &$target) use ($dayIndex) {
    foreach ($rows as $row) {
        $row = (array) $row;
        $created = isset($row['created']) ? (int) $row['created'] : 0;
        $key = date('Y-m-d', $created);
        if ($created > 0 && isset($dayIndex[$key])) {
            $target[$dayIndex[$key]]++;
        }
    }
};

$bucket($db->fetchAll($db->select('created')
    ->from('table.contents')
    ->where('type = ?', 'post')
    ->where('status = ?', 'publish')
    ->where('created >= ?', $startTime)
    ->where('created <= ?', $endTime)), $postsData);

$bucket($db->fetchAll($db->select('created')
    ->from('table.comments')
    ->where('status = ?', 'approved')
    ->where('created >= ?', $startTime)
    ->where('created <= ?', $endTime)), $commentsData);

$chartDays = json_encode($days);
$chartPosts = json_encode($postsData);
$chartComments = json_encode($commentsData);
?>
<main class="flex-1 flex flex-col overflow-hidden">
    <!-- Top Header -->
    <header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-6 z-10">
        <div class="flex items-center text-discord-muted">
            <button id="mobile-menu-btn" class="mr-4 md:hidden text-discord-text focus:outline-none">
                <i class="fas fa-bars"></i>
            </button>
            <i class="fas fa-home mr-2 hidden md:inline"></i>
            <span class="mx-2 hidden md:inline">/</span>
            <span class="font-medium text-discord-text"><?php _e('控制台'); ?></span>
        </div>
        <div class="flex items-center space-x-4">
            <a href="<?php $options->siteUrl(); ?>" class="text-discord-muted hover:text-discord-accent transition-colors" title="<?php _e('查看网站'); ?>" target="_blank">
                <i class="fas fa-globe"></i>
            </a>
            <a href="<?php $options->adminUrl('profile.php'); ?>" class="text-discord-muted hover:text-discord-accent transition-colors" title="<?php _e('个人资料'); ?>">
                <i class="fas fa-user-circle"></i>
            </a>
        </div>
    </header>

    <!-- Content Area -->
    <div class="flex-1 overflow-y-auto p-4 md:p-8">
        <div class="w-full max-w-none mx-auto" id="booadmin-content">
            
            <!-- Welcome Section -->
            <div class="bg-white p-6 mb-6 flex items-center justify-between border-l-4 border-discord-accent">
                <div>
                    <h2 class="text-2xl font-bold text-discord-text mb-2"><?php _e('欢迎回来, %s!', $user->screenName); ?></h2>
                    <p class="text-discord-muted"><?php _e('目前有 <strong>%s</strong> 篇文章, 并有 <strong>%s</strong> 条关于你的评论在 <strong>%s</strong> 个分类中.',
                        $stat->myPublishedPostsNum, $stat->myPublishedCommentsNum, $stat->categoriesNum); ?></p>
                </div>
                <div class="flex space-x-3">
                    <?php if ($canWrite): ?>
                    <a href="<?php $options->adminUrl('write-post.php'); ?>" class="bg-discord-accent hover:bg-blue-600 text-white px-4 py-2 font-medium transition-colors text-sm">
                        <i class="fas fa-pen mr-2"></i> <?php _e('撰写新文章'); ?>
                    </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Card 1 -->
                <div class="stat-card p-6">
                    <div class="stat-card-gradient-accent"></div>
                    <div class="flex items-center justify-between mb-4 relative z-1">
                        <h3 class="stat-card-label font-bold text-xs uppercase tracking-wider"><?php _e('总文章数'); ?></h3>
                        <div class="stat-card-icon-accent">
                            <i class="fas fa-file-alt text-lg"></i>
                        </div>
                    </div>
                    <div class="text-3xl font-black stat-card-value relative z-1"><?php echo $stat->myPublishedPostsNum; ?></div>
                    <div class="mt-2 text-xs stat-card-trend font-bold flex items-center relative z-1">
                        <i class="fas fa-arrow-up mr-1"></i> <span><?php _e('持续更新中'); ?></span>
                    </div>
                </div>
                <!-- Card 2 -->
                <div class="stat-card p-6">
                    <div class="stat-card-gradient-success"></div>
                    <div class="flex items-center justify-between mb-4 relative z-1">
                        <h3 class="stat-card-label font-bold text-xs uppercase tracking-wider"><?php _e('总评论数'); ?></h3>
                         <div class="stat-card-icon-success">
                            <i class="fas fa-comments text-lg"></i>
                        </div>
                    </div>
                    <div class="text-3xl font-black stat-card-value relative z-1"><?php echo $stat->myPublishedCommentsNum; ?></div>
                    <div class="mt-2 text-xs stat-card-trend font-bold flex items-center relative z-1">
                         <i class="fas fa-chart-line mr-1"></i> <span><?php _e('互动活跃'); ?></span>
                    </div>
                </div>
                 <!-- Card 3 -->
                 <div class="stat-card p-6">
                    <div class="stat-card-gradient-warning"></div>
                    <div class="flex items-center justify-between mb-4 relative z-1">
                        <h3 class="stat-card-label font-bold text-xs uppercase tracking-wider"><?php _e('待审核'); ?></h3>
                         <div class="stat-card-icon-warning">
                            <i class="fas fa-hourglass-half text-lg"></i>
                        </div>
                    </div>
                    <div class="text-3xl font-black stat-card-value relative z-1"><?php echo $stat->waitingCommentsNum; ?></div>
                    <div class="mt-2 text-xs stat-card-label font-bold flex items-center relative z-10">
                        <span><?php _e('需要处理'); ?></span>
                    </div>
                </div>
                 <!-- Card 4 -->
                 <div class="stat-card p-6">
                    <div class="stat-card-gradient-info"></div>
                    <div class="flex items-center justify-between mb-4 relative z-1">
                        <h3 class="stat-card-label font-bold text-xs uppercase tracking-wider"><?php _e('分类数量'); ?></h3>
                         <div class="stat-card-icon-info">
                            <i class="fas fa-folder text-lg"></i>
                        </div>
                    </div>
                    <div class="text-3xl font-black stat-card-value relative z-1"><?php echo $stat->categoriesNum; ?></div>
                     <div class="mt-2 text-xs stat-card-label font-bold flex items-center relative z-10">
                        <span><?php _e('内容架构'); ?></span>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
             <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                <!-- Main Activity Chart -->
                <div class="lg:col-span-2 dashboard-card p-6">
                    <div class="flex items-center justify-between mb-6">
                         <h3 class="text-lg font-bold dashboard-card-title flex items-center">
                            <i class="fas fa-chart-area mr-2 text-discord-accent"></i>
                            <?php _e('内容趋势'); ?>
                        </h3>
                         <div class="flex items-center space-x-4 text-xs">
                            <span class="flex items-center"><span class="w-3 h-3 rounded-full dashboard-chart-legend-blue mr-1"></span><?php _e('文章'); ?></span>
                            <span class="flex items-center"><span class="w-3 h-3 rounded-full dashboard-chart-legend-green mr-1"></span><?php _e('评论'); ?></span>
                        </div>
                    </div>
                    <div id="activity-chart" style="height: 300px; width: 100%;"></div>
                </div>
                
                <!-- Distribution Chart -->
                <div class="dashboard-card p-6">
                    <h3 class="text-lg font-bold dashboard-card-title mb-6 flex items-center">
                         <i class="fas fa-chart-pie mr-2 text-discord-accent"></i>
                        <?php _e('内容分布'); ?>
                    </h3>
                    <div id="distribution-chart" style="height: 300px; width: 100%;"></div>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <!-- Recent Posts -->
                <div class="lg:col-span-2 dashboard-card p-0 overflow-hidden">
                    <div class="p-6 dashboard-card-header flex justify-between items-center">
                        <h3 class="text-lg font-bold dashboard-card-title flex items-center">
                            <i class="fas fa-newspaper mr-2 text-discord-muted"></i>
                            <?php _e('最近文章'); ?>
                        </h3>
                        <?php if ($canWrite): ?><a href="<?php $options->adminUrl('write-post.php'); ?>" class="text-xs font-medium text-discord-accent hover:underline"><?php _e('写文章'); ?></a><?php endif; ?>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm dashboard-table">
                            <thead>
                                <tr>
                                    <th class="py-3 px-6 font-medium"><?php _e('标题'); ?></th>
                                    <th class="py-3 px-6 font-medium"><?php _e('日期'); ?></th>
                                    <th class="py-3 px-6 font-medium text-right"><?php _e('操作'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php \Widget\Contents\Post\Recent::alloc('pageSize=8')->to($posts); ?>
                                <?php if ($posts->have()): ?>
                                    <?php while ($posts->next()): ?>
                                        <tr class="border-b transition-colors group">
                                            <td class="py-3 px-6 font-medium">
                                                <a href="<?php $posts->permalink(); ?>" class="dashboard-card-title group-hover:text-discord-accent transition-colors"><?php $posts->title(); ?></a>
                                            </td>
                                            <td class="py-3 px-6 dashboard-table-date text-xs"><?php $posts->date('Y-m-d'); ?></td>
                                            <td class="py-3 px-6 text-right">
                                                <?php if ($canEdit || ($canWrite && $posts->authorId == $user->uid)): ?>
                                                <a href="<?php $options->adminUrl('write-post.php?cid=' . $posts->cid); ?>" class="dashboard-table-action transition-colors p-2" title="<?php _e('编辑'); ?>"><i class="fas fa-pencil-alt"></i></a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="3" class="py-8 text-center dashboard-table-date"><?php _e('暂时没有文章'); ?></td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Recent Comments -->
                <div class="dashboard-comments p-0 overflow-hidden flex flex-col">
                     <div class="p-6 dashboard-comments-header flex justify-between items-center">
                        <h3 class="text-lg font-bold dashboard-card-title flex items-center">
                            <i class="fas fa-comments mr-2 text-discord-muted"></i>
                            <?php _e('最新回复'); ?>
                        </h3>
                    </div>
                    <div class="p-0 flex-1 overflow-y-auto max-h-[400px]">
                        <?php \Widget\Comments\Recent::alloc('pageSize=5')->to($comments); ?>
                        <?php if ($comments->have()): ?>
                            <div class="divide-y">
                            <?php while ($comments->next()): ?>
                                <div class="p-4 dashboard-comments-item transition-colors">
                                    <div class="flex items-start space-x-3">
                                        <?php echo getAvatar($comments->mail, $comments->author, 40, 'comment-avatar'); ?>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center justify-between mb-1">
                                                <p class="text-sm font-bold dashboard-comments-author truncate"><?php echo htmlspecialchars($comments->author() ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
                                                <span class="text-xs dashboard-comments-date"><?php $comments->date('m-d'); ?></span>
                                            </div>
                                            <div class="text-xs dashboard-comments-content mb-1">
                                                <?php _e('在'); ?> <a href="<?php $comments->permalink(); ?>" class="text-discord-accent hover:underline"><?php $comments->title(); ?></a>
                                            </div>
                                            <div class="text-sm dashboard-comments-excerpt p-2 border mt-1 line-clamp-2">
                                                <?php $comments->excerpt(60, '...'); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <div class="p-8 text-center text-sm dashboard-comments-date"><?php _e('暂时没有回复'); ?></div>
                        <?php endif; ?>
                    </div>
                    <?php if ($canEdit): ?><a href="<?php $options->adminUrl('manage-comments.php'); ?>" class="block p-3 text-center text-xs font-bold dashboard-comments-footer transition-colors uppercase tracking-wide"><?php _e('查看所有评论'); ?></a><?php endif; ?>
                </div>

                <!-- Official Log (Compact) -->
                <div class="lg:col-span-3 booadmin-news-section p-5 border mt-2">
                    <div class="flex items-center justify-between mb-4">
                         <h3 class="booadmin-news-header-title text-sm font-bold flex items-center">
                            <span class="booadmin-news-header-icon w-8 h-8 flex items-center justify-center mr-3">
                                <i class="fas fa-bullhorn text-white text-xs"></i>
                            </span>
                            <?php _e('Typecho 官方动态'); ?>
                        </h3>
                        <a href="https://typecho.org" target="_blank" class="booadmin-news-official-link text-xs font-medium flex items-center transition-colors">
                            <?php _e('访问官网'); ?> <i class="fas fa-external-link-alt ml-1 text-[10px]"></i>
                        </a>
                    </div>
                    <div id="typecho-message" class="booadmin-news-message text-sm">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3" id="typecho-news-grid">
                            <div class="booadmin-news-skeleton-item flex items-center space-x-3 p-3 border">
                                <div class="booadmin-news-skeleton-icon w-10 h-10"></div>
                                <div class="flex-1">
                                    <div class="booadmin-news-skeleton-line h-3 w-3/4 mb-2"></div>
                                    <div class="booadmin-news-skeleton-line-soft h-2 w-1/2"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>
    <!-- Footer在main内部 -->
    <?php include 'copyright.php'; ?>
</main>
<?php
include 'common-js.php';
?>
<script>
    /**
     * 三个彼此独立的模块：图表 / 官方动态 / 版本检查。
     * 任一模块异常（例如 ECharts CDN 不可达）都不会阻断其余模块，
     * 避免第三方资源故障导致整个控制台脚本级联失效。
     */
    (function () {
        function module(name, init) {
            $(function () {
                try {
                    init();
                } catch (e) {
                    if (window.console && typeof console.error === 'function') {
                        console.error('[BooAdmin] 控制台模块失败: ' + name, e);
                    }
                }
            });
        }

        var chartDays = <?php echo $chartDays; ?>;
        var chartPosts = <?php echo $chartPosts; ?>;
        var chartComments = <?php echo $chartComments; ?>;

        /** 外部数据统一按纯文本渲染，供各模块共用 */
        function esc(value) {
            return $('<div/>').text(value == null ? '' : String(value)).html();
        }

        /** 链接只允许 http/https，避免 javascript: 注入 */
        function safeUrl(url) {
            return /^https?:\/\//i.test(String(url || '')) ? String(url) : '#';
        }

        /**
         * 带 TTL 的 sessionStorage 封装。
         * 缓存一律以 {t: 时间戳, d: 数据} 形式存储，过期自动丢弃；
         * 旧格式（纯 JSON 数组/对象）没有 t 字段，会被判定为过期并重新拉取。
         */
        var CACHE_TTL = 60 * 60 * 1000; // 1 小时
        function cacheGet(key) {
            try {
                if (!window.sessionStorage) { return null; }
                var raw = sessionStorage.getItem(key);
                if (!raw) { return null; }
                var box = JSON.parse(raw);
                if (!box || typeof box.t !== 'number' || (Date.now() - box.t) > CACHE_TTL) {
                    sessionStorage.removeItem(key);
                    return null;
                }
                return box.d;
            } catch (e) {
                return null;
            }
        }
        function cacheSet(key, data) {
            try {
                if (window.sessionStorage) {
                    sessionStorage.setItem(key, JSON.stringify({ t: Date.now(), d: data }));
                }
            } catch (e) {
                // 配额耗尽 / 隐私模式：忽略，仅失去缓存
            }
        }

        module('charts', function () {
        // ECharts 由 CDN 以 defer 加载；不可用时给出占位而非抛 ReferenceError
        if (typeof window.echarts === 'undefined') {
            $('#activity-chart, #distribution-chart').html(
                '<div class="booadmin-chart-unavailable"><?php _e('图表组件加载失败，请检查网络后刷新。'); ?></div>'
            );
            return;
        }

        // 活动图表配置（使用真实数据）

        var css = getComputedStyle(document.documentElement);
        var chartColor = {
            tooltipBg: css.getPropertyValue('--booadmin-chart-tooltip-bg').trim() || 'rgba(255,255,255,0.95)',
            border: css.getPropertyValue('--booadmin-border').trim() || '#e5e7eb',
            text: css.getPropertyValue('--booadmin-chart-text').trim() || '#374151',
            axis: css.getPropertyValue('--booadmin-chart-axis').trim() || '#9ca3af',
            grid: css.getPropertyValue('--booadmin-chart-grid').trim() || '#f3f4f6',
            posts: css.getPropertyValue('--booadmin-chart-posts').trim() || '#5865F2',
            postsSoft: css.getPropertyValue('--booadmin-chart-posts-soft').trim() || 'rgba(88, 101, 242, 0.3)',
            postsFade: css.getPropertyValue('--booadmin-chart-posts-fade').trim() || 'rgba(88, 101, 242, 0.05)',
            comments: css.getPropertyValue('--booadmin-chart-comments').trim() || '#3BA55C',
            commentsSoft: css.getPropertyValue('--booadmin-chart-comments-soft').trim() || 'rgba(59, 165, 92, 0.2)',
            commentsFade: css.getPropertyValue('--booadmin-chart-comments-fade').trim() || 'rgba(59, 165, 92, 0.02)',
            warn: css.getPropertyValue('--booadmin-chart-warn').trim() || '#FAA61A',
            danger: css.getPropertyValue('--booadmin-chart-danger').trim() || '#ED4245',
            surface: css.getPropertyValue('--booadmin-surface').trim() || '#ffffff'
        };
        
        var activityOption = {
            grid: { top: 30, right: 20, bottom: 30, left: 40, containLabel: true },
            tooltip: { 
                trigger: 'axis',
                backgroundColor: chartColor.tooltipBg,
                borderColor: chartColor.border,
                borderWidth: 1,
                textStyle: { color: chartColor.text },
                formatter: function(params) {
                    var result = '<div class="booadmin-chart-tooltip-title">' + params[0].axisValue + '</div>';
                    params.forEach(function(item) {
                        result += '<div class="booadmin-chart-tooltip-row">' +
                            '<span class="booadmin-chart-tooltip-dot" style="background:' + item.color + '"></span>' +
                            '<span class="booadmin-chart-tooltip-label">' + item.seriesName + ':</span>' +
                            '<strong class="booadmin-chart-tooltip-value">' + item.value + '</strong></div>';
                    });
                    return result;
                }
            },
            xAxis: {
                type: 'category',
                data: chartDays,
                axisLine: { show: false },
                axisTick: { show: false },
                axisLabel: { color: chartColor.axis, fontSize: 11 }
            },
            yAxis: {
                type: 'value',
                minInterval: 1,
                splitLine: { lineStyle: { type: 'dashed', color: chartColor.grid } },
                axisLabel: { color: chartColor.axis }
            },
            series: [
                {
                    name: '<?php _e('文章'); ?>',
                    data: chartPosts,
                    type: 'line',
                    smooth: true,
                    symbol: 'circle',
                    symbolSize: 8,
                    itemStyle: { color: chartColor.posts },
                    areaStyle: {
                        color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
                            { offset: 0, color: chartColor.postsSoft },
                            { offset: 1, color: chartColor.postsFade }
                        ])
                    },
                    lineStyle: { width: 3 }
                },
                {
                    name: '<?php _e('评论'); ?>',
                    data: chartComments,
                    type: 'line',
                    smooth: true,
                    symbol: 'circle',
                    symbolSize: 8,
                    itemStyle: { color: chartColor.comments },
                    areaStyle: {
                        color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
                            { offset: 0, color: chartColor.commentsSoft },
                            { offset: 1, color: chartColor.commentsFade }
                        ])
                    },
                    lineStyle: { width: 3 }
                }
            ]
        };

        // 分布图表配置
        var distributionOption = {
             tooltip: { trigger: 'item' },
             legend: { bottom: '0%', left: 'center', icon: 'circle' },
             series: [
                 {
                     name: '内容统计',
                     type: 'pie',
                     radius: ['40%', '70%'],
                     avoidLabelOverlap: false,
                     itemStyle: {
                         borderColor: chartColor.surface,
                         borderWidth: 2
                     },

                     label: { show: false, position: 'center' },
                     emphasis: {
                         label: { show: true, fontSize: '18', fontWeight: 'bold' }
                     },
                     labelLine: { show: false },
                     data: [
                         { value: <?php echo $stat->myPublishedPostsNum; ?>, name: '<?php _e('文章'); ?>', itemStyle: { color: chartColor.posts } },
                         { value: <?php echo $stat->myPublishedCommentsNum; ?>, name: '<?php _e('评论'); ?>', itemStyle: { color: chartColor.comments } },
                         { value: <?php echo $stat->categoriesNum; ?>, name: '<?php _e('分类'); ?>', itemStyle: { color: chartColor.warn } },
                         { value: <?php echo $stat->waitingCommentsNum; ?>, name: '<?php _e('待审'); ?>', itemStyle: { color: chartColor.danger } }
                     ]
                 }
             ]
         };

        var activityChart = echarts.init(document.getElementById('activity-chart'));
        activityChart.setOption(activityOption);
        
        var distributionChart = echarts.init(document.getElementById('distribution-chart'));
        distributionChart.setOption(distributionOption);

        // 窗口尺寸变化时重绘图表
        window.addEventListener('resize', function() {
            activityChart.resize();
            distributionChart.resize();
        });
        });

        module('news', function () {
        var newsGrid = $('#typecho-news-grid'),
            NEWS_CACHE_KEY = 'feed_v5';

        var newsIcons = ['fa-newspaper', 'fa-code-branch', 'fa-rocket', 'fa-star', 'fa-bolt', 'fa-gift'];
        var newsBadgeClasses = [
            'booadmin-news-badge-1',
            'booadmin-news-badge-2',
            'booadmin-news-badge-3',
            'booadmin-news-badge-4',
            'booadmin-news-badge-5',
            'booadmin-news-badge-6'
        ];

        function renderNews(o) {
            var html = '';
            if (o && o.length) {
                for (var i = 0; i < o.length && i < 6; i++) {
                    var item = o[i];
                    var icon = newsIcons[i % newsIcons.length];
                    var badgeClass = newsBadgeClasses[i % newsBadgeClasses.length];
                    html += '<a href="' + esc(safeUrl(item.link)) + '" target="_blank" rel="noopener noreferrer" class="booadmin-news-item group flex items-center space-x-3 p-3 transition-all">' +
                        '<div class="booadmin-news-icon w-10 h-10 ' + badgeClass + ' flex items-center justify-center flex-shrink-0 group-hover:scale-105 transition-transform">' +
                        '<i class="fas ' + icon + ' booadmin-news-icon-glyph text-sm"></i></div>' +
                        '<div class="flex-1 min-w-0">' +
                        '<p class="booadmin-news-title text-sm font-medium truncate transition-colors">' + esc(item.title) + '</p>' +
                        '<p class="booadmin-news-meta text-xs mt-0.5">' + esc(item.date) + '</p>' +
                        '</div>' +
                        '<i class="fas fa-chevron-right booadmin-news-arrow text-xs transition-colors"></i></a>';
                }
            } else {
                html = '<div class="col-span-full text-center py-4 booadmin-news-empty"><i class="fas fa-inbox mr-2"></i><?php _e('暂无动态'); ?></div>';
            }
            newsGrid.html(html);
            return html;
        }

        var emptyNews = '<div class="col-span-full text-center py-4 booadmin-news-empty"><i class="fas fa-inbox mr-2"></i><?php _e('暂无动态'); ?></div>';

        /**
         * 缓存只存结构化数据，绝不缓存渲染后的 HTML，
         * 避免 sessionStorage 被污染时整段 HTML 被直接回放造成 XSS。
         * cacheGet 带 TTL 与格式校验，非法/过期数据一律重新拉取。
         */
        var cached = cacheGet(NEWS_CACHE_KEY);

        if (Array.isArray(cached)) {
            renderNews(cached);
        } else {
            // 异步获取官方动态：带超时与失败兜底，避免后端慢请求阻塞页面
            $.ajax({
                url: '<?php $options->index('/action/ajax?do=feed'); ?>',
                type: 'GET',
                dataType: 'json',
                timeout: 5000,
                success: function (o) {
                    renderNews(o);
                    if (Array.isArray(o)) {
                        cacheSet(NEWS_CACHE_KEY, o);
                    }
                },
                error: function () {
                    // 获取失败（超时 / 网络错误）时直接显示占位，不再长时间停留在骨架
                    newsGrid.html(emptyNews);
                }
            });
        }
        });

        module('version', function () {
        var UPDATE_CACHE_KEY = 'update_v2';
        var update = cacheGet(UPDATE_CACHE_KEY);

        function applyUpdate(o) {
            if (!o || !o.available) {
                return;
            }

            // 版本号与链接均按纯文本/白名单处理
            var current = esc(o.current), latest = esc(o.latest), link = esc(safeUrl(o.link));
            var $banner = $('<div class="booadmin-update-banner p-3 mb-4 text-sm"><i class="fas fa-info-circle mr-1"></i> '
                + '<?php _e('您当前使用的版本是 %s'); ?>'.replace('%s', current)
                + ' <a href="' + link + '" target="_blank" rel="noopener noreferrer" class="booadmin-update-link font-bold underline ml-2">'
                + '<?php _e('官方最新版本是 %s'); ?>'.replace('%s', latest) + '</a></div>');

            // 原实现插入目标是 .typecho-page-main / .max-w-7xl，
            // 这两个类在 BooAdmin 模板中均不存在，导致横幅永远插不进 DOM。
            var $target = $('#booadmin-content');
            if ($target.length) {
                $target.prepend($banner);
            } else {
                $('.w-full.max-w-none.mx-auto').first().prepend($banner);
            }
        }

        if (update && typeof update === 'object') {
            applyUpdate(update);
        } else {
            // 版本检查同样异步并带超时，失败不影响页面其余部分
            $.ajax({
                url: '<?php $options->index('/action/ajax?do=checkVersion'); ?>',
                type: 'GET',
                dataType: 'json',
                timeout: 5000,
                success: function (o) {
                    applyUpdate(o);
                    if (o && typeof o === 'object') {
                        cacheSet(UPDATE_CACHE_KEY, o);
                    }
                },
                error: function () { /* 版本检查失败不阻塞页面 */ }
            });
        }
        });
    })();
</script>
<?php include 'footer.php'; ?>
