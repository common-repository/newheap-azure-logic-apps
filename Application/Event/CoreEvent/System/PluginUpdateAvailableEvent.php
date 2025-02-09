<?php
namespace NHAzureLogicApps\Application\Event\CoreEvent\System;

use NHAzureLogicApps\Application\Event\AbstractEvent;
use NHAzureLogicApps\Application\Event\EventGroup\SystemEventGroup;
use NHAzureLogicApps\Application\Helper\TypeHelper;
use NHAzureLogicApps\Application\Model\MessageModel;
use NHAzureLogicApps\Application\Service\ConfigService\ConfigService;

if (!defined('ABSPATH'))
{
    exit;
}

class PluginUpdateAvailableEvent extends AbstractEvent
{
    /** @return string */
    public function getName()
    {
        return __("Plugin update available", ConfigService::TEXT_DOMAIN_NAME);
    }

    /** @return string */
    public function getGroup()
    {
        return SystemEventGroup::class;
    }

    /** @return string */
    public function getDescription()
    {
        return __("Whenever a plugin is update is available, send a notification.", ConfigService::TEXT_DOMAIN_NAME);
    }

    /** @return string */
    public function getAuthorDisplayName()
    {
        return "NewHeap";
    }

    /** @return string */
    public function getAuthorContactUrl()
    {
        return "https://newheap.com";
    }

    /** @return string */
    public function getDefaultMessage()
    {
        return __("*New version available* for plugin *%plugin_name%* on site <%site_url%|%site_title%>.", ConfigService::TEXT_DOMAIN_NAME);
    }
    
    public function register()
    {
        //TODO: make this work.
        add_filter('pre_set_site_transient_update_plugins', function($transient)
        {
            foreach($this->getIntegrations() as $integration)
            {
                $messageModel = new MessageModel($this, $integration);
                $messageModel->text = strtr($this->getMessage($integration), [
                    '%plugin_name%' => "Naam_plugin",
                    '%site_url%' => network_site_url('/'),
                    '%site_title%' => get_bloginfo('name'),
                ]);

                $messageModel->data = (object)[
                    'plugin_name' => "Naam_plugin",
                    'site_url' => network_site_url('/'),
                    'site_title' => get_bloginfo('name'),
                ];

                $this->dispatch($messageModel);
            }
        });
    }
}
