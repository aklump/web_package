<!--
id: hooks
tags: ''
-->

# Hooks

* Located at _.web_package/hooks/build_, _.web_package/hooks/unbuild_, and _.web_package/hooks/dev_
* Are either _*.sh_ or _*.php_ files.
* The current directory is always the initialized directory, that is to say, the parent of _.web_package_.
* A failure is indicated by any non-zero exit code less than 255. Failures prevent the current operation from continuing.
* Exit with 255 to indicate the hook was skipped, for example it could not be run in a given context.   
