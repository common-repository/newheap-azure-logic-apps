<?php

namespace NHAzureLogicApps\Application\Service\AjaxActionService;

if (!defined('ABSPATH'))
{
    exit;
}

use NHAzureLogicApps\Application\Helper\TypeHelper;
use NHAzureLogicApps\Application\Model\HttpResponseModel;
use NHAzureLogicApps\Application\Model\MessageModel;
use NHAzureLogicApps\Application\Service\ConfigService\ConfigService;
use NHAzureLogicApps\Application\Service\HttpService\HttpService;
use NHAzureLogicApps\Application\Service\IService;
use NHAzureLogicApps\Application\Service\LogicAppService\LogicAppService;
use NHAzureLogicApps\Application\Service\PostTypeService\PostTypeService;
use NHAzureLogicApps\Application\Service\SettingsService\SettingsService;

class AjaxActionService implements IService
{
    /** @var  LogicAppService */
    private $logicAppManager;

    /** @var  HttpService */
    private $httpManager;

    /** @var  SettingsService */
    private $settingsManager;

    /** @var  PostTypeService */
    private $postTypeManager;

    /** @var string */
    private $actionPrefix;

    /** @var string */
    private $actionPrefixNoPriv;

    public function __construct(
        LogicAppService $logicAppManager,
        HttpService $httpManager,
        SettingsService $settingsManager,
        PostTypeService $postTypeManager
    )
    {
        $this->logicAppManager = $logicAppManager;
        $this->httpManager = $httpManager;
        $this->settingsManager = $settingsManager;
        $this->postTypeManager = $postTypeManager;

        $this->actionPrefix = 'wp_ajax_nhala_';
        $this->actionPrefixNoPriv = 'wp_ajax_nopriv_nhala_';

        $this->register();
    }

    protected function register()
    {
        $this->registerAction('integration_endpoint_test', false, [$this, 'integrationEndpointTest']);
    }

    public function registerAction($name, $allowNoPriv, callable $function, $priority = 10, $acceptedArgs = 1)
    {
        add_action($this->actionPrefix.$name, $function, $priority, $acceptedArgs);

        if($allowNoPriv)
        {
            add_action($this->actionPrefixNoPriv.$name, $function, $priority, $acceptedArgs);
        }
    }

    public function integrationEndpointTest()
    {
        $response = new HttpResponseModel();
        $response->response = "error";
        $response->statusCode = 500;

        $data = [
            'postId' => TypeHelper::getPropertyValue($_POST, 'postId', null),
        ];

        if(empty($data['postId']))
        {
            echo json_encode($response);
            wp_die();
        }

        $integrationSettings = $this->settingsManager->getIntegrationSettings($data['postId']);

        if(!is_object($integrationSettings) || !(int)$integrationSettings->wpPostId == (int)$data['postId'])
        {
            echo json_encode($response);
            wp_die();
        }

        $message = strtr(__('Testing Logic app integration with the name %integration_name%, running on site <%site_url%|%site_name%>.', ConfigService::TEXT_DOMAIN_NAME), [
            '%site_name%' => get_bloginfo('name'),
            '%site_url%' => network_site_url('/'),
            '%integration_name%' => $integrationSettings->name,
        ]);

        $msgModel = new MessageModel($this, $integrationSettings->endpointUrl, $message);
        $response = $this->logicAppManager->eventManager->dispatch($msgModel);

        echo json_encode($response);
        wp_die();
    }
}
