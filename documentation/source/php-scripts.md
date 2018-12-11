# PHP Hooks

Here is an example stub for PHP build script:

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

* The working directory is the directory containing _.web_package_.
* Files should be namespaced to `AKlump\WebPackage`.
* If you want to stop the build, throw an instance of _\AKlump\WebPackage\BuildFailException_.
* To indicate a hook failure, but continue the build, throw an instance of _\AKlump\WebPackage\HookException_.
* Use `echo` or `print` to write to the screen.
* Use `caution` and `danger` to write dramatic output while continuing the flow.
* The file _.web_package/hooks/bootstrap.php_, if it exists, will be included automatically.    You may use it for shared functions and configuration across scripts, for example.
* Web Package provides some PHP functions which are autoloaded as well; review the file _includes/wp_functions.php_ for those functions.

## Using the `$build` object

You may want to leverage the `$build` instance in your PHP hook files as it contains a growing amount of common use case functionality.  Refer to _\AKlump\WebPackage\HookService_ for more info.

* You should always call `displayMessages()` as the last chained method (see example below). 
* Relative paths are considered relative to the directory that contains _.web_package_.

Add configuration to `$build` inside of _bootstrap.php_, which will apply to every PHP hook file thereafter, e.g.,

    <?php
    
    /**
     * @file
     * Loaded before running PHP hooks.
     */
     
    $build->setDistributionDir('dist');

Then inside your hook file do something with it, e.g.,

    <?php
    
    /**
     * @file
     * Load a source file, replace tokens and save to dist folder.
     */
    
    namespace AKlump\WebPackage;
    
    $build
      ->loadFile('src/smart-images.js')
      ->replaceTokens()
      ->saveToDist()
      ->displayMessages();

## Callback Arguments

These are deprecated, you should only use them as a last resort.

|           data           | build.php |
|:-------------------------|:----------|
| prev version             | $argv[1]  |
| new version              | $argv[2]  |
| package name             | $argv[3]  |
| description              | $argv[4]  |
| homepage                 | $argv[5]  |
| author                   | $argv[6]  |
| path to root             | $argv[7]  |
| date/time                | $argv[8]  |
| path to info file        | $argv[9]  |
| dir of the script        | $argv[10] |
| dir of functions.php/.sh | $argv[11] |
| root dir of web_package  | $argv[12] |
| path to hooks dir        | $argv[13] |
