#!/usr/bin/env bash
SRC_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
CONFIG_FILE=$SRC_DIR/lint.config.php

pushd $SRC_DIR/../src
php ./vendor/bin/php-cs-fixer fix --config=$CONFIG_FILE $@
popd
