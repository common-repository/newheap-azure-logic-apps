<?php
namespace NHAzureLogicApps\Application\Model;

use NHAzureLogicApps\Application\Event\AbstractEvent;

if (!defined('ABSPATH'))
{
    exit;
}

class MessageModel implements IModel
{
    /** @var  IntegrationSettingsModel */
    public $integration;

    /** @var  string */
    public $text;

    /** @var AttachmentModel[]  */
    public $attachments;

    /** @var AbstractEvent */
    public $event;

    /** @var mixed */
    public $data;

    public function __construct(AbstractEvent $event, IntegrationSettingsModel $integration, $text = null, $data = null)
    {
        $this->event = $event;
        $this->integration = $integration;
        $this->text = $text;
        $this->data = $data;
        $this->attachments = [];
    }
}
