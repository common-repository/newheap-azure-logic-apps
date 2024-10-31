<?php
namespace NHAzureLogicApps\Application\Event\EventGroup;

use NHAzureLogicApps\Application\Service\ConfigService\ConfigService;

if (!defined('ABSPATH'))
{
    exit;
}

class AttachmentEventGroup extends AbstractEventGroup
{
    /** @var  string */
    public function getDisplayName()
    {
        return __("Attachment", ConfigService::TEXT_DOMAIN_NAME);
    }
}
