<?php
namespace NHAzureLogicApps\Application\Event;

use NHAzureLogicApps\Application\Helper\TypeHelper;
use NHAzureLogicApps\Application\Model\IntegrationEventModel;
use NHAzureLogicApps\Application\Model\IntegrationSettingsModel;
use NHAzureLogicApps\Application\Model\AttachmentModel;
use NHAzureLogicApps\Application\Model\FieldModel;
use NHAzureLogicApps\Application\Service\ConfigService\ConfigService;
use NHAzureLogicApps\Application\Service\SettingsService\SettingsService;
use NHAzureLogicApps\Application\UserInterface\PostTypeSelectorUISettingElement;

if (!defined('ABSPATH'))
{
    exit;
}

abstract class AbstractPostEvent extends AbstractEvent
{
    /**
     * @param mixed $index
     * @param IntegrationEventModel $eventSettings
     * @return string
     */
    public function getSettingsUi($index, $eventSettings)
    {
        ob_start();

        echo $this->getPostTypeSettingsUi($index, $eventSettings);

        $content = ob_get_clean();

        return $content;
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
        $this->savePostTypeSettings($postId, $rawPost, $postedSettings, $postedEvent, $eventSettings);
    }

    /**
     * @param mixed $index
     * @param IntegrationEventModel $eventSettings
     * @return string
     */
    public function getPostTypeSettingsUi($index, $eventSettings)
    {
        $rawData = is_object($eventSettings) ? $eventSettings->rawData : '';
        ob_start();

        ?>
        <div>
            <div class="property-title"><?=__('Post types', ConfigService::TEXT_DOMAIN_NAME)?></div>
            <div class="nhala-pad-5">
                <?php
                $postTypeSelector = new PostTypeSelectorUISettingElement(SettingsService::INTEGRATION_SETTING_EVENT.'['.$index.'][postTypes]', TypeHelper::getPropertyValue($rawData, 'postTypes', []));
                $postTypeSelector->renderContent();
                ?>
            </div>
            <p class="description">
                <?=__("Choose the post types which this event will fire on.", ConfigService::TEXT_DOMAIN_NAME)?>
            </p>
        </div>
        <?php

        $content = ob_get_clean();

        return $content;
    }

    /**
     * @param int $postId
     * @param array $rawPost
     * @param IntegrationSettingsModel $postedSettings
     * @param array $postedEvent
     * @param array &$eventSettings
     */
    private function savePostTypeSettings($postId, $rawPost, $postedSettings, $postedEvent, &$eventSettings)
    {
        $postedPostTypes = TypeHelper::getPropertyValue($postedEvent, 'postTypes', []);
        $activePostTypes = [];

        foreach($postedPostTypes as $postType)
        {
            if((bool)TypeHelper::getPropertyValue($postType, 'isActive', false))
            {
                $postTypeName = TypeHelper::getPropertyValue($postType, 'name', '');

                if(!empty($postTypeName))
                {
                    $activePostTypes[] = sanitize_text_field($postTypeName);
                }
            }
        }

        $eventSettings['postTypes'] = $activePostTypes;
    }

    public function renderMessage($integration, $postId, $post)
    {
        $currentUser = wp_get_current_user();
        $authorUser = get_user_by('ID', $post->post_author);
        $currentUsername = 'unknown';
        $authorUsername = 'unknown';

        if(is_object($currentUser))
        {
            $currentUsername = $currentUser->user_login;
        }

        if(is_object($authorUser))
        {
            $authorUsername = $authorUser->user_login;
        }

        $postType = get_post_type($postId);

        $message = strtr($this->getMessage($integration), [
            '%user_name%' => $currentUsername,
            '%post_url%' => get_permalink($postId),
            '%post_title%' => $post->post_title,
            '%post_status%' => ucfirst(get_post_status($postId)),
            '%post_type%' => $postType,
            '%site_url%' => network_site_url('/'),
            '%site_title%' => get_bloginfo('name'),
            '%date_time%' => date('Y-m-d H:i:s',current_time('timestamp', 1)).' (GMT)',
        ]);

        return $message;
    }

    public function getData($integration, $postId, $post)
    {
        $currentUser = wp_get_current_user();
        $authorUser = get_user_by('ID', $post->post_author);
        $currentUsername = 'unknown';
        $authorUsername = 'unknown';

        if(is_object($currentUser))
        {
            $currentUsername = $currentUser->user_login;
        }

        if(is_object($authorUser))
        {
            $authorUsername = $authorUser->user_login;
        }

        $postType = get_post_type($postId);

        $data = (object)[
            'user_name' => $currentUsername,
            'post_url' => get_permalink($postId),
            'post_title' => $post->post_title,
            'post_status' => ucfirst(get_post_status($postId)),
            'post_type' => $postType,
            'site_url' => network_site_url('/'),
            'site_title' => get_bloginfo('name'),
            'date_time' => date('Y-m-d H:i:s',current_time('timestamp', 1)).' (GMT)',
        ];

        return $data;
    }

    public function getAttachment($integration, $postId, $post)
    {
        $currentUser = wp_get_current_user();
        $authorUser = get_user_by('ID', $post->post_author);

        $attachment = new AttachmentModel();
        $attachment->title = get_the_title($postId);
        $attachment->title_link = get_permalink($postId);
        $attachment->author_name = $currentUser->display_name;
        $attachment->author_link = get_author_posts_url($currentUser->ID);
        $attachment->author_icon = get_avatar_url($currentUser->ID, 32);
        $attachment->text = wp_trim_words(strip_tags($post->post_content), 30, '...');

        $postThumbUrl = get_the_post_thumbnail_url($postId);

        if(!empty($postThumbUrl))
        {
            $attachment->thumb_url = $postThumbUrl;
        }


        $attachment->fields[] = new FieldModel(
            __('Edited by', ConfigService::TEXT_DOMAIN_NAME),
            TypeHelper::getPropertyValue($currentUser, 'user_login', __('Unknown', ConfigService::TEXT_DOMAIN_NAME))
        );

        $attachment->fields[] = new FieldModel(
            __('Content author', ConfigService::TEXT_DOMAIN_NAME),
            TypeHelper::getPropertyValue($authorUser, 'user_login', __('Unknown', ConfigService::TEXT_DOMAIN_NAME))
        );

        $attachment->fields[] = new FieldModel(
            __('Content type', ConfigService::TEXT_DOMAIN_NAME),
            ucfirst(get_post_type($postId))
        );

        $attachment->fields[] = new FieldModel(
            __('Content status', ConfigService::TEXT_DOMAIN_NAME),
            ucfirst(get_post_status($postId))
        );

        $attachment->fields[] = new FieldModel(
            strtr(__('Edit %post_type%', ConfigService::TEXT_DOMAIN_NAME), ['%post_type%' => get_post_type($postId)]),
            get_edit_post_link($postId)
        );

        return $attachment;
    }
}
