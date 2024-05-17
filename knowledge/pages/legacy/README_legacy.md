<!--
id: readme_legacy
tags: ''
-->

# Web Package

## Summary

A shell script to help the management of files as a package through easy version number manipulation. Supports name, description, author and version number. Integrates with .git and uses [gitflow methodology](http://nvie.com/posts/a-successful-git-branching-model) to automatically merge and tag.

## Script Installation

The web_package scripts need to be installed on your system once to make the tool available.  Don't confuse this with intializing a project which comes later.

1. Download [this package](http://www.intheloftstudios.com/packages/bash/web_package/download) somewhere on your computer, e.g., `~/lib/web_package-master`.
1. Run `composer install` to download dependencies.
1. Make sure that `~/bin` is in your `$PATH` variable of BASH.  To verify type `echo $PATH` in a shell window and look for the path `~/bin`; **note that it will probably appear as the expanded home directory, not `~`**.
1. Create a symlink to `web_package.sh` as `bump` as seen in the following snippet. (In all examples the symlink is called `bump` but that is not functionally necessary; you may rename as desired.)  For this snippet we're assuming that the package has been saved to `~/lib/web_package-master`
    
        cd ~/bin && ln -s ~/lib/web_package-master/web_package.sh bump && bump

1. If all went well you should see something like this:

        web_package.info not found. Have you created your Web Package yet?

## Why Use Web Package?

* To simplify using Gitflow.
* To incorporate build scripts in package management.
* To automate the process of version incrementing a package.

This will save you keystrokes if you're used to typing this:
    
    $ git add .
    $ git cim 'some changes'
    $ git checkout -b release-1.1
    $ cat version.info
    version = 1.0
    $ echo version.info > "version = 1.1"
    $ git add version.info
    $ git cim 'Bumped version to 1.1'
    $ git checkout develop
    $ git merge --no-ff release-1.1
    $ git checkout master
    $ git merge --no-ff release-1.1
    $ git br -d release-1.1
    $ git checkout develop


How would you rather type this, instead from now on...
    
    $ git add .
    $ git cim 'some changes'
    $ bump release
    $ bump done

If so, Web Package is for you! Read on...

## About Version Numbers
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

### Stepping by > 1, odd or even
By default, the major, minor and patch step increment is 1.  This can be changed in a `local_config` file as necessary (either user or project level).

    major_step = 1
    minor_step = 1
    patch_step = 2

Let's say that two developers are working on the same master branch and they are rolling out rapid hotfixes.  Wouldn't it be nice if developer A only did odd-numbered hotfixes and developer B only did even-numbered hotfixes, so as to not step on each others' toes?  This the idea behind version incrementing with steps > 1.

You can override the default using the `local_config` and use odd steps by adding the following lines, be sure to include a line break at the end of the line.

    patch_step = "odd"

For even just change the value to `"even"`.

_In this example, if you were to add this directive to the `config` file, that would negate the whole point, since `config` is checked into the repo.  You need to use the `local_config`._

### Testing A Version Schema
1. type `bump help test` for more info


## About the `.info` File
Web Package looks for a file with the .info extension and will use that for storing the meta data about your project.  This file is one that can be parsed using php's `parse_ini_file()` function.  If none is found, then `web_package.info` will be created.  You may configure the actual filename in the config file e.g. `info_file = some_other_file.info` if these first two options do not work for you. Here is a basic `.info` file.

    name = "Apple"
    description = "A red, green or yellow fruit."
    homepage = http://www.intheloftstudios.com/packages/jquery/apple
    version = 0.0.1
    author = Aaron Klump <sourcecode@intheloftstudios.com>

## Beginning A New Project
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

## Reversing `bump init`
By the way--to reverse the action of `bump init`, simply delete the folder `.web_package` from the root of your project.  You may or may not want to delete the `*.info` file from the root of your project.

## What to add to your project's version control
1. You should include `.web_package` in version control.  It has a .gitignore file that you can refer to for files that may safely be excluded.

## Developing A Project
1. In this example you make your changes to the develop branch (see [gitflow](http://nvie.com/posts/a-successful-git-branching-model)) and then commit as normal. You could have also done things on feature branches and merged then back into develop.
1. When all your work is done and you're ready for the release then type `bump release`; this is shorthand for `bump minor release`. If you only want a patch release then type `bump patch release`.  Consequently if you want a major release type `bump major release`. 
2. Immediately thereafter (unless you know what you're doing), type `bump done`

        $ git checkout -b develop
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

## Hotfix On Existing Project
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
6. Now go back the hotfix or release branch: `git checkout hotfix…`
7. Re-initiate `bump done`

## Alpha, Beta and RC
There are three commands that will move your package through the stages, but only in the logical order.

1. `bump alpha` call this when your version doesn't already contain alpha, beta or rc as the patch prefix.  Example, calling `bump alpha` on a version of `1.0` will bump your version to `1.1-alpha1`.
2. Calling `bump beta` on when your project is already in alpha will move it to beta stage, e.g. `bump beta` when your version is `1.1-alpha5` moves it to `1.1-beta1`.
3. Calling `bump rc` on a `1.1-beta3` bumps it to `1.1-rc1`
4. You can go directly to beta or to rc, but not the other direction, e.g. if your version is `2.3` you can `bump rc` and it becomes `2.4-rc1`.


##Contact
**In the Loft Studios**  
Aaron Klump - Developer  
PO Box 29294 Bellingham, WA 98228-1294  
aim: theloft101  
skype: intheloftstudios  
<http://www.InTheLoftStudios.com>
