<?php

/**
 * Class Urber_ProductFeed_Model_Config
 *
 * @property array cron
 * @property array filter
 * @property array fields
 */
class Urber_ProductFeed_Model_Config
{
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
    }

    /**
     * Load configuration from magento scope
     */
    public function load()
    {
        $this->_config = Mage::getStoreConfig('productfeed_config');
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
        $param = false;

        if (stripos($name, '/')) {
            list($name, $param) = explode('/', $name);
        }

        if (!isset($this->_config[$name])) {
            throw new Exception("Try to get unknown configuration field - `{$name}`");
        }

        if ($param !== false) {
            if (!isset($this->_config[$name][$param])) {
                throw new Exception("Try to get unknown parameter of configuration field - `{$name}/{$param}`");
            }

            return $this->_config[$name][$param];
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
        $param = false;

        if (stripos($name, '/')) {
            list($name, $param) = explode('/', $name);
        }

        if (!isset($this->_config[$name])) {
            throw new Exception("Try to set unknown configuration field - `{$name}`");
        }

        if ($param !== false) {
            if (!isset($this->_config[$name][$param])) {
                throw new Exception("Try to set unknown parameter of configuration field - `{$name}/{$param}`");
            }

            $this->_config[$name][$param] = $value;

        } else {
            $this->_config[$name] = $value;
        }

        return $this;
    }

    /**
     * @param string $name
     * @return mixed
     * @throws Exception
     */
    public function __get($name)
    {
        return $this->get($name);
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return mixed
     * @throws Exception
     */
    public function __set($name, $value)
    {
        return $this->set($name, $value);
    }
}