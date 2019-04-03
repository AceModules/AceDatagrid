<?php

namespace Ace\Datagrid;

use Zend\ServiceManager\Factory\InvokableFactory;

class ConfigProvider
{
    /**
     * @return array
     */
    public function __invoke()
    {
        return [
            'dependencies' => $this->getDependencyConfig(),
            'view_helpers' => $this->getViewHelperConfig(),
        ];
    }

    /**
     * @return array
     */
    public function getDependencyConfig()
    {
        return [
            'aliases' => [
                'DatagridManager' => DatagridManager::class,
            ],
            'factories' => [
                DatagridManager::class => InvokableFactory::class,
            ],
        ];
    }

    /**
     * @return array
     */
    public function getViewHelperConfig()
    {
        return [
            'aliases' => [
                'sortControl' => Helper\SortControl::class,
                'SortControl' => Helper\SortControl::class,
            ],
            'factories' => [
                Helper\SortControl::class => InvokableFactory::class,
            ],
        ];
    }
}
