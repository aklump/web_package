<!--
id: bash_scripts
tags: ''
-->

# BASH Hooks

[@see Hooks for more info](@hooks) 

## Callback Arguments

|           data           | build.sh |
|:-------------------------|:---------|
| prev version             | $1       |
| current version          | $2       |
| package name             | $3       |
| description              | $4       |
| homepage                 | $5       |
| author                   | $6       |
| path to root             | $7       |
| date/time                | $8       |
| path to info file        | $9       |
| dir of the script        | ${10}    |
| dir of functions.php/.sh | ${11}    |
| root dir of web_package  | ${12}    |
| path to hooks dir        | ${13}    |
