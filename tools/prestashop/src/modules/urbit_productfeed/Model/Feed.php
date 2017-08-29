<?php

include_once(_PS_MODULE_DIR_ . 'urbit_productfeed' . DIRECTORY_SEPARATOR . 'Model' . DIRECTORY_SEPARATOR . 'Feed' . DIRECTORY_SEPARATOR . 'FeedProduct.php');

/**
 * Class Feed
 */
class Feed
{
    const SCHEDULE_INTERVAL_HOURLY = 'HOURLY';
    const SCHEDULE_INTERVAL_DAILY = 'DAILY';
    const SCHEDULE_INTERVAL_WEEKLY = 'WEEKLY';
    const SCHEDULE_INTERVAL_MONTHLY = 'MONTHLY';

    const SCHEDULE_INTERVAL_HOURLY_TIME = 1;
    const SCHEDULE_INTERVAL_DAILY_TIME = 24;
    const SCHEDULE_INTERVAL_WEEKLY_TIME = 168;
    const SCHEDULE_INTERVAL_MONTHLY_TIME = 5040;

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
                $feedProduct = new FeedProduct($product);

                if ($feedProduct->process()) {
                    $inventory[] = $feedProduct->toArray();
                }

            } else { //product with variables
                foreach ($combinations as $combId => $combination) {
                    $feedProduct = new FeedProduct($product, $combId, $combination);

                    if ($feedProduct->process()) {
                        $inventory[] = $feedProduct->toArray();
                    }
                }
            }
        }

        $this->data = $inventory;
    }

    public function toArray()
    {
        if (empty($this->data)) {
            $this->process();
        }

        $lang = $this->context->language->locale;
        $version = $this->getFeedVersion();

        return [
            '$schema'            => "https://raw.githubusercontent.com/urbitassociates/urbit-merchant-feeds/master/schemas/products/{$version}/products.json",
            'content_language'   => $lang,
            'attribute_language' => $lang,
            'content_type'       => 'products',
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
        $cacheDuration = Configuration::get('URBIT_PRODUCTFEED_CACHE_DURATION', null);

        if (!$cacheDuration) {
            return static::SCHEDULE_INTERVAL_HOURLY;
        }

        foreach ([
            self::SCHEDULE_INTERVAL_HOURLY_TIME  => self::SCHEDULE_INTERVAL_HOURLY,
            self::SCHEDULE_INTERVAL_DAILY_TIME   => self::SCHEDULE_INTERVAL_DAILY,
            self::SCHEDULE_INTERVAL_WEEKLY_TIME  => self::SCHEDULE_INTERVAL_WEEKLY,
            self::SCHEDULE_INTERVAL_MONTHLY_TIME => self::SCHEDULE_INTERVAL_MONTHLY,
        ] as $time => $val) {
            if ($cacheDuration <= $time) {
                return $val;
            }
        }

        return static::SCHEDULE_INTERVAL_HOURLY;
    }

    public function getFeedVersion()
    {
        return static::FEED_VERSION;
    }
}
