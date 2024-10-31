<?php
namespace NHAzureLogicApps\Application\Event\EventGroup;

use NHAzureLogicApps\Application\Service\ConfigService\ConfigService;

if (!defined('ABSPATH'))
{
    exit;
}

class PostsEventGroup extends AbstractEventGroup
{
    /** @var  string */
    public function getDisplayName()
    {
        return __("Posts", ConfigService::TEXT_DOMAIN_NAME);
    }
}
