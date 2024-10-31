<?php
namespace NHAzureLogicApps\Application\Model;

if (!defined('ABSPATH'))
{
    exit;
}

class FieldModel implements IModel
{
    /** @var  string */
    public $title;

    /** @var  string */
    public $value;

    /** @var  string */
    public $short;

    public function __construct($title, $value, $short = true)
    {
        $this->title = $title;
        $this->value = $value;
        $this->short = $short;
    }
}
