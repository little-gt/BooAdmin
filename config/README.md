# Tailwind CSS 构建配置 / Build Configuration / ビルド構成

> ⚠️ **唯一权威构建链是 `config/v3/`**。`config/v4/` 仅为兼容声明，不可用于生成生产产物。
> 详见下方「构建链说明」。

## 构建链说明（重要）

BooAdmin 的 Tailwind 产物**不含有任何硬编码字面色值**。`config/v3/tailwind.config.js`
把整个调色板映射到 `--booadmin-*` 语义变量，而变量值只由
`admin/css/light.css` 与 `admin/css/dark.css` 决定。

由此带来的约束，修改样式前务必了解：

| 约束 | 说明 |
| ---- | ---- |
| 不要重新引入字面色类覆盖 | 新增颜色需求请修改 `tailwind.config.js` 的语义映射，或在组件层用 `var(--booadmin-*)`；不要在 `dark.css` 里追加 `.bg-xxx { ... !important }` 补丁 |
| 颜色类自动跟随明暗 | 包括 `hover:`/`focus:`/`group-hover:`/`divide-`/`ring-`/`from-`/`to-` 及 `/NN` 透明度变体，无需额外维护 |
| `bg-` 与 `text-` 分别映射 | 暗色下 `bg-white` 变深、`text-white` 保持浅色，靠 `backgroundColor` / `textColor` 分离的调色板实现 |
| v4 不可用于生产 | v4 的 `@theme` 按颜色名建模，`--color-white` 同时驱动 `bg-white` 与 `text-white`，无法做上述分离 |

### 两个产物文件

| 产物 | 内容 | 加载位置 |
| ---- | ---- | -------- |
| `admin/css/tailwind-base.css` | preflight / reset | 在 `normalize.css` 之后、组件层之前 |
| `admin/css/tailwind.css` | components + utilities | 在所有组件样式之后（最后） |

**必须分开加载**：preflight 若放在组件层之后，会覆盖 `style.css` / `light.css` / `dark.css`
中 `body`、`a`、`h1`-`h6` 等基础元素规则，历史上正是靠大量 `!important` 补丁补救。
拆分后层叠顺序确定：`reset → components → utilities`，不再依赖补丁。

同名类冲突另由 `style.css` 中的 `:where(.w-N)` 处理（特异性降为 0，utilities 稳定胜出）。

## 中文

### 目录结构

```
config/
├── v3/                       # ✅ 权威构建链（Tailwind CSS v3）
│   ├── tailwind.config.js    # 语义调色板（所有颜色映射到 --booadmin-*）
│   ├── input-base.css        # 入口：@tailwind base → tailwind-base.css
│   └── input.css             # 入口：components + utilities → tailwind.css
└── v4/                       # ⚠️ 兼容声明，非生产入口
    ├── input.css             # @theme 变量转发，不产生硬编码色值
    └── tailwind.config.js    # 兼容占位
```

### 环境要求

仓库根目录已提供独立的 Tailwind CLI 二进制文件，无需 Node.js 或 `npm install`：

| 二进制文件                        | 用途                     |
| ------------------------------- | ------------------------ |
| `tailwindcss-3.4.17.exe`        | ✅ 权威构建（实际 v3.4.19）|
| `tailwindcss-4.4.3.exe`         | ⚠️ 兼容（实际 v4.3.3）    |

> 二进制文件名与内部版本号不完全一致，以 `*.exe --help` 输出为准。

所有命令请在项目根目录下运行。

### 构建（必须同时生成两个文件）

```bash
# preflight → admin/css/tailwind-base.css
.\tailwindcss-3.4.17.exe -i ./config/v3/input-base.css -o ./admin/css/tailwind-base.css --config ./config/v3/tailwind.config.js --minify

# utilities → admin/css/tailwind.css
.\tailwindcss-3.4.17.exe -i ./config/v3/input.css -o ./admin/css/tailwind.css --config ./config/v3/tailwind.config.js --minify
```

开发时可加 `-w` 启用监听（两个命令各自一个终端）。**不要**把 `@tailwind base`
重新加回 `input.css`，也不要只构建其中一个文件。

### 验证构建正确性

```bash
# 产物中不应出现硬编码主题色（允许少量 Tailwind 内部渐变/阴影）
Select-String -Path ./admin/css/tailwind.css -Pattern ':\s*#[0-9a-fA-F]{3,8}'
```

### 修改主题颜色

- 亮色值 → `admin/css/light.css` 的 `:root`
- 暗色值 → `admin/css/dark.css` 的 `@media (prefers-color-scheme: dark) :root`
- 新增颜色类映射 → `config/v3/tailwind.config.js`

## English

> ⚠️ **The single authoritative build chain is `config/v3/`.** `config/v4/` is a
> compatibility declaration only and must not be used to produce shipping assets.

BooAdmin's Tailwind output contains **no hardcoded literal theme colors**.
`config/v3/tailwind.config.js` maps the entire palette onto `--booadmin-*`
semantic variables, whose values live solely in `light.css` / `dark.css`.
Consequently every color utility — including `hover:`, `group-hover:`, `divide-`,
`ring-`, gradient stops and `/NN` alpha variants — follows the light/dark theme
automatically. Never add `.bg-xxx { ... !important }` patches to `dark.css`.

Two outputs must be built and loaded separately:

| Output | Contents | Load order |
| ------ | -------- | ---------- |
| `admin/css/tailwind-base.css` | preflight / reset | after `normalize.css`, before component styles |
| `admin/css/tailwind.css` | components + utilities | last |

Loading preflight after the component layer would override base element rules
(`body`, `a`, `h1`-`h6`) and force the `!important` patching this project used
to rely on. Splitting fixes the cascade as `reset → components → utilities`.

v4 cannot be used because its `@theme` models colors by name: `--color-white`
drives both `bg-white` and `text-white`, whereas dark mode requires them to
resolve differently.

Standalone CLI binaries are provided at the repository root (no Node.js needed):

```bash
.\tailwindcss-3.4.17.exe -i ./config/v3/input-base.css -o ./admin/css/tailwind-base.css --config ./config/v3/tailwind.config.js --minify
.\tailwindcss-3.4.17.exe -i ./config/v3/input.css -o ./admin/css/tailwind.css --config ./config/v3/tailwind.config.js --minify
```

Do not re-add `@tailwind base` to `input.css`, and always build both files.

## 日本語

> ⚠️ **本番用の唯一のビルドチェーンは `config/v3/` です。** `config/v4/` は互換宣言のみで、
> 本番アセットの生成には使用しないでください。

Tailwind の出力には**ハードコードされたテーマ色を一切含めません**。
`config/v3/tailwind.config.js` がパレット全体を `--booadmin-*` 意味変数へ
割り当て、値は `light.css` / `dark.css` だけで定義されます。そのため
`hover:` / `group-hover:` / `divide-` / `ring-` / グラデーション / `/NN`
透過度バリアントを含む全ての色ユーティリティが自動的に明暗に追従します。
`dark.css` に `.bg-xxx { ... !important }` のパッチを追加しないでください。

2 つの成果物を個別にビルド・読み込みします：

| 成果物 | 内容 | 読み込み順 |
| ------ | ---- | ---------- |
| `admin/css/tailwind-base.css` | preflight / reset | `normalize.css` の後、コンポーネント層の前 |
| `admin/css/tailwind.css` | components + utilities | 最後 |

preflight をコンポーネント層の後に置くと `body` / `a` / `h1`-`h6` などの
基本要素ルールを上書きし、かつてのように多数の `!important` パッチが必要になります。
分割により `reset → components → utilities` の順序が確定します。

v4 は `@theme` が色名単位で設計されており `--color-white` が
`bg-white` と `text-white` の両方を駆動するため、暗色モードで必要な
分離ができず使用できません。

```bash
.\tailwindcss-3.4.17.exe -i ./config/v3/input-base.css -o ./admin/css/tailwind-base.css --config ./config/v3/tailwind.config.js --minify
.\tailwindcss-3.4.17.exe -i ./config/v3/input.css -o ./admin/css/tailwind.css --config ./config/v3/tailwind.config.js --minify
```

`@tailwind base` を `input.css` に戻さないでください。両ファイルを必ずビルドしてください。
