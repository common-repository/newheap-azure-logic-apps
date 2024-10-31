<?php
namespace NHAzureLogicApps\Application\PostType;

use NHAzureLogicApps\Application\Helper\TypeHelper;
use NHAzureLogicApps\Application\Model\IntegrationEventModel;
use NHAzureLogicApps\Application\Model\IntegrationSettingsModel;
use NHAzureLogicApps\Application\Service\ConfigService\ConfigService;
use NHAzureLogicApps\Application\Service\SettingsService\SettingsService;
use NHAzureLogicApps\Application\UserInterface\PostTypeSelectorUISettingElement;

if (!defined('ABSPATH'))
{
    exit;
}

class LogicAppIntegrationPostType extends AbstractPostType
{
    protected $name = "nhala_integr";

    public function getName()
    {
        return $this->name;
    }

    public function init()
    {
        add_action('init', array($this, 'register'));
        add_action('admin_menu', array($this, 'removeSubmitDiv'));
        add_filter(sprintf( 'manage_%s_posts_columns', $this->name), array($this, 'columnsHeader'));
        add_action(sprintf( 'manage_%s_posts_custom_column', $this->name), array($this, 'columnRow'), 10, 2);
        add_filter('post_row_actions', array($this, 'rowActions'), 10, 2);
        add_filter(sprintf( 'bulk_actions-edit-%s', $this->name), array($this, 'bulkActions'));
        add_filter('views_edit-'.$this->name, array($this, 'hideSubSubSub'));
        add_action('before_delete_post', array($this, 'registerOnPostDelete'));
    }

    public function registerOnPostDelete($postId)
    {
        $postType = get_post_type($postId);
        if($postType === $this->name)
        {
            delete_metadata ($this->name, $postId, SettingsService::INTEGRATION_SETTINGS, null, true);
            delete_metadata ($this->name, $postId, SettingsService::INTEGRATION_SETTING_EVENT, null, true);
        }
    }

    public function register()
    {
        $args = [
            'description'         => '',
            'public'              => false,
            'publicly_queryable'  => false,
            'show_in_nav_menus'   => true,
            'show_in_admin_bar'   => true,
            'exclude_from_search' => true,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'menu_position'       => 75,
            'menu_icon'           => 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4NCjwhLS0gR2VuZXJhdG9yOiBBZG9iZSBJbGx1c3RyYXRvciAyMC4xLjAsIFNWRyBFeHBvcnQgUGx1Zy1JbiAuIFNWRyBWZXJzaW9uOiA2LjAwIEJ1aWxkIDApICAtLT4NCjxzdmcgdmVyc2lvbj0iMS4xIiBpZD0iTGF5ZXJfMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgeD0iMHB4IiB5PSIwcHgiDQoJIHZpZXdCb3g9IjAgMCA1MCA1MCIgc3R5bGU9ImVuYWJsZS1iYWNrZ3JvdW5kOm5ldyAwIDAgNTAgNTA7IiB4bWw6c3BhY2U9InByZXNlcnZlIj4NCjxzdHlsZSB0eXBlPSJ0ZXh0L2NzcyI+DQoJLnN0MHtmaWxsOiM1OUI0RDk7fQ0KCS5zdDF7ZmlsbDojN0ZCQTAwO30NCgkuc3Qye2ZpbGw6IzAwNzJDNjt9DQo8L3N0eWxlPg0KPHBhdGggY2xhc3M9InN0MCIgZD0iTTI2LDE5Ljd2LTVoLTIuNXY1YzAsMC45LTAuOCwxLjctMi4xLDIuMUwxOCwyMi41Yy0yLjMsMC43LTMuOSwyLjUtMy45LDQuNXY1LjdoMi41VjI3YzAtMC45LDAuOC0xLjcsMi4xLTIuMQ0KCWwzLjQtMC44QzI0LjIsMjMuNywyNS44LDIxLjksMjYsMTkuN3oiLz4NCjxwYXRoIGNsYXNzPSJzdDEiIGQ9Ik0xOS42LDM1LjN2LTQuNmMwLTEuMS0wLjktMi0yLTJIMTNjLTEuMSwwLTIsMC45LTIsMnY0LjZjMCwxLjEsMC45LDIsMiwyaDQuNkMxOC43LDM3LjMsMTkuNiwzNi40LDE5LjYsMzUuM3oiDQoJLz4NCjxwYXRoIGNsYXNzPSJzdDAiIGQ9Ik0yMy4yLDE5Ljd2LTVoMi41djVjMCwwLjksMC44LDEuNywyLjEsMi4xbDQuMiwwLjljMi4zLDAuNywzLjksMi41LDMuOSw0LjV2NS43aC0yLjV2LTUuNw0KCWMwLTAuOS0wLjgtMS43LTIuMS0yLjFsLTQuMi0wLjlDMjQuOCwyMy41LDIzLjIsMjEuNywyMy4yLDE5Ljd6Ii8+DQo8cGF0aCBjbGFzcz0ic3QxIiBkPSJNMzAuNCwzNS41di00LjZjMC0xLjEsMC45LTIsMi0ySDM3YzEuMSwwLDIsMC45LDIsMnY0LjZjMCwxLjEtMC45LDItMiwyaC00LjZDMzEuMywzNy41LDMwLjQsMzYuNiwzMC40LDM1LjV6Ig0KCS8+DQo8cmVjdCB4PSIyMy4yIiB5PSIxNC43IiBjbGFzcz0ic3QwIiB3aWR0aD0iMi44IiBoZWlnaHQ9IjQuOCIvPg0KPHBhdGggY2xhc3M9InN0MiIgZD0iTTI2LjUsMTAuMXYzLjdoLTMuN3YtMy43SDI2LjUgTTI3LjMsNy4zSDIyYy0xLjEsMC0yLDAuOS0yLDJ2NS4zYzAsMS4xLDAuOSwyLDIsMmg1LjNjMS4xLDAsMi0wLjksMi0yVjkuMw0KCUMyOS4zLDguMiwyOC40LDcuMywyNy4zLDcuM3oiLz4NCjxwYXRoIGNsYXNzPSJzdDAiIGQ9Ik03LjksNDIuOGMtMS42LDAuMS0zLjItMC4zLTQuNS0xLjFjLTAuOS0wLjgtMS4zLTIuMS0xLjMtNFYyNy4zYzAtMS43LTAuNy0yLjYtMi4xLTIuNnYtMi42DQoJYzEuNCwwLDIuMS0wLjksMi4xLTIuN1Y5LjJjMC0xLjksMC40LTMuMywxLjMtNC4xUzUuOCw0LDcuOSw0djIuNmMtMS41LDAtMi4zLDAuOC0yLjMsMi41djEwYzAsMi4zLTAuNywzLjctMi4yLDQuM2wwLDANCgljMS40LDAuNiwyLjIsMiwyLjIsNC4zdjkuOWMwLDAuNywwLjEsMS40LDAuNSwyYzAuNSwwLjQsMS4xLDAuNywxLjcsMC42QzcuOCw0MC4yLDcuOSw0Mi44LDcuOSw0Mi44eiIvPg0KPHBhdGggY2xhc3M9InN0MCIgZD0iTTQyLjEsNGMxLjYtMC4xLDMuMiwwLjMsNC41LDEuMWMwLjksMC44LDEuMywyLjEsMS4zLDR2MTAuNGMwLDEuNywwLjcsMi42LDIuMSwyLjZ2Mi42Yy0xLjQsMC0yLjEsMC45LTIuMSwyLjcNCgl2MTAuMWMwLDEuOS0wLjQsMy4zLTEuMyw0LjFzLTIuNCwxLjItNC41LDEuMnYtMi42YzEuNSwwLDIuMy0wLjgsMi4zLTIuNXYtMTBjMC0yLjMsMC43LTMuNywyLjItNC4zbDAsMGMtMS40LTAuNi0yLjItMi0yLjItNC4zDQoJVjkuMmMwLTAuNy0wLjEtMS40LTAuNS0yYy0wLjUtMC40LTEuMS0wLjctMS43LTAuNkw0Mi4xLDR6Ii8+DQo8L3N2Zz4NCg==',
            'can_export'          => true,
            'delete_with_user'    => true,
            'hierarchical'        => false,
            'has_archive'         => false,
            'query_var'           => false,
            'map_meta_cap' => false,
            'capabilities' => [
                'edit_post'              => 'manage_options',
                'read_post'              => 'manage_options',
                'delete_post'            => 'manage_options',
                'create_posts'           => 'manage_options',
                'edit_posts'             => 'manage_options',
                'edit_others_posts'      => 'manage_options',
                'publish_posts'          => 'manage_options',
                'read_private_posts'     => 'manage_options',
                'read'                   => 'manage_options',
                'delete_posts'           => 'manage_options',
                'delete_private_posts'   => 'manage_options',
                'delete_published_posts' => 'manage_options',
                'delete_others_posts'    => 'manage_options',
                'edit_private_posts'     => 'manage_options',
                'edit_published_posts'   => 'manage_options',
            ],
            'rewrite' => false,
            'supports' => [''],
            'labels' => [
                'name'               => __('Logic app integrations',              ConfigService::TEXT_DOMAIN_NAME),
                'singular_name'      => __('Logic app integration',              ConfigService::TEXT_DOMAIN_NAME),
                'menu_name'          => __('Azure Logic apps',                         ConfigService::TEXT_DOMAIN_NAME),
                'name_admin_bar'     => __('Azure Logic apps',                         ConfigService::TEXT_DOMAIN_NAME),
                'add_new'            => __('Add New',                        ConfigService::TEXT_DOMAIN_NAME),
                'add_new_item'       => __('Add New Logic app integration',      ConfigService::TEXT_DOMAIN_NAME),
                'edit_item'          => __('Edit Logic app integration',         ConfigService::TEXT_DOMAIN_NAME),
                'new_item'           => __('New Logic app integration',          ConfigService::TEXT_DOMAIN_NAME),
                'view_item'          => __('View Logic app integration',         ConfigService::TEXT_DOMAIN_NAME),
                'search_items'       => __('Search Logic app integration',       ConfigService::TEXT_DOMAIN_NAME),
                'not_found'          => __('No Logic app integration found',     ConfigService::TEXT_DOMAIN_NAME),
                'not_found_in_trash' => __('No Logic app integration in trash',  ConfigService::TEXT_DOMAIN_NAME),
                'all_items'          => __('Logic app integrations',             ConfigService::TEXT_DOMAIN_NAME),
            ]
        ];

        register_post_type($this->name, $args);

        add_action('add_meta_boxes_'.$this->name, array($this, 'addSettingsMetaBox'));
        add_action('add_meta_boxes_'.$this->name, array($this, 'addEventsMetaBox'));
        add_action('save_post', array($this, 'saveSettings'));

        add_action('add_meta_boxes', array($this, 'addSubmitMetaBox'));
    }

    public function removeSubmitDiv()
    {
        remove_meta_box('submitdiv', $this->name, 'side');
    }

    public function bulkActions($actions) {
        unset($actions['edit']);

        return $actions;
    }

    public function columnsHeader($columns) {
        unset($columns['title']);
        unset($columns['date']);

        $columns['name'] = __('Name', ConfigService::TEXT_DOMAIN_NAME);
        $columns['endpointUrl'] = __('Service URL', ConfigService::TEXT_DOMAIN_NAME);
        $columns['activeEventCount'] = __('Active events', ConfigService::TEXT_DOMAIN_NAME);
        $columns['isActive'] = __('Is active', ConfigService::TEXT_DOMAIN_NAME);

        return $columns;
    }

    public function columnRow($column, $postId)
    {
        $settings = $this->settingsManager->getIntegrationSettings($postId);
        $events = $this->settingsManager->getIntegrationEvents($postId);

        /** @var IntegrationEventModel[] $activeEvents */
        $activeEvents = [];
        foreach($events as $event)
        {
            if($event->isActive)
            {
                $activeEvents[] = $event;
            }
        }

        switch ($column) {
            case 'name':
                echo TypeHelper::getPropertyValue($settings, 'name', '');
                break;
            case 'endpointUrl':
                echo TypeHelper::getPropertyValue($settings, 'endpointUrl', '');
                break;
            case 'activeEventCount':
                echo count($activeEvents);
                break;
            case 'isActive':
                echo (TypeHelper::getPropertyValue($settings, 'isActive', 0) == 1) ? 'Yes' : 'No';
                break;
        }
    }

    public function rowActions($actions)
    {
        $post = get_post();

        if (get_post_type($post) === $this->name)
        {
            unset($actions['inline hide-if-no-js']);
        }

        return $actions;
    }

    public function hideSubSubSub()
    {
        return [];
    }

    public function addSettingsMetaBox() {
        add_meta_box(
            'nhala_integration_settings_meta_box',
            __( 'Integration Settings', ConfigService::TEXT_DOMAIN_NAME),
            function($post){
                $settings = $this->settingsManager->getIntegrationSettings($post->ID);

                wp_nonce_field($this->name.'_nonce', $this->name.'_nonce');
                ?>
                <div class="nhala-integration-settings-container">
                    <table class="form-table">
                        <tbody>
                        <tr valign="top">
                            <th scope="row">
                                <label for="<?=SettingsService::INTEGRATION_SETTINGS?>[name]"><?php _e('Name', ConfigService::TEXT_DOMAIN_NAME); ?></label>
                            </th>
                            <td>
                                <input type="text" class="regular-text" name="<?=SettingsService::INTEGRATION_SETTINGS?>[name]" id="<?=SettingsService::INTEGRATION_SETTINGS?>[name]" value="<?=TypeHelper::getPropertyValue($settings, 'name', '')?>">
                                <p class="description">
                                    <?php _e( 'Provide a name as identification for this webhook.', ConfigService::TEXT_DOMAIN_NAME); ?>
                                </p>
                            </td>
                        </tr>

                        <tr valign="top">
                            <th scope="row">
                                <label for="<?=SettingsService::INTEGRATION_SETTINGS?>[endpointUrl]"><?php _e('Webhook url', ConfigService::TEXT_DOMAIN_NAME); ?></label>
                            </th>
                            <td>
                                <input type="text" class="regular-text" name="<?=SettingsService::INTEGRATION_SETTINGS?>[endpointUrl]" id="<?=SettingsService::INTEGRATION_SETTINGS?>[endpointUrl]" value="<?=TypeHelper::getPropertyValue($settings, 'endpointUrl', '')?>">
                                <p class="description">
                                    <?php _e( 'The incomming webhook URL. Example: <code>https://x-x.logic.azure.com:443/workflows/xxxxxxxxxxxxxxxx/triggers/manual/paths/invoke?api-version=xxxx-xx-xx&sp=%2Ftriggers%2Fmanual%2Frun&sv=x.0&sig=xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx</code>.', ConfigService::TEXT_DOMAIN_NAME); ?>
                                </p>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">
                                <label for="<?=SettingsService::INTEGRATION_SETTINGS?>[isActive]"><?php _e( 'Active', ConfigService::TEXT_DOMAIN_NAME); ?></label>
                            </th>
                            <td>
                                <label>
                                    <input type="checkbox" name="<?=SettingsService::INTEGRATION_SETTINGS?>[isActive]" id="<?=SettingsService::INTEGRATION_SETTINGS?>[isActive]" <?=checked((bool)TypeHelper::getPropertyValue($settings, 'isActive', false));?>>
                                    <?php _e( 'Activate this integration.', ConfigService::TEXT_DOMAIN_NAME); ?>
                                </label>
                                <p class="description">
                                    <?php _e( 'This integration is disabled by default. To enable it please check the checkbox.', ConfigService::TEXT_DOMAIN_NAME); ?>
                                </p>
                            </td>
                        </tr>
                        <!--<tr valign="top">
                            <th scope="row">
                                <label></label>
                            </th>
                            <td>
                                <button class="button-primary nhala-test-connection" data-nhala-interation-id="<?=$post->ID?>"><?=__('Test connection', ConfigService::TEXT_DOMAIN_NAME)?></button>
                            </td>
                        </tr>-->
                        <script type="text/javascript">
                            <?php
                            //TODO: move to JS file and make this functionality work.
                            ?>
                            jQuery(document).ready(function($){
                                $('.nhala-test-connection').click(function(e){
                                    $(this).disabled(true);
                                });
                            });
                        </script>
                        </tbody>
                    </table>
                </div>
                <?php
            },
            $this->name,
            'advanced',
            'high'
        );
    }

    public function addEventsMetaBox()
    {
        add_meta_box(
            'nhala_integration_settings_events_meta_box',
            __( 'Integration events', ConfigService::TEXT_DOMAIN_NAME),
            function($post){
                $settings = $this->settingsManager->getIntegrationSettings($post->ID);
                $eventSettings = $this->settingsManager->getIntegrationEvents($post->ID);
                $eventGroups = $this->logicAppEventManager->getEventGroups();

                ?>
                <div class="nhala-row">
                    <div class="nhala-col-md-12">
                        <h2 class="nav-tab-wrapper" style="padding:0 14px;">
                            <?php
                            $isActiveTabSet = false;
                            $tabIndex = 0;
                            foreach($eventGroups as $i => $eventGroup)
                            {
                                if (count($eventGroup->getEventCategories()) < 1 || count($eventGroup->getEvents()) < 1)
                                {
                                    continue;
                                }
                                ?>
                                <a href="#nhala-settings-tab-<?=$tabIndex?>" class="nhala-settings-event-group-tab-btn nav-tab<?=($isActiveTabSet ? '': ' nav-tab-active')?>"><?=$eventGroup->getDisplayName()?></a>
                                <?php
                                $isActiveTabSet = true;
                                $tabIndex++;
                            }
                            ?>
                        </h2>
                    </div>
                </div>
                <div class="nhala-row">
                    <div class="nhala-col-md-12">
                        <?php
                        $activeTabSet = false;
                        $eventIndex = 0;
                        $tabIndex = 0;
                        foreach($eventGroups as $eventGroup)
                        {
                            if(count($eventGroup->getEventCategories()) < 1 || count($eventGroup->getEvents()) < 1)
                            {
                                continue;
                            }
                            ?>
                            <div class="nhala-row nhala-integration-settings-event-group-tab nhala-tab<?=(!$activeTabSet ? ' nhala-active' : '')?>" id="nhala-settings-tab-<?=$tabIndex?>">
                                <div class="nhala-col-md-12">
                                    <div class="nhala-row nhala-settings-event-category-crumble">
                                        <div class="nhala-col-md-12">
                                            <ul class="subsubsub">
                                                <?php
                                                $categoryLoopIndex = 0;
                                                $categoryIndex = 0;
                                                $categoryContents = [];
                                                $delimiterPlaceholder = '%PLACEHOLDER__DELIMITER%';

                                                foreach($eventGroup->getEventCategories() as $eventCategory)
                                                {
                                                    $categoryLoopIndex++;

                                                    if(count($eventCategory->getEvents()) < 1)
                                                    {
                                                        continue;
                                                    }

                                                    ob_start();
                                                    ?>
                                                    <li class="all">
                                                        <a data-target-id="nhala-settings-tab-category-<?=$tabIndex.'_'.$categoryIndex?>" class="nhala-settings-event-category-tab-btn <?=($categoryIndex == 0) ? 'current' : ''?>"><?=$eventCategory->getDisplayName()?> <span class="count">(<span class="all-count"><?=count($eventCategory->getEvents())?></span>)</span></a>
                                                        <?=$delimiterPlaceholder?>
                                                    </li>
                                                    <?php
                                                    $categoryContents[] = ob_get_clean();
                                                    $categoryIndex++;
                                                }

                                                foreach($categoryContents as $i => $categoryContent)
                                                {
                                                    $delimiter = ' |';

                                                    if(($i + 1) >= count($categoryContents))
                                                    {
                                                        $delimiter = '';
                                                    }

                                                    echo str_replace($delimiterPlaceholder, $delimiter, $categoryContent);
                                                }
                                                ?>
                                            </ul>
                                        </div>
                                    </div>
                                    <?php
                                    $activeTabSet = true;
                                    $categoryIndex = 0;

                                    foreach($eventGroup->getEventCategories() as $eventCategory)
                                    {
                                        if(count($eventCategory->getEvents()) < 1)
                                        {
                                            continue;
                                        }

                                        ?>
                                        <div class="nhala-row nhala-integration-settings-event-category-tab nhala-tab<?=($categoryIndex == 0) ? ' nhala-active' : ''?>" id="nhala-settings-tab-category-<?=$tabIndex.'_'.$categoryIndex?>">
                                            <div class="nhala-col-md-12">
                                                <?php
                                                $categoryEventIndex = 1;
                                                foreach($eventCategory->getEvents() as $event)
                                                {
                                                    if(($categoryEventIndex % 4) == 0)
                                                    {
                                                        ?>
                                                        <div class="nhala-row">
                                                        <?php
                                                    }
                                                    ?>
                                                    <div class="nhala-col-md-3">
                                                        <div class="nhala-settings-event-block">
                                                            <?php
                                                            /** @var IntegrationEventModel $eventSetting */
                                                            $eventSetting = null;

                                                            foreach($eventSettings as $possibleEventSetting)
                                                            {
                                                                if(TypeHelper::getCleanClassNameString($possibleEventSetting->className) === TypeHelper::getCleanClassNameString(get_class($event)))
                                                                {
                                                                    $eventSetting = $possibleEventSetting;
                                                                    break;
                                                                }
                                                            }
                                                            ?>
                                                            <div class="nhala-row">
                                                                <div class="nhala-header">
                                                                    <div class="title">
                                                                        <b title="<?=$event->getName()?>"><?=$event->getName()?></b>
                                                                    </div>
                                                                    <label class="active-wrapper">
                                                                        <input class="event-active-action" type="checkbox" name="<?=SettingsService::INTEGRATION_SETTING_EVENT?>[<?=$eventIndex?>][isActive]" id="<?=SettingsService::INTEGRATION_SETTING_EVENT?>[<?=$eventIndex?>][isActive]" <?=checked((bool)TypeHelper::getPropertyValue($eventSetting, 'isActive', false))?>>
                                                                    </label>
                                                                </div>
                                                                <div class="nhala-body">
                                                                    <div class="content">
                                                                        <input type="hidden" name="<?=SettingsService::INTEGRATION_SETTING_EVENT?>[<?=$eventIndex?>][className]" value="<?=TypeHelper::getCleanClassNameString(get_class($event))?>" />
                                                                        <p class="description">
                                                                            <?=$event->getDescription()?>
                                                                        </p>
                                                                    </div>
                                                                    <div class="active-content<?=(true === (bool)TypeHelper::getPropertyValue($eventSetting, 'isActive', false)) ? ' nhala-active' : ''?>">
                                                                        <label>
                                                                            <div class="property-title"><?=__('Message', ConfigService::TEXT_DOMAIN_NAME)?></div>
                                                                            <textarea class="large-text" rows="5" name="<?=SettingsService::INTEGRATION_SETTING_EVENT?>[<?=$eventIndex?>][message]" id="<?=SettingsService::INTEGRATION_SETTING_EVENT?>[<?=$eventIndex?>][message]"><?=$event->getMessage($settings)?></textarea>
                                                                            <p class="description">
                                                                                <?=__('Default message:', ConfigService::TEXT_DOMAIN_NAME)?> <?=$event->getDefaultMessage()?>
                                                                            </p>
                                                                        </label>
                                                                        <?php
                                                                        echo $event->getSettingsUi($eventIndex, $eventSetting);
                                                                        ?>
                                                                    </div>

                                                                </div>
                                                                <div class="nhala-footer">

                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <?php
                                                    if(($categoryEventIndex % 4) == 0)
                                                    {
                                                        ?>
                                                        </div>
                                                        <br />
                                                        <?php
                                                    }
                                                    $categoryEventIndex++;
                                                    $eventIndex++;
                                                }
                                                ?>
                                            </div>
                                        </div>
                                        <?php
                                        $categoryIndex++;
                                    }
                                    ?>
                                </div>
                            </div>

                            <?php
                            $tabIndex++;
                        }
                        ?>
                    </div>
                </div>
                <script type="text/javascript">
                    <?php
                    //TODO: move to js file.
                    ?>
                    jQuery(document).ready(function($){
                        var eventsScopeId = '#nhala_integration_settings_events_meta_box';

                        $(eventsScopeId + ' .nhala-settings-event-group-tab-btn').click(function(e){
                            var self = $(this);

                            $(eventsScopeId + ' .nhala-settings-event-group-tab-btn').removeClass('nav-tab-active');
                            self.addClass('nav-tab-active');

                            var tabSelector = $(this).attr('href');
                            $(eventsScopeId + ' .nhala-integration-settings-event-group-tab').removeClass('nhala-active');
                            $(tabSelector).addClass('nhala-active');

                            var activeCategoryBtnSelector = $(tabSelector).find('.nhala-settings-event-category-tab-btn').first();
                            activeCategoryBtnSelector.trigger('click');
                        });

                        $(eventsScopeId + ' .nhala-settings-event-category-tab-btn').click(function(e){
                            var self = $(this);

                            $(eventsScopeId + ' .nhala-settings-event-category-tab-btn').removeClass('current');
                            self.addClass('current');

                            var tabSelector = '#' + $(this).attr('data-target-id');
                            $(eventsScopeId + ' .nhala-integration-settings-event-category-tab').removeClass('nhala-active');
                            $(tabSelector).addClass('nhala-active');
                        });

                        $(eventsScopeId + ' .nhala-settings-event-block .event-active-action').change(function(e){
                            var elementSelector = $(this).closest('.nhala-settings-event-block').find('.active-content').first();
                            if($(this).is(":checked") && !elementSelector.hasClass('nhala-active'))
                            {
                                elementSelector.addClass('nhala-active');
                            }else{
                                elementSelector.removeClass('nhala-active');
                            }
                        });
                    });
                </script>
                <?php
            },
            $this->name,
            'advanced',
            'high'
        );
    }

    public function saveSettings($postId)
    {
        if (get_post_type($postId) !== $this->name) {
            return;
        }

        $postedWpNonce = TypeHelper::getPropertyValue($_POST, $this->name . '_nonce', null);
        $postedSettings = TypeHelper::getPropertyValue($_POST, SettingsService::INTEGRATION_SETTINGS, []);
        $postedEvents = TypeHelper::getPropertyValue($_POST, SettingsService::INTEGRATION_SETTING_EVENT, []);

        if (empty($postedWpNonce))
        {
            return;
        }

        if (!wp_verify_nonce($postedWpNonce, $this->name.'_nonce')
            || (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
            || !current_user_can( 'manage_options')
            || empty($postedSettings)
        )
        {
            return;
        }

        $settings = new IntegrationSettingsModel();
        $settings->wpPostId = $postId;
        $settings->name = sanitize_text_field(TypeHelper::getPropertyValue($postedSettings, 'name', ''));
        $settings->endpointUrl = esc_url_raw(TypeHelper::getPropertyValue($postedSettings, 'endpointUrl', ''));
        $settings->channelName = sanitize_text_field(TypeHelper::getPropertyValue($postedSettings, 'channelName', ''));
        $settings->username = sanitize_text_field(TypeHelper::getPropertyValue($postedSettings, 'username', ''));
        $settings->iconEmoji = sanitize_text_field(TypeHelper::getPropertyValue($postedSettings, 'iconEmoji', ''));
        $settings->isActive = !empty(TypeHelper::getPropertyValue($postedSettings, 'isActive', null));

        $this->settingsManager->saveIntegrationSettings($settings);

        /** @var array $integrationEvents */
        $integrationEvents = [];
        foreach($postedEvents as $postedEvent)
        {
            $integrationEvent['wpPostId'] = $postId;
            $integrationEvent['className'] = sanitize_text_field(TypeHelper::getPropertyValue($postedEvent, 'className', ''));
            $integrationEvent['message'] = sanitize_text_field(TypeHelper::getPropertyValue($postedEvent, 'message', ''));
            $integrationEvent['isActive'] = !empty(TypeHelper::getPropertyValue($postedEvent, 'isActive', null));

            $event = $this->logicAppEventManager->getRegisteredEvent($integrationEvent['className']);
            if(is_object($event))
            {
                if(strtolower(trim($event->getDefaultMessage())) === strtolower(trim($integrationEvent['message'])))
                {
                    //TODO: make this work with multitple languages
                    $integrationEvent['message'] = ''; //If the message is the default, clear it so that changes in default messages will be applied.
                }

                //Call save for custom event settings. Values should be added to the $integrationEvent array
                $event->saveSettings($postId, $_POST, $postedSettings, $postedEvent, $integrationEvent);
            }

            $integrationEvents[] = $integrationEvent;
        }

        $this->settingsManager->saveIntegrationEvents($postId, $integrationEvents);
    }

    public function addSubmitMetaBox($postType) {
        if ($this->name === $postType) {
            add_meta_box(
                'nhala_submit_meta_box',
                __('Save', 'textdp,aom'),
                function($post){
                    ?>
                    <div class="submitbox" id="submitpost">

                        <div style="display:none;">
                            <?php submit_button( __('Save', ConfigService::TEXT_DOMAIN_NAME), 'button', 'save'); ?>
                        </div>

                        <?php // Always publish. ?>
                        <input type="hidden" name="post_status" id="hidden_post_status" value="publish" />

                        <div id="major-publishing-actions">

                            <div id="delete-action">
                                <?php
                                if ( ! EMPTY_TRASH_DAYS ) {
                                    $delete_text = __('Delete Permanently', ConfigService::TEXT_DOMAIN_NAME);
                                } else {
                                    $delete_text = __('Move to Trash', ConfigService::TEXT_DOMAIN_NAME);
                                }
                                ?>
                                <a class="submitdelete deletion" href="<?php echo get_delete_post_link( $post->ID ); ?>"><?php echo $delete_text; ?></a>
                            </div>

                            <div id="publishing-action">
                                <span class="spinner"></span>

                                <input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e( 'Save', ConfigService::TEXT_DOMAIN_NAME) ?>" />
                                <input name="save" type="submit" class="button button-primary button-large" id="publish" accesskey="p" value="<?php esc_attr_e( 'Save', ConfigService::TEXT_DOMAIN_NAME); ?>" />
                            </div>
                            <div class="clear"></div>

                        </div>
                        <div class="clear"></div>
                    </div>
                    <?php
                },
                null,
                'side',
                'core'
            );
        }
    }
}
