#
# @file Run `bump build` in all custom child extensions (custom module, custom theme, etc.)
#

# ========= Begin configuration =========
export WEB_PACKAGE_PHP=/usr/local/opt/php@8.1/bin/php
bump="$HOME/Code/Packages/cli/web-package/app/web-package"
declare -a children=("./web/modules/custom/my_module" "./web/themes/custom/my_theme")
# ========= End configuration =========

[ "$bump" ] || exit 255
echo "BUILDING CHILDREN"
for path in "${children[@]}" ; do
  echo "├── 🧩 $(basename $path)"
  if [ -d "$path/.web_package" ]; then
    (cd "$path" && $bump build) || exit 1
  fi
done

## Do anything else custom here, e.g. `./bin/build.css.sh`
#(cd "./web/themes/custom/my_other_theme" && ./bin/build_css.sh && echo "CSS Built") || exit 1
