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
        $property = strtolower(preg_replace("/^unset/", $name));
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
        $product = $this->product;

        $this->id = empty($this->combination) ?
            ($product->reference ? $product->reference : (string) $product->id):
            ($this->combination['reference'] ? $this->combination['reference'] : $product->id . '-' . $this->combId)
        ;

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
     * @param integer $productId
     * @param null|integer $combId
     * @param boolean $useReduction
     * @return string
     */
    protected function getPrice($productId, $combId = null, $useReduction = true)
    {
        return number_format(
            Product::getPriceStatic($productId, true, ($combId ? $combId : null), 6, NULL, false, $useReduction),
            2,'.',''
        );
    }

    /**
     * Process product prices
     */
    protected function processPrices()
    {
        $product = $this->product;

        $regularPrice = $this->getPrice($product->id, $this->combId, false);
        $salePrice    = $this->getPrice($product->id, $this->combId, true);

        $prices = [
            [
                "currency" => $this->currencyCode,
                "value"    => $regularPrice,
                "type"     => "regular",
            ],
        ];

        if ($regularPrice !== $salePrice) {
            $prices[] = [
                "currency" => $this->currencyCode,
                "value"    => $salePrice,
                "type"     => "sale",
            ];
        }

        $this->prices = $prices;
    }

    protected function getFullPrice($price, $reductionType, $reductionValue)
    {

        switch ($reductionType) {
            case 'amount':
                return $price + $reductionValue;
                break;
            case 'percentage':
                return number_format(floor(($price / (1.00 - $reductionValue)) * 100) / 100, 2, '.', '');
                break;
        }

        return $price;
    }

    protected function processInventory()
    {
        $inventory = [];

        if (!$this->combId) {
            $quantity = Product::getQuantity($this->product->id);
            $location = ((isset($this->product->location)) && ($this->product->location != '')) ? $this->product->location : 1;

        } else {
            $location = ((isset($this->combination['location'])) && ($this->combination['location'] != '')) ? $this->combination['location'] : 1;
            $quantity = $this->combination['quantity'];
        }

        $inventory[] = [
            'location' => $location, // Location of current stock
            'quantity' => $quantity, // Currently stocked items for location
        ];

        $this->inventory = $inventory;

        return true;
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

}
