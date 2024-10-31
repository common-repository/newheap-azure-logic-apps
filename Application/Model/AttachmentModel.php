<?php
namespace NHAzureLogicApps\Application\Model;

if (!defined('ABSPATH'))
{
    exit;
}

class AttachmentModel implements IModel
{
    /** @var  string */
    public $fallback;

    /** @var  string */
    public $color;

    /** @var  string */
    public $pretext;

    /** @var  string */
    public $author_name;

    /** @var  string */
    public $author_link;

    /** @var  string */
    public $author_icon;

    /** @var  string */
    public $title;

    /** @var  string */
    public $title_link;

    /** @var  string */
    public $text;

    /** @var  FieldModel[] */
    public $fields;

    /** @var  string */
    public $image_url;

    /** @var  string */
    public $thumb_url;

    /** @var  string */
    public $footer;

    /** @var  string */
    public $ts;


    public function __construct()
    {
        $this->fields = [];
        $this->footer = 'nhala';
        $this->ts = (new \DateTime())->getTimestamp();
    }
}
