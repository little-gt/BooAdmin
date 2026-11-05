/**
 * BooAdmin - Tailwind CSS v3 构建配置（唯一权威调色板）
 *
 * 设计原则：Tailwind 产物中【不允许出现任何硬编码字面色值】。
 * 所有颜色工具类都引用 --booadmin-* 语义变量，明暗主题由
 * admin/css/light.css 与 admin/css/dark.css 中的变量值决定。
 *
 * 由此：
 *  - 新增任意颜色类（含 hover:/group-hover:/divide-/ring-/from-/to- 变体）
 *    都自动跟随系统明暗，无需在 dark.css 里追加 !important 补丁；
 *  - bg-white 与 text-white 可分别映射（暗色下背景变深、文字保持浅色）；
 *  - light.css 是唯一的亮色取值来源，dark.css 只做变量覆盖。
 *
 * 注意：Tailwind 默认色板中未被本项目使用的色阶同样做了映射，
 * 以保证未来新增类不会引入「亮色补丁」。
 *
 * @type {import('tailwindcss').Config}
 */

/**
 * 生成主题色引用。
 * 用 color-mix + <alpha-value> 占位符，使 Tailwind 能同时生成
 * bg-/text-/border- 的 /NN 透明度变体（否则 var() 形式会丢失这些类）。
 * 字面 fallback 保证变量未定义时颜色仍可读。
 */
const v = (name, fallback) =>
    `color-mix(in srgb, var(--booadmin-${name}, ${fallback}) calc(<alpha-value> * 100%), transparent)`;

/** 中性灰阶：按用途分别映射，避免 bg-/text- 语义互相污染 */
const neutral = {
    50:  { bg: v('surface-2', '#f9fafb'),       text: v('text', '#111827'),        border: v('border', '#e5e7eb') },
    100: { bg: v('surface-muted', '#f3f4f6'),   text: v('text', '#111827'),        border: v('border', '#e5e7eb') },
    200: { bg: v('active', '#e5e7eb'),          text: v('muted', '#6b7280'),       border: v('border', '#e5e7eb') },
    300: { bg: v('border-strong', '#d1d5db'),   text: v('muted', '#6b7280'),       border: v('border-strong', '#d1d5db') },
    400: { bg: v('border-strong', '#9ca3af'),   text: v('muted', '#9ca3af'),       border: v('border-strong', '#d1d5db') },
    500: { bg: v('control-bg-hover', '#6b7280'),text: v('muted', '#6b7280'),       border: v('border-strong', '#d1d5db') },
    600: { bg: v('control-bg-hover', '#4b5563'),text: v('text', '#4b5563'),        border: v('border-strong', '#d1d5db') },
    700: { bg: v('control-bg-hover', '#374151'),text: v('text', '#374151'),        border: v('border-strong', '#d1d5db') },
    800: { bg: v('nav-bg', '#1f2937'),          text: v('text', '#1f2937'),        border: v('border-strong', '#d1d5db') },
    900: { bg: v('nav-bg', '#111827'),          text: v('text', '#111827'),        border: v('border-strong', '#d1d5db') },
};

/** 语义色阶：50/100 为浅底，500/600 为主色，700/800 为深字 */
const semantic = (token, soft, strong) => ({
    50:  { bg: v(soft, '#f9fafb'),   text: v(token, '#111827'), border: v(token, '#e5e7eb') },
    100: { bg: v(soft, '#f3f4f6'),   text: v(token, '#111827'), border: v(token, '#e5e7eb') },
    200: { bg: v(soft, '#e5e7eb'),   text: v(token, '#111827'), border: v(token, '#e5e7eb') },
    300: { bg: v(token, '#d1d5db'),  text: v(token, '#111827'), border: v(token, '#e5e7eb') },
    400: { bg: v(token, '#9ca3af'),  text: v(token, '#111827'), border: v(token, '#e5e7eb') },
    500: { bg: v(token, '#6b7280'),  text: v(token, '#111827'), border: v(token, '#e5e7eb') },
    600: { bg: v(token, '#4b5563'),  text: v(token, '#111827'), border: v(token, '#e5e7eb') },
    700: { bg: v(strong, '#374151'), text: v(token, '#111827'), border: v(token, '#e5e7eb') },
    800: { bg: v(strong, '#1f2937'), text: v(strong, '#111827'),border: v(token, '#e5e7eb') },
    900: { bg: v(strong, '#111827'), text: v(strong, '#111827'),border: v(token, '#e5e7eb') },
});

/** 把 {50..900} 的分组映射拆成 Tailwind 需要的三个独立调色板 */
function splitPalettes(families) {
    const backgroundColor = {};
    const textColor = {};
    const borderColor = {};

    Object.keys(families).forEach(function (name) {
        const shades = families[name];
        backgroundColor[name] = {};
        textColor[name] = {};
        borderColor[name] = {};

        Object.keys(shades).forEach(function (shade) {
            const slot = shades[shade];
            backgroundColor[name][shade] = slot.bg;
            textColor[name][shade] = slot.text;
            borderColor[name][shade] = slot.border;
        });
    });

    return { backgroundColor: backgroundColor, textColor: textColor, borderColor: borderColor };
}

const palettes = splitPalettes({
    gray: neutral,
    red: semantic('danger', 'error-bg', 'danger-strong'),
    orange: semantic('warning', 'warning-bg', 'danger-strong'),
    amber: semantic('warning', 'warning-bg', 'warning-text'),
    yellow: semantic('warning', 'warning-bg', 'warning-text'),
    green: semantic('success', 'success-bg', 'success-text-strong'),
    emerald: semantic('success', 'success-bg', 'success-text-strong'),
    teal: semantic('info', 'info-bg', 'info-hover'),
    cyan: semantic('info', 'info-bg', 'info-hover'),
    sky: semantic('info', 'info-bg', 'info-hover'),
    blue: semantic('accent', 'link-soft-bg', 'accent-active'),
    indigo: semantic('accent', 'token-bg', 'accent-active'),
    violet: semantic('accent', 'token-bg', 'accent-active'),
    purple: semantic('accent', 'token-bg', 'accent-active'),
    fuchsia: semantic('accent', 'token-bg', 'accent-active'),
    pink: semantic('danger', 'error-bg', 'danger-strong'),
    rose: semantic('danger', 'error-bg', 'danger-strong'),
    slate: neutral,
    zinc: neutral,
    neutral: neutral,
    stone: neutral,
});

/** 品牌色：沿用 light.css/dark.css 中已存在的兼容别名，同样支持 alpha 变体 */
const dv = (name) =>
    `color-mix(in srgb, var(--color-discord-${name}) calc(<alpha-value> * 100%), transparent)`;

const discord = {
    light:   dv('light'),
    sidebar: dv('sidebar'),
    active:  dv('active'),
    accent:  dv('accent'),
    text:    dv('text'),
    muted:   dv('muted'),
};

function withDiscord(palette) {
    return Object.assign({}, palette, { discord: discord });
}

/** 白/黑/透明：背景与文字必须分离 */
const whiteBg = v('surface', '#ffffff');
const whiteText = v('on-accent', '#ffffff');
const blackBg = v('overlay-solid', '#000000');
const blackText = v('text', '#000000');

const backgroundColor = Object.assign(
    { white: whiteBg, black: blackBg, transparent: 'transparent', current: 'currentColor' },
    withDiscord(palettes.backgroundColor)
);
const textColor = Object.assign(
    { white: whiteText, black: blackText, transparent: 'transparent', current: 'currentColor' },
    withDiscord(palettes.textColor)
);
const borderColor = Object.assign(
    { DEFAULT: v('border', '#e5e7eb'), white: whiteBg, black: blackBg, transparent: 'transparent', current: 'currentColor' },
    withDiscord(palettes.borderColor)
);

module.exports = {
    content: [
        './admin/**/*.php',
        './admin/**/*.js',
    ],
    theme: {
        // 不覆盖 theme.colors：保留 Tailwind 原始色板供未列出的工具类回退。
        // 本项目所有颜色类由下面三个「用途独立」的调色板生成。
        extend: {
            backgroundColor: backgroundColor,
            textColor: textColor,
            borderColor: borderColor,
            divideColor: borderColor,
            ringColor: Object.assign({}, borderColor, { DEFAULT: v('accent', '#5865f2') }),
            ringOffsetColor: backgroundColor,
            placeholderColor: textColor,
            caretColor: textColor,
            accentColor: backgroundColor,
            fill: backgroundColor,
            stroke: borderColor,
            gradientColorStops: Object.assign({}, backgroundColor, { transparent: 'transparent', current: 'currentColor' }),
        },
    },
    plugins: [],
};
