#!/usr/bin/env bash

#
# This file is part of the Alice package.
#
# (c) Nelmio <hello@nelm.io>
#
# For the full copyright and license information, please view the LICENSE
# file that was distributed with this source code.
#

export INFO_COLOR="\e[34m"
export NO_COLOR="\e[0m"

log() {
    local message=$1;
    echo -en "${INFO_COLOR}${message}${NO_COLOR}\n";
}

set -e

log "Core library"
vendor/bin/phpunit -c phpunit.xml.dist

log "Symfony bridge"
rm -rf fixtures/Bridge/Symfony/Application/cache/*

vendor-bin/symfony/bin/phpunit -c phpunit_symfony.xml.dist
