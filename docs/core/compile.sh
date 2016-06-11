#!/bin/bash

SOURCE="${BASH_SOURCE[0]}"
while [ -h "$SOURCE" ]; do # resolve $SOURCE until the file is no longer a symlink
  DIR="$( cd -P "$( dirname "$SOURCE" )" && pwd )"
  SOURCE="$(readlink "$SOURCE")"
  [[ $SOURCE != /* ]] && SOURCE="$DIR/$SOURCE" # if $SOURCE was a relative symlink, we need to resolve it relative to the path where the symlink file was located
done
CORE="$( cd -P "$( dirname "$SOURCE" )" && pwd )"

#
# Installs the .gitignore file
#
function install_gitignore() {
  local file="$CORE/.gitignore"
  if [ -f "$file" ]; then
    rm "$file"
    echo '/*' > "$file"
    echo '!"$file"' >> "$file"
    echo '!core-config.sh' >> "$file"
    echo '!source' >> "$file"
    echo '!source/*' >> "$file"
  fi
}

#
# Return the full path to the version file
#
#
function get_version_file() {
  local file
  file=${docs_version_file[0]}
  if [[ "${file:0:1}" == '/' ]]; then
    file="$file";
  else
    file="$docs_root_dir/$file";
  fi

  # file=($(ls $file))
  file=($(find . -name $file))
  echo ${file[0]}
}

# Usage
# result=$(func_name arg)

#
# Call the version hook and return the version string
get_version_return=''
function get_version() {
  local hook=$(realpath "$CORE/${docs_version_hook[0]}")
  if [ "$hook" ] && [ -f "$hook" ]; then
    get_version_return=$(do_hook_file "$hook")
    if [[ "$get_version_return" ]]; then
      echo "`tput setaf 2`Version ($get_version_return) obtained from $hook.`tput op`"
    fi
  fi

  if [[ ! "$get_version_return" ]]; then
    get_version_return='1.0'
    echo "`tput setaf 3`Using default version $get_version_return.`tput op`"
  fi
}

#
# Return the realpath
# 
# @param string $path
# 
function realpath() {
  local path=$($docs_php "$CORE/realpath.php" "$1")
  echo $path
}

#
# Execute a .sh or .php hook file
# 
# @param string $file
# 
function do_hook_file() {
  local file=$1
  if [[ ${file##*.} == 'php' ]]; then
    cmd="$docs_php"
  elif [[ ${file##*.} == 'sh' ]]; then
    cmd=$docs_bash
  fi

  if [[ ! -f $file ]]; then
    echo "`tput setaf 1`Hook file not found: $file`tput op`"
  elif [[ "$cmd" ]]; then
    source=$(realpath "$docs_root_dir/$docs_source_dir")
    $cmd "$file" "$source" "$CORE" "$docs_version_file" "$docs_root_dir"
    # echo $($cmd "$file" "$source" "$CORE" "$docs_root_dir/$docs_version_file")
  fi
}

#
# Do the pre-compile hook
# 
function do_pre_hooks() {
  local hook

  # Hack to fix color, no time to figure out 2015-11-14T13:58, aklump
  echo "`tty -s && tput setaf 6``tty -s && tput op`"

  echo "Running pre-compile hooks..."
  for hook in ${docs_pre_hooks[@]}; do
    hook=$(realpath "$docs_hooks_dir/$hook")
    echo "`tty -s && tput setaf 2`Hook file: $hook`tty -s && tput op`"
    echo $(do_hook_file $hook)
  done

  # Internal pre hooks should always come after the user-supplied
  do_todos
}

#
# Do the post-compile hook
# 
function do_post_hooks() {
  local hook
  echo "Running post-compile hooks..."
  for hook in ${docs_post_hooks[@]}; do
    hook=$(realpath "$docs_hooks_dir/$hook")
    echo "`tty -s && tput setaf 2`Hook file: $hook`tty -s && tput op`"
    echo $(do_hook_file $hook)
  done

  # Internal post hooks should always come after the user-supplied
  # Remove the _tasklist.md file
  ! test -e "$docs_source_dir/$docs_todos" || rm "$docs_source_dir/$docs_todos"
}

#
# Do the todo item gathering
# 
function do_todos() {
  if [[ "$docs_todos" ]]; then
    local global="$docs_source_dir/$docs_todos"
    echo "Aggregating todo items into $global..."
    
    if [[ ! -f "$global" ]]; then
      touch "$global";
    fi

    for file in $(find $docs_source_dir -type f -iname "*.md"); do
      if [ "$file" != "$global" ]; then
        echo "Scanning $file for todo items."
        # Send a single file over for processing todos via php
        $docs_php "$CORE/todos.php" "$file" "$global"
      fi
    done
  fi
  echo "`tput setaf 2`Tasklist complete.`tput op`"
  echo
}

##
 # End execution with a message
 #
 # @param string $1
 #   A message to display
 #
function end() {
  echo
  echo $1
  echo
  exit;
}

##
 # Checks to see if a file was generated and displays a message
 #
 # @param string $1
 #   filename to check
 #
 # @return NULL
 #   Sets the value of global $func_name_return
 #
function _check_file() {
  if [ -f "$1" ]
  then
    echo "`tput setaf 2`$1 has been generated.`tput op`"
  else
    echo "`tput setaf 1`Failed generating $1`tput op`"
  fi
}

##
 # Determine if an output format is enabled
 #
 # @param string $1
 #   The output format to check e.g., 'html'
 #
 # @return 0|1
 #
function is_disabled() {
  local seeking=$1
  local in=1
  for element in "${docs_disabled[@]}"; do
   if [[ $element == $seeking ]]; then
     in=0
     break
   fi
  done
  return $in
}


##
 # Load the configuration file
 #
 # Lines that begin with [ or # will be ignored
 # Format: Name = "Value"
 # Value does not need wrapping quotes if no spaces
 # File MUST HAVE an EOL char!
 #
function load_config() {
  if [ ! -f core-config.sh ]; then
    cp "$CORE/config-example" core-config.sh
    installing=1
  fi

  # defaults
  docs_disabled="doxygene"
  docs_php=$(which php)
  docs_bash=$(which bash)
  docs_lynx=$(which lynx)
  docs_source_dir='source'
  docs_root_dir=$(realpath "$CORE/..")
  docs_source_path=`cd "$docs_root_dir/$docs_source_dir";pwd`
  docs_kit_dir='kit'
  docs_doxygene_dir='doxygene'
  docs_website_dir='public_html'
  docs_html_dir='html'
  docs_mediawiki_dir='mediawiki'
  docs_text_dir='text'
  docs_drupal_dir='advanced_help'
  docs_tmp_dir="$CORE/tmp"
  docs_todos="_tasklist.md"
  docs_version_hook='version_hook.php'
  docs_pre_hooks=''
  docs_post_hooks=''
  docs_autogenerated_outline='auto-generated.outline.json'

  #Determine which is our tpl dir
  docs_tpl_dir='core/tpl'
  if [ -d 'tpl' ]; then
    docs_tpl_dir='tpl'
  fi

  #
  # 
  # Discover the outline file
  #   
  if test -e "$docs_source_dir/$docs_autogenerated_outline"; then
    rm "$docs_source_dir/$docs_autogenerated_outline"
  fi

  # We're looking ultimately for outline.json
  docs_outline_file=$(find $docs_source_path -name outline.json)
  
  # If it's not there we'll try to generate from a .ini file.
  if [[ ! "$docs_outline_file" ]]; then
    # Ini file
    docs_help_ini=$(find $docs_source_path -name *.ini)

    if [[ "$docs_help_ini" ]]; then
      # Convert this to $docs_autogenerated_outline
      $docs_php "$CORE/includes/ini_to_json.php" "$docs_help_ini" "$docs_source_path" "$docs_source_path/$docs_autogenerated_outline"
      docs_outline_file="$docs_source_path/$docs_autogenerated_outline"

      echo "`tty -s && tput setaf 3`You are using the older .ini version of the configutation; consider changing to outline.json, a template has been created for you as '$docs_autogenerated_outline'.  See README for more info.`tty -s && tput op`"
    fi
  fi

  # If we still don't have it then we'll generate from the file structure.
  if [[ ! "$docs_outline_file" ]]; then
    # Create $docs_autogenerated_outline from the file contents
    $docs_php "$CORE/includes/files_to_json.php" "$docs_source_path" "$docs_source_dir/$docs_autogenerated_outline"

    docs_outline_file="$docs_source_path/$docs_autogenerated_outline"
  fi
  
  # custom
  parse_config core-config.sh

  # 
  # put anything that comes AFTER parsing config file below this line
  # 

  docs_text_enabled=1
  if ! lynx_loc="$(type -p "$docs_lynx")" || [ -z "$lynx_loc" ]; then
    echo "`tput setaf 3`Lynx not found; .txt files will not be created.`tput op`"
    docs_text_enabled=0
  fi

  # Below this line, anything that is dependent upon $docs_root_dir which can
  # be overridden by the config file
  if [[ ! "$docs_hooks_dir" ]]; then
    docs_hooks_dir="$docs_root_dir/hooks"
  fi

  if [[ ! "$docs_version_file" ]]; then
    docs_version_file="$docs_root_dir/*.info"
  fi
  docs_version_file="$(get_version_file)"
  
  docs_disabled=($docs_disabled)
}

##
 # Parse a config file
 #
 # @param string $1
 #   The filepath of the config file
 #
function parse_config() {
  if [ -f $1 ]
  then
    while read line; do
      if [[ "$line" =~ ^[^#[]+ ]]; then
        name=${line% =*}
        value=${line##*= }
        if [[ "$name" ]]
        then
          eval docs_$name=$value
        fi
      fi
    done < $1
  fi
}

# Pull in config vars
installing=0
load_config

do_pre_hooks

# These dirs need to be created
declare -a dirs=("$docs_html_dir" "$docs_mediawiki_dir" "$docs_website_dir" "$docs_text_dir" "$docs_drupal_dir" "$docs_kit_dir" "$docs_tmp_dir" "$docs_source_dir" "$docs_doxygene_dir");

# These dirs need to be emptied before we start
declare -a dirs_to_empty=("$docs_html_dir" "$docs_mediawiki_dir" "$docs_website_dir" "$docs_text_dir" "$docs_drupal_dir" "$docs_kit_dir" "$docs_tmp_dir");

# These dirs need to be removed at that end
declare -a dirs_to_delete=("$docs_tmp_dir" "$docs_kit_dir")

# Add all enabled formats to dir array
for format in "${docs_disabled[@]}"; do
  if is_disabled "$format"; then
    dir=docs_${format}_dir
    dir=$(eval "echo \$${dir}")
    dirs_to_delete=("${dirs_to_delete[@]}" "$dir")
  fi
done

# If source does not exist then copy core example
if [ ! -d "$docs_source_dir" ]; then
  rsync -av "$CORE/patterns/source/" $docs_source_dir/
fi

# Empty dirs
for var in "${dirs_to_empty[@]}"
do
  if [ "$var" ] && [ -d "$var" ]; then
    rm -rf $var;
  fi
done

# Assert dir exists
for var in "${dirs[@]}"
do
  if [ ! "$var" ]; then
    end "`tput setaf 1`Bad Config $var`tput op`"
    return
  fi
  if [ ! -d "$var" ]; then
      mkdir $var
  fi
done

# Copy the patterns into place to be ready to receive files
rsync -av "$CORE/patterns/public_html/" "$docs_root_dir/$docs_website_dir"
rsync -av "$CORE/patterns/html/" "$docs_root_dir/$docs_html_dir"
rsync -av "$CORE/patterns/mediawiki/" "$docs_root_dir/$docs_mediawiki_dir"
rsync -av "$CORE/patterns/text/" "$docs_root_dir/$docs_text_dir"
rsync -av "$CORE/patterns/advanced_help/" "$docs_root_dir/$docs_drupal_dir"
rsync -av "$CORE/patterns/doxygene/" "$docs_root_dir/$docs_doxygene_dir"

# Delete the text directory if no lynx
if [ "$docs_text_enabled" -eq 0 ]; then
  rmdir $docs_text_dir
fi

# Installation steps
if [ $installing -eq 1 ]; then
  echo "`tput setaf 3`Installing Loft Docs...`tput op`"

  install_gitignore

  ## Setup the codekit file with the correct kit output
  #codekit_file="codekit-config.json"
  #if [ -f "$codekit_file" ]; then
  #  rm "$codekit_file"
  #fi
  #echo "{"projectSettings" : {"kitAutoOutputPathRelativePath" : "..\/$docs_html_dir"}}" > "$codekit_file"
fi

get_version

# Build index.html from home.php
echo '' > "$docs_kit_dir/index.kit"
$docs_php "$CORE/includes/page_vars.php" "$docs_outline_file" "index" "$get_version_return" >> "$docs_kit_dir/index.kit"
$docs_php "$CORE/includes/home.php" "$docs_outline_file" "$docs_tpl_dir" >> "$docs_kit_dir/index.kit"
_check_file "$docs_kit_dir/index.kit"

# Copy over files in the tmp directory, but compile anything with a .md
# extension as it goes over; this is our baseline html that we will further
# process for the intended audience.
for file in $docs_source_dir/*; do
  if [ -f "$file" ]; then
    basename=${file##*/}

    # Process .md files and output as .html

    if echo "$file" | grep -q '.md$'; then
      basename=$(echo $basename | sed 's/\.md$//g').html
      
      # This uses the perl compiler
      $docs_php "$CORE/markdown.php" "$file" "$docs_tmp_dir/$basename"

    # Css files pass through to the website and html dir
    elif echo "$file" | grep -q '.css$'; then
      cp $file $docs_html_dir/$basename
      _check_file "$docs_html_dir/$basename"
      cp $file $docs_website_dir/$basename
      _check_file "$docs_website_dir/$basename"

    # Html files pass through to drupal, website and html
    elif echo "$file" | grep -q '.html$'; then
      cp $file $docs_drupal_dir/$basename
      _check_file "$docs_drupal_dir/$basename"
      cp $file $docs_website_dir/$basename
      _check_file "$docs_website_dir/$basename"
      cp $file $docs_html_dir/$basename
      _check_file "$docs_html_dir/$basename"

    # text files pass through to drupal, website and txt
    elif echo "$file" | grep -q '.txt$'; then
      cp $file $docs_drupal_dir/$basename
      _check_file "$docs_drupal_dir/$basename"
      cp $file $docs_website_dir/$basename
      _check_file "$docs_website_dir/$basename"
      cp $file $docs_text_dir/$basename
      _check_file "$docs_text_dir/$basename"

    # Rename the .ini file; we should only ever have one
    elif echo "$file" | grep -q '.ini$' && [ ! -f "$docs_drupal_dir/$docs_drupal_module.$basename" ]; then
      cp $file "$docs_drupal_dir/$docs_drupal_module.$basename"
      _check_file "$docs_drupal_dir/$docs_drupal_module.$basename"

    # All files types pass through to drupal and webpage
    else
      cp $file $docs_drupal_dir/$basename
      _check_file "$docs_drupal_dir/$basename"
      cp $file $docs_website_dir/$basename
      _check_file "$docs_website_dir/$basename"
    fi

  elif [ -d "$file" ]; then
    basename=${file##*/}
    echo "Copying dir $basename..."
    rsync -rv $docs_source_dir/$basename/ $docs_drupal_dir/$basename/
    rsync -rv $docs_source_dir/$basename/ $docs_website_dir/$basename/
    rsync -rv $docs_source_dir/$basename/ $docs_html_dir/$basename/
  fi
done

# Iterate over all html files and send to CodeKit; then iterate over all html
# files and send to drupal and website
for file in $docs_tmp_dir/*.html; do
  if [ -f "$file" ]; then
    basename=${file##*/}
    basename=$(echo $basename | sed 's/\.html$//g')
    html_file="$basename.html"
    kit_file="$basename.kit"
    tmp_file="$basename.kit.txt"
    txt_file="$basename.txt"

    # Send over html snippet files to html
    cp "$file" "$docs_html_dir/$html_file"
    _check_file "$docs_html_dir/$html_file"

    # Convert to plaintext
    if [[ "$docs_text_dir" ]] && lynx_loc="$(type -p "$docs_lynx")" && [ ! -z "$lynx_loc" ]; then
      textname=`basename $file html`
      textname=${textname}txt
      $docs_lynx --dump $file > "$docs_text_dir/${textname}"
      _check_file "$docs_text_dir/${textname}"
    fi

    # Process each file for advanced help markup
    if [[ "$docs_drupal_dir" ]]; then
      $docs_php "$CORE/advanced_help.php" "$docs_tmp_dir/$html_file" "$docs_drupal_module" > "$docs_drupal_dir/$html_file"
    fi

    # Convert to mediawiki
    if [[ "$docs_mediawiki_dir" ]]; then
      $docs_php "$CORE/mediawiki.php"  "$docs_tmp_dir/$html_file" > "$docs_mediawiki_dir/$txt_file"
    fi

    # Convert to offline .html
    echo '' > "$docs_kit_dir/$tmp_file"
    $docs_php "$CORE/includes/page_vars.php" "$docs_outline_file" "$basename"  "$get_version_return" >> "$docs_kit_dir/$tmp_file"
    echo '<!-- @include ../'$docs_tpl_dir'/header.kit -->' >> "$docs_kit_dir/$tmp_file"
    cat $file >> "$docs_kit_dir/$tmp_file"
    echo '<!-- @include ../'$docs_tpl_dir'/footer.kit -->' >> "$docs_kit_dir/$tmp_file"

    $docs_php "$CORE/iframes.php" "$docs_kit_dir/$tmp_file" "$docs_credentials" > "$docs_kit_dir/$kit_file"
    rm "$docs_kit_dir/$tmp_file"
    _check_file "$docs_kit_dir/$kit_file"
  fi
done

# Get all stylesheets
for file in $docs_tpl_dir/*.css; do
  if [ -f "$file" ]; then
    basename=${file##*/}
    cp $file $docs_website_dir/$basename
    _check_file "$docs_website_dir/$basename"
  fi
done

# Drupal likes to have a README.txt file in the module root directory; this
# little step facilitates that need. It also supports other README type
# files.
if [ "$docs_README" ]; then
  destinations=($docs_README)
  for destination in "${destinations[@]}"; do
    destination=${docs_source_dir}/${destination}
    readme_file=${destination##*/}
    readme_dir=${destination%/*}
    if echo "$readme_file" | grep -q '.txt$'; then
      readme_source="$docs_text_dir/$readme_file"
    elif echo "$readme_file" | grep -q '.md$'; then
      readme_source="$docs_source_dir/$readme_file"
    fi
    if [ -d "$readme_dir" ]; then
      cp "$readme_source" "$destination"
      _check_file "$destination"
    fi
  done
fi

# Now process our CodeKit directory and produce our website
$docs_php "$CORE/webpage.php" "$docs_root_dir/$docs_kit_dir" "$docs_root_dir/$docs_website_dir" "$docs_outline_file" "$CORE"

# Provide search support
$docs_php "$CORE/includes/search.inc" "$docs_outline_file" "$CORE" "$docs_root_dir" "$docs_root_dir/$docs_website_dir"

# Doxygene implementation
echo 'Not yet implemented' > "$docs_doxygene_dir/README.md"

# Cleanup dirs that are not enabled or were temp
for var in "${dirs_to_delete[@]}"; do
  if [ "$var" ] && [ -d "$var" ]; then
    rm -rf $var;
  fi
done

do_post_hooks
