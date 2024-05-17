<!--
id: faq
tags: ''
-->

# Questions

## When should I use 'bump hotfix'?

When you need to make an immediate change to the production state (master branch) of the project, you will use `bump hotfix`.  A hotfix is unique in that the release number gets bumped _before_ the work is done.

## When should I use 'bump release'?

When you have finished work on the development branch and you want to release it to the production-ready state of the project, you will use `bump release`.  A release is different from a hotfix, in that the version is bumped _after_ the work is done.

## When should I use 'bump major', 'bump minor', or 'bump patch'?

These three commands are unique in that they do not interact with git in any way, they simply modify `web_package.info`.  The choice of which of the three to use is based on the severity of the changes and your versioning mandates.  However, _why_ you would use one of these three can be answered thus: __Any time that you will need to step away from the development branch for an extended period of time, but cannot release the package.__  This way you can be certain that no implementation of your web_package thinks it has the most recent version.  I would argue it's best practice to `bump_patch` at the end of each work session, if you have multiple projects underway.
