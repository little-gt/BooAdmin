/**
 * Tailwind CSS v4 兼容占位配置。
 *
 * ⚠️ 非生产构建入口。唯一权威构建链为 config/v3/。
 * v4 采用 CSS-first 配置，实际主题与内容路径请见同目录 input.css 的
 * @theme / @source 声明；本文件仅为兼容旧工具链保留，不再承载调色板定义。
 * 详见 config/README.md。
 *
 * @type {import('tailwindcss').Config}
 */
module.exports = {
  content: [
    '../../admin/**/*.php',
    '../../admin/**/*.js',
  ],
  plugins: [],
};
