<?php

/**
 * Class Inventory
 */
class Urbit_Inventoryfeed_Inventory
{
    /**
     * Array with product fields
     * @var array
     */
    protected $data = [];

    /**
     * Product Object
     * @var Object
     */
    protected $product;

    /**
     * Combination Id
     * @var int
     */
    protected $combId;

    /**
     * Product combination with quantity, price, attributes information
     * @var array
     */
    protected $combination = [];

    /**
     * PrestaShop Context
     * @var object
     */
    protected $context = null;

    /**
     * Inventory constructor.
     * @param $product
     * @param null $combId
     * @param null $combination
     */
    public function __construct($product, $combId = null, $combination = null)
    {
        $this->product = new Product($product['id_product']);
        $this->context = Context::getContext();

        if ($combId) {
            $this->combId = $combId;
            $this->combination = $combination;
        }
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->data;
    }

    /**
     * Get feed product data fields
     * @param string $name
     * @return mixed|null
     */
    public function __get($name)
    {
        if (isset($this->data[$name])) {
            return $this->data[$name];
        }

        if (stripos($name, 'is') === 0 && method_exists($this, $name)) {
            return $this->{$name}();
        }

        $getMethod = "get{$name}";

        if (method_exists($this, $getMethod)) {
            return $this->{$getMethod}();
        }

        return null;
    }

    /**
     * Set feed product data fields
     * @param string $name
     * @param mixed $value
     */
    public function __set($name, $value)
    {
        $setMethod = "set{$name}";

        if (method_exists($this, $setMethod)) {
            $this->{$setMethod}($value);

            return;
        }

        $this->data[$name] = $value;
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return $this|mixed|null
     * @throws Exception
     */
    public function __call($name, $arguments)
    {
        $property = strtolower(preg_replace("/^unset/", '', $name));
        $propertyExist = isset($this->data[$property]);

        if ($propertyExist) {
            if (stripos($name, 'unset') === 0) {
                unset($this->data[$property]);

                return $this;
            }

            if (stripos($name, 'get') === 0) {
                return $this->{$property};
            }

            if (stripos($name, 'set') === 0 && isset($arguments[0])) {
                $this->{$property} = $arguments[0];

                return $this;
            }
        }

        throw new Exception("Unknown method {$name}");
    }

    /**
     * Get data for feed
     * @return bool
     */
    public function process()
    {
        $this->processId();

        //add inventory information to feed
        $positive_quantity = $this->processInventory();

        if (!$positive_quantity) {
            return false;
        }

        //add price information to feed
        $this->processPrices();

        return true;
    }

    /**
     * @param string $name
     * @return mixed
     */
    protected function _processAttribute($name)
    {
        return Urbit_Inventoryfeed_Fields_Factory::processAttribute($this, $name);
    }

    /**
     * @param string $key
     * @return mixed
     */
    protected function _processAttributeByKey($key)
    {
        return Urbit_Inventoryfeed_Fields_Factory::processAttributeByKey($this, $key);
    }


    /**
     * @param string $name
     * @param string $key
     * @return mixed
     */
    protected function _processAttributeOrByKey($name, $key)
    {
        return $this->_processAttribute($name) ?: $this->_processAttributeByKey($key);
    }

    /**
     * Process product id
     * add to feed product id
     */
    protected function processId()
    {
        if ($id = $this->_processAttribute('URBIT_INVENTORYFEED_ATTRIBUTE_ID')) {
            $this->id = (string)$id . (empty($this->combination) ? '' : '-' . $this->getCombId());
        } elseif (empty($this->combination)) {
            $this->id = (string)$this->getProduct()->id;
        } else {
            $combinations = $this->getCombination();
            $true = isset($combinations['reference']) && $combinations['reference'];
            $cid = $this->getCombId();

            $this->id = $true ? $combinations['reference'] . '-' . $cid : $this->getProduct()->id . '-' . $cid;
        }
    }

    /**
     * Process product prices
     */
    protected function processPrices()
    {
        $prices = [];

        $regularPrice = $this->_processAttributeOrByKey('URBIT_INVENTORYFEED_REGULAR_PRICE_VALUE', 'calc_RegularPrice');
        $salePrice    = $this->_processAttributeOrByKey('URBIT_INVENTORYFEED_SALE_PRICE_VALUE', 'calc_SalePrice');

        // regular price
        $prices[] = [
            'currency' => $this->_processAttributeOrByKey('URBIT_INVENTORYFEED_REGULAR_PRICE_CURRENCY', 'calc_Currency'),
            'value'    => $regularPrice,
            'vat'      => $this->_processAttributeOrByKey('URBIT_INVENTORYFEED_REGULAR_PRICE_VAT', 'calc_TaxRate'),
            'type'     => 'regular',
        ];

        //sale price
        if ($salePrice != $regularPrice) {
            $sPrice = [
                'currency' => $this->_processAttributeOrByKey('URBIT_INVENTORYFEED_SALE_PRICE_CURRENCY', 'calc_Currency'),
                'value'    => $salePrice,
                'vat'      => $this->_processAttributeOrByKey('URBIT_INVENTORYFEED_SALE_PRICE_VAT', 'calc_TaxRate'),
                'type'     => 'sale',
            ];

            if ($salePriceDate = $this->_processAttribute('URBIT_INVENTORYFEED_PRICE_EFFECTIVE_DATE')) {
                $sPrice['price_effective_date'] = $salePriceDate;
            }

            $prices[] = $sPrice;
        }

        $this->prices = $prices;
    }

    /**
     * Process product inventory
     */
    protected function processInventory()
    {
        $location = $this->_processAttributeOrByKey('URBIT_INVENTORYFEED_INVENTORY_LOCATION', 'calc_Location');
        $qty      = $this->_processAttributeOrByKey('URBIT_INVENTORYFEED_INVENTORY_QUANTITY', 'calc_Quantity');

        if ($qty <= 0) {
            return false;
        }

        $this->inventory = [[
            'location' => $location,
            'quantity' => $qty,
        ]];

        return true;
    }

    /**
     * @return Object|Product
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @param string $name
     * @return string
     */
    public function getProductAttribute($name)
    {
        if (isset($this->product->{$name})) {
            return $this->product->{$name};
        }

        return '';
    }

    /**
     * @return Context|object
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @return array|null
     */
    public function getCombination()
    {
        return $this->combination;
    }

    /**
     * @return int|null
     */
    public function getCombId()
    {
        return $this->combId;
    }

    /**
     * Helper function
     * Get currency code for current store
     * @return string
     */
    protected function getCurrencyCode()
    {
        $context = Context::getContext();

        return $context->currency->iso_code;
    }

    /**
     * @param $configValue
     * @return null
     */
    protected function getFieldValueByConfigValue($configValue)
    {
        $product = $this->product;
        $type = substr($configValue, 0, 1);
        $id = substr($configValue, 1);

        switch ($type) {
            // attribute
            case 'a':
                $attributeCombinations = $product->getAttributeCombinations($this->context->language->id);

                foreach ($attributeCombinations as $attributeCombination) {
                    if ($attributeCombination['id_product_attribute'] == $this->combId && $attributeCombination['id_attribute_group'] == $id) {
                        return $attributeCombination['attribute_name'];
                    }
                }
                break;

            // feature
            case 'f':
                $FrontFeatures = $product->getFrontFeatures($this->context->language->id);

                foreach ($FrontFeatures as $frontFeature) {
                    if ($frontFeature['id_feature'] == $id) {
                        return $frontFeature['value'];
                    }
                }
                break;
        }

        return null;
    }
}
