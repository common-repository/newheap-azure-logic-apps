<?php
namespace NHAzureLogicApps\Application\Event\CoreEvent\Attachment;

use NHAzureLogicApps\Application\Event\AbstractEvent;
use NHAzureLogicApps\Application\Event\EventGroup\AttachmentEventGroup;
use NHAzureLogicApps\Application\Model\AttachmentModel;
use NHAzureLogicApps\Application\Model\FieldModel;
use NHAzureLogicApps\Application\Model\MessageModel;
use NHAzureLogicApps\Application\Service\ConfigService\ConfigService;

if (!defined('ABSPATH'))
{
    exit;
}

class AttachmentNewEvent extends AbstractEvent
{
    /** @return string */
    public function getName()
    {
        return __("New attachment", ConfigService::TEXT_DOMAIN_NAME);
    }

    /** @return string */
    public function getGroup()
    {
        return AttachmentEventGroup::class;
    }

    /** @return string */
    public function getDescription()
    {
        return __("Whenever a new attachment is added, send a notification.", ConfigService::TEXT_DOMAIN_NAME);
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
        return __("*New attachment* <%attachment_url%|%attachment_name%> added by %user_name% on site <%site_url%|%site_title%> at %date_time%.", ConfigService::TEXT_DOMAIN_NAME);
    }
    
    public function register()
    {
        add_action('add_attachment', function($postId){
            $wpAttachment = get_post($postId);
            $attachmentMimeType = get_post_mime_type($postId);
            $currentUser = wp_get_current_user();

            foreach($this->getIntegrations() as $integration)
            {
                $messageModel = new MessageModel($this, $integration);
                $messageModel->text = strtr($this->getMessage($integration), [
                    '%attachment_name%' => get_the_title($postId),
                    '%attachment_url%' => wp_get_attachment_url($postId),
                    '%user_name%' => $currentUser->user_login,
                    '%site_url%' => network_site_url('/'),
                    '%site_title%' => get_bloginfo('name'),
                    '%date_time%' => date('Y-m-d H:i:s',current_time('timestamp', 1)).' (GMT)',
                ]);

                $messageModel->data = (object)[
                    'attachment_name' => get_the_title($postId),
                    'attachment_url' => wp_get_attachment_url($postId),
                    'user_name' => $currentUser->user_login,
                    'site_url' => network_site_url('/'),
                    'site_title' => get_bloginfo('name'),
                    'date_time' => date('Y-m-d H:i:s',current_time('timestamp', 1)).' (GMT)',
                ];

                $wpAttachmentImg = wp_get_attachment_image_src($postId, 'medium' );
                $wpAttachmentImgUrl = !empty($wpAttachmentImg[0] ) ? $wpAttachmentImg[0] : '';

                $attachment = new AttachmentModel();
                $attachment->title = get_the_title($postId);
                $attachment->title_link = get_permalink($postId);
                $attachment->author_name = $currentUser->user_login;
                $attachment->author_link = get_author_posts_url($currentUser->ID);
                $attachment->author_icon = get_avatar_url($currentUser->ID, 32);
                $attachment->text = wp_trim_words(strip_tags($wpAttachment->post_content), 30, '...');

                if(!empty($wpAttachmentImgUrl))
                {
                    $attachment->image_url = $wpAttachmentImgUrl;
                }

                $attachment->fields[] = new FieldModel(
                    __('Attachment name', ConfigService::TEXT_DOMAIN_NAME),
                    '<'.wp_get_attachment_url($postId).'|'.get_the_title($postId).'>'
                );

                $attachment->fields[] = new FieldModel(
                    __('Attachment type', ConfigService::TEXT_DOMAIN_NAME),
                    $attachmentMimeType
                );

                $attachment->fields[] = new FieldModel(
                    __('Edit attachment', ConfigService::TEXT_DOMAIN_NAME),
                    get_edit_post_link($postId)
                );

                if($wpAttachment->post_parent > 0)
                {
                    $attachment->fields[] = new FieldModel(
                        __('Added to', ConfigService::TEXT_DOMAIN_NAME),
                        get_permalink($wpAttachment->post_parent)
                    );
                }

                if(!empty($wpAttachment->post_excerpt))
                {
                    $attachment->fields[] = new FieldModel(
                        __('Attachment caption', ConfigService::TEXT_DOMAIN_NAME),
                        wp_trim_words(strip_tags($wpAttachment->post_excerpt), 30, '...')
                    );
                }

                $messageModel->attachments[] = $attachment;

                $this->dispatch($messageModel);
            }
        });
    }
}
