<?php

namespace AceDatagrid;

use AceDbTools\Factory\DoctrineAwareFactory;
use Zend\ServiceManager\Factory\InvokableFactory;

return [
    'dependencies' => [
        'aliases' => [
            'DatagridManager' => DatagridManager::class,
        ],
        'factories' => [
            DatagridManager::class => DoctrineAwareFactory::class,
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
    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
];
