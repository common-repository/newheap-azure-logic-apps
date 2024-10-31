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

class PluginActivatedEvent extends AbstractEvent
{
    /** @return string */
    public function getName()
    {
        return __("Plugin activated", ConfigService::TEXT_DOMAIN_NAME);
    }

    /** @return string */
    public function getGroup()
    {
        return SystemEventGroup::class;
    }

    /** @return string */
    public function getDescription()
    {
        return __("Whenever a plugin is activated, send a notification.", ConfigService::TEXT_DOMAIN_NAME);
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
        return __("%user_name% *activated plugin* %plugin_name% on site <%site_url%|%site_title%> at %date_time%.", ConfigService::TEXT_DOMAIN_NAME);
    }
    
    public function register()
    {
        if(!function_exists('get_plugin_data'))
        {
            require_once ABSPATH.'wp-admin/includes/plugin.php';
        }

        add_action('activated_plugin', function($plugin, $networkWide){

            $currentUser = wp_get_current_user();
            $pluginPath = wp_normalize_path(WP_PLUGIN_DIR."/".$plugin);
            $pluginData = get_plugin_data($pluginPath);

            foreach($this->getIntegrations() as $integration)
            {
                //$eventSettings = $this->getSettings($integration);

                $messageModel = new MessageModel($this, $integration);
                $messageModel->text = strtr($this->getMessage($integration), [
                    '%plugin_name%' => $pluginData['Name'],
                    '%user_name%' => $currentUser->user_login,
                    '%site_url%' => network_site_url('/'),
                    '%site_title%' => get_bloginfo('name'),
                    '%date_time%' => date('Y-m-d H:i:s',current_time('timestamp', 1)).' (GMT)',
                ]);

                $messageModel->data = (object)[
                    'plugin_name' => $pluginData['Name'],
                    'user_name' => $currentUser->user_login,
                    'site_url' => network_site_url('/'),
                    'site_title' => get_bloginfo('name'),
                    'date_time' => date('Y-m-d H:i:s',current_time('timestamp', 1)).' (GMT)',
                ];

                $attachment = new AttachmentModel();
                $attachment->color = 'good';
                $attachment->author_name = $currentUser->display_name;
                $attachment->author_link = get_author_posts_url($currentUser->ID);
                $attachment->author_icon = get_avatar_url($currentUser->ID, 32);
                $attachment->text = wp_trim_words(TypeHelper::getPropertyValue($pluginData, 'Description',__('No description', ConfigService::TEXT_DOMAIN_NAME)), 30, '...');

                $attachment->fields[] = new FieldModel(
                    __('Plugin name', ConfigService::TEXT_DOMAIN_NAME),
                    '<'.TypeHelper::getPropertyValue($pluginData, 'PluginURI', __('', ConfigService::TEXT_DOMAIN_NAME)).'|'.TypeHelper::getPropertyValue($pluginData, 'Name', __('Unknown', ConfigService::TEXT_DOMAIN_NAME)).'>'
                );

                $attachment->fields[] = new FieldModel(
                    __('Plugin author', ConfigService::TEXT_DOMAIN_NAME),
                    '<'.TypeHelper::getPropertyValue($pluginData, 'AuthorURI', __('', ConfigService::TEXT_DOMAIN_NAME)).'|'.TypeHelper::getPropertyValue($pluginData, 'AuthorName', __('Unknown', ConfigService::TEXT_DOMAIN_NAME)).'>'
                );

                $attachment->fields[] = new FieldModel(
                    __('Plugin version', ConfigService::TEXT_DOMAIN_NAME),
                    TypeHelper::getPropertyValue($pluginData, 'Version', __('Unknown', ConfigService::TEXT_DOMAIN_NAME))
                );

                $attachment->fields[] = new FieldModel(
                    __('Activated by', ConfigService::TEXT_DOMAIN_NAME),
                    TypeHelper::getPropertyValue($currentUser, 'user_login', __('Unknown', ConfigService::TEXT_DOMAIN_NAME))
                );

                $messageModel->attachments[] = $attachment;

                $this->dispatch($messageModel);
            }

        }, 10, 2);
    }
}
