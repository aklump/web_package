<?php
/**
 * @file
 * Summary of the file. e.g. Handles file uploads.
 *
 * @def, in, or addtogroup name Group Name
 */

function lobster_echo($string)
{
    echo $string . PHP_EOL;
}

function lobster_success($string)
{
    echo $string . PHP_EOL;
}

function lobster_error($string)
{
    echo $string . PHP_EOL;
}

function lobster_notice($string)
{
    echo $string . PHP_EOL;
}


// function lobster_color_echo($color, $string) {
//   $esc = "\033";
//   $fore = "1;36";
//   $string = "${esc}[${fore}m${string}${esc}[0m";
//   lobster_echo($string);
// }

function lobster_exit()
{
    exit(99);
}

function lobster_has_flag($flag)
{
    global $lobster_conf;

    return in_array($flag, $lobster_conf->app->flags);
}

function lobster_get_param($param)
{
    return isset($lobster_conf->app->params[$param]) ? $lobster_conf->app->params[$param] : null;
}

function lobster_has_param($param)
{
    global $lobster_conf;

    return in_array($param, $lobster_conf->app->params);
}
