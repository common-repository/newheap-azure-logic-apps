<?php

namespace NHAzureLogicApps\Application\Service\LogicAppEventService;

if (!defined('ABSPATH'))
{
    exit;
}

use NHAzureLogicApps\Application\Event\AbstractEvent;
use NHAzureLogicApps\Application\Event\EventCategory\AbstractEventCategory;
use NHAzureLogicApps\Application\Event\EventGroup\AbstractEventGroup;
use NHAzureLogicApps\Application\Helper\TypeHelper;
use NHAzureLogicApps\Application\Model\MessageModel;
use NHAzureLogicApps\Application\Service\ConfigService\ConfigService;
use NHAzureLogicApps\Application\Service\HttpService\HttpService;
use NHAzureLogicApps\Application\Service\IService;
use NHAzureLogicApps\Application\Service\SettingsService\SettingsService;

class LogicAppEventService implements IService
{
    /** @var  HttpService */
    private $httpManager;

    /** @var SettingsService */
    private $settingsManager;

    /** @var  ConfigService */
    private $configManager;

    /** @var AbstractEventGroup[] */
    private $eventGroups;

    public function __construct(SettingsService $settingsManager, HttpService $httpManager, ConfigService $configManager)
    {
        $this->settingsManager = $settingsManager;
        $this->httpManager = $httpManager;
        $this->configManager = $configManager;

        $this->eventGroups = [];
    }

    /**
     * @return AbstractEventGroup[]
     */
    public function getEventGroups()
    {
        /** @var AbstractEventGroup[] $eventGroups */
        $eventGroups = [];

        foreach($this->eventGroups as $i => $eventGroup)
        {
            $eventGroups[] = $eventGroup;
        }

        usort($eventGroups, function($a, $b){
            return strcmp($a->getDisplayName(), $b->getDisplayName());
        });

        return $eventGroups;
    }

    /**
     * @param $eventGroupClassName
     * @throws \Exception
     */
    public function registerEventGroup($eventGroupClassName)
    {
        if(!class_exists($eventGroupClassName))
        {
            throw new \Exception("Unable to find provided class.");
        }

        /** @var AbstractEventGroup $event */
        $eventGroup = new $eventGroupClassName();

        if(!$this->isEventGroup($eventGroup))
        {
            throw new \Exception("Invalid class.");
        }

        if(!$this->isEventGroupRegistered($eventGroupClassName))
        {
            $this->eventGroups[] = $eventGroup;
        }else{
            unset($eventGroup);
        }

        usort($this->eventGroups, function($a, $b){
            return strcmp($a->getDisplayName(), $b->getDisplayName());
        });
    }

    /**
     * @param $eventGroupClassName
     * @return mixed|null|AbstractEventGroup
     */
    public function getEventGroup($eventGroupClassName)
    {
        $eventGroup = null;

        foreach($this->eventGroups as $possibleEventGroup)
        {
            if(TypeHelper::getCleanClassNameString(get_class($possibleEventGroup)) === TypeHelper::getCleanClassNameString($eventGroupClassName))
            {
                $eventGroup = $possibleEventGroup;
                break;
            }
        }

        return $eventGroup;
    }

    /**
     * @param $eventGroupIn
     * @return bool
     */
    public function isEventGroup($eventGroupIn)
    {
        $eventGroup = null;

        if(is_string($eventGroupIn))
        {
            if(!class_exists($eventGroupIn))
            {
                return false;
            }

            $eventGroup = new $eventGroupIn();
        }else{
            $eventGroup = $eventGroupIn;
        }

        if(is_object($eventGroup))
        {
            if($eventGroup instanceof AbstractEventGroup)
            {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $eventGroupClassName
     * @return bool
     */
    public function isEventGroupRegistered($eventGroupClassName)
    {
        $eventGroup = $this->getEventGroup($eventGroupClassName);

        return !empty($eventGroup);
    }

    /**
     * @param $eventCategoryClassName
     * @throws \Exception
     */
    public function registerEventCategory($eventCategoryClassName)
    {
        if(!class_exists($eventCategoryClassName))
        {
            throw new \Exception("Unable to find provided class.");
        }

        /** @var AbstractEventCategory $event */
        $eventCategory = new $eventCategoryClassName();

        if(!$this->isEventCategory($eventCategory))
        {
            throw new \Exception("Invalid class.");
        }

        if(!$this->isEventCategoryRegistered($eventCategoryClassName))
        {
            foreach($this->eventGroups as $i => $eventGroup)
            {
                /** @var AbstractEventCategory $event */
                $eventCategory = new $eventCategoryClassName();
                $eventGroup->addEventCategory($eventCategory);
            }
        }else{
            unset($eventCategory);
        }

        usort($this->eventGroups, function($a, $b){
            return strcmp($a->getDisplayName(), $b->getDisplayName());
        });
    }

    /**
     * @param $eventCategoryClassName
     * @return AbstractEventCategory[]
     */
    public function getEventCategory($eventCategoryClassName)
    {
        /** @var AbstractEventCategory[] $eventCategories */
        $eventCategories = [];

        foreach($this->eventGroups as $a => $eventGroup)
        {
            foreach($eventGroup->getEventCategories() as $b => $possibleEventCategory)
            {
                if(TypeHelper::getCleanClassNameString(get_class($possibleEventCategory)) === TypeHelper::getCleanClassNameString($eventCategoryClassName))
                {
                    $eventCategories[] = $possibleEventCategory;
                }
            }
        }

        return $eventCategories;
    }

    /**
     * @param $eventCategoryIn
     * @return bool
     */
    public function isEventCategory($eventCategoryIn)
    {
        $eventCategory = null;
        if(is_string($eventCategoryIn))
        {
            if(!class_exists($eventCategoryIn))
            {
                return false;
            }

            $eventCategory = new $eventCategoryIn();
        }else{
            $eventCategory = $eventCategoryIn;
        }

        if(is_object($eventCategory))
        {
            if($eventCategory instanceof AbstractEventCategory)
            {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $eventCategoryClassName
     * @return bool
     */
    public function isEventCategoryRegistered($eventCategoryClassName)
    {
        $eventCategories = $this->getEventCategory($eventCategoryClassName);
        return !empty($eventCategories);
    }

    /**
     * @return \NHAzureLogicApps\Application\Event\AbstractEvent[]
     */
    public function getRegisteredEvents()
    {
        /** @var AbstractEvent[] $events */
        $events = [];

        foreach($this->eventGroups as $a => $eventGroup)
        {
            foreach($eventGroup->getEventCategories() as $b => $eventCategory)
            {
                foreach($eventCategory->getEvents() as $c => $event)
                {
                    $events[] = $event;
                }
            }
        }

        usort($events, function($a, $b){
            return strcmp($a->getName(), $b->getName());
        });

        return $events;
    }

    /**
     * @param $eventClassName
     * @return AbstractEvent
     */
    public function getRegisteredEvent($eventClassName)
    {
        $events = $this->getRegisteredEvents();
        $event = null;

        foreach($events as $possibleEvent)
        {
            if(TypeHelper::getCleanClassNameString(get_class($possibleEvent)) === TypeHelper::getCleanClassNameString($eventClassName))
            {
                $event = $possibleEvent;
                break;
            }
        }

        return $event;
    }

    /**
     * @param $eventClassName
     * @throws \Exception
     */
    public function registerEvent($eventClassName)
    {
        if(!class_exists($eventClassName))
        {
            throw new \Exception("Unable to find provided class.");
        }

        /** @var AbstractEvent $event */
        $event = new $eventClassName($this, $this->settingsManager, $this->configManager);

        if(!$this->isEvent($event))
        {
            throw new \Exception("Invalid class.");
        }

        if(!$this->isEventGroupRegistered($event->getGroup()))
        {
            throw new \Exception("Invalid class.");
        }

        if(!$this->isEventCategoryRegistered($event->getCategory()))
        {
            throw new \Exception("Invalid class.");
        }

        $eventGroup = $this->getEventGroup($event->getGroup());
        if($eventGroup instanceof AbstractEventGroup)
        {
            foreach($eventGroup->getEventCategories() as $i => $eventCategory)
            {
                if(TypeHelper::getCleanClassNameString(get_class($eventCategory)) === TypeHelper::getCleanClassNameString($event->getCategory()))
                {
                    $eventCategory->addEvent($event);
                    if($event->hasIntegrations())
                    {
                        $event->register();
                    }
                    break;
                }
            }
        }
    }

    /**
     * @param $eventIn
     * @return bool
     */
    public function isEvent($eventIn)
    {
        $event = null;

        if(is_string($eventIn))
        {
            if(!class_exists($eventIn))
            {
                return false;
            }

            $event = new $eventIn($this);
        }else{
            $event = $eventIn;
        }

        if(is_object($event))
        {
            if($event instanceof AbstractEvent)
            {
                return true;
            }
        }

        return false;
    }

    /**
     * @param MessageModel $messageModel
     * @return \NHAzureLogicApps\Application\Model\HttpResponseModel
     */
    public function dispatch(MessageModel $messageModel)
    {
        global $wp;

        $payload = new \stdClass();
        $payload->integration_name = $messageModel->integration->name;
        $payload->referer = home_url($wp->request);
        $payload->type = (is_object($messageModel->event))
            ? get_class($messageModel->event)
            : null;
        $payload->message = $messageModel->text;
        $payload->attachments = $messageModel->attachments;
        $payload->data = $messageModel->data;

        $response = $this->httpManager->postRequest(
            $messageModel->integration->endpointUrl,
            json_encode($payload),
            [
                'Content-Type' => 'application/json',
                'User-Agent' => $this->configManager->getName().'/'.$this->configManager->getVersion().' (NewHeap-Azure-Logic-apps; Wordpress extension)'
            ]
        );

        return $response;
    }
}
