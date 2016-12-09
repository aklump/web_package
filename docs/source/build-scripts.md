# Timesaving hooks: build, unbuild, dev
There are three commands that are important to know about.  On their own, that is without special implementation, they do nothing.  However they can be configured to help your process out and save time and reduce errors

## `bump build`
Scripts added to `.web_package/hooks/build` are run.  By design, these scripts should serve the purpose of building the package and making it ready for a release.  Such as create a `dist` directory and copy over distribution files, etc.  **Of the three, this is the only command that is automatically called by calling `bump done`.

## `bump unbuild`
Scripts added to `.web_package/hooks/unbuild` are run.  You may delete files as necessary to reverse the build step.  May not be that useful for most.

## `bump dev`
Scripts added to `.web_package/hooks/dev` are run.  The intention here is to set up the project to be developed.  I use this to symlink dependencies during development.  These symlinks get reversed in my build scripts.

## Build Scripts
You may add php or shell scripts to `.web_package/hooks/build` and they will be run each time the version increments.  You may also trigger a build by calling `bump build`.

You can also trigger a single file by passing it's filename; you would want to do this if you need to trigger this one script from an external program, while preserving the callback arguments.  Here is an example (this also works for unbuild and dev):

        bump build only_this_script.sh

Any file ending in `.php` or `.sh` found in the build script folder will be called during version bumping.  Refer to the parameter chart below for script arguments:

One example of using a build script, say with a jQuery plugin, is when you want to embed the version string _inside_ the `jquery.pluging_name.js` file.  In such a case the  `.info` file strategy doesn't meet your needs.  For such a scenario you would use a build script.

## Callback Arguments
| data                     | build.php   | build.sh   |
|--------------------------|-------------|------------|
| prev version             | $argv[1]    | $1         |
| new version              | $argv[2]    | $2         |
| package name             | $argv[3]    | $3         |
| description              | $argv[4]    | $4         |
| homepage                 | $argv[5]    | $5         |
| author                   | $argv[6]    | $6         |
| path to root             | $argv[7]    | $7         |
| date/time                | $argv[8]    | $8         |
| path to info file        | $argv[9]    | $9         |
| dir of the script        | $argv[10]   | ${10}      |
| dir of functions.php/.sh | $argv[11]   | ${11}      |
| root dir of web_package  | $argv[12]   | ${12}      |
| path to hooks dir        | $argv[13]   | ${13}      |

* Your script should print/echo details as to what it did; this will be output to the console.
* Be sure to use _path to root_ in your scripts when you reference any files in your project.
* If you need to source another script, e.g. `config` that sits in your scripts directory, use `dir of the script`, here's a shell example...  Notice that without the `.sh` extension, the file will not automatically get called, which is a good way to do a shared config script.

        #!/bin/bash
        source "${10}/config"
        source "${11}/functions.sh"
        ...
