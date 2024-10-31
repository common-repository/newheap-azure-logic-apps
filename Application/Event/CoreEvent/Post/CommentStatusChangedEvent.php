<?php
namespace NHAzureLogicApps\Application\Event\CoreEvent\Post;

use NHAzureLogicApps\Application\Event\AbstractCommentEvent;
use NHAzureLogicApps\Application\Event\EventGroup\PostsEventGroup;
use NHAzureLogicApps\Application\Event\EventGroup\UsersEventGroup;
use NHAzureLogicApps\Application\Helper\TypeHelper;
use NHAzureLogicApps\Application\Model\MessageModel;
use NHAzureLogicApps\Application\Service\ConfigService\ConfigService;

if (!defined('ABSPATH'))
{
    exit;
}

class CommentStatusChangedEvent extends AbstractCommentEvent
{
    /** @return string */
    public function getName()
    {
        return __("Comment status change", ConfigService::TEXT_DOMAIN_NAME);
    }

    /** @return string */
    public function getGroup()
    {
        return PostsEventGroup::class;
    }

    /** @return string */
    public function getDescription()
    {
        return __("Whenever a comment it's status is changes, send a notification.", ConfigService::TEXT_DOMAIN_NAME);
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
        return __("A comment on *%post_type%* <%post_url%|%post_title%> by %user_name% changed status from *%comment_status_old%* to *%comment_status%* on site <%site_url%|%site_title%> at %date_time%.", ConfigService::TEXT_DOMAIN_NAME);
    }
    
    public function register()
    {
        add_action('transition_comment_status', function($newStatus, $oldStatus, $comment){

            if(!$newStatus === $oldStatus)
            {
                return;
            }

            $postType = get_post_type($comment->comment_post_ID);

            foreach($this->getIntegrations() as $integration)
            {
                $eventSettings = $this->getSettings($integration);
                $allowedPostTypes = TypeHelper::getPropertyValue($eventSettings->rawData, 'postTypes', []);

                if(!in_array($postType, $allowedPostTypes))
                {
                    return;
                }

                $messageModel = new MessageModel($this, $integration);
                $messageModel->text = strtr($this->renderMessage($integration, $comment), [
                    '%comment_status_old%' => ucfirst($oldStatus)
                ]);
                $messageModel->attachments[] = $this->getAttachment($integration, $comment);
                $messageModel->data = $this->getData($integration, $comment);

                $this->dispatch($messageModel);
            }
        }, 10, 3);
    }
}
