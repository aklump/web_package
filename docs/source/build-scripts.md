# Hooks: build, unbuild, dev

The following commands are there to run scripts located in _.web_package/hooks_.

## `bump build`
Scripts added to `.web_package/hooks/build` are run.  By design, these scripts should serve the purpose of building the package and making it ready for a release.  Such as create a `dist` directory and copy over distribution files, etc.  **Of the three, this is the only command that is automatically called by calling `bump done`.

## `bump unbuild`
Scripts added to `.web_package/hooks/unbuild` are run.  You may delete files as necessary to reverse the build step.

## `bump dev`
Scripts added to `.web_package/hooks/dev` are run.  The intention here is to set up the project to be developed.  I use this to symlink dependencies during development.  These symlinks get reversed in my build scripts.

## Build Scripts

* Any file ending in `.php` or `.sh` found in the build script folder will be called during version bumping.
* Any hook file can be temporarily disabled by renaming the file to begin with a single underscore
* All hooks will be skipped if you pass `--no-hooks` in the command.
* All hooks will be fired if you pass `--hooks` in the command.
* There are lots of example build scripts that get installed when you to `bump init`.  They can be found in _.web_package/examples_.

You may add PHP or BASH scripts to `.web_package/hooks/build` and they will be run each time the version increments.  You may also trigger a build by calling `bump build`.

You can also trigger a single file by passing it's filename; you would want to do this if you need to trigger this one script from an external program, while preserving the callback arguments.  Here is an example (this also works for unbuild and dev):

        bump build only_this_script.sh

See also [BASH Scripts](bash-scripts.html)
See also [PHP Scripts](php-scripts.html)



