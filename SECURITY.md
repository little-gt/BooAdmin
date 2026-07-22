# 安全策略

> BooAdmin 是一个完全开源、未压缩加密的 Typecho 后台主题，代码透明可审计。如发现安全问题，您可以根据本安全策略的说明进行处理，并且通知 BooAdmin 的开发者。

![BooAdmin](https://img.shields.io/badge/BooAdmin-v1.3.0-blue?style=for-the-badge)
![License](https://img.shields.io/badge/License-GPLv3-blue?style=for-the-badge)

---

## 📌 支持版本

| 版本 | 状态 | 说明 |
| :--- | :---: | :--- |
| **v1.3.0 系列** | ✅ 支持 | 当前主分支，持续接收安全更新 |
| **v1.2.x 系列** | ❌ EOL | 已抵达生命周期，停止维护，请升级至 v1.3.0 及更高 |
| **v1.1.x 系列** | ❌ EOL | 已抵达生命周期，停止维护，请升级至 v1.3.0 及更高 |
| **v1.0.x 系列** | ❌ EOL | 已抵达生命周期，停止维护，请升级至 v1.3.0 及更高 |

---

## 🔗 资源说明

BooAdmin 通过 [GARFIELDTOM'S NEST CDN](https://cdn.garfieldtom.cool) 加速分发外部静态资源，包括 FontAwesome、ECharts.js 及 TailwindCSS 等。为了确保用户始终获得良好的体验，CDN 会定期更新与整理缓存资源。因此，当 BooAdmin 发布新版本时，早期版本所引用的资源可能会被替换，建议您及时更新以获得最佳的显示效果。若您发现您的 BooAdmin 显示异常，请尝试更新主题版本。

如您希望自行托管这些资源以便更好地控制更新节奏，我们建议您确保资源版本与主题要求保持一致。同时，也建议您定期关注主题更新，以便及时获取最新的质量改进、功能优化与安全修复。

---

## 📮 报告漏洞

> **请勿在 GitHub Issues 或公开场合披露漏洞**，请通过邮件披露，避免漏洞在被及时修复前被滥用，BooAdmin 感谢您的理解和支持。

**联系邮箱**：📬 [coolerxde@gt.ac.cn](mailto:coolerxde@gt.ac.cn)

**邮件标题格式**：`[Security] BooAdmin - 漏洞简述 - 版本 1.3.x`

**建议包含**：
- 影响版本与漏洞类型（XSS/CSRF/SQL注入等）
- 危害评估与复现步骤
- PoC 代码与环境信息（PHP/Typecho版本）

---

## 🎯 范围流程

| ✅ 属于本项目 | ❌ 不属于本项目 |
| :--- | :--- |
| `admin/` 目录下主题 PHP 文件漏洞 | Typecho 核心漏洞 |
| 主题自带 JS/CSS 前端安全问题      | 第三方插件隐患   |
| 主题引发的 XSS/CSRF 漏洞         | 服务器配置不当    |
| 主题接口权限控制缺陷              | 弱口令攻击       |

**当收到处理范围内的报告时：**

1. **确认接收** - 48小时内回复
2. **评估修复** - 开发补丁期间请保持静默
3. **发布更新** - 在 v1.3.x 分支发布修复版本
4. **公开致谢** - 可选择是否署名

---

## ⚖️ 免责声明

本项目按 GNU GENERAL PUBLIC LICENSE V3 开源协议发布，使用者需自行承担风险，项目维护者不对任何损失承担法律责任。