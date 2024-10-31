<?php

namespace NHAzureLogicApps\Application\Service\LogicAppService;

if (!defined('ABSPATH'))
{
    exit;
}

use NHAzureLogicApps\Application\Service\IService;
use NHAzureLogicApps\Application\Service\LogicAppEventService\LogicAppEventService;

class LogicAppService implements IService
{
    /** @var  LogicAppEventService */
    public $eventManager;

    public function __construct(LogicAppEventService $eventManager)
    {
        $this->eventManager = $eventManager;
    }
}
