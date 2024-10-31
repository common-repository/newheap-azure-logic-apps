<?php
/*
    Plugin Name: NewHeap Azure Logic apps
    Plugin URI: https://www.newheap.com/
    Version: 1.0.0
    Author: NewHeap
    Description: Azure Logic apps integration for Wordpress.
*/

if (!defined('ABSPATH'))
{
    exit;
}

require_once (__DIR__) . '/Application/NHAzureLogicAppsModule.php';

use NHAzureLogicApps\Application\NHAzureLogicAppsModule;

NHAzureLogicAppsModule::getInstance();




