<?php
namespace NHAzureLogicApps\Application\Event\CoreEvent\User;

use NHAzureLogicApps\Application\Event\AbstractEvent;
use NHAzureLogicApps\Application\Event\EventGroup\UsersEventGroup;
use NHAzureLogicApps\Application\Helper\TypeHelper;
use NHAzureLogicApps\Application\Model\AttachmentModel;
use NHAzureLogicApps\Application\Model\FieldModel;
use NHAzureLogicApps\Application\Model\MessageModel;
use NHAzureLogicApps\Application\Service\ConfigService\ConfigService;

if (!defined('ABSPATH'))
{
    exit;
}

class UserUpdatedEvent extends AbstractEvent
{
    /** @return string */
    public function getName()
    {
        return __("User updated", ConfigService::TEXT_DOMAIN_NAME);
    }

    /** @return string */
    public function getGroup()
    {
        return UsersEventGroup::class;
    }

    /** @return string */
    public function getDescription()
    {
        return __("Whenever a new user's account information is updated, send a notification.", ConfigService::TEXT_DOMAIN_NAME);
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
        return __("*%user_name%* his *account information has been updated* on site <%site_url%|%site_title%> at %date_time%.", ConfigService::TEXT_DOMAIN_NAME);
    }

    public function register()
    {
        add_action('profile_update', function($userId, $oldUserData){
            $currentUser = get_userdata($userId);

            foreach($this->getIntegrations() as $integration)
            {
                $messageModel = new MessageModel($this, $integration);
                $messageModel->text = strtr($this->getMessage($integration), [
                    '%user_name%' => $currentUser->user_login,
                    '%site_url%' => network_site_url('/'),
                    '%site_title%' => get_bloginfo('name'),
                    '%date_time%' => date('Y-m-d H:i:s',current_time('timestamp', 1)).' (GMT)',
                ]);

                $messageModel->data = (object)[
                    'user_name' => $currentUser->user_login,
                    'site_url' => network_site_url('/'),
                    'site_title' => get_bloginfo('name'),
                    'date_time' => date('Y-m-d H:i:s',current_time('timestamp', 1)).' (GMT)',
                ];

                $attachment = new AttachmentModel();
                $attachment->author_name = $currentUser->display_name;
                $attachment->author_link = get_author_posts_url($currentUser->ID);
                $attachment->author_icon = get_avatar_url($currentUser->ID, 32);
                $attachment->text = wp_trim_words(strip_tags(get_the_author_meta('description', $currentUser->ID)), 30, '...');

                if(!empty($attachment->author_icon))
                {
                    $attachment->thumb_url = $attachment->author_icon;
                }

                $attachment->fields[] = new FieldModel(
                    __('Display name', ConfigService::TEXT_DOMAIN_NAME),
                    TypeHelper::getPropertyValue($currentUser, 'display_name',__('Unknown', ConfigService::TEXT_DOMAIN_NAME))
                );

                $attachment->fields[] = new FieldModel(
                    __('Username', ConfigService::TEXT_DOMAIN_NAME),
                    TypeHelper::getPropertyValue($currentUser, 'user_login',__('Unknown', ConfigService::TEXT_DOMAIN_NAME))
                );

                $attachment->fields[] = new FieldModel(
                    __('E-mail address', ConfigService::TEXT_DOMAIN_NAME),
                    TypeHelper::getPropertyValue($currentUser, 'user_email',__('Unknown', ConfigService::TEXT_DOMAIN_NAME))
                );

                $attachment->fields[] = new FieldModel(
                    __('Role(s)', ConfigService::TEXT_DOMAIN_NAME),
                    implode(',', $currentUser->roles)
                );

                $messageModel->attachments[] = $attachment;

                $this->dispatch($messageModel);
            }
        }, 10, 2);
    }
}
