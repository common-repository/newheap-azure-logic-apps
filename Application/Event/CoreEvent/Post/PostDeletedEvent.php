<?php
namespace NHAzureLogicApps\Application\Event\CoreEvent\Post;

use NHAzureLogicApps\Application\Event\AbstractPostEvent;
use NHAzureLogicApps\Application\Event\EventGroup\PostsEventGroup;
use NHAzureLogicApps\Application\Helper\TypeHelper;
use NHAzureLogicApps\Application\Model\MessageModel;
use NHAzureLogicApps\Application\Service\ConfigService\ConfigService;

if (!defined('ABSPATH'))
{
    exit;
}

class PostDeletedEvent extends AbstractPostEvent
{
    /** @return string */
    public function getName()
    {
        return __("On post deleted", ConfigService::TEXT_DOMAIN_NAME);
    }

    /** @return string */
    public function getGroup()
    {
        return PostsEventGroup::class;
    }

    /** @return string */
    public function getDescription()
    {
        return __("Whenever a post is deleted, send a notification.", ConfigService::TEXT_DOMAIN_NAME);
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
        return __("%user_name% *deleted %post_type%* <%post_url%|%post_title%> on site <%site_url%|%site_title%> at %date_time%.", ConfigService::TEXT_DOMAIN_NAME);
    }
    
    public function register()
    {
        add_action('deleted_post', function($postId){

            $postType = get_post_type($postId);

            $post = get_post($postId);
            foreach($this->getIntegrations() as $integration)
            {
                $eventSettings = $this->getSettings($integration);
                $allowedPostTypes = TypeHelper::getPropertyValue($eventSettings->rawData, 'postTypes', []);

                if(!in_array($postType, $allowedPostTypes))
                {
                    return;
                }

                $messageModel = new MessageModel($this, $integration);
                $messageModel->text = $this->renderMessage($integration, $postId, $post);
                $messageModel->attachments[] = $this->getAttachment($integration, $postId, $post);
                $messageModel->data = $this->getData($integration, $postId, $post);

                $this->dispatch($messageModel);
            }

        });
    }
}
