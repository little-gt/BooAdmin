## Install

1. Download the latest version and uncompress it.

2. Upload all `*.mo` files to `<Your Typecho Directory>/usr/langs` . If that directory doesn't exist, you can create one before uploading.

3. Go to `admin > options > genral options`, there will be a language selector shows below.

## How-to

1. [Fork](https://github.com/typecho/languages/fork) this repo and clone to local.
2. Start your translation road by using [PoEdit](http://poedit.net/) or other gettext software. The `message.pot` file contains all recent translation strings, but it is in Chinese, so you might want to start with the English translation file `en_US.po` and update the missing messages from `messages.pot`.
3. Push update to your forked repo, and then pull a request to offical languages repo: named `typecho/languages`.

Also please see http://docs.typecho.org/translate/start