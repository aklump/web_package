<!--
id: readme
tags: ''
-->

# Web Package

![Web Package](../../images/web_package.jpg)

This project does the following for your web project.

* Manages version numbers.
* Provides a framework for building assets before release.

{{ composer.install }}

## Configuration

- [ ] Create a global alias for `bump` pinned to the PHP version you installed composer with, e.g.:

```
alias bump='export WEB_PACKAGE_PHP=/usr/local/opt/php@8.1/bin/php; $HOME/Code/Packages/cli/web-package/web-package'
```

## New Release Workflow

```bash
bump release
bump build
git add .
git commit -m 'Build assets.'
bump done
```
