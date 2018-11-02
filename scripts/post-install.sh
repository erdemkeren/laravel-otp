#!/usr/bin/env bash

CURRPHP=$(which php)
SOURCE="${BASH_SOURCE[0]}"
DIR="$( cd -P "$( dirname "$SOURCE" )/.." && pwd )"

# Make sure the php-cs-fixer package is installed. Otherwise, install it.
if ! [ -x ./scripts/php-cs-fixer/php-cs-fixer ]
then
    echo "php-cs-fixer package is not detected in your scripts folder."
    echo ""
    echo "Installing php-cs-fixer package."
    mkdir ./scripts/php-cs-fixer
    echo ""
    wget -nv http://cs.sensiolabs.org/download/php-cs-fixer-v2.phar -O ./scripts/php-cs-fixer/php-cs-fixer
    echo ""
    echo "Set executable flag for the binary."
    chmod +x ./scripts/php-cs-fixer/php-cs-fixer
    echo ""
    echo "php-cs-fixer package has been successfully installed."
fi

# Copy the pre-commit hook to git hooks directory.
# Also make sure that it is executable.
cp $DIR/contrib/pre-commit $DIR/.git/hooks/pre-commit
chmod +x $DIR/.git/hooks/pre-commit

# Add the config template to the project.
git config commit.template $DIR/contrib/commit-template

# Copy the commit-msg hook to git hooks directory.
# Also make sure that it is executable.
cp $DIR/contrib/commit-msg $DIR/.git/hooks/commit-msg
chmod +x $DIR/.git/hooks/commit-msg
