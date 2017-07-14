<?php

namespace Urbit\ProductFeed\Model\Config;


class ConfigModel
{
    /**
     * @var Config
     */
    private $_config;

    public function __construct()
    {
        $this->_config = $configFactory->create();
    }

    protected final function config()
    {
        return $this->_config;
    }

    protected final function getConfig($name)
    {
        return $this->config()->get($name);
    }
}