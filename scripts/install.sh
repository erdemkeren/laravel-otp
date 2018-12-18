#!/usr/bin/env bash

SOURCE="${BASH_SOURCE[0]}"
DIR="$( cd -P "$( dirname "$SOURCE" )/.." && pwd )"

/usr/local/bin/composer install --no-interaction --prefer-dist --no-scripts
/usr/local/bin/composer install --no-interaction --prefer-dist

chmod +x $DIR/scripts/post-install.sh
sh $DIR/scripts/post-install.sh
