#!/usr/bin/env bash
x(){ echo "No script dir" >&2;return 1 2>/dev/null||exit 1;};if [ -n "${BASH_VERSION:-}" ];then s="${BASH_SOURCE[0]}";elif [ -n "${ZSH_VERSION:-}" ];then eval 's="${(%):-%x}"';else x;fi;[ -n "$s" ]||x;while [ -h "$s" ];do d="$(cd -P "$(dirname "$s")"&&pwd)"||x;s="$(readlink "$s")"||x;[[ $s != /* ]]&&s="$d/$s";done;__DIR__="$(cd -P "$(dirname "$s")"&&pwd)"||x;unset s d;unset -f x

# ========= Begin Configuration =========

# The directory path to the phpswap package, it must contain phpswap_execute.php
phpswap_directory=$(cd "$__DIR__/../vendor/aklump/phpswap/" 2>/dev/null && pwd)

# The PHP versions to run the test suite against, in order.
PHP_VERSIONS=(8.1 8.2 8.3 8.4)

# The command to execute under each PHP version, relative to CWD.
PHPUNIT='./vendor/bin/phpunit -c tests_phpunit/phpunit.xml'
# ========= End Configuration =========

function failed() {
  local message="$1"

  NO_FORMAT="\033[0m"
  F_BOLD="\033[1m"
  C_YELLOW="\033[48;5;226m"
 echo -e "${F_BOLD}${C_YELLOW}$message${NO_FORMAT}"
}

# ========= Validation =========
error=false
message="                                                                        "
if [[ -z "$phpswap_directory" ]]; then
  error=true
  message="$message\n     You seem to be missing this: https://github.com/aklump/phpswap     "
  message="$message\n     Try running: composer require --dev aklump/phpswap                 "
fi
if ! [ -e ./vendor/bin/phpunit ]; then
  error=true
  message="$message\n     You seem to be missing this: PHPUnit                               "
  message="$message\n     Try running: composer require --dev phpunit/phpunit                "
fi
if [[ "$error" == true ]]; then
    message="$message\n                                                                        "
    failed "$message"
    exit 1
fi

# ========= Execute PHPUnit =========
verbose=''
if [[ "${*}" == *'-v'* ]]; then
  verbose='-v'
fi
for version in "${PHP_VERSIONS[@]}"; do
  ! "$phpswap_directory/phpswap_execute.php" supports "$version" && failed "     PHP $version is not available in this environment.     " && continue
  ! "$phpswap_directory/phpswap_execute.php" using "$version" $verbose "$PHPUNIT" && failed "     PHP $version tests failed.     " && exit 1
done
