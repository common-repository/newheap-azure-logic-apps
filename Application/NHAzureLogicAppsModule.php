<?php

namespace NHAzureLogicApps\Application;

use NHAzureLogicApps\Application\Event\CoreEvent\Attachment\AttachmentDeletedEvent;
use NHAzureLogicApps\Application\Event\CoreEvent\Attachment\AttachmentModifiedEvent;
use NHAzureLogicApps\Application\Event\CoreEvent\Attachment\AttachmentNewEvent;
use NHAzureLogicApps\Application\Event\CoreEvent\Post\CommentNewEvent;
use NHAzureLogicApps\Application\Event\CoreEvent\Post\CommentStatusChangedEvent;
use NHAzureLogicApps\Application\Event\CoreEvent\Post\PostCreatedEvent;
use NHAzureLogicApps\Application\Event\CoreEvent\Post\PostDeletedEvent;
use NHAzureLogicApps\Application\Event\CoreEvent\Post\PostThrashedEvent;
use NHAzureLogicApps\Application\Event\CoreEvent\Post\PostUpdatedEvent;
use NHAzureLogicApps\Application\Event\CoreEvent\System\PluginActivatedEvent;
use NHAzureLogicApps\Application\Event\CoreEvent\System\PluginDeactivatedEvent;
use NHAzureLogicApps\Application\Event\CoreEvent\System\PluginDeletedEvent;
use NHAzureLogicApps\Application\Event\CoreEvent\User\UserDeletedEvent;
use NHAzureLogicApps\Application\Event\CoreEvent\User\UserLoginFailedEvent;
use NHAzureLogicApps\Application\Event\CoreEvent\User\UserLoginSuccessfulEvent;
use NHAzureLogicApps\Application\Event\CoreEvent\User\UserNewEvent;
use NHAzureLogicApps\Application\Event\CoreEvent\User\UserRoleChangedEvent;
use NHAzureLogicApps\Application\Event\CoreEvent\User\UserUpdatedEvent;
use NHAzureLogicApps\Application\Event\EventCategory\AuthenticationCategory;
use NHAzureLogicApps\Application\Event\EventCategory\GeneralEventCategory;
use NHAzureLogicApps\Application\Event\EventCategory\MiscEventCategory;
use NHAzureLogicApps\Application\Event\EventGroup\AttachmentEventGroup;
use NHAzureLogicApps\Application\Event\EventGroup\SystemEventGroup;
use NHAzureLogicApps\Application\Event\EventGroup\WooCommerceEventGroup;
use NHAzureLogicApps\Application\Event\EventGroup\GeneralEventGroup;
use NHAzureLogicApps\Application\Event\EventGroup\PostsEventGroup;
use NHAzureLogicApps\Application\Event\EventGroup\UsersEventGroup;
use NHAzureLogicApps\Application\PostType\LogicAppIntegrationPostType;
use NHAzureLogicApps\Application\Service\ConfigService\ConfigService;
use NHAzureLogicApps\Application\Service\AjaxActionService\AjaxActionService;
use NHAzureLogicApps\Application\Service\HttpService\HttpService;
use NHAzureLogicApps\Application\Service\PostTypeService\PostTypeService;
use NHAzureLogicApps\Application\Service\LogicAppEventService\LogicAppEventService;
use NHAzureLogicApps\Application\Service\SettingsService\SettingsService;
use NHAzureLogicApps\Application\Service\LogicAppService\LogicAppService;

if (!defined('ABSPATH'))
{
    exit;
}

final class NHAzureLogicAppsModule
{
    /** @var \NHAzureLogicApps\Application\NHAzureLogicAppsModule */
    private static $instance;

    /** @var  ConfigService */
    protected $configManager;

    /** @var  HttpService */
    protected $httpManager;

    /** @var  LogicAppService */
    protected $logicAppManager;

    /** @var  PostTypeService */
    protected $postTypeManager;

    /** @var  SettingsService */
    protected $settingsManager;

    /** @var  AjaxActionService */
    protected $ajaxActionManager;

    /**
     * @return \NHAzureLogicApps\Application\NHAzureLogicAppsModule
     */
    public static function getInstance()
    {
        if (!isset(static::$instance)) {
            static::$instance = new static();

            $instance = static::$instance;

            $instance->loadApplication();
            $instance->loadServices();
            $instance->loadPostTypes();
            $instance->registerEventGroups();
            $instance->registerEventCategories();
            $instance->registerEvents();
            $instance->enqueueStyles();
            $instance->enqueueScripts();
        }

        return self::$instance;
    }

    private function __construct()
    {
    }

    private function __clone()
    {
    }

    /**
     * @return LogicAppService
     */
    public function getLogicAppManager()
    {
        return $this->logicAppManager;
    }

    private function loadServices()
    {
        $this->configManager = new ConfigService(trailingslashit(plugin_dir_path(__DIR__)));
        $this->httpManager = new HttpService();
        $this->settingsManager = new SettingsService();
        $this->logicAppManager = new LogicAppService(new LogicAppEventService($this->settingsManager, $this->httpManager, $this->configManager));
        $this->postTypeManager = new PostTypeService($this->settingsManager);
        $this->ajaxActionManager = new AjaxActionService($this->logicAppManager, $this->httpManager, $this->settingsManager, $this->postTypeManager);
    }

    private function loadPostTypes()
    {
        $this->postTypeManager->register(new LogicAppIntegrationPostType($this->settingsManager, $this->logicAppManager->eventManager));
    }

    private function registerEventGroups()
    {
        $this->logicAppManager->eventManager->registerEventGroup(GeneralEventGroup::class);
        $this->logicAppManager->eventManager->registerEventGroup(PostsEventGroup::class);
        $this->logicAppManager->eventManager->registerEventGroup(UsersEventGroup::class);
        $this->logicAppManager->eventManager->registerEventGroup(AttachmentEventGroup::class);
        $this->logicAppManager->eventManager->registerEventGroup(SystemEventGroup::class);
        $this->logicAppManager->eventManager->registerEventGroup(WooCommerceEventGroup::class);

        do_action('nhala_register_event_group', $this->logicAppManager->eventManager);
    }

    private function registerEventCategories()
    {
        $this->logicAppManager->eventManager->registerEventCategory(GeneralEventCategory::class);
        $this->logicAppManager->eventManager->registerEventCategory(AuthenticationCategory::class);
        $this->logicAppManager->eventManager->registerEventCategory(MiscEventCategory::class);

        do_action('nhala_register_event_category', $this->logicAppManager->eventManager);
    }

    private function registerEvents()
    {
        //<editor-fold desc="Core events">
        $this->logicAppManager->eventManager->registerEvent(PostCreatedEvent::class);
        $this->logicAppManager->eventManager->registerEvent(PostUpdatedEvent::class);
        $this->logicAppManager->eventManager->registerEvent(PostDeletedEvent::class);
        $this->logicAppManager->eventManager->registerEvent(PostThrashedEvent::class);

        $this->logicAppManager->eventManager->registerEvent(CommentNewEvent::class);
        $this->logicAppManager->eventManager->registerEvent(CommentStatusChangedEvent::class);

        $this->logicAppManager->eventManager->registerEvent(UserLoginSuccessfulEvent::class);
        $this->logicAppManager->eventManager->registerEvent(UserLoginFailedEvent::class);
        $this->logicAppManager->eventManager->registerEvent(UserNewEvent::class);
        $this->logicAppManager->eventManager->registerEvent(UserUpdatedEvent::class);
        $this->logicAppManager->eventManager->registerEvent(UserDeletedEvent::class);
        $this->logicAppManager->eventManager->registerEvent(UserRoleChangedEvent::class);

        $this->logicAppManager->eventManager->registerEvent(PluginActivatedEvent::class);
        $this->logicAppManager->eventManager->registerEvent(PluginDeActivatedEvent::class);
        $this->logicAppManager->eventManager->registerEvent(PluginDeletedEvent::class);
        //$this->logicAppManager->eventManager->registerEvent(PluginUpdateAvailableEvent::class);

        $this->logicAppManager->eventManager->registerEvent(AttachmentNewEvent::class);
        $this->logicAppManager->eventManager->registerEvent(AttachmentModifiedEvent::class);
        $this->logicAppManager->eventManager->registerEvent(AttachmentDeletedEvent::class);
        //</editor-fold>

        do_action('nhala_register_event', $this->logicAppManager->eventManager);
    }

    private function loadApplication()
    {
        $pluginDirectory = trailingslashit(plugin_dir_path(__DIR__));
        $applicationDirectory = trailingslashit($pluginDirectory."Application");

        /** @var \SplFileInfo[] $applicationFiles */
        $applicationFiles = $this->locateFilesFromDir($applicationDirectory);

        foreach($applicationFiles as $applicationFile)
        {
            if($applicationFile->getExtension() !== "php")
            {
                continue;
            }

            spl_autoload_register(function($className) use ($pluginDirectory){

                if(!empty($className))
                {
                    if(strpos($className, "NHAzureLogicApps\\Application\\") !== false)
                    {
                        $classNameExplode = explode('\\', $className);
                        unset($classNameExplode[0]);
                        $path = $pluginDirectory.implode('/', $classNameExplode).'.php';

                        require_once($path);
                    }
                }
            });
        }
    }

    /**
     * @param $directory
     * @param bool $includeEmptyDirs
     * @return \SplFileInfo[]
     */
    private function locateFilesFromDir($directory, $includeEmptyDirs = false)
    {
        /** @var \SplFileInfo[] $files */
        $files = [];

        //Scan directory recursive, skip dots and use leaves only.
        $it = new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS);
        $itMode = (true === $includeEmptyDirs) ? \RecursiveIteratorIterator::SELF_FIRST : \RecursiveIteratorIterator::LEAVES_ONLY;

        /**
         * @var string $filename
         * @var \SplFileInfo $file
         */
        foreach (new \RecursiveIteratorIterator($it, $itMode) as $filename => $file)
        {
            $files[] = $file;
        }

        return $files;
    }

    private function enqueueScripts()
    {
        add_action( 'admin_enqueue_scripts', function($hook){

            wp_enqueue_script($this->configManager->getName().'_backend_js', $this->configManager->getAssetsUrl().'js/backend.js', array('jquery'), false, true);
        }, 100);

        add_action( 'wp_enqueue_scripts', function($hook){

        });
    }

    private function enqueueStyles()
    {
        add_action('admin_print_styles', function(){
            wp_enqueue_style($this->configManager->getName().'_backend_css', $this->configManager->getAssetsUrl().'css/backend.css');
        });
    }
}
