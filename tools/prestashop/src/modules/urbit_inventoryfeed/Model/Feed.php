<?php

include_once(_PS_MODULE_DIR_ . 'urbit_inventoryfeed' . DIRECTORY_SEPARATOR . 'Model' . DIRECTORY_SEPARATOR . 'Feed' . DIRECTORY_SEPARATOR . 'Inventory.php');

/**
 * Class Feed
 */
class Feed
{
    const SCHEDULE_INTERVAL_5MIN = '5MIN';
    const SCHEDULE_INTERVAL_15MIN = '15MIN';
    const SCHEDULE_INTERVAL_30MIN = '30MIN';
    const SCHEDULE_INTERVAL_45MIN = '45MIN';
    const SCHEDULE_INTERVAL_HOURLY = 'HOURLY';

    const SCHEDULE_INTERVAL_5MIN_TIME = 5;
    const SCHEDULE_INTERVAL_15MIN_TIME = 15;
    const SCHEDULE_INTERVAL_30MIN_TIME = 30;
    const SCHEDULE_INTERVAL_45MIN_TIME = 45;
    const SCHEDULE_INTERVAL_HOURLY_TIME = 60;

    const FEED_VERSION = '2017-06-28-1';

    /**
     * Valid products for using in feed
     * @var array
     */
    protected $data = [];

    /**
     * Collection of shop's products
     * @var array
     */
    protected $collection = [];

    /**
     * Prestashop Context
     * @var object
     */
    protected $context = null;

    /**
     * Feed constructor.
     * @param $collection
     */
    public function __construct($collection)
    {
        $this->collection = $collection;
        $this->context = Context::getContext();
    }

    /**
     * Process products to use in feed
     */
    protected function process()
    {
        $inventory = [];

        foreach ($this->collection as $product) {
            //get all combinations of product
            $combinations = $this->getCombinations($product['id_product']);

            if (empty($combinations)) { //simple product
                $feedInventory = new Inventory($product);

                if ($feedInventory->process()) {
                    $inventory[] = $feedInventory->toArray();
                }

            } else { //product with variables

                foreach ($combinations as $combId => $combination) {
                    $feedInventory = new Inventory($product, $combId, $combination);

                    if ($feedInventory->process()) {
                        $inventory[] = $feedInventory->toArray();
                    }
                }
            }
        }

        $this->data = $inventory;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        if (empty($this->data)) {
            $this->process();
        }

        $lang = $this->context->language->locale;
        $version = $this->getFeedVersion();

        return [
            '$schema'            => "https://raw.githubusercontent.com/urbitassociates/urbit-merchant-feeds/master/schemas/inventory/{$version}/inventory.json",
            'content_language'   => $lang,
            'attribute_language' => $lang,
            'content_type'       => 'inventory',
            'target_country'     => [
                $lang,
            ],
            'version'            => $version,
            'feed_format'        => [
                "encoding" => "UTF-8",
            ],
            'schedule'           => [
                'interval' => $this->getIntervalText(),
            ],
            'entities'           => $this->data,
        ];
    }

    /**
     * return all combinations(variants) of product
     * @param $productId
     * @return array Combinations with quantity, price, attributes information
     */
    public function getCombinations($productId)
    {
        $context = Context::getContext();
        $productEntity = new Product($productId);

        $infoArray = [];

        //get all variants of product
        $combinations = $productEntity->getAttributeCombinations($context->language->id);

        foreach ($combinations as $combination) {
            if (!array_key_exists($combination['id_product_attribute'], $infoArray)) {
                $infoArray[$combination['id_product_attribute']] = [
                    'quantity'   => $combination['quantity'],
                    'price'      => number_format((float)Product::getPriceStatic($productId, true, $combination['id_product_attribute']), 2, '.', ''),
                    'attributes' => [$combination['group_name'] => $combination['attribute_name']],
                ];
            } else {
                $infoArray[$combination['id_product_attribute']]['attributes'][$combination['group_name']] = $combination['attribute_name'];
            }
        }

        return $infoArray;
    }

    /**
     * Get schedule interval value
     * @return string
     */
    public function getIntervalText()
    {
        $cacheDuration = Configuration::get('URBIT_INVENTORYFEED_CACHE_DURATION', null);

        if (!$cacheDuration) {
            return static::SCHEDULE_INTERVAL_HOURLY;
        }

        foreach ([
            self::SCHEDULE_INTERVAL_5MIN_TIME   => self::SCHEDULE_INTERVAL_5MIN,
            self::SCHEDULE_INTERVAL_15MIN_TIME  => self::SCHEDULE_INTERVAL_15MIN,
            self::SCHEDULE_INTERVAL_30MIN_TIME  => self::SCHEDULE_INTERVAL_30MIN,
            self::SCHEDULE_INTERVAL_45MIN_TIME  => self::SCHEDULE_INTERVAL_45MIN,
            self::SCHEDULE_INTERVAL_HOURLY_TIME => self::SCHEDULE_INTERVAL_HOURLY,
        ] as $time => $val) {
            if ($cacheDuration <= $time) {
                return $val;
            }
        }

        return static::SCHEDULE_INTERVAL_HOURLY;
    }

    /**
     * @return string
     */
    public function getFeedVersion()
    {
        return static::FEED_VERSION;
    }
}
