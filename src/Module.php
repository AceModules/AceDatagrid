<?php

namespace AceDatagrid;

class Module
{
    public function getModuleDependencies()
    {
        return [
            'DoctrineORMModule',
            'AceTools',
        ];
    }

    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }
}
