# Configuration
The configuration file is created during `bump init` and is located at `.web_package/config`.  Default contents look like this:

    master = "master"
    develop = "develop"
    git_remote = origin
    create_tags = yes
    push_tags = ask
    push_master = ask
    push_develop = ask
    
The entire `.web_package` directory should not be included in source control.  **To modify the configurations simply edit `.web_package/config` directly.**  Make sure to have spaces surrounding your equal signs as `create_tags=yes` is not the same as `create_tags = yes`.  **Also you must have a line break at the end of the file!**

## master: `(string)`
The name of the branch you consider master.  If you have more than one branch you consider a master then list them all, separated by spaces, e.g. `"master1 master2"` For more insight into this feature, see the [Drupal Modules/Themes section](#drupal) below.

## develop: `(string)`
The name of the branch you consider develop.  If you have more than one branch you consider a develop branch then list them all, separated by spaces, e.g. `"develop1 develop2"`.  **In the case of multiples: Make sure that you list them in the exact order as the master list, as the correlation of master to develop is imperative.**

## remote: `(string)`
The name of the git remote to be used with `git push [git_remote] release-1.0`

## create_tags: `major`, `minor`, `patch ` or `no`
When executing `bump done`, determine what severity level will create a git tag.  Set to `no` to never create a tag.

## push_tags: `no`, `ask` or `auto`
If tags should be pushed to `git_remote`.  Set to `auto` and you will not be prompted first.

## push_master: `no`, `ask` or `auto`
If master branches should be pushed to `git_remote`.  Set to `auto` and you will not be prompted first.

## push_develop: `no`, `ask` or `auto`
If develop branches should be pushed to `git_remote`.  Set to `auto` and you will not be prompted first.

## info_file: `(string)`
(Optional) If you have more than one .info file and you need to force Web Package to use the correct one then add this option. The name of the file containing the version info.

## author: `"(string)"`
(Optional) This one needs to be wrapped in quotes if you have spaces like this example… "Aaron Klump \<sourcecode@intheloftstudios.com>".  This will get written to `web_package.info` file during the init process.

## init_version: `"(string)"`
(Optional)  This is used during `bump init` to set the default version of a package.

## patch_prefix: `"(string)"`
(Optional)  This is used as the default patch_prefix.

## php: `"(string)"`
(Optional)  The path to the php to use for PHP build scripts.

## bash: `"(string)"`
(Optional)  The path to bash to use for shell build scripts.

## pause: `"(int)"`
(Optional)  Enter a number of seconds to pause before git creates a branch and adds files.  This is here in case you need to allow time for file processing after the version file has been updated. If you want to be prompted before git does it's thing enter a -1 here.


## Global Configuration
A global configuration file may be created at `~/.web_package/config`, the contents of which will be used as defaults for new projects or existing projects without said parameter.  This is most useful for the `author` and `info_file` parameters.  **Note: if a global config parameter is set, but the project does not override it, the global will apply for that project, even after `bump init`.
