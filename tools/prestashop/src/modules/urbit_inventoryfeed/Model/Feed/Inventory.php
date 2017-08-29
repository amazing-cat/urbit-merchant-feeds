<?php

/**
 * Class Inventory
 */
class Inventory
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

        if (!empty($this->combination)) {
            $this->id = (string)$this->combId;
        } else {
            $this->id = (string)$product->id;
        }

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
     * Process product prices
     */
    protected function processPrices()
    {
        //TODO: correct currency code
        $product = $this->product;

        if (!$this->combId) {
            // Regular price
            $prices = [
                [
                    "currency" => $this->currencyCode,
                    "value"    => number_format($product->price, 2, '.', ''),
                    "type"     => "regular",
                ],
            ];
        } else {
            //Special Price
            $specialPrice = SpecificPrice:: getByProductId($product->id, $this->combId);

            if (isset($specialPrice[0]['reduction'])  && ((float)$specialPrice[0]['reduction'] > 0.00)) {
                $prices = [
                    [
                        "currency" => $this->currencyCode,
                        "value"    => $this->combination['price'] + $specialPrice[0]['reduction'],
                        "type"     => "regular",
                    ],
                    [
                        "currency" => $this->currencyCode,
                        "value"    => $this->combination['price'],
                        "type"     => "sale",
                    ],
                ];
            } else {
                // Regular price
                $prices = [
                    [
                        "currency" => $this->currencyCode,
                        "value"    => $this->combination['price'],
                        "type"     => "regular",
                    ],
                ];
            }
        }

        $this->prices = $prices;
    }

    protected function processInventory()
    {
        //TODO: correct location + check quantity > 0

        $inventory = [];

        if (!$this->combId) {
            $quantity = Product::getQuantity($this->product->id);
            $location = isset($this->product->location) ? $this->product->location : 1;

            $inventory[] = [
                'location' => $location, // Location of current stock
                'quantity' => $quantity,   // Currently stocked items for location
            ];
        } else {

            $location = isset($this->combination['location']) ? $this->combination['location'] : 1;
            $inventory[] = [
                'location' => 1, // Location of current stock
                'quantity' => $this->combination['quantity'],   // Currently stocked items for location
            ];
        }

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
