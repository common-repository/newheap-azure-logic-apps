<?php
namespace NHAzureLogicApps\Application\Model;

if (!defined('ABSPATH'))
{
    exit;
}

class HttpResponseModel implements IModel
{
    public $statusCode;
    public $response;
}
