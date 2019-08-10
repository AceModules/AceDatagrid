<?php

namespace AceDatagrid;

use Zend\ServiceManager\Factory\InvokableFactory;

return [
    'dependencies' => [
        'aliases' => [
            'DatagridManager' => DatagridManager::class,
        ],
        'factories' => [
            DatagridManager::class => InvokableFactory::class,
        ],
    ],
    'view_helpers' => [
        'aliases' => [
            'sortControl' => Helper\SortControl::class,
            'SortControl' => Helper\SortControl::class,
        ],
        'factories' => [
            Helper\SortControl::class => InvokableFactory::class,
        ],
    ],
];
