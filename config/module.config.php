<?php

namespace AceDatagrid;

use Zend\ServiceManager\Factory\InvokableFactory;

return [
    'dependencies' => [
        'aliases' => [
            'DatagridManager' => DatagridManager::class,
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view',
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
