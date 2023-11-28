<!--
id: hooks
tags: ''
-->

# Hooks

* Located at _.web_package/hooks/build_, _.web_package/hooks/unbuild_, and _.web_package/hooks/dev_
* Are either _*.sh_ or _*.php_ files.
* The current directory is always the initialized directory, that is to say, the parent of _.web_package_.

## Bash Hooks Only

* For all output use `echo`.
* A failure is indicated by any non-zero exit code less than 255. Failures prevent the current operation from continuing.
* Exit with 255 to indicate the hook was skipped, for example it could not be run in a given context.
* For best results do not duplicate exit codes in the same file. Do something like you see here if you have multiple fail points. The exception handler will be more accurate at pointing to the issue.

```bash
#!/usr/bin/env bash

[ ! -f "foo.txt" ] && exit 1
[ ! -f "bar.txt" ] && exit 2
```

## PHP Hooks Only

* For non-error output, simply use `echo`.
* A failure is indicated by throwing any exception.
* Set the exception code to 255 to indicate the hook was skipped, for example it could not be run in a given context.
* PHP hooks may share values with one another using the `$sandbox` array.
* The namespace `AKlump\WebPackage\User` is automatically mapped to _.web_package/src_ for PSR-4 classes.
* If _.web_package/vendor/autoload.php_ exists, it will be loaded automatically.
