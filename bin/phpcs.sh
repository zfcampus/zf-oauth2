#!/bin/sh
SCRIPT_PATH=$(php -r "echo readlink('${0}');")
WORKDIR=$(php -r "echo dirname(dirname('${SCRIPT_PATH}'));")

PHP_VERSION=$(php -v | grep '^PHP [[:digit:]].[[:digit:]]' | cut -d ' ' -f2)
IS_PHP_5_3=$(php -r "echo version_compare('${PHP_VERSION}', '5.4.0');")

ARGS="--standard=PSR2 --ignore=test/Bootstrap.php"
if [ "-1" -eq "$IS_PHP_5_3" ];then
    ARGS="$ARGS,src/Adapter/BcryptTrait.php"
fi

COMMAND="./vendor/bin/phpcs $ARGS src test"
(cd $WORKDIR ; exec ${COMMAND})
