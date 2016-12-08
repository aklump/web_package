<?php
/**
 * @file
 * Summary of the file. e.g. Handles file uploads.
 *
 * @def, in, or addtogroup name Group Name
 */

function lobster_echo($string)
{
<<<<<<< HEAD
    echo $string . PHP_EOL;
=======
    global $lobster_conf;
    $color = $lobster_conf->lobster->color_settings->current;
    $parts = array();
    if ($color) {
        $parts[] = lobster_colorize($color, $string);
    }
    else {
        $parts[] = $string;
    }
    $parts[] = PHP_EOL;

    echo implode('', $parts);
}

function lobster_colorize($color, $string)
{
    global $lobster_conf;

    // Expand a symantic color
    if (!is_numeric(substr($color, 0, 1))) {
        $color = $lobster_conf->lobster->colors->{$color};
    }

    $parts = array();
    $parts[] = $lobster_conf->lobster->color_settings->escape;
    $parts[] = '[' . $color . 'm';
    $parts[] = $string;
    $parts[] = $lobster_conf->lobster->color_settings->escape;
    $parts[] = '[0m';

    return implode('', $parts);
}


function lobster_color_echo($color, $output)
{
    global $lobster_conf;
    $stash = $lobster_conf->lobster->color_settings->current;
    $lobster_conf->lobster->color_settings->current = $lobster_conf->lobster->colors->{$color};
    lobster_echo($output);
    $lobster_conf->lobster->color_settings->current = $stash;
>>>>>>> release
}

function lobster_success($string)
{
<<<<<<< HEAD
    echo $string . PHP_EOL;
=======
    lobster_color_echo('success', $string);
>>>>>>> release
}

function lobster_error($string)
{
<<<<<<< HEAD
    echo $string . PHP_EOL;
=======
    lobster_color_echo('error', $string);
>>>>>>> release
}

function lobster_notice($string)
{
<<<<<<< HEAD
    echo $string . PHP_EOL;
=======
    lobster_color_echo('notice', $string);
>>>>>>> release
}


// function lobster_color_echo($color, $string) {
//   $esc = "\033";
//   $fore = "1;36";
//   $string = "${esc}[${fore}m${string}${esc}[0m";
//   lobster_echo($string);
// }

function lobster_exit()
{
<<<<<<< HEAD
=======
    lobster_set_route_status(99);
>>>>>>> release
    exit(99);
}

function lobster_has_flag($flag)
{
    global $lobster_conf;

    return in_array($flag, $lobster_conf->app->flags);
}

function lobster_get_param($param)
{
<<<<<<< HEAD
=======
    global $lobster_conf;

>>>>>>> release
    return isset($lobster_conf->app->params[$param]) ? $lobster_conf->app->params[$param] : null;
}

function lobster_has_param($param)
{
    global $lobster_conf;

    return in_array($param, $lobster_conf->app->params);
<<<<<<< HEAD
=======
}

/**
 * Sets the route status
 *
 * @param int $code Any non zero value means the route failed.
 *
 * @return int|false
 */
function lobster_set_route_status($code)
{
    $file = getenv('LOBSTER_TMPDIR') . '/route_status';

    return file_put_contents($file, $code);
}

function lobster_get_route_status()
{
    $file = getenv('LOBSTER_TMPDIR') . '/route_status';

    return file_get_contents($file);
>>>>>>> release
}
