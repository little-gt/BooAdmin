# Tailwind CSS 构建配置 / Build Configuration / ビルド構成

## 中文

BooAdmin 随附两套 Tailwind CSS 配置：**v4**（推荐）与 **v3**（旧版）。两者均将 `config/<version>/input.css` 编译为 `admin/css/tailwind.css`。

### 目录结构

```
config/
├── v4/                     # Tailwind CSS v4（推荐）
│   ├── input.css           # 入口文件 — CSS-first 配置 + Discord 主题
│   └── tailwind.config.js  # 最小化配置（仅内容路径）
└── v3/                     # Tailwind CSS v3（旧版）
    ├── input.css           # 入口文件
    └── tailwind.config.js  # 基于 JS 的配置
```

### 环境要求

仓库根目录已提供独立的 Tailwind CLI 二进制文件，因此无需 Node.js 或 `npm install`：

| 二进制文件                | 用途     |
| ----------------------- | -------- |
| `tailwindcss-4.4.3.exe` | v4 构建  |
| `tailwindcss-3.4.17.exe`| v3 构建  |

所有命令请在项目根目录下运行。

### 构建

#### Tailwind CSS v4（推荐）

```bash
# 开发 — 监听模式
.\tailwindcss-4.4.3.exe -i ./config/v4/input.css -o ./admin/css/tailwind.css --config ./config/v4/tailwind.config.js -w

# 生产 — 压缩
.\tailwindcss-4.4.3.exe -i ./config/v4/input.css -o ./admin/css/tailwind.css --config ./config/v4/tailwind.config.js --minify
```

v4 采用 CSS-first 配置：内容路径与主题变量通过 `input.css` 中的 `@source` 与 `@theme` 直接声明。

#### Tailwind CSS v3（旧版）

```bash
.\tailwindcss-3.4.17.exe -i ./config/v3/input.css -o ./admin/css/tailwind.css --config ./config/v3/tailwind.config.js
```

追加 `-w` 启用监听模式，或 `--minify` 生成生产环境产物。

### Discord 主题变量

设计变量在 `discord` 命名空间下定义，并解析为 CSS 变量：

| 变量                      | 值       | 用途                         |
| ------------------------- | -------- | ---------------------------- |
| `--color-discord-light`   | `#f3f4f6`| 浅色背景                     |
| `--color-discord-sidebar` | `#202225`| 侧边栏背景                   |
| `--color-discord-active`  | `#36393f`| 激活/选中背景                |
| `--color-discord-accent`  | `#5865f2`| 强调色/主要操作              |
| `--color-discord-text`    | `#dcddde`| 正文文字                     |
| `--color-discord-muted`   | `#72767d`| 次要/弱化文字                |

相应的工具类会自动生成，例如 `bg-discord-accent`、`text-discord-muted`、`border-discord-sidebar`。

### 提示

本版本使用 TailwindCSS 4 进行样式编译。如果你不喜欢 TailwindCSS 4，或认为编译后的样式存在问题，可以自行使用版本 3 进行编译。

## 日本語

BooAdmin には Tailwind CSS の構成が 2 つ同梱されています：**v4**（推奨）と **v3**（レガシー）。いずれも `config/<version>/input.css` を `admin/css/tailwind.css` にコンパイルします。

### ディレクトリ構成

```
config/
├── v4/                     # Tailwind CSS v4（推奨）
│   ├── input.css           # エントリーファイル — CSS-first 構成 + Discord テーマ
│   └── tailwind.config.js  # 最小構成（コンテンツパスのみ）
└── v3/                     # Tailwind CSS v3（レガシー）
    ├── input.css           # エントリーファイル
    └── tailwind.config.js  # JS ベースの構成
```

### 前提条件

スタンドアロンな Tailwind CLI バイナリがリポジトリのルートに用意されているため、Node.js や `npm install` は不要です：

| バイナリ                 | 用途     |
| ----------------------- | -------- |
| `tailwindcss-4.4.3.exe` | v4 ビルド |
| `tailwindcss-3.4.17.exe`| v3 ビルド |

すべてのコマンドはプロジェクトのルートから実行してください。

### ビルド

#### Tailwind CSS v4（推奨）

```bash
# 開発 — 監視モード
.\tailwindcss-4.4.3.exe -i ./config/v4/input.css -o ./admin/css/tailwind.css --config ./config/v4/tailwind.config.js -w

# 本番 — 圧縮
.\tailwindcss-4.4.3.exe -i ./config/v4/input.css -o ./admin/css/tailwind.css --config ./config/v4/tailwind.config.js --minify
```

v4 は CSS-first 構成を採用しており、コンテンツパスとテーマトークンは `input.css` 内の `@source` と `@theme` で直接宣言します。

#### Tailwind CSS v3（レガシー）

```bash
.\tailwindcss-3.4.17.exe -i ./config/v3/input.css -o ./admin/css/tailwind.css --config ./config/v3/tailwind.config.js
```

監視モードには `-w` を、本番出力には `--minify` を付けます。

### Discord テーマトークン

デザイントークンは `discord` 名前空間で定義され、CSS 変数として解決されます：

| トークン                  | 値       | 用途                         |
| ------------------------- | -------- | ---------------------------- |
| `--color-discord-light`   | `#f3f4f6`| 明るい背景                   |
| `--color-discord-sidebar` | `#202225`| サイドバーの背景             |
| `--color-discord-active`  | `#36393f`| アクティブ / 選択中の背景     |
| `--color-discord-accent`  | `#5865f2`| アクセント / 主要な操作       |
| `--color-discord-text`    | `#dcddde`| 本文テキスト                 |
| `--color-discord-muted`   | `#72767d`| 弱調 / 補助テキスト           |

対応するユーティリティクラスは自動生成されます（例：`bg-discord-accent`、`text-discord-muted`、`border-discord-sidebar`）。

### ヒント

このバージョンではスタイルのコンパイルに TailwindCSS 4 を使用しています。TailwindCSS 4 がお好みでない場合や、コンパイルされたスタイルに問題がある場合は、ご自身でバージョン 3 を使ってコンパイルできます。

## English

BooAdmin ships with two Tailwind CSS setups: **v4** (recommended) and **v3** (legacy). Both compile `config/<version>/input.css` into `admin/css/tailwind.css`.

### Directory Layout

```
config/
├── v4/                     # Tailwind CSS v4 (recommended)
│   ├── input.css           # Entry file — CSS-first config + Discord theme
│   └── tailwind.config.js  # Minimal config (content path only)
└── v3/                     # Tailwind CSS v3 (legacy)
    ├── input.css           # Entry file
    └── tailwind.config.js  # JS-based config
```

### Prerequisites

Standalone Tailwind CLI binaries are provided at the repository root, so no Node.js or `npm install` is required:

| Binary                  | Purpose   |
| ----------------------- | --------- |
| `tailwindcss-4.4.3.exe` | v4 builds |
| `tailwindcss-3.4.17.exe`| v3 builds |

Run all commands from the project root.

### Build

#### Tailwind CSS v4 (recommended)

```bash
# Development — watch mode
.\tailwindcss-4.4.3.exe -i ./config/v4/input.css -o ./admin/css/tailwind.css --config ./config/v4/tailwind.config.js -w

# Production — minified
.\tailwindcss-4.4.3.exe -i ./config/v4/input.css -o ./admin/css/tailwind.css --config ./config/v4/tailwind.config.js --minify
```

v4 uses a CSS-first configuration: content paths and theme tokens are declared directly in `input.css` via `@source` and `@theme`.

#### Tailwind CSS v3 (legacy)

```bash
.\tailwindcss-3.4.17.exe -i ./config/v3/input.css -o ./admin/css/tailwind.css --config ./config/v3/tailwind.config.js
```

Append `-w` for watch mode or `--minify` for production output.

### Discord Theme Tokens

Design tokens are defined under the `discord` namespace and resolve to CSS variables:

| Token                     | Value    | Usage                        |
| ------------------------- | -------- | ---------------------------- |
| `--color-discord-light`   | `#f3f4f6`| Light background             |
| `--color-discord-sidebar` | `#202225`| Sidebar background           |
| `--color-discord-active`  | `#36393f`| Active / selected background |
| `--color-discord-accent`  | `#5865f2`| Accent / primary actions     |
| `--color-discord-text`    | `#dcddde`| Body text                    |
| `--color-discord-muted`   | `#72767d`| Muted / secondary text       |

Corresponding utility classes are generated automatically, e.g. `bg-discord-accent`, `text-discord-muted`, `border-discord-sidebar`.

### Tips

This version uses TailwindCSS 4 for style compilation. If you don't like TailwindCSS 4 or think there are issues with the compiled styles, you can compile the styles using version 3 yourself.
