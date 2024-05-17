<!--
id: hooks
tags: ''
-->

# Hooks

## Highlights

* Hooks are namely BASH or PHP files saved to:
    * _.web\_package/hooks/build/_
    * _.web\_package/hooks/unbuild/_
    * _.web\_package/hooks/dev/_
* The current directory is always the initialized directory, that is to say, the parent of _.web\_package_.

## BASH Hooks

> For BASH hooks that fail exit with N < 255. To indicate the hook was skipped exit with 255.

* For **all output** use `echo`.
* A failure is indicated by any non-zero exit code less than 255. Failures prevent the current operation from continuing.
* Exit with 255 to indicate the hook was skipped, for example it could not be run in a given context.
* For best results do not duplicate exit codes in the same file. Do something like you see here if you have multiple fail points. The exception handler will be more accurate at pointing to the issue.

```bash
#!/usr/bin/env bash

[ ! -f "foo.txt" ] && exit 1
[ ! -f "bar.txt" ] && exit 2
```

## PHP Hooks

> For PHP hooks that fail, throw exceptions, with an exception code N < 255. To indicate the hook was skipped throw any exception with a code of 255.

* For **non-error output** use `echo`.
* A failure is indicated by throwing any exception.
* Set the exception code to 255 to indicate the hook was skipped, for example it could not be run in a given context.
* PHP hooks may share values with one another using the `$sandbox` array.
* The namespace `AKlump\WebPackage\User` is automatically mapped to _.web\_package/src_ for PSR-4 classes.
* If _.web\_package/vendor/autoload.php_ exists, it will be loaded automatically.
