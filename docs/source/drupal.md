# Drupal Modules/Themes
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
    $ git checkout -b 7.x-1.x
    $ git checkout -b 8.x-1.x
    Switched to a new branch '6.x-1.x'
    $ git br -l
      6.x-1.x
      7.x-1.x
    * 8.x-1.x  

At this point the workflow is pretty much the same, although as noted you will be able to choose `bump hotfix` or `bump release` from each major Drupal version branch.  You get to decide which is best.

