<?php
namespace NHAzureLogicApps\Application\Event\EventGroup;

use NHAzureLogicApps\Application\Service\ConfigService\ConfigService;

if (!defined('ABSPATH'))
{
    exit;
}

class WooCommerceEventGroup extends AbstractEventGroup
{
    /** @var  string */
    public function getDisplayName()
    {
        return __("WooCommerce", ConfigService::TEXT_DOMAIN_NAME);
    }
}
