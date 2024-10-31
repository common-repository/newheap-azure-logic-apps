<?php
namespace NHAzureLogicApps\Application\Event;

use NHAzureLogicApps\Application\Helper\TypeHelper;
use NHAzureLogicApps\Application\Model\IntegrationEventModel;
use NHAzureLogicApps\Application\Model\IntegrationSettingsModel;
use NHAzureLogicApps\Application\Service\ConfigService\ConfigService;
use NHAzureLogicApps\Application\Service\SettingsService\SettingsService;
use NHAzureLogicApps\Application\UserInterface\PostTypeSelectorUISettingElement;

if (!defined('ABSPATH'))
{
    exit;
}

abstract class AbstractWooCommerceEvent extends AbstractEvent
{
    /** @return bool */
    public function canActivate()
    {
        return (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins'))));
    }
}
