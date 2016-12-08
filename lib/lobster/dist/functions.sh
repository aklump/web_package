#!/bin/bash
# 
# @file
# Defines Lobster core functions.

#
# Load the configuration cascade by name
# 
# Given 1='my_app' the files will load in this order
# 
# 2. $LOBSTER_APP_ROOT/.my_appconfig
# 3. ~/.my_appconfig
# 5. The first file found in parent dirs, if found.
# 
# @param string app name, e.g. 'my_app'
#
function lobster_load_config() {
  base=$1
  if ! test -e "$LOBSTER_APP_ROOT/install/$base"; then
    lobster_failed "You must create /install/$base before your app will run.";
  fi
  declare -a cascade=("$LOBSTER_APP_ROOT/install/$base" "$HOME/$base" "$LOBSTER_INSTANCE_ROOT/$base" );
  for file in "${cascade[@]}"; do
    if [ -f "$file" ]; then
      lobster_core_verbose "Loading config file: $file"
      source "$file"
    fi
  done

#  path=$(lobster_upfind $base && echo "$lobster_upfind_dir")
#  if [ "$path" != "" ] && [ -f "$path" ]; then
#    source "$path"
#    lobster_core_verbose "Loading config file: $path"
#  fi
}

function lobster_verbose() {
  if lobster_has_flag "v"; then
    lobster_color_echo "verbose" ${@}
  fi
}

function lobster_core_verbose() {
  if [ ! "$lobster_core_verbose" ] || [ $lobster_core_verbose -eq 1 ]; then 
    lobster_verbose ${@}
  fi
}

##
 # Recursive search for file in parent dirs
 # 
 # @param string This may only be  filename, not a dir/name
 #  
 # usage
 #   path=$(lobster_upfind $base && echo "$lobster_upfind_dir")
 #
lobster_upfind_dir=''
function lobster_upfind () {
  lobster_upfind_dir=''
  file=$(basename "$1")
  test / == "$PWD" && return 1 || test -e "$file" && lobster_upfind_dir="${PWD}/$file" && return || cd .. && lobster_upfind "$file"
}


#
# Produces an error output
#
# @param string $arg
#
function lobster_error() {
  lobster_color_echo error "$1"
}

#
# Produces an error output
#
# @param string $arg
#
function lobster_warning() {
  lobster_color_echo warning "$1"
}

#
# Produces an error output
#
# @param string $arg
#
function lobster_success() {
  lobster_color_echo success "$1"
}

#
# Produces an error output
#
# @param string $arg
#
function lobster_notice() {
  lobster_color_echo notice "$1"
}

#
# Sets the output color.
#
# @param int|string  One of a color name or semantic string or a color 0-7.
# 
# You can also set the color to null and lobster_echo will not print the tty
# commands.
#
function lobster_color() {
  # First allow the passing of a number
  lobster_color_current=$1

  case $1 in
    
    # Color names
    'grey' )
      lobster_color_current="$lobster_color_bright;30"
      ;;
    'red' )
      lobster_color_current="$lobster_color_bright;31"
      ;;
    'green' )
      lobster_color_current="$lobster_color_bright;32"
      ;;
    'yellow' )
      lobster_color_current="$lobster_color_bright;33"
      ;;
    'blue' )
      lobster_color_current="$lobster_color_bright;34"
      ;;                  
    'magenta' )
      lobster_color_current="$lobster_color_bright;35"
      ;;
    'pink' )
      lobster_color_current="$lobster_color_bright;35"
      ;;
    'cyan' )
      lobster_color_current="$lobster_color_bright;36"
      ;;
    'white' )
      lobster_color_current="$lobster_color_bright;37"
      ;;

    # Semantic
    'notice' )
      lobster_color $lobster_color_notice
      ;;
    
    'warning' )
      lobster_color $lobster_color_warning
      ;;
    
    'error' )
      lobster_color $lobster_color_error
      ;;

    'success' )
      lobster_color $lobster_color_success
      ;;            

    'confirm' )
      lobster_color $lobster_color_confirm
      ;;   

    'verbose' )
      lobster_color $lobster_color_verbose
      ;;

    'info' )
      lobster_color $lobster_color_info
      ;;
  esac
}

#
# Prints one or more messages in the current color.
#
# @param string|array $arg
# 
# @todo Support for background colors.
#
function lobster_echo() {
  line="${@}"
  if [ -d "$lobster_logs" ]; then
    echo -e $line >> "$lobster_logs/echo.txt"
  fi

  if [ "$lobster_debug" == "1" ] || ! lobster_has_param 'lobster-quiet'; then
    if [ "$lobster_color_current" == "null" ] || [ ! "$line" ] || [ ! "$lobster_escape_char" ]; then
      echo -e "$line"
    else
      esc=$lobster_escape_char
      fore=$lobster_color_current
      echo -e "${esc}[${fore}m${line}${esc}[0m"
    fi
  fi
}

#
# Writes a message to a log file.
#
# @param string|array $arg
function lobster_log() {
  if [ "$lobster_logs" ]; then
    type=$1
    message=$2
    severity=$3
    line="\"$(lobster_datetime)\",\"$(whoami)\",\"$message\",\"$severity\""
    echo -e $line >> "$lobster_logs/$type.csv"
  else
    lobster_error "Please enable $lobster_logs"
  fi
}

#
#
# Outputs the argument(s) in bold
#
function lobster_strong() {
  esc=$lobster_escape_char
  line=${@}
  echo -e "$esc[1m$line$esc[0m"
}

#
#
# Outputs the argument(s) underlined
#
function lobster_underline() {
  esc=$lobster_escape_char
  line=${@}
  echo -e "$esc[4m$line$esc[0m" 
}

#
# Prints one or more lines in a color, but does not change the color setting.
# 
# @param string|int The argument to pass to lobster_color
# @param string|array $lines Will be passed to lobster_echo.
#
function lobster_color_echo() {
  local stash=$lobster_current_color
  lobster_color $1
  lobster_echo "${@:2}"
  lobster_color $stash
}

lobster_theme_source=''
function lobster_theme() {
  if [ $lobster_debug -eq 0 ] && lobster_has_param 'lobster-quiet'; then
    return
  fi

  source=$1
  if lobster_has_param "lobster-nowrap" && ( [ "$source" == 'header' ] || [ "$source" == 'footer' ] ); then
    return
  fi

  if [ ! -f "$source" ]; then
    for ext in "${lobster_tpl_extensions[@]}"; do
      if [ -f "$LOBSTER_APP_ROOT/themes/$lobster_theme/tpl/$1.$ext" ]; then
        source="$LOBSTER_APP_ROOT/themes/$lobster_theme/tpl/$1.$ext"
      fi
    done
  fi

  if [ ! "$source" ]; then
    return
  fi
  ext="${source##*.}"

  # preprocess
  processor=$source
  processor="${processor/tpl/pre}"
  processor="${processor/$ext/sh}"
  if [ -f "$processor" ]; then
    source "$processor"
  fi
  
  # Load the file content.
  if [ -f "$source" ]; then
    lobster_theme_source="$source"
    output=$(cat "$source")
    if [ "$output" ]; then
      lobster_echo "$output"
    fi
  fi

  # postprocess
  processor=$source
  processor="${processor/tpl/post}"
  processor="${processor/$ext/sh}"
  if [ -f "$processor" ]; then
    source "$processor"
  fi
}

function lobster_failed() {
  lobster_error "$1"
  lobster_include "failed"
  lobster_exit 1
}

#
# Prints the footer and exits the script with optional exit status 0-5
function lobster_exit() {
  lobster_theme 'footer'
  lobster_include 'shutdown'
  # @todo Can't find a way to typecast so arg is a numeric argument.
  case "$1" in
  1)
    exit 1 ;;
  2)
    exit 2 ;;
  3)
    exit 3 ;;
  4)
    exit 4 ;;
  5)
    exit 5 ;;
  esac

  exit 0
}

#
# This function is called at the end of the route.
#
<<<<<<< HEAD
# It may need to be chaned to 'return' for some apps.  See docs for more info
#
function lobster_route_end() {
=======
# It may need to be changed to 'return' for some apps.  See docs for more info
#
function lobster_route_end() {
  lobster_set_route_status 0
>>>>>>> release
  lobster_theme 'footer'
  lobster_include 'shutdown'
  exit 0
}

function lobster_show_debug {
  if [ $lobster_debug -eq 1 ]; then
    lobster_include 'debug'
  fi  
}

#
# Includes a script cascade by basename
# 
# If the argument is not a path, it will be assumed to be located in
# $LOBSTER_APP_ROOT/includes.  Scripts may be of type .sh or .php. .sh scripts are executed
# before .php scripts if ever the basename is the same.
#
# @param string $script  Script name without extension or path without extension
#
function lobster_include() {
  local basename=$1
  local dirname=''
  if [ "$basename" == "${basename##*/}" ]; then
    dir="$LOBSTER_APP_ROOT/includes"
  fi

  # Run the include at the project layer
  if [ -f "$dir/$basename.sh" ]; then
    lobster_core_verbose "include file: $dir/$basename.sh"
    source "$dir/$basename.sh"
  elif [ -f "$dir/$basename.php" ]; then
    lobster_core_verbose "include file: $dir/$basename.php"
    $lobster_php "$dir/$basename.php"
  fi
}

#
# Checks for a value in a an array
#
# @param array  The first element will be shifted off and used as the needle
# 
# @code
#   declare -a array=("e" "do" "re" "e")
#   if lobster_in_array ${array[@]}; then
#     echo "found"
#   fi
# @endcode
# 
# @code
#   needle="do"
#   haystack=("do" "re" "e")
#   array=($needle "${haystack[@]}")
#   if lobster_in_array ${array[@]}; then
#     echo "found"
#   fi
# @endcode
#
function lobster_in_array() {
  needle=$1
  for var in "${@:2}"; do
    if [[ "$var" =~ "$needle" ]]; then
      return 0
    fi
  done
  return 1  
}


#
# Extracts all flags (values beginning with a single -) from an array
# 
# @param array
# 
# @code
#   lobster_get_flags ${@}
#   declare -a lobster_flags=("${lobster_get_flags_return[@]}")
# @endcode
#
declare -a lobster_get_flags_return=();
function lobster_get_flags() {
  for arg in "$@"; do
    if [[ "$arg" =~ ^-([a-z]+) ]]; then
      for ((i=0; i < ${#BASH_REMATCH[1]}; i++)); do
        lobster_get_flags_return=("${lobster_get_flags_return[@]}" "${BASH_REMATCH[1]:$i:1}")
      done
    fi
  done
}

#
# Extracts all flags (values beginning with a single -) from an array
# 
# @param array
#
declare -a lobster_get_params_return=();
function lobster_get_params() {
  for arg in "$@"; do
    if [[ "$arg" =~ ^--(.*) ]]; then
      lobster_get_params_return=("${lobster_get_params_return[@]}" "${BASH_REMATCH[1]}")
    fi
  done
}

#
# Extracts all flags (values beginning with a single -) from an array
# 
# @param array
#
declare -a lobster_get_args_return=();
function lobster_get_args() {
  for arg in "$@"; do
    if [[ ! "$arg" =~ ^-(.*) ]]; then
      lobster_get_args_return=("${lobster_get_args_return[@]}" "$arg")
    fi
  done
}

##
 # Test for a flag
 #
 # @code
 # if has_flag s; then
 # @endcode
 #
 # @param string $1
 #   The flag name to test for, omit the -
 #
 # @return int
 #   0: it has the flag
 #   1: it does not have the flag
 #
function lobster_has_flag() {
  for var in "${lobster_flags[@]}";do
    if [[ "$var" =~ $1 ]];then
      return 0
    fi
  done
  return 1
}

##
 # Test for a parameter
 #
 # @code
 # if has_param code; then
 # @endcode
 #
 # @param string $1a
 #   The param name to test for, omit the -
 #
 # @return int
 #   0: it has the param
 #   1: it does not have the param
 #
function lobster_has_param() {
  for var in "${lobster_params[@]}"; do
    if [[ "$var" =~ $1 ]]; then
      return 0
    fi
  done
  return 1
}

##
 # Test for a parameter
 #
 # @code
 #   declare -a array=('ini' 'json' 'yaml' 'yml');
 #   if lobster_has_params ${array[@]}; then
 #     info_file="$lobster_app_name.$lobster_has_params_return";
 #   fi
 # @endcode
 #
 # @param string $1a
 #   The param name to test for, omit the -
 #
 # @return int
 #   0: it has one of the params; $lobster_has_params_return is set with the first matched param.
 #   1: it does not have any of the param
 #
lobster_has_params_return=''
function lobster_has_params() {
  for var in "${lobster_params[@]}"; do
    declare -a test=("$var" "${@}")
    if lobster_in_array ${test[@]}; then
      lobster_has_params_return="$var"
      return 0
    fi
  done
  return 1
}

##
 # Extract the value of a script param e.g. (--param=value)
 #
 # @code
 # value=$(lobster_get_param try)
 # @endcode
 #
 # @param string $1
 #   The name of the param
 #
function lobster_get_param() {
  for var in "${lobster_params[@]}"
  do
    if [[ "$var" =~ ^(.*)\=(.*) ]] && [ ${BASH_REMATCH[1]} == $1 ]; then
      echo ${BASH_REMATCH[2]}
      return
    fi
  done
}

#
# Returns a json string for passing to php scripts
#
# @param string $arg
#
function lobster_json() {
  local json=''
  local snippet=''

  #
  #
  # Begin child: lobster
  #
  json=$json{\"lobster\":{
  json=$json\"root\"\:\"$LOBSTER_ROOT\",
  json=$json\"tmpdir\"\:\"$LOBSTER_TMPDIR\",

  json=$json\"default_route\"\:\"$lobster_default_route\",
  json=$json\"theme\"\:\"$lobster_theme\",
  json=$json\"debug\"\:$lobster_debug,
  json=$json\"bash\"\:\"$lobster_bash\",
  json=$json\"php\"\:\"$lobster_php\",

  # Pass the colors
  json=$json\"color_settings\"\:\{
  snippet=''
  snippet=$snippet\"escape\":\"\\$lobster_escape_char\",
  snippet=$snippet\"bright\":\"$lobster_color_bright\",
  snippet=$snippet\"current\":\"$lobster_color_current\",
  json=$json${snippet%,}\},

  json=$json\"colors\"\:\{
  snippet=''
  snippet=$snippet\"default\":\"$lobster_color_default\",
  snippet=$snippet\"confirm\":\"$lobster_color_confirm\",
  snippet=$snippet\"input\":\"$lobster_color_input\",
  snippet=$snippet\"input_suggestion\":\"$lobster_color_input_suggestion\",
  snippet=$snippet\"verbose\":\"$lobster_color_verbose\",
  snippet=$snippet\"info\":\"$lobster_color_info\",
  snippet=$snippet\"notice\":\"$lobster_color_notice\",
  snippet=$snippet\"warning\":\"$lobster_color_warning\",
  snippet=$snippet\"error\":\"$lobster_color_error\",
  snippet=$snippet\"success\":\"$lobster_color_success\",
  json=$json${snippet%,}\},

  json=$json\"route_extensions\"\:\[
  snippet=''
  for flag in "${lobster_route_extensions[@]}"; do
    snippet=$snippet\"$flag\",
  done
  json=$json${snippet%,}\],

  json=$json\"tpl_extensions\"\:\[
  snippet=''
  for flag in "${lobster_tpl_extensions[@]}"; do
    snippet=$snippet\"$flag\",
  done
  json=$json${snippet%,}\]

  json=$json\},

  #
  #
  # Begin child: app
  #
  json=$json\"app\":{
  json=$json\"root\"\:\"$LOBSTER_APP_ROOT\",
  json=$json\"config\"\:\"$LOBSTER_APP_ROOT/$lobster_app_config\",
  json=$json\"cwd\"\:\"$LOBSTER_CWD\",
  json=$json\"name\"\:\"$lobster_app_name\",
  json=$json\"title\"\:\"$lobster_app_title\",
  json=$json\"op\"\:\"$lobster_op\",
  json=$json\"route_id\"\:\"$lobster_route_id\",
  json=$json\"route\"\:\"$lobster_route\",
  json=$json\"tpl\"\:\"$lobster_theme_source\",

  #Add in the suggestions
  json=$json\"suggestions\"\:\[
  snippet=''
  for flag in "${lobster_suggestions[@]}"; do
    snippet=$snippet\"$flag\",
  done
  json=$json${snippet%,}\],

  # The args
  json=$json\"args\"\:\[
  snippet=''
  for flag in "${lobster_args[@]}"; do
    snippet=$snippet\"$flag\",
  done
  json=$json${snippet%,}\],  

  # Add in the flags
  json=$json\"flags\"\:\[
  snippet=''
  for flag in "${lobster_flags[@]}"; do
    snippet=$snippet\"$flag\",
  done
  json=$json${snippet%,}\],
  
  # Add in the params
  json=$json\"parameters\"\:\{
  snippet=''
  for param in "${lobster_params[@]}"; do
    if [[ "$param" =~ ^(.*)\=(.*) ]]; then
      snippet=$snippet\"${BASH_REMATCH[1]}\"\:\"${BASH_REMATCH[2]}\",
    fi
  done
  json=$json${snippet%,}\}
  json=$json\},
<<<<<<< HEAD
=======

>>>>>>> release

  #
  #
  # Begin child: instance
  #
  json=$json\"instance\":{
  json=$json\"root\"\:\"$LOBSTER_INSTANCE_ROOT\",
  json=$json\"config\"\:\"$LOBSTER_INSTANCE_ROOT/$lobster_app_config\"
  json=$json\},
  #
  #
  # Begin child: global
  #
  json=$json\"global\":{
  json=$json\"root\"\:\"$HOME\",
  json=$json\"config\"\:\"$HOME/$lobster_app_config\"
  json=$json\}

<<<<<<< HEAD
  #
  #
  # Begin child: instance
  #
  json=$json\"instance\":{
  json=$json\"root\"\:\"$LOBSTER_INSTANCE_ROOT\",
  json=$json\"config\"\:\"$LOBSTER_INSTANCE_ROOT/$lobster_app_config\"
  json=$json\},
  #
  #
  # Begin child: global
  #
  json=$json\"global\":{
  json=$json\"root\"\:\"$HOME\",
  json=$json\"config\"\:\"$HOME/$lobster_app_config\"
  json=$json\}

=======
>>>>>>> release
  # Close out the object
  json=$json\}
  echo $json
}

#
# Trim whitespace from a string
#
# @param string $string
# 
# result=$(lobster_trim arg)
#
function  lobster_trim() {
  echo -e "${1}" | sed -e 's/^[[:space:]]*//' -e 's/[[:space:]]*$//'
}

##
 # Accept a y/n confirmation message or end
 #
 # @param string $1
 #   A question to ask
 # @param string $2
 #   A flag, e.g. noend; which means a n will not exit
 #
 # @return bool
 #   Sets the value of confirm_result
 #
function lobster_confirm() {
  local esc=$lobster_escape_char
  local fore=$lobster_color_confirm
  local prompt="${esc}[${fore}m${1}? [y/N] ${esc}[0m"
  local response

  # The echo -n option suppresses the trailing newline.
  echo -e -n $prompt
  # -n 1 means to read just 1 char
  read -n 1 response && echo
  case $response in
      [yY][eE][sS]|[yY])
          return 0
          ;;
  esac
  return 1
}

##
 # Capture some input (w/optional default)
 #
 # @param string The question to ask.
 # @param string An optional default value.
 #
 # @see $lobster_input_return
 #
 # @code
 #   input=$(lobster_input "First day?" 'Sunday')
 # @endcode
 #
lobster_input_return=''
function lobster_input() {
  local default="$2"
  local esc=$lobster_escape_char
  local fore=$lobster_color_input
  local fore2=$lobster_color_input_suggestion
  local prompt="$1"
  local response
  if [ "$default" ]; then
    prompt="$prompt [${esc}[0m${esc}[${fore2}m$default${esc}[0m${esc}[${fore}m]${esc}[0m"
  fi
  echo -e -n "${esc}[${fore}m${prompt}${esc}[${fore}m?:${esc}[0m "
  read -e input
  lobster_input_return="${input:-$default}"
}

##
 # Echos the current date and time
 #
function lobster_date() {
  echo $(date +"%B %_d, %Y")
}

##
 # Echos the current date and time
 #
function lobster_datetime() {
  echo $(date +"%Y-%m-%dT%H:%M:%S%z")
}

##
 # Echos the current unix timestamp
 #
function lobster_time() {
  echo $(date +"%s")
}

##
 # Determine if a shell function exists
 #
function lobster_function_exists() {
  declare -f -F $1 > /dev/null
  return $?
}

##
 # Access check for routing.
 #
function lobster_access() {
  if ! lobster_function_exists $1; then
    lobster_failed "The required access callback '$1' does not exist."
  fi
  if ! eval ${1}; then
    if [ "$lobster_access_denied" ]; then
      lobster_error "$lobster_access_denied"
    fi
    lobster_failed
  fi
}

##
 # Clears all previously added twig vars.
 #
function lobster_clear_twig_vars() {
  file="$LOBSTER_TMPDIR/twig_vars.csv"
  test -e $file && rm "$file"
  lobster_verbose "Twig vars cleared from $LOBSTER_TMPDIR/twig_vars.csv"
}

##
 # Add a key/value variable to be used by lobster_process_twig()
 #
 # @code
 #   lobster_add_twig_var varName 'value'
 # @endcode
 #
function lobster_add_twig_var() {
  file="$LOBSTER_TMPDIR/twig_vars.csv"
  echo "$1,$2" >> $file
  lobster_verbose "Twig var $1 added to $file"
}

##
 # Process a twig file located at $1 with all vars added via lobster_add_twig_var.
 #
 # @code
 #   replaced=$(lobster_process_twig '/path/to/template.twig')
 # @endcode
 #
function lobster_process_twig() {
  file=$1
  if ! test -e $file; then
    lobster_error "Cannot process non-existent twig file: $file"
  fi
  source="$(cat $file)"
  while IFS='' read -r line || [[ -n "$line" ]]; do
    data=(${line//,/ })
    find="{{ ${data[0]} }}"
    replace="${data[1]}"
    source="${source/$find/$replace}"
  done < "$LOBSTER_TMPDIR/twig_vars.csv"

  echo "$source"
}

function lobster_array_get_shortest_value() {
  local arrayname=${1:?Array name required} varname=${2:-shortest}
  local IFS= string e

  eval "array=( \"\${$arrayname[@]}\" )"
  shortest=${array[0]}
  for e in "${array[@]}"; do
    [[ ${#e} -lt ${#shortest} ]] && shortest=$e
  done
  [[ "$varname" != shortest ]] && eval "$varname=${shortest}"
}

#
# Shift the first element from an array
#
<<<<<<< HEAD
# @param string The name of an array; omit the $, your passing a string of the array name, not the array reference!
=======
# @param string The name of an array; omit the dollar sign, your passing a string of the array name, not the array reference!
>>>>>>> release
#
# @code
#   declare -a my_array=( do re mi )
#   lobster_array_shift my_array
# @endcode
# ... my_array === ( re mi )
function lobster_array_shift() {
  local arrayname=${1:?Array name required}
  eval "$arrayname=( \"\${$arrayname[@]:1}\" )"
}
<<<<<<< HEAD
=======

#
# Sets the route status
#
# @param int $1 Any non zero value means the route failed.
#
function lobster_set_route_status() {
    echo $1 > "$LOBSTER_TMPDIR/route_status"
}

#
#
# @code
#   if [ $(lobster_get_route_status) -eq 0 ]; then...
# @endcode
#
function lobster_get_route_status() {
   status=$(cat "$LOBSTER_TMPDIR/route_status")

   return $status
}
>>>>>>> release
