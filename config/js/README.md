# JavaScript 来源清单 / Vendor Manifest

## 目录职责

| 路径 | 角色 | 是否可编辑 |
| ---- | ---- | ---------- |
| `config/js/*.js` | **可读源文件**（唯一权威来源，含许可证头与版本信息） | ✅ 只改这里 |
| `admin/js/*.js` | 由源文件压缩生成的**运行时产物**，单行 | ❌ 不直接编辑 |

`admin/` 是 Typecho 原生后台目录的**整体替换**，因此这些库必须随主题自带。
但主题只应维护源文件，压缩产物一律由构建生成，避免「只有一堆单行文件、无法审计 /
无法升级」的状态。

## 来源与版本

版本取自各源文件头部许可证注释（可用 `Select-String -Pattern 'v?\d+\.\d+'` 复核）。

| 源文件 | 产物 | 库 / 版本 | 上游 | 备注 |
| ------ | ---- | --------- | ---- | ---- |
| `jquery.js` | `admin/js/jquery.js` | jQuery JavaScript Library **v2.1.1-rc2** | jquery.com | Typecho 1.x 后台依赖版本 |
| `jquery-ui.js` | `admin/js/jquery-ui.js` | jQuery UI **v1.10.3** (2013-10-16) | jqueryui.com | 仅含 core/widget/mouse/datepicker/slider/effect/effect-highlight |
| `typecho.js` | `admin/js/typecho.js` | **Typecho 核心后台脚本** | Typecho 核心 `admin/js/typecho.js` | ⚠️ 见下方「核心脚本同步」 |
| `purify.js` | `admin/js/purify.js` | DOMPurify **2.2.2** | cure53/DOMPurify | 评论/编辑器内容净化 |
| `pagedown.js` | `admin/js/pagedown.js` | PageDown Markdown Editor | Stack Exchange | 源文件未标注版本号 |
| `hyperdown.js` | `admin/js/hyperdown.js` | HyperDown (CoffeeScript **2.5.1** 生成) | SegmentFault HyperDown | Markdown 解析器 |
| `tokeninput.js` | `admin/js/tokeninput.js` | jQuery Tokeninput **1.6.0** | loopj/jquery-tokeninput | 标签输入 |
| `timepicker.js` | `admin/js/timepicker.js` | jQuery Timepicker Addon **v1.4** (2013-08-11) | trentrichardson.com | 日期时间选择 |

## 核心脚本同步（重要）

`typecho.js` 提供后台运行所需的核心 API：

- `$.fn.dropdownMenu`、`$.fn.tableSelectable`、`$.fn.resizeable`
- `jQuery.cookie`
- `window.Typecho`、`scrollableEditor`

**替换式架构的固有风险**：升级 Typecho 核心后，核心自带的 `admin/js/typecho.js` 会变化，
而主题自带的副本仍是旧版，两者可能错配。

规避约定：

1. **主题不再覆写核心 API**。BooAdmin 的下拉菜单增强已改为独立命名空间
   `$.fn.booadminDropdown`（定义于 `admin/common-js.php`），核心 `$.fn.dropdownMenu`
   保持原样，第三方插件调用它时拿到的是核心行为。
2. 页面用到的核心 API 在 `admin/common-js.php` 中做**能力自检**（见下），
   缺失时给出明确诊断，而不是让页面静默失效。
3. 升级 Typecho 后，把核心 `admin/js/typecho.js` 的**未压缩源**复制到 `config/js/`
   比对差异，再重新生成产物；不要直接覆盖 `admin/js/typecho.js`。

## 核心 API 能力自检

`common-js.php` 在加载完全部库后检查以下符号，任一缺失即在控制台输出
`[BooAdmin] 核心脚本版本不匹配` 并列出缺失项：

```text
$.fn.tableSelectable   $.fn.dropdownMenu   $.fn.resizeable
$.cookie               $.fn.booadminDropdown（主题自身）
window.Typecho         window.DOMPurify（按需）
```

这样「核心升级导致错配」会从难排查的页面怪行为，变成一条可定位的日志。

## 构建 / 压缩

产物为普通单行压缩 JS，保留许可证头即可。任选一种压缩器（示例用 `terser`）：

```bash
npx terser config/js/typecho.js -c -m \
  --comments '/^!/' \
  -o admin/js/typecho.js
```

批量处理（PowerShell）：

```powershell
Get-ChildItem config/js/*.js | ForEach-Object {
  npx terser $_.FullName -c -m --comments '/^!/' -o "admin/js/$($_.Name)"
}
```

约束：

- 必须保留 `/*! ... */` 许可证注释（`--comments '/^!/'`）。
- 生成后用 `node --check admin/js/<file>.js` 逐个校验语法。
- 不要在 `admin/js/` 里手写任何逻辑；主题自有代码放在
  `admin/js/booadmin.js`、`admin/js/neo-theme.js` 或各 `*-js.php` 片段中。
- 每次重新生成后，同步更新本文的版本表与 `BOOADMIN_VERSION`（`admin/common.php`），
  以保证 `?v=` 缓存破坏生效。
