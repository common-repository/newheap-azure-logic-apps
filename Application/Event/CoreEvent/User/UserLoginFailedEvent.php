<?php
namespace NHAzureLogicApps\Application\Event\CoreEvent\User;

use NHAzureLogicApps\Application\Event\AbstractEvent;
use NHAzureLogicApps\Application\Event\EventCategory\AuthenticationCategory;
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

class UserLoginFailedEvent extends AbstractEvent
{
    /** @return string */
    public function getName()
    {
        return __("User login failed", ConfigService::TEXT_DOMAIN_NAME);
    }

    /** @return string */
    public function getGroup()
    {
        return UsersEventGroup::class;
    }

    /** @return string */
   /* public function getCategory()
    {
        return AuthenticationCategory::class;
    }*/

    /** @return string */
    public function getDescription()
    {
        return __("Whenever a user fails to log into this site, send a notification.", ConfigService::TEXT_DOMAIN_NAME);
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
        return __("*Failed login attempt* on account %user_name% at site <%site_url%|%site_title%> at %date_time%.", ConfigService::TEXT_DOMAIN_NAME);
    }
    
    public function register()
    {
        add_action('wp_login_failed', function($username){

            $possibleUser = get_user_by('user_login', $username);

            foreach($this->getIntegrations() as $integration)
            {
                $messageModel = new MessageModel($this, $integration);
                $messageModel->text = strtr($this->getMessage($integration), [
                    '%user_name%' => $username,
                    '%site_url%' => network_site_url('/'),
                    '%site_title%' => get_bloginfo('name'),
                    '%date_time%' => date('Y-m-d H:i:s',current_time('timestamp', 1)).' (GMT)',
                ]);

                $messageModel->data = (object)[
                    'user_name' => $username,
                    'site_url' => network_site_url('/'),
                    'site_title' => get_bloginfo('name'),
                    'date_time' => date('Y-m-d H:i:s',current_time('timestamp', 1)).' (GMT)',
                ];

                $attachment = new AttachmentModel();
                $attachment->color = 'danger';
                $attachment->author_name = 'System';
                $attachment->author_link = network_site_url('/');

                if(!empty($attachment->author_icon))
                {
                    $attachment->thumb_url = $attachment->author_icon;
                }

                $attachment->fields[] = new FieldModel(
                    __('Username', ConfigService::TEXT_DOMAIN_NAME),
                    !empty($username) ? $username : __('Unknown', ConfigService::TEXT_DOMAIN_NAME)
                );

                $attachment->fields[] = new FieldModel(
                    __('From IP address', ConfigService::TEXT_DOMAIN_NAME),
                    $_SERVER['REMOTE_ADDR']
                );

                $attachment->fields[] = new FieldModel(
                    __('Is existing user', ConfigService::TEXT_DOMAIN_NAME),
                    is_object($possibleUser) ? __('Yes', ConfigService::TEXT_DOMAIN_NAME) : __('No', ConfigService::TEXT_DOMAIN_NAME)
                );

                if(is_object($possibleUser))
                {
                    $attachment->fields[] = new FieldModel(
                        __('E-mail address', ConfigService::TEXT_DOMAIN_NAME),
                        TypeHelper::getPropertyValue($possibleUser, 'user_email',__('Unknown', ConfigService::TEXT_DOMAIN_NAME))
                    );

                    $attachment->fields[] = new FieldModel(
                        __('Role(s)', ConfigService::TEXT_DOMAIN_NAME),
                        implode(',', $possibleUser->roles)
                    );
                }

                $messageModel->attachments[] = $attachment;

                $this->dispatch($messageModel);
            }
        });
    }
}
