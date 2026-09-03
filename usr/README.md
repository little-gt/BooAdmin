# BooAdmin 语言包说明 / Language Pack / 言語パッケージ

## 中文

### 安装

1. 下载最新版本并解压。
2. 将所有 `*.mo` 文件上传到 `<你的 Typecho 目录>/usr/langs`。若该目录不存在，可在上传前自行创建。
3. 进入 `管理后台 > 设置 > 基本设置`，下方将出现语言选择框。

### 如何参与翻译

1. [Fork](https://github.com/typecho/languages/fork) 本仓库并克隆到本地。
2. 使用 [PoEdit](http://poedit.net/) 或其他 gettext 工具开始翻译。`message.pot` 文件包含所有最新的翻译字符串，但它是中文的，因此建议从英文翻译文件 `en_US.po` 入手，并从 `messages.pot` 中补全缺失的内容。
3. 将更新推送到你 Fork 的仓库，然后向官方语言仓库 `typecho/languages` 提交 Pull Request。

另请参阅 http://docs.typecho.org/translate/start

## 日本語

### インストール

1. 最新バージョンをダウンロードし、展開してください。
2. すべての `*.mo` ファイルを `<Typecho のディレクトリ>/usr/langs` にアップロードしてください。そのディレクトリが存在しない場合は、アップロード前に作成できます。
3. `管理画面 > 設定 > 一般設定` に移動すると、下部に言語選択肢が表示されます。

### 翻訳の方法

1. このリポジトリを [フォーク](https://github.com/typecho/languages/fork) し、ローカルにクローンしてください。
2. PoEdit またはその他の gettext ソフトウェアを使って翻訳を始めてください。`message.pot` ファイルには最新の翻訳文字列がすべて含まれていますが、中国語で書かれています。そのため、英語翻訳ファイル `en_US.po` から始め、`messages.pot` から不足しているメッセージを更新するとよいでしょう。
3. フォークしたリポジトリに更新をプッシュし、公式言語リポジトリ `typecho/languages` へプルリクエストを送ってください。

詳細は http://docs.typecho.org/translate/start もご参照ください。

## English

### Install

1. Download the latest version and uncompress it.
2. Upload all `*.mo` files to `<Your Typecho Directory>/usr/langs` . If that directory doesn't exist, you can create one before uploading.
3. Go to `admin > options > general options`, there will be a language selector shows below.

### How-to

1. [Fork](https://github.com/typecho/languages/fork) this repo and clone to local.
2. Start your translation road by using [PoEdit](http://poedit.net/) or other gettext software. The `message.pot` file contains all recent translation strings, but it is in Chinese, so you might want to start with the English translation file `en_US.po` and update the missing messages from `messages.pot`.
3. Push update to your forked repo, and then pull a request to offical languages repo: named `typecho/languages`.

Also please see http://docs.typecho.org/translate/start
