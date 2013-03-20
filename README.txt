SUMMARY: A shell script to help the management of files as a package. Supports
name, description and version number. Integrates with .git and uses gitflow
methodology to automatically merge and tag.


INSTALLATION:
* Create a symlink to web_package.sh, I suggest 'bump' and add it to ~/bin you
  can then call this script by typing bump on the command line as in the
  examples below
* Make sure that ~/bin is in your PATH variable
* Below you will see three basic examples for usage:
* For more info see: http://nvie.com/posts/a-successful-git-branching-model/
* For help type 'bump'


BEGINNING A NEW PROJECT:
* In this example you see how we being a new project called example, initialize
  the git repository and start with a version number of 0.1
* Had you wanted to start with version 1.0, then you would have used 'bump
  major'; and if you had wanted to start with version 0.0.1 you would have used
  'bump micro'

$ mkdir example
$ cd example
$ git init
Initialized empty Git repository in ~/Repos/git/example/.git/
$ bump minor
web_package.info not found.  Create Y/N?
Enter package name: Example Package
Enter package description: An example package showing how to do this
web_package.info file was created.
Version bumped:  0.0.0 ---> 0.1.0
$ git add .
$ git commit -m 'initial commit'
[master (root-commit) 715de1e] initial commit
 1 file changed, 3 insertions(+)
 create mode 100644 web_package.info


DEVELOPING A PROJECT:
* Following gitflow we develop on the branch 'develop'
* In this example you make your changes to the develop branch and then commit as
  normal. You could have also done things on feature branches and merged then
  back into develop.
* When ready for the release then type 'bump release'
* Immediately thereafter (unless you know what you're doing), type 'bump done'

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
Continue to master? (y/n)y
Switched to branch 'master'
Merge made by the 'recursive' strategy.
 web_package.info |    2 +-
 1 file changed, 1 insertion(+), 1 deletion(-)
 create mode 100644 do
 create mode 100644 mi
 create mode 100644 re
Delete release-0.2.0? (y/n)y
Deleted branch release-0.2.0 (was 83abd01).

* Now you can list the tags an see that a tag was just created that matches the
  current version of your package.

$ git tag -l
0.2.0
$ bump -i
name = Example Package
description = An example package showing how to do this
version = 0.2.0
$


HOTFIX ON EXISTING PROJECT:
* An example of a hotfix on the master branch

$ git status
# On branch master
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

* Again, checking that a tag was created

$ git tag -l
0.2.0
0.2.1
$ bump -i
name = Example Package
description = An example package showing how to do this
version = 0.2.1



--------------------------------------------------------
CONTACT:
In the Loft Studios
Aaron Klump - Web Developer
PO Box 29294 Bellingham, WA 98228-1294
aim: theloft101
skype: intheloftstudios

http://www.InTheLoftStudios.com
