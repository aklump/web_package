#!/usr/bin/env bash
bump_sass=$(type sass >/dev/null &2>&1 && which sass)

$bump_sass  --style=compressed --no-cache --update "$7/scss/noscript.scss:$7/dist/noscript.css"
$bump_sass --no-cache --update "$7/scss/itls18_theme.scss:$7/dist/itls18_theme.css"
