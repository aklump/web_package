##Summary

A shell script to help the management of files as a package. Supports
name, description and version number. Integrates with .git and uses gitflow methodology to automatically merge and tag.

##Installation
1. Create a symlink to `web_package.sh`, I suggest `bump` and add it to `~/bin` you can then call this script by typing `bump` on the command line as in the examples below.
1. Make sure that `~/bin` is in your PATH variable
2. Consider adding `.web_package` to your global `.git_ignore` file. **Make sure you are using the leading '.'; you do not want to add `web_package` to the ignores.**to 
1. Below you will see three basic examples for usage:
1. For more info see: <http://nvie.com/posts/a-successful-git-branching-model>
1. For a list of commands, type `bump`.


##About Version Numbers
1. The versioning schema of `major.minor.micro` is used.
2. There is no limit the to value of each part; as an example something like this is theoretically possible `999.999.999`; further the next minor version after `1.9` is not `2.0`, but rather `1.10`.
2. Version numbers may begin with a prefix, demarkated by a '-', an example of a valid version number with prefix is `7.x-1.0`
3. Read more about version numbers here <http://en.wikipedia.org/wiki/Software_versioning>
4. At this time there is no support for alphanumeric versions (beyond the prefix) such as `1.0-rc1`, `1.0-alpha2`, etc.


##Beginning A New Project
1. In this example you see how we being a new project called example, initialize the git repository and start with a version number of 0.1
1. Had you wanted to start with version 1.0, then you would have used `bump major`; and if you had wanted to start with version 0.0.1 you would have used `bump micro`.

<pre>
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
</pre>

##Developing A Project
1. In this example you make your changes to the develop branch (see [gitflow](http://nvie.com/posts/a-successful-git-branching-model)) and then commit as normal. You could have also done things on feature branches and merged then back into develop.
1. When all your work is done and you're ready for the release then type `bump release`; this is shorthand for `bump minor release`. If you only want a micro release then type `bump micro release`.  Consequently if you want a major release type `bump major release`. 
2. Immediately thereafter (unless you know what you're doing), type `bump done`

<pre>
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
</pre>

Now you can list the tags and see that a tag was just created that matches the current version of your package.

<pre>
$ git tag -l
0.2.0
$ bump -i
name = Example Package
description = An example package showing how to do this
version = 0.2.0
$
</pre>

At this point you need to use `git push` as necessary for your remotes.

##Hotfix On Existing Project
An example of a hotfix to the master branch.

1. While on the master branch type `bump hotfix`.
2. Do the work needed.
3. Type `bump done`.

<pre>
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
</pre>

Again, checking that a tag was created...

<pre>
$ git tag -l
0.2.0
0.2.1
$ bump -i
name = Example Package
description = An example package showing how to do this
version = 0.2.1
</pre>

##Questions
###When should I use 'bump hotfix'?
When you need to make an immediate change to the production state of the project, you will use `bump hotfix`.  A hotfix is unique in that the release number gets bumped _before_ the work is done.

###When should I use 'bump release'?
When you have finished work on the development branch and you want to release it to the production-ready state of the project, you will use `bump release`.  A release is different from a hotfix, in that the version is bumped _after_ the work is done.

###When should I use 'bump major', 'bump minor', or 'bump micro'?
These three commands are unique in that they do not interact with git in any way, they simply modify `web_package.info`.  The choice of which of the three to use is based on the severity of the changes and your versioning mandates.  However, _why_ you would use one of these three can be answered thus: __Any time that you will need to step away from the development branch for an extended period of time, but cannot release the package.__  This way you can be certain that no implementation of your web_package thinks it has the most recent version.  I would argue it's best practice to `bump_micro` at the end of each work session, if you have multiple projects underway.

Another time to `bump micro` is after you push to a staging server, that way you know your staging server is behind your local.  This may or may not make sense for your situation.


--------------------------------------------------------
#Contact
In the Loft Studios

Aaron Klump - Web Developer

PO Box 29294 Bellingham, WA 98228-1294

aim: theloft101

skype: intheloftstudios

[http://www.InTheLoftStudios.com](http://www.InTheLoftStudios.com)
