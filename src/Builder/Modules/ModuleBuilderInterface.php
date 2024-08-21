<?php

namespace Ephect\WebApp\Builder\Modules;

interface ModuleBuilderInterface
{
    public function describeComponents(array &$list): void;
}