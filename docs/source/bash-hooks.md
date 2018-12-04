# BASH Hooks

Here is an example stub for BASH build script:

    #!/usr/bin/env bash
    
    if little_problem; then
      hook_exception "Hook failed, but the build must go on."
    elif big_problem; then
      build_fail_exception "Stop the build!"
    fi
    
    echo "The hook worked as expected"

## Quick Start

* If you want to stop the build, use `hook_exception`.
* To indicate a hook failure, but continue the build, use `build_fail_exception`.
* Use `echo` to write to the screen.
* The file _.web_package/hooks/bootstrap.sh_, if it exists, will be included automatically.  You may use it for shared functions and configuration across scripts, for example.
* Web Package provides some BASH functions which are autoloaded as well; review the file _includes/wp_functions.sh_ for those functions.
