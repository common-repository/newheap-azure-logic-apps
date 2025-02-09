<?php
namespace NHAzureLogicApps\Application\Event\CoreEvent\System;

use NHAzureLogicApps\Application\Event\AbstractEvent;
use NHAzureLogicApps\Application\Event\EventGroup\SystemEventGroup;
use NHAzureLogicApps\Application\Helper\TypeHelper;
use NHAzureLogicApps\Application\Model\AttachmentModel;
use NHAzureLogicApps\Application\Model\FieldModel;
use NHAzureLogicApps\Application\Model\MessageModel;
use NHAzureLogicApps\Application\Service\ConfigService\ConfigService;

if (!defined('ABSPATH'))
{
    exit;
}

class PluginDeletedEvent extends AbstractEvent
{
    /** @return string */
    public function getName()
    {
        return __("Plugin deleted", ConfigService::TEXT_DOMAIN_NAME);
    }

    /** @return string */
    public function getGroup()
    {
        return SystemEventGroup::class;
    }

    /** @return string */
    public function getDescription()
    {
        return __("Whenever a plugin is deleted, send a notification.", ConfigService::TEXT_DOMAIN_NAME);
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
        return __("%user_name% *deleted plugin* %plugin_name% from site <%site_url%|%site_title%> at %date_time%.", ConfigService::TEXT_DOMAIN_NAME);
    }
    
    public function register()
    {
        add_action('deleted_plugin', function($pluginFile, $deleted){

            $currentUser = wp_get_current_user();

            foreach($this->getIntegrations() as $integration)
            {
                //$eventSettings = $this->getSettings($integration);

                $messageModel = new MessageModel($this, $integration);
                $messageModel->text = strtr($this->getMessage($integration), [
                    '%plugin_name%' => $pluginFile,
                    '%user_name%' => $currentUser->user_login,
                    '%site_url%' => network_site_url('/'),
                    '%site_title%' => get_bloginfo('name'),
                    '%date_time%' => date('Y-m-d H:i:s',current_time('timestamp', 1)).' (GMT)',
                ]);

                $messageModel->data = (object)[
                    'plugin_name' => $pluginFile,
                    'user_name' => $currentUser->user_login,
                    'site_url' => network_site_url('/'),
                    'site_title' => get_bloginfo('name'),
                    'date_time' => date('Y-m-d H:i:s',current_time('timestamp', 1)).' (GMT)',
                ];

                $attachment = new AttachmentModel();
                $attachment->color = 'danger';
                $attachment->author_name = $currentUser->display_name;
                $attachment->author_link = get_author_posts_url($currentUser->ID);
                $attachment->author_icon = get_avatar_url($currentUser->ID, 32);

                $attachment->fields[] = new FieldModel(
                    __('Plugin file', ConfigService::TEXT_DOMAIN_NAME),
                    $pluginFile
                );

                $attachment->fields[] = new FieldModel(
                    __('Deleted by', ConfigService::TEXT_DOMAIN_NAME),
                    TypeHelper::getPropertyValue($currentUser, 'user_login', __('Unknown', ConfigService::TEXT_DOMAIN_NAME))
                );

                $messageModel->attachments[] = $attachment;

                $this->dispatch($messageModel);
            }

        }, 10, 2);
    }
}
