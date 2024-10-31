<?php
namespace NHAzureLogicApps\Application\Event\EventGroup;

use NHAzureLogicApps\Application\Service\ConfigService\ConfigService;

if (!defined('ABSPATH'))
{
    exit;
}

class GeneralEventGroup extends AbstractEventGroup
{
    /** @var  string */
    public function getDisplayName()
    {
        return __("General", ConfigService::TEXT_DOMAIN_NAME);
    }
}
