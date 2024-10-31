<?php
namespace NHAzureLogicApps\Application\Model;

if (!defined('ABSPATH'))
{
    exit;
}

class ResponseModel implements IModel
{
    /** @var  int */
    public $resultCode;
}
