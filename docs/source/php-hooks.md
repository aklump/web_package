# PHP Hooks

Here is an example stub for a PHP build script:

    <?php
    
    /**
     * @file
     * An example PHP hook file.
     */
    
    namespace AKlump\WebPackage;
    
    if ($little_problem) {
      throw new HookException("Hook failed, but the build must go on.");
    }
    elseif ($big_problem) {
      throw new BuildFailException("Stop the build!");
    }
    
    echo "The hook worked as expected";
    
    
## Quick Start

* Files should be namespaced to `AKlump\WebPackage`.
* If you want to stop the build, throw an instance of _\AKlump\WebPackage\BuildFailException_.
* To indicate a hook failure, but continue the build, throw an instance of _\AKlump\WebPackage\HookException_.
* Use `echo` or `print` to write to the screen.  Output may be split on `\n` and itemized.
* The file _.web_package/hooks/bootstrap.php_, if it exists, will be included automatically.  You may use it for shared functions across scripts, for example.
* Web Package provides some PHP functions which are autoloaded as well; review the file _includes/wp_functions.php_ for those functions.
