<?php
namespace NHAzureLogicApps\Application\PostType;

use NHAzureLogicApps\Application\Service\SettingsService\SettingsService;
use NHAzureLogicApps\Application\Service\LogicAppEventService\LogicAppEventService;

if (!defined('ABSPATH'))
{
    exit;
}

abstract class AbstractPostType
{
    /** @var  SettingsService */
    protected $settingsManager;

    /** @var LogicAppEventService */
    protected $logicAppEventManager;

    public function __construct(SettingsService $settingsManager, LogicAppEventService $logicAppEventManager)
    {
        $this->settingsManager = $settingsManager;
        $this->logicAppEventManager = $logicAppEventManager;
    }

    /** @return string */
    public abstract function getName();
    public abstract function init();
}
