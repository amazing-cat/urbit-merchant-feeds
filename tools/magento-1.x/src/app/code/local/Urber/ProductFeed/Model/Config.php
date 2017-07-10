<?php

class Urber_ProductFeed_Model_Config
{
    /**
     * Default configuration fields
     * @var array
     */
    protected $_configDefault = [
        'category'       => [],
        'tag'            => [],
        'manufacturer'   => [],
        'stock'          => false,
        'cache_duration' => 3600,
    ];

    /**
     * Current configuration
     * @var array
     */
    protected $_config;

    /**
     * Urber_ProductFeed_Model_Config constructor.
     */
    public function __construct()
    {
        $this->load();
        $this->_config = $this->_config + $this->_configDefault;
    }

    /**
     * @return bool status of fetching data from db
     */
    public function load()
    {
        // TODO: Load configuration from db
        $this->_config = array();
        $config = Mage::getStoreConfig('productfeed_config');

        return true;
    }

    /**
     * @return bool status of saving data from db
     */
    public function save()
    {
        // TODO: Save configuration to db
        return true;
    }

    /**
     * @param string $name
     * @return mixed
     * @throws Exception
     */
    public function get($name)
    {
        if (!isset($this->_config[$name])) {
            throw new Exception("Try to get unknown configuration field - `{$name}`");
        }

        return $this->_config[$name];
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return mixed
     * @throws Exception
     */
    public function set($name, $value)
    {
        if (!isset($this->_config[$name])) {
            throw new Exception("Try to set unknown configuration field - `{$name}`");
        }

        $this->_config[$name] = $value;

        return $this;
    }

    /**
     * @param string $name
     * @return mixed
     * @throws Exception
     */
    public function __get($name)
    {
        if (isset($this->_config[$name])) {
            return $this->get($name);
        }

        throw new Exception("Try to get unknown configuration field - `{$name}`");
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return mixed
     * @throws Exception
     */
    public function __set($name, $value)
    {
        if (isset($this->_config[$name])) {
            return $this->set($name, $value);
        }

        throw new Exception("Try to set unknown configuration field - `{$name}`");
    }
}