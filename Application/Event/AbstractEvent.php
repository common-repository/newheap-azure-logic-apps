<?php
namespace NHAzureLogicApps\Application\Event;

use NHAzureLogicApps\Application\Event\EventCategory\GeneralEventCategory;
use NHAzureLogicApps\Application\Event\EventGroup\GeneralEventGroup;
use NHAzureLogicApps\Application\Helper\TypeHelper;
use NHAzureLogicApps\Application\Model\HttpResponseModel;
use NHAzureLogicApps\Application\Model\IntegrationEventModel;
use NHAzureLogicApps\Application\Model\IntegrationSettingsModel;
use NHAzureLogicApps\Application\Model\MessageModel;
use NHAzureLogicApps\Application\Service\ConfigService\ConfigService;
use NHAzureLogicApps\Application\Service\LogicAppEventService\LogicAppEventService;
use NHAzureLogicApps\Application\Service\SettingsService\SettingsService;

if (!defined('ABSPATH'))
{
    exit;
}

abstract class AbstractEvent
{
    /** @var IntegrationSettingsModel[] */
    private $integrations;

    /** @var LogicAppEventService  */
    protected $logicAppEventManager;

    /** @var SettingsService */
    protected $settingsManager;

    /** @var ConfigService */
    protected $configManager;

    public function __construct(
        LogicAppEventService $logicAppEventManager,
        SettingsService $settingsManager,
        ConfigService $configManager
    )
    {
        $this->logicAppEventManager = $logicAppEventManager;
        $this->settingsManager = $settingsManager;
        $this->configManager = $configManager;
        $this->integrations = [];
    }

    /** @return string */
    public abstract function getName();

    /** @return string */
    public abstract function getDescription();

    /** @return string */
    public abstract function getAuthorDisplayName();

    /** @return string */
    public abstract function getAuthorContactUrl();

    /** @return string */
    public abstract function getDefaultMessage();

    public abstract function register();

    /**
     * @param mixed $index
     * @param IntegrationEventModel $eventSettings
     * @return string
     */
    public function getSettingsUi($index, $eventSettings)
    {
        return '';
    }

    /**
     * @param int $postId
     * @param array $rawPost
     * @param IntegrationSettingsModel $postedSettings
     * @param array $postedEvent
     * @param array &$eventSettings
     */
    public function saveSettings($postId, $rawPost, $postedSettings, $postedEvent, &$eventSettings)
    {
    }

    /** @return string */
    public function getGroup()
    {
        return GeneralEventGroup::class;
    }

    /** @return string */
    public function getCategory()
    {
        return GeneralEventCategory::class;
    }

    /** @return bool */
    public function canActivate()
    {
        //Checks should be added here. For example, I want to create events for WooCommerce, then i should here return if Woocommerce is installed and active on this site.
        return true;
    }

    /**
     * @param bool $forceReload
     * @return IntegrationSettingsModel[]
     */
    public function getIntegrations($forceReload = false)
    {
        if(empty($this->integrations) || $forceReload)
        {
            $this->integrations = $this->settingsManager->getIntegrationsByActiveEvent(TypeHelper::getCleanClassNameString(get_class($this)));
        }

        return $this->integrations;
    }

    /**
     * @return bool
     */
    public function hasIntegrations()
    {
        return !empty($this->getIntegrations());
    }

    /**
     * @param IntegrationSettingsModel $integrationSettingsModel
     * @return \NHAzureLogicApps\Application\Model\IntegrationEventModel
     */
    public function getSettings(IntegrationSettingsModel $integrationSettingsModel)
    {
        return $this->settingsManager->getIntegrationEvent($integrationSettingsModel->wpPostId, get_class($this));
    }

    /**
     * @param MessageModel $messageRequest
     * @return bool
     */
    protected function isValidMessage(MessageModel $messageRequest)
    {
        //TODO: validate the message
        return true;
    }

    /**
     * @param IntegrationSettingsModel $integrationSettingsModel
     * @return null|string
     */
    public function getMessage($integrationSettingsModel)
    {
        $message = null;

        if($integrationSettingsModel instanceof IntegrationSettingsModel)
        {
            $integrationEvents = $this->settingsManager->getIntegrationEvents($integrationSettingsModel->wpPostId);
            $integrationEvent = null;

            foreach($integrationEvents as $possibleIntegrationEvent)
            {
                if(TypeHelper::getCleanClassNameString($possibleIntegrationEvent->className) === TypeHelper::getCleanClassNameString(get_class($this)))
                {
                    $message = $possibleIntegrationEvent->message;
                }
            }
        }

        if(empty($message))
        {
            $message = $this->getDefaultMessage();
        }

        return $message;
    }
    
    /**
     * @param MessageModel $messageModel
     * @return \NHAzureLogicApps\Application\Model\HttpResponseModel
     */
    public function dispatch(MessageModel $messageModel)
    {
        $defaultResponseModel = new HttpResponseModel();
        $defaultResponseModel->statusCode = 500;
        $defaultResponseModel->response = "can_active_false";

        if(!$this->canActivate())
        {
            return $defaultResponseModel;
        }

        $messageModel = apply_filters('nhala_register_event_before_dispatch', $messageModel, $this);

        //TODO: validate the message
        //TODO: on error response we might want to build in e-mail notification to site owner to notify failure.
        return $this->logicAppEventManager->dispatch($messageModel);
    }
}
