#!/usr/bin/env bash
! [ -d ./knowledge/vendor ] && echo "cd knowledge && composer install" && exit 1
php ./knowledge/vendor/aklump/knowledge/bin/book.php bind ./knowledge
