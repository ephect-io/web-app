@echo off

cd vendor\ephect-io\web-app
php use install:module %cd% %1 %2
