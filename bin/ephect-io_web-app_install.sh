#!/usr/bin/env sh

cd vendor/ephect-io/web-app
php use install:module "$(pwd)" $1 $2