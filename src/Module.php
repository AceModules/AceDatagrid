<?php

namespace Ace\Datagrid;

use Zend\ModuleManager\ModuleManager;

class Module
{
    /**
     * @return array
     */
    public function getConfig()
    {
        $provider = new ConfigProvider();
        return [
            'service_manager' => $provider->getDependencyConfig(),
            'view_helpers'    => $provider->getViewHelperConfig(),
        ];
    }

    public function init(ModuleManager $moduleManager)
    {
        $moduleManager->loadModule('TwbBundle');
    }
}
