<?php

namespace NHAzureLogicApps\Application\Service\ConfigService;

if (!defined('ABSPATH'))
{
    exit;
}

use NHAzureLogicApps\Application\Service\IService;

class ConfigService implements IService
{
    const TEXT_DOMAIN_NAME = 'nhala';

    /** @var string */
    private $name;

    /** @var string */
    private $version;

    /** @var  string */
    private $baseDirectory;

    /** @var  string */
    private $baseUrl;

    /** @var  string */
    private $assetsUrl;

    /** @var  string */
    private $assetsDirectory;

    public function __construct($baseDirectory)
    {
        $this->name = 'Azure Logic apps by NewHeap';
        $this->version = '1.0.0';
        $this->baseDirectory = trailingslashit($baseDirectory);
        $this->baseUrl = trailingslashit(plugin_dir_url($this->baseDirectory."fakeFile.dist"));
        $this->assetsDirectory = trailingslashit($this->baseDirectory."Assets");
        $this->assetsUrl = trailingslashit($this->baseUrl."Assets");
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @return string
     */
    public function getBaseDirectory()
    {
        return $this->baseDirectory;
    }

    /**
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    /**
     * @return string
     */
    public function getAssetsUrl()
    {
        return $this->assetsUrl;
    }

    /**
     * @return string
     */
    public function getAssetsDirectory()
    {
        return $this->assetsDirectory;
    }
}
