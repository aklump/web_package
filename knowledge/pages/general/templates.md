<!--
id: templates
tags: ''
-->

# Configuration Templates

Let's take the use case of a Drupal module, which has a different configuration setup than a website project. For our module we want the following configuration:

    master = 8.x-1.x 7.x-1.x 6.x-1.x
    develop = 8.x-1.x 7.x-1.x 6.x-1.x
    remote = origin
    create_tags = patch
    push_tags = ask
    push_develop = no
    push_master = ask
    info_file = web_package.info
    patch_prefix = -rc

Wouldn't it be nice to not have to retype that for every new Drupal module? Well you don't have to if you use a Global Template.

## Defining a Global Template

For a template called `drupal`, create a file at `~/.web_package/config_drupal`, containing the configuration you wish to use for that class of projects. So the pattern is `~/.web_package/config_[template name]`.

## Implementing a Global Template

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

## Tokens

* You may use the token `__DIR__` in the `version_file` value when creating templates. It will be replaced with the basename of the WP root directory.
