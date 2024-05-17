<!--
id: hooklib
tags: ''
-->

# Hooks Library

The hooks library is a directory of common used hook files.  By default the core hooks library (you should not modify this directory) comes with a number of pre-written scripts that can be copied into your projects.  Access your library of hooks by calling:

    bump hookslib

You may choose to define your own Hooks Library by doing the following:

1. Open or create `~/.web_packageconfig`
1. Add the following line, replacing the value with the path to your own hooks library.

        wp_hooklib="$HOME/wp-hooks"

By default the directory will try to be opened.  You can define an alternative command to use by adding this to the above mentioned config file:

        wp_hooklib_action="ls"
