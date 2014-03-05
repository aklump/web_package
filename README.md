##Summary

A shell script to help the management of files as a package through easy version number manipulation. Supports name, description, author and version number. Integrates with .git and uses gitflow methodology to automatically merge and tag.

##Why Use Web Package?
If you're used to typing this:
    
    $ git add .
    $ git cim 'some changes'
    $ git co -b release-1.1
    $ cat version.info
    version = 1.0
    $ echo version.info > "version = 1.1"
    $ git add version.info
    $ git cim 'Bumped version to 1.1'
    $ git co develop
    $ git merge --no-ff release-1.1
    $ git co master
    $ git merge --no-ff release-1.1
    $ git br -d release-1.1
    $ git co develop


How would you rather type this, instead from now on...
    
    $ git add .
    $ git cim 'some changes'
    $ bump release
    $ bump done

If so, Web Package is for you! Read on...
    
##Installation
1. Create a symlink to `web_package.sh`, I suggest `bump` and add it to `~/bin` you can then call this script by typing `bump` on the command line as in the examples below.
1. Make sure that `~/bin` is in your PATH variable
2. Consider adding `.web_package` to your global `.git_ignore` file. **Make sure you are using the leading '.'; you do not want to add `web_package` to the ignores.**to 
1. Below you will see three basic examples for usage:
1. For more info see: <http://nvie.com/posts/a-successful-git-branching-model>
1. For a list of commands, type `bump`.


##About Version Numbers
1. Two versioning schemas may be used `(prefix-)major.minor.patch` and `(prefix-)major.minor(patch_prefix)patch`.
2. There is no limit the to value of each part; as an example something like this is theoretically possible `999.999.999`.  Important to note: the next minor version after `1.9` is not `2.0`, but rather `1.10`.
3. `(prefix-)` is a string of any chars ending in a hyphen, e.g. `7.x-`.
4. `(patch_prefix)` is a string of one or more non numbers, e.g. `-alpha`.
3. Read more about version numbers here <http://semver.org/> && <http://en.wikipedia.org/wiki/Software_versioning>
4. Read more about Drupal version numbers here <http://drupal.org/node/1015226>
5. To see many version examples type `bump test`.

### Valid Examples
* `1.0`
* `1.0.1`
* `7.x-1.0`
* `7.x-1.0.1`
* `1.0-rc1`
* `1.0-alpha1`
* `7.x-1.0-rc1`
    
### Invalid Examples
* `1` (missing minor digit, use `1.0` instead.)
* `1-rc1` (missing minor digit, use `1.0-rc1` instead.)
* `1.0-alpha` (missing patch digit, use `1.0-alpha1` instead.)
* `1.0-dev` (missing patch digit, don't use `-dev` or add a patch digit.)

## About Version Incrementing
### Example 1:

    patch: 0.0.1 --> 0.0.2
    minor: 0.0.1 --> 0.1
    major: 0.0.1 --> 1.0

### Example 2:
The key difference to notice is that when you `bump minor` in this schema, it simply drops the patch prefix and the patch values and does _not_ increment the minor value.  Also if you `bump major` it will carry over the patch_prefix for you automatically and set the patch value to 1.

    patch: 8.x-2.0-alpha6 --> 8.x-2.0-alpha7
    minor: 8.x-2.0-alpha6 --> 8.x-2.0
    major: 8.x-2.0-alpha6 --> 8.x-3.0-alpha1


###Testing A Version Schema
To test this script against your version schema, you may call `bump test`.  The arguments the follow are: the version string to test, the expected outcome of a bump for: patch, minor, major, alpha, beta, rc.  Here is an example:

    $ bump test 1.0 1.0.1 1.1 2.0 1.1-alpha1 1.1-beta1 1.1-rc1
    patch: 1.0 --> 1.0.1  [OK]
    minor: 1.0 --> 1.1  [OK]
    major: 1.0 --> 2.0  [OK]
    alpha: 1.0 --> 1.1-alpha1  [OK]
    beta: 1.0 --> 1.1-beta1  [OK]
    rc: 1.0 --> 1.1-rc1  [OK]


##About the `.info` File
Web Package looks for a file with the .info extension and will use that for storing the meta data about your project.  If none is found, then `web_package.info` will be created.  You may configure the actual filename in the config file e.g. `info_file = some_other_file.info` if these first two options do not work for you. Here is a basic `.info` file.

    name = "Apple"
    description = "A red, green or yellow fruit."
    homepage = http://www.intheloftstudios.com/packages/jquery/apple
    version = 0.0.1
    author = Aaron Klump <sourcecode@intheloftstudios.com>

## Build Scripts
You may add php or shell scripts to `.web_package/build` and they will be run each time the version increments.  You may also trigger a build by calling `bump build`.

Any file ending in `.php` or `.sh` found in the build script folder will be called during version bumping.  Refer to the parameter chart below for script arguments:

One example of using a build script, say with a jQuery plugin, is when you want to embed the version string _inside_ the `jquery.pluging_name.js` file.  In such a case the  `.info` file strategy doesn't meet your needs.  For such a scenario you would use a build script.

### Callback Arguments
| data          | build.php | build.sh |
|---------------|-------------|------------|
| prev version  | $argv[1]    | $1         |
| new version   | $argv[2]    | $2         |
| package name  | $argv[3]    | $3         |
| description   | $argv[4]    | $4         |
| homepage      | $argv[5]    | $5         |
| author        | $argv[6]    | $6         |
| path to root  | $argv[7]    | $7         |

* Your script should print/echo details as to what it did; this will be output to the console.
* Be sure to use _path to root_ in your scripts when you reference any files in your project.

##Beginning A New Project
1. In this example you see how we being a new project called example, initialize the git repository and start with a version number of 0.1
1. Had you wanted to start with version 1.0, then you would have used `bump major`; and if you had wanted to start with version 0.0.1 you would have used `bump patch`.


        $ mkdir example
        $ cd example
        $ git init
        Initialized empty Git repository in /Library/WebServer/Documents/globalonenessproject/site-dev/public_html/sites/all/modules/contrib/example/.git/
        $ bump init
        Enter package name: Example Package
        Enter package description: An example package showing how to do this.

        A new web_package "Example Package" has been created.

        $ bump minor
        Version bumped:  0.0.0 ---> 0.1
        $ git add .
        $ git commit -m 'initial commit'
        [master (root-commit) e604ade] initial commit
         1 file changed, 3 insertions(+)
         create mode 100644 web_package.info
        $

## What to add to Version Control
1. You should include `.web_package` in version control, but not the subfolder `tmp`.

##Developing A Project
1. In this example you make your changes to the develop branch (see [gitflow](http://nvie.com/posts/a-successful-git-branching-model)) and then commit as normal. You could have also done things on feature branches and merged then back into develop.
1. When all your work is done and you're ready for the release then type `bump release`; this is shorthand for `bump minor release`. If you only want a patch release then type `bump patch release`.  Consequently if you want a major release type `bump major release`. 
2. Immediately thereafter (unless you know what you're doing), type `bump done`

        $ git co -b develop
        Switched to a new branch 'develop'
        $ touch do
        $ touch re
        $ touch mi
        $ git add .
        $ git commit -m 'added do re mi'
        [develop 7094ae4] added do re mi
         0 files changed
         create mode 100644 do
         create mode 100644 mi
         create mode 100644 re
        $ bump release
        Version bumped:  0.1.0 ---> 0.2.0
        M	web_package.info
        Switched to a new branch 'release-0.2.0'
        [release-0.2.0 83abd01] Version bumped from  0.1.0 to 0.2.0
         1 file changed, 1 insertion(+), 1 deletion(-)
        $ bump done
        Merging into develop...
        Switched to branch 'develop'
        Merge made by the 'recursive' strategy.
         web_package.info |    2 +-
         1 file changed, 1 insertion(+), 1 deletion(-)
        Continue to master? (y/n) y
        Switched to branch 'master'
        Merge made by the 'recursive' strategy.
         web_package.info |    2 +-
         1 file changed, 1 insertion(+), 1 deletion(-)
         create mode 100644 do
         create mode 100644 mi
         create mode 100644 re
        Delete release-0.2.0? (y/n) y
        Deleted branch release-0.2.0 (was 83abd01).

Now you can list the tags and see that a tag was just created that matches the current version of your package.

    $ git tag -l
    0.2.0
    $ bump -i
    name = Example Package
    description = An example package showing how to do this
    version = 0.2.0
    $

At this point you need to use `git push` as necessary for your remotes.

##Hotfix On Existing Project
An example of a hotfix to the master branch.

1. While on the master branch type `bump hotfix`.
2. Do the work needed.
3. Type `bump done`.

        $ git status
        On branch master
        nothing to commit (working directory clean)
        $ bump hotfix
        Version bumped:  0.2.0 ---> 0.2.1
        M	web_package.info
        Switched to a new branch 'hotfix-0.2.1'
        [hotfix-0.2.1 75b936a] Version bumped from  0.2.0 to 0.2.1
         1 file changed, 1 insertion(+), 1 deletion(-)
        $ touch fa
        $ git add .
        $ git commit -m 'added fa'
        [hotfix-0.2.1 75b936a] added fa
         0 files changed
         create mode 100644 fa
        $ bump done
        Merging into develop...
        Switched to branch 'develop'
        Merge made by the 'recursive' strategy.
         web_package.info |    2 +-
         1 file changed, 1 insertion(+), 1 deletion(-)
        Continue to master? (y/n)y
        Switched to branch 'master'
        Merge made by the 'recursive' strategy.
         web_package.info |    2 +-
         1 file changed, 1 insertion(+), 1 deletion(-)
        Delete hotfix-0.2.1? (y/n)y
        Deleted branch hotfix-0.2.1 (was 75b936a).

Again, checking that a tag was created...

    $ git tag -l
    0.2.0
    0.2.1
    $ bump -i
    name = Example Package
    description = An example package showing how to do this
    version = 0.2.1

## Merge conflict during `bump done`
Follow these steps if you experience a merge conflict after typing `bump done`; they should get you through it.

1. Open the file with the conflict
2. Remove all conflicts
3. Save the file and `git add [file]`
4. Repeat until all conflicts are resolved
5. Type `git commit`
6. Now go back the hotfix or release branch: `git co hotfix…`
7. Re-initiate `bump done`

## Alpha, Beta and RC
There are three commands that will move your package through the stages, but only in the logical order.

1. `bump alpha` call this when your version doesn't already contain alpha, beta or rc as the patch prefix.  Example, calling `bump alpha` on a version of `1.0` will bump your version to `1.1-alpha1`.
2. Calling `bump beta` on when your project is already in alpha will move it to beta stage, e.g. `bump beta` when your version is `1.1-alpha5` moves it to `1.1-beta1`.
3. Calling `bump rc` on a `1.1-beta3` bumps it to `1.1-rc1`
4. You can go directly to beta or to rc, but not the other direction, e.g. if your version is `2.3` you can `bump rc` and it becomes `2.4-rc1`.

##Configuration
The configuration file is created during `bump init` and is located at `.web_package/config`.  Default contents look like this:

    master = "master"
    develop = "develop"
    git_remote = origin
    create_tags = yes
    push_tags = ask
    push_master = ask
    push_develop = ask
    
The entire `.web_package` directory should not be included in source control.  **To modify the configurations simply edit `.web_package/config` directly.**  Make sure to have spaces surrounding your equal signs as `create_tags=yes` is not the same as `create_tags = yes`.  **Also you must have a line break at the end of the file!**

###master: `(string)`
The name of the branch you consider master.  If you have more than one branch you consider a master then list them all, separated by spaces, e.g. `"master1 master2"` For more insight into this feature, see the [Drupal Modules/Themes section](#drupal) below.

###develop: `(string)`
The name of the branch you consider develop.  If you have more than one branch you consider a develop branch then list them all, separated by spaces, e.g. `"develop1 develop2"`.  **In the case of multiples: Make sure that you list them in the exact order as the master list, as the correlation of master to develop is imperative.**

###remote: `(string)`
The name of the git remote to be used with `git push [git_remote] release-1.0`

###create_tags: `major`, `minor`, `patch ` or `no`
When executing `bump done`, determine what severity level will create a git tag.  Set to `no` to never create a tag.

###push_tags: `no`, `ask` or `auto`
If tags should be pushed to `git_remote`.  Set to `auto` and you will not be prompted first.

###push_master: `no`, `ask` or `auto`
If master branches should be pushed to `git_remote`.  Set to `auto` and you will not be prompted first.

###push_develop: `no`, `ask` or `auto`
If develop branches should be pushed to `git_remote`.  Set to `auto` and you will not be prompted first.

###info_file: `(string)`
(Optional) If you have more than one .info file and you need to force Web Package to use the correct one then add this option. The name of the file containing the version info.

###author: `"(string)"`
(Optional) This one needs to be wrapped in quotes if you have spaces like this example… "Aaron Klump \<sourcecode@intheloftstudios.com>".  This will get written to `web_package.info` file during the init process.

###init_version: `"(string)"`
(Optional)  This is used during `bump init` to set the default version of a package.

###patch_prefix: `"(string)"`
(Optional)  This is used as the default patch_prefix.

###php: `"(string)"`
(Optional)  The path to the php to use for PHP build scripts.

###bash: `"(string)"`
(Optional)  The path to bash to use for shell build scripts.

###pause: `"(int)"`
(Optional)  Enter a number of seconds to pause before git creates a branch and adds files.  This is here in case you need to allow time for file processing after the version file has been updated. If you want to be prompted before git does it's thing enter a -1 here.


##Global Configuration
A global configuration file may be created at `~/.web_package/config`, the contents of which will be used as defaults for new projects or existing projects without said parameter.  This is most useful for the `author` and `info_file` parameters.  **Note: if a global config parameter is set, but the project does not override it, the global will apply for that project, even after `bump init`.

##Configuration Templates
Let's take the use case of a Drupal module, which has a different configuration setup than a website project.  For our module we want the following configuration:

    master = 8.x-1.x 7.x-1.x 6.x-1.x
    develop = 8.x-1.x 7.x-1.x 6.x-1.x
    remote = origin
    create_tags = patch
    push_tags = ask
    push_develop = no
    push_master = ask
    info_file = web_package.info
    patch_prefix = -rc

Wouldn't it be nice to not have to retype that for every new Drupal module?  Well you don't have to if you use a Global Template.

###Defining a Global Template
For a template called `drupal`, create a file at `~/.web_package/config_drupal`, containing the configuration you wish to use for that class of projects.  So the pattern is `~/.web_package/config_[template name]`.

###Implementing a Global Template
When creating a new project, use the command `bump init drupal` and your template will automatically be used as the default configuration.

    $ mkdir new_drupal
    $ cd new_drupal
    $ bump init drupal
    Template drupal used.
    Enter package name: Drupal Module
    Enter package description: a good one.
    
    A new web_package "Drupal Module" (version: 7.x-1.0-alpha1) has been created.
    
    $ bump config
    master = 8.x-1.x 7.x-1.x 6.x-1.x
    develop = 8.x-1.x 7.x-1.x 6.x-1.x
    remote = origin
    create_tags = patch
    push_tags = ask
    push_develop = no
    push_master = ask
    info_file = web_package.info
    patch_prefix = -rc
    $

##[Drupal Modules/Themes](id:drupal)
When I use this with my Drupal modules, the workflow is a bit different.  For starters, there is no master branch.  Actually the master and development branches are one in the same, but we have one branch for each major version of Drupal.  The tags become really important as the release packages are built from them.  Observe `git br -l`

    * 7.x-1.x
      6.x-1.x
      5.x-1.x
      
Here's how to modify the config file for a Drupal project.  Change the appropriate lines in `.web_config/config` to look like this, assuming that you are maintaining your module for Drupal versions 6-8…

    master = "8.x-1.x 7.x-1.x 6.x-1.x"
    develop = "8.x-1.x 7.x-1.x 6.x-1.x"
    create_tags = yes
    push_tags = ask    

In summary what you are saying is this: **I have three master branches, which are one in the same with my develop branches.**  This has the benefit of letting you `bump hotfix` and `bump release` off of the same branch.  Your workflow would then resemble this:

    $ mkdir drupal_module
    $ cd drupal_module
    $ git init
    Initialized empty Git repository in /Volumes/Data/Users/aklump/Repos/git/drupal_module/.git/
    $ bump init
    Enter package name: Drupal Module
    Enter package description: Drupal module example

    A new web_package "Drupal Module" has been created. Please set the initial version now.

    $ bump major
    Version bumped:  0.0.0 ---> 1.0
    $ bump i
    name = Drupal Module
    description = Drupal module example
    version = 1.0
    $ git add .
    $ git cim 'initial commit'
    [master (root-commit) 7194384] initial import
     1 file changed, 3 insertions(+)
     create mode 100644 web_package.info
    $ git br -m 6.x-1.x
    $ git co -b 7.x-1.x
    $ git co -b 8.x-1.x
    Switched to a new branch '6.x-1.x'
    $ git br -l
      6.x-1.x
      7.x-1.x
    * 8.x-1.x  

At this point the workflow is pretty much the same, although as noted you will be able to choose `bump hotfix` or `bump release` from each major Drupal version branch.  You get to decide which is best.

##Questions
###When should I use 'bump hotfix'?
When you need to make an immediate change to the production state (master branch) of the project, you will use `bump hotfix`.  A hotfix is unique in that the release number gets bumped _before_ the work is done.

###When should I use 'bump release'?
When you have finished work on the development branch and you want to release it to the production-ready state of the project, you will use `bump release`.  A release is different from a hotfix, in that the version is bumped _after_ the work is done.

###When should I use 'bump major', 'bump minor', or 'bump patch'?
These three commands are unique in that they do not interact with git in any way, they simply modify `web_package.info`.  The choice of which of the three to use is based on the severity of the changes and your versioning mandates.  However, _why_ you would use one of these three can be answered thus: __Any time that you will need to step away from the development branch for an extended period of time, but cannot release the package.__  This way you can be certain that no implementation of your web_package thinks it has the most recent version.  I would argue it's best practice to `bump_patch` at the end of each work session, if you have multiple projects underway.


##Contact
**In the Loft Studios**  
Aaron Klump - Developer  
PO Box 29294 Bellingham, WA 98228-1294  
aim: theloft101  
skype: intheloftstudios  
<http://www.InTheLoftStudios.com>
