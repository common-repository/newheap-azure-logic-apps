<?php
namespace NHAzureLogicApps\Application\Event\EventGroup;

use NHAzureLogicApps\Application\Service\ConfigService\ConfigService;

if (!defined('ABSPATH'))
{
    exit;
}

class UsersEventGroup extends AbstractEventGroup
{
    /** @var  string */
    public function getDisplayName()
    {
        return __("Users", ConfigService::TEXT_DOMAIN_NAME);
    }
}
